<?php
// Opens an uploaded image and fixes sideways phone photos
function loadUploadedImage($path, $mime)
{
    $img = false;

    if ($mime === "image/jpeg") {
        $img = @imagecreatefromjpeg($path);
    } elseif ($mime === "image/png") {
        $img = @imagecreatefrompng($path);
    } elseif ($mime === "image/webp" && function_exists("imagecreatefromwebp")) {
        $img = @imagecreatefromwebp($path);
    }

    if (!$img) {
        return false;
    }

    // Phones save photos sideways with an "orientation" tag; rotate them upright
    if ($mime === "image/jpeg" && function_exists("exif_read_data")) {
        $exif = @exif_read_data($path);
        $orientation = $exif["Orientation"] ?? 1;
        $angles = [3 => 180, 6 => -90, 8 => 90];

        if (isset($angles[$orientation])) {
            $rotated = imagerotate($img, $angles[$orientation], 0);
            if ($rotated) {
                $img = $rotated;
            }
        }
    }

    return $img;
}

function resizeImage($img, $maxSize)
{
    $width  = imagesx($img);
    $height = imagesy($img);
    $scale  = min(1, $maxSize / max($width, $height));

    $newWidth  = max(1, (int) round($width * $scale));
    $newHeight = max(1, (int) round($height * $scale));

    $output = imagecreatetruecolor($newWidth, $newHeight);

    if (function_exists("imagewebp")) {
        imagealphablending($output, false);
        imagesavealpha($output, true);
        imagefill($output, 0, 0, imagecolorallocatealpha($output, 0, 0, 0, 127));
    } else {
        // JPEG has no transparency, adding white bg
        imagefill($output, 0, 0, imagecolorallocate($output, 255, 255, 255));
    }

    imagecopyresampled($output, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    return $output;
}

// Saves the image as WebP (smaller) or JPEG if WebP isn't available
function encodeImage($img)
{
    ob_start();

    if (function_exists("imagewebp")) {
        imagewebp($img, null, 80);
        $mime = "image/webp";
        $ext  = "webp";
    } else {
        imagejpeg($img, null, 82);
        $mime = "image/jpeg";
        $ext  = "jpg";
    }

    return ["data" => ob_get_clean(), "mime" => $mime, "ext" => $ext];
}

function makeImageVersions($path, $mime, array $sizes)
{
    $img = loadUploadedImage($path, $mime);
    if (!$img) {
        return false;
    }

    $versions = [];
    foreach ($sizes as $name => $maxSize) {
        $resized = resizeImage($img, $maxSize);
        $versions[$name] = encodeImage($resized);
        unset($resized);
    }

    unset($img);
    return $versions;
}
