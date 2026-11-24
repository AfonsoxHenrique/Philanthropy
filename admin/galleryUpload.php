<?php
require __DIR__ . '/include/auth.php';
require __DIR__ . '/../supabase.php';
require __DIR__ . '/include/supabaseAdmin.php';
require __DIR__ . '/include/image-tools.php';

header("Content-Type: application/json; charset=utf-8");

function respond($ok, $error = "", $extra = [], $status = 200)
{
    http_response_code($status);
    echo json_encode(array_merge(["ok" => $ok, "error" => $error], $extra));
    exit;
}

$bucket         = "site-images";
$maxUploadBytes = 15 * 1024 * 1024;   // 15 MB before resizing
$maxPixels      = 50000000;           // 50 megapixels
$allowedTypes   = ["image/jpeg", "image/png", "image/webp"];

if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
    respond(false, "Upload photos from the gallery editor.", [], 405);
}

// Bigger than post_max_size: PHP throws the whole request away
if (empty($_POST) && (int) ($_SERVER["CONTENT_LENGTH"] ?? 0) > 0) {
    respond(false, "This photo is too large for the server. Choose one under 15 MB.", [], 413);
}

if (!hash_equals($_SESSION["csrf_token"] ?? "", $_POST["csrf_token"] ?? "")) {
    respond(false, "The page expired. Refresh it and upload again.", [], 403);
}

$itemId = $_POST["item_id"] ?? "";
if (!preg_match('/^[A-Za-z0-9-]{1,64}$/', $itemId)) {
    respond(false, "This milestone couldn't be found.", [], 400);
}

if (!function_exists("imagecreatetruecolor")) {
    respond(false, "Photo resizing isn't available. Enable extension=gd in php.ini and restart Apache.", [], 500);
}

$file        = $_FILES["photo"] ?? null;
$uploadError = $file["error"] ?? UPLOAD_ERR_NO_FILE;

if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
    respond(false, "This photo is larger than the server allows (" . ini_get("upload_max_filesize") . "). Choose a smaller one.", [], 413);
}
if ($uploadError !== UPLOAD_ERR_OK) {
    respond(false, "The photo didn't upload properly. Try again.", [], 400);
}
if ($file["size"] > $maxUploadBytes) {
    respond(false, "Choose a photo under 15 MB. This one is " . round($file["size"] / 1048576, 1) . " MB.", [], 413);
}

// Check the real file type, not just the name
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file["tmp_name"]);
$size = @getimagesize($file["tmp_name"]);

if (!in_array($mime, $allowedTypes, true) || $size === false) {
    respond(false, "Choose a JPG, PNG or WebP image.", [], 415);
}
if ($size[0] * $size[1] > $maxPixels) {
    respond(false, "This photo's resolution is too high. Choose one under 50 megapixels.", [], 413);
}

//Check the milestone exists
$itemResult = supabaseAdminRequest("GET", "gallery_items", null, "?select=id,cover_photo_id&id=eq." . urlencode($itemId));

if ($itemResult["code"] !== 200 || empty($itemResult["data"])) {
    respond(false, "This milestone couldn't be found. It may have been deleted.", [], 404);
}
$item = $itemResult["data"][0];

//Resize
ini_set("memory_limit", "512M");
set_time_limit(120);

$versions = makeImageVersions($file["tmp_name"], $mime, ["full" => 1600, "thumb" => 800]);
if (!$versions) {
    respond(false, "This image couldn't be read. Try saving it as a JPG and uploading again.", [], 422);
}

//Upload both sizes to Supabase Storage
$baseName = "gallery/" . $itemId . "/" . date("Ymd-His") . "-" . bin2hex(random_bytes(4));
$uploaded = [];

foreach ($versions as $name => $version) {
    $path = $baseName . ($name === "thumb" ? "-thumb" : "") . "." . $version["ext"];

    $tempFile = tempnam(sys_get_temp_dir(), "img");
    file_put_contents($tempFile, $version["data"]);
    $upload = supabaseStorageUpload($bucket, $path, $tempFile, $version["mime"]);
    unlink($tempFile);

    if ($upload["code"] !== 200) {
        // Remove any size that already uploaded so storage doesn't fill with half-saved photos
        supabaseStorageDelete($bucket, array_column($uploaded, "path"));
        respond(false, "The photo couldn't be saved to Supabase Storage (error " . $upload["code"] . ").", [], 502);
    }

    $uploaded[$name] = ["path" => $path, "url" => $upload["url"]];
}

// New photos go to the end of the milestone
$last = supabaseAdminRequest("GET", "gallery_photos", null, "?select=sort_order&item_id=eq." . urlencode($itemId) . "&order=sort_order.desc&limit=1");
$nextOrder = (int) ($last["data"][0]["sort_order"] ?? 0) + 10;

$insert = supabaseAdminRequest("POST", "gallery_photos", [
    "item_id"      => $itemId,
    "image_url"    => $uploaded["full"]["url"],
    "thumb_url"    => $uploaded["thumb"]["url"],
    "storage_path" => $uploaded["full"]["path"],
    "thumb_path"   => $uploaded["thumb"]["path"],
    "alt_text"     => "",
    "caption"      => "",
    "sort_order"   => $nextOrder
]);

if ($insert["code"] !== 201 || empty($insert["data"][0]["id"])) {
    supabaseStorageDelete($bucket, array_column($uploaded, "path"));
    respond(false, "The photo uploaded but couldn't be added to the milestone (error " . $insert["code"] . ").", [], 502);
}

$photo = $insert["data"][0];

// First photo in a milestone becomes its cover
if (empty($item["cover_photo_id"])) {
    supabaseAdminRequest("PATCH", "gallery_items", ["cover_photo_id" => $photo["id"]], "?id=eq." . urlencode($itemId));
}

respond(true, "", ["photo" => ["id" => $photo["id"], "thumb_url" => $photo["thumb_url"]]]);
