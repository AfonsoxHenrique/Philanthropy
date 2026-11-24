<?php
require __DIR__ . '/include/auth.php';
require __DIR__ . '/../supabase.php';
require __DIR__ . '/include/supabaseAdmin.php';

$activePage = "gallery";
$bucket     = "site-images";

// Escape text for HTML output
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function fieldError($errors, $key)
{
    return isset($errors[$key]) ? '<div class="invalid-feedback d-block">' . e($errors[$key]) . '</div>' : "";
}

function invalidClass($errors, $key)
{
    return isset($errors[$key]) ? " is-invalid" : "";
}

// Older photos may use paths like "assets/images/x.jpg"; admin pages are one folder down
function adminImg($url)
{
    if ($url === "" || preg_match('#^(https?:)?//#', $url) || str_starts_with($url, "/")) {
        return $url;
    }
    return "../" . $url;
}

function bodyToText($body)
{
    if (is_string($body)) {
        $decoded = json_decode($body, true);
        $body = is_array($decoded) ? $decoded : [$body];
    }
    return is_array($body) ? implode("\n\n", $body) : "";
}

function textToBody($text)
{
    $paragraphs = preg_split('/\R\s*\R/', trim($text));
    return array_values(array_filter(array_map('trim', $paragraphs), fn($p) => $p !== ""));
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

//no id = adding a new one
$itemId = $_GET["id"] ?? "";
$isNew  = $itemId === "";

if (!$isNew && !preg_match('/^[A-Za-z0-9-]{1,64}$/', $itemId)) {
    header("Location: gallery.php");
    exit;
}

$item      = ["title" => "", "date_label" => "", "body" => [], "is_published" => false, "cover_photo_id" => null];
$photos    = [];
$loadError = "";

if (!$isNew) {
    $itemResult = supabaseAdminRequest("GET", "gallery_items", null, "?select=id,title,date_label,body,is_published,cover_photo_id&id=eq." . urlencode($itemId));

    if ($itemResult["code"] !== 200) {
        $loadError = "This milestone couldn't be loaded (error " . $itemResult["code"] . "). Refresh to try again.";
    } elseif (empty($itemResult["data"])) {
        header("Location: gallery.php?msg=missing");
        exit;
    } else {
        $item = $itemResult["data"][0];

        $photoResult = supabaseAdminRequest("GET", "gallery_photos", null, "?select=*&item_id=eq." . urlencode($itemId) . "&order=sort_order.asc,id.asc");
        if ($photoResult["code"] === 200 && is_array($photoResult["data"])) {
            $photos = $photoResult["data"];
        } else {
            $loadError = "The photos couldn't be loaded (error " . $photoResult["code"] . "). Refresh to try again.";
        }
    }
}

$form = [
    "title"        => $item["title"] ?? "",
    "date_label"   => $item["date_label"] ?? "",
    "body"         => bodyToText($item["body"] ?? []),
    "is_published" => !empty($item["is_published"])
];

$errors      = [];
$photoErrors = [];
$saveError   = "";
$photoInput  = [];

//Handle form submissions 
if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST" && $loadError === "") {
    $formType = $_POST["form"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
        $saveError = "The page expired. Refresh it and make your changes again.";
    } elseif ($formType === "details") {
        //Milestone details
        $form["title"]        = trim($_POST["title"] ?? "");
        $form["date_label"]   = trim($_POST["date_label"] ?? "");
        $form["body"]         = trim($_POST["body"] ?? "");
        $form["is_published"] = isset($_POST["is_published"]);

        if ($form["title"] === "") {
            $errors["title"] = "Enter a title.";
        } elseif (mb_strlen($form["title"]) > 80) {
            $errors["title"] = "Keep the title under 80 characters.";
        }
        if ($form["date_label"] === "") {
            $errors["date_label"] = "Enter when it happened, like March 2026.";
        } elseif (mb_strlen($form["date_label"]) > 40) {
            $errors["date_label"] = "Keep this under 40 characters.";
        }
        if (mb_strlen($form["body"]) > 1500) {
            $errors["body"] = "Keep the description under 1,500 characters. It's " . mb_strlen($form["body"]) . " now.";
        }

        if (empty($errors)) {
            $data = [
                "title"        => $form["title"],
                "date_label"   => $form["date_label"],
                "body"         => textToBody($form["body"]),
                "is_published" => $form["is_published"]
            ];

            if ($isNew) {
                // New milestones go to the end of the gallery
                $last = supabaseAdminRequest("GET", "gallery_items", null, "?select=sort_order&order=sort_order.desc&limit=1");
                $data["sort_order"] = (int) ($last["data"][0]["sort_order"] ?? 0) + 10;

                $result = supabaseAdminRequest("POST", "gallery_items", $data);
                if ($result["code"] === 201 && !empty($result["data"][0]["id"])) {
                    header("Location: galleryEdit.php?id=" . urlencode($result["data"][0]["id"]) . "&msg=created");
                    exit;
                }
            } else {
                $result = supabaseAdminRequest("PATCH", "gallery_items", $data, "?id=eq." . urlencode($itemId));
                if ($result["code"] === 200 && !empty($result["data"])) {
                    header("Location: galleryEdit.php?id=" . urlencode($itemId) . "&msg=details");
                    exit;
                }
            }

            $saveError = $result["code"] === 200 || $result["code"] === 201
                ? "Supabase didn't save the changes. Check your account has the admin role, then log out and back in."
                : "The details couldn't be saved (error " . $result["code"] . "). Try again. Your changes are still in the form.";
        } else {
            $saveError = "Fix the highlighted " . (count($errors) === 1 ? "field" : "fields") . ", then save again.";
        }
    } elseif ($formType === "photos" && !$isNew) {
        //Photo details, order, cover and delete
        $posted = $_POST["photos"] ?? [];
        $rows   = [];

        foreach ($photos as $index => $photo) {
            $id    = (string) $photo["id"];
            $input = is_array($posted[$id] ?? null) ? $posted[$id] : [];

            $alt     = trim($input["alt"] ?? $photo["alt_text"]);
            $caption = trim($input["caption"] ?? $photo["caption"]);
            $order   = (int) ($input["order"] ?? $index);

            $photoInput[$id] = ["alt" => $alt, "caption" => $caption];

            if (mb_strlen($alt) > 150) {
                $photoErrors[$id]["alt"] = "Keep the description under 150 characters.";
            }
            if (mb_strlen($caption) > 200) {
                $photoErrors[$id]["caption"] = "Keep the caption under 200 characters.";
            }

            // Every row needs the same columns for the bulk save
            $rows[] = [
                "id"           => $photo["id"],
                "item_id"      => $photo["item_id"],
                "image_url"    => $photo["image_url"],
                "thumb_url"    => $photo["thumb_url"],
                "storage_path" => $photo["storage_path"],
                "thumb_path"   => $photo["thumb_path"],
                "alt_text"     => $alt,
                "caption"      => $caption,
                "sort_order"   => $order
            ];
        }

        if (!empty($photoErrors)) {
            $saveError = "Fix the highlighted photo " . (count($photoErrors) === 1 ? "field" : "fields") . ", then save again.";
        } else {
            // Re-number in the order shown on screen: 10, 20, 30...
            usort($rows, fn($a, $b) => $a["sort_order"] <=> $b["sort_order"]);
            foreach ($rows as $i => &$row) {
                $row["sort_order"] = ($i + 1) * 10;
            }
            unset($row);

            $msg = "photos";
            $ok  = true;

            if (!empty($rows)) {
                // Saves every photo in one request 
                $result = supabaseAdminRequest("POST", "gallery_photos", $rows, "?on_conflict=id", true, "resolution=merge-duplicates,return=representation");
                $ok = in_array($result["code"], [200, 201], true) && !empty($result["data"]);
                if (!$ok) {
                    $saveError = "The photo changes couldn't be saved (error " . $result["code"] . "). Try again.";
                }
            }

            $photoIds    = array_map(fn($p) => (string) $p["id"], $photos);
            $deleteId    = (string) ($_POST["delete_photo"] ?? "");
            $coverId     = (string) ($_POST["set_cover"] ?? "");

            // Delete one photo
            if ($ok && $deleteId !== "" && in_array($deleteId, $photoIds, true)) {
                $toDelete = $photos[array_search($deleteId, $photoIds, true)];
                $result = supabaseAdminRequest("DELETE", "gallery_photos", null, "?id=eq." . urlencode($deleteId));

                if ($result["code"] === 200 && !empty($result["data"])) {
                    supabaseStorageDelete($bucket, [$toDelete["storage_path"] ?? "", $toDelete["thumb_path"] ?? ""]);

                    //  Use the first remaining photo instead if deleted
                    if ((string) $item["cover_photo_id"] === $deleteId) {
                        $remaining = array_values(array_filter($rows, fn($r) => (string) $r["id"] !== $deleteId));
                        supabaseAdminRequest("PATCH", "gallery_items", ["cover_photo_id" => $remaining[0]["id"] ?? null], "?id=eq." . urlencode($itemId));
                    }
                    $msg = "deleted";
                } else {
                    $ok = false;
                    $saveError = "The photo couldn't be deleted (error " . $result["code"] . "). Try again.";
                }
            }

            // Set the cover photo
            if ($ok && $coverId !== "" && in_array($coverId, $photoIds, true)) {
                $result = supabaseAdminRequest("PATCH", "gallery_items", ["cover_photo_id" => $coverId], "?id=eq." . urlencode($itemId));
                if ($result["code"] === 200 && !empty($result["data"])) {
                    $msg = "cover";
                } else {
                    $ok = false;
                    $saveError = "The cover photo couldn't be changed (error " . $result["code"] . "). Try again.";
                }
            }

            if ($ok) {
                header("Location: galleryEdit.php?id=" . urlencode($itemId) . "&msg=" . $msg . "#photos");
                exit;
            }
        }
    }
}

$messages = [
    "created" => "Milestone created. Now add some photos.",
    "details" => "Details saved.",
    "photos"  => "Photo changes saved.",
    "deleted" => "Photo deleted.",
    "cover"   => "Cover photo updated.",
    "uploaded" => (int) ($_GET["count"] ?? 0) === 1 ? "1 photo uploaded." : (int) ($_GET["count"] ?? 0) . " photos uploaded."
];
$successMessage = $saveError === "" ? ($messages[$_GET["msg"] ?? ""] ?? "") : "";

$coverPhotoId = (string) ($item["cover_photo_id"] ?? "");
$missingAlt = count(array_filter($photos, fn($p) => trim($p["alt_text"] ?? "") === ""));
$pageTitle  = $isNew ? "Add Milestone" : "Edit Milestone";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?> | Phill Anthropy Console</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../style/admin.css" rel="stylesheet">
</head>

<body class="admin-body">
    <?php include __DIR__ . '/include/header.php' ?>

    <div class="admin-wrapper">
        <?php include __DIR__ . '/include/sidebar.php' ?>

        <main class="admin-main">

            <a href="gallery.php" class="back-link d-inline-block mb-3"><i class="bi bi-arrow-left"></i> All milestones</a>

            <!-- Page Heading -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="admin-title"><?php echo $pageTitle; ?></h1>
                    <p class="admin-subtitle mb-0">
                        <?php echo $isNew
                            ? "Add the milestone details first. You can upload photos once it's created."
                            : "Edit this milestone's details and photos."; ?>
                    </p>
                </div>
                <?php if (!$isNew): ?>
                    <span class="status-badge <?php echo $form["is_published"] ? "status-completed" : "status-default"; ?>">
                        <?php echo $form["is_published"] ? "Published" : "Hidden"; ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Messages -->
            <?php if ($successMessage !== ""): ?>
                <div class="alert form-alert-success d-flex gap-2 align-items-start" role="status">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?php echo e($successMessage); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($loadError !== "" || $saveError !== ""): ?>
                <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                    <i class="bi bi-exclamation-circle"></i>
                    <div><?php echo e($loadError !== "" ? $loadError : $saveError); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($loadError === ""): ?>

                <!-- Details -->
                <form method="POST" action="galleryEdit.php<?php echo $isNew ? "" : "?id=" . urlencode($itemId); ?>" id="detailsForm" class="track-changes" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION["csrf_token"]); ?>">
                    <input type="hidden" name="form" value="details">

                    <div class="card panel-card mb-4">
                        <div class="card-body">
                            <h2 class="panel-title mb-4">Details</h2>

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="title" class="form-label admin-label">Title</label>
                                    <input type="text" id="title" name="title"
                                        class="form-control admin-input<?php echo invalidClass($errors, "title"); ?>"
                                        value="<?php echo e($form["title"]); ?>" maxlength="80" required>
                                    <?php echo fieldError($errors, "title"); ?>
                                </div>

                                <div class="col-md-4">
                                    <label for="date_label" class="form-label admin-label">When</label>
                                    <input type="text" id="date_label" name="date_label"
                                        class="form-control admin-input<?php echo invalidClass($errors, "date_label"); ?>"
                                        value="<?php echo e($form["date_label"]); ?>" maxlength="40" placeholder="March 2026" required>
                                    <?php echo fieldError($errors, "date_label"); ?>
                                </div>

                                <div class="col-12">
                                    <label for="body" class="form-label admin-label">Description <span class="optional">(optional)</span></label>
                                    <textarea id="body" name="body" rows="5"
                                        class="form-control admin-input<?php echo invalidClass($errors, "body"); ?>"
                                        maxlength="1500"><?php echo e($form["body"]); ?></textarea>
                                    <small class="field-hint">Leave a blank line between paragraphs.</small>
                                    <?php echo fieldError($errors, "body"); ?>
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is_published" name="is_published"
                                            <?php echo $form["is_published"] ? "checked" : ""; ?>>
                                        <label class="form-check-label" for="is_published">Show on the website</label>
                                    </div>
                                    <small class="field-hint">Milestones without any photos stay hidden on the website even when this is on.</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-save">
                                    <i class="bi bi-check-lg"></i> <?php echo $isNew ? "Create Milestone" : "Save Details"; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <?php if (!$isNew): ?>
                    <!-- Photos -->
                    <div class="card panel-card mb-4" id="photos">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-1">
                                <h2 class="panel-title mb-0">Photos <span class="photo-total">(<?php echo count($photos); ?>)</span></h2>
                                <?php if ($missingAlt > 0): ?>
                                    <span class="stat-tag tag-orange">
                                        <?php echo $missingAlt; ?> <?php echo $missingAlt === 1 ? "photo needs" : "photos need"; ?> a description
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="panel-hint mb-4">Drag photos to reorder them. The cover photo is shown on the gallery card.</p>

                            <!-- Upload -->
                            <div class="upload-box mb-4">
                                <label for="photoFiles" class="form-label admin-label">Add photos</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <input type="file" id="photoFiles" class="form-control admin-input flex-grow-1 w-auto"
                                        accept="image/jpeg,image/png,image/webp" multiple>
                                    <button type="button" class="btn btn-save" id="uploadButton">
                                        <i class="bi bi-upload"></i> Upload
                                    </button>
                                </div>
                                <small class="field-hint">JPG, PNG or WebP, up to 15 MB each. Choose as many as you like. Photos are resized automatically.</small>

                                <div id="uploadStatus" class="upload-status mt-3 d-none" aria-live="polite">
                                    <div class="progress upload-progress mb-2" role="progressbar" aria-label="Upload progress" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" id="uploadBar" style="width: 0%"></div>
                                    </div>
                                    <p class="mb-1 upload-message" id="uploadMessage"></p>
                                    <ul class="upload-errors mb-0" id="uploadErrors"></ul>
                                    <a href="galleryEdit.php?id=<?php echo urlencode($itemId); ?>#photos" class="btn btn-export mt-2 d-none" id="uploadDone">Show uploaded photos</a>
                                </div>
                            </div>

                            <?php if (empty($photos)): ?>
                                <div class="empty-photos text-center">
                                    <i class="bi bi-images"></i>
                                    <p class="mb-0">No photos yet. Upload some above.</p>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="galleryEdit.php?id=<?php echo urlencode($itemId); ?>" id="photosForm" class="track-changes" novalidate>
                                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION["csrf_token"]); ?>">
                                    <input type="hidden" name="form" value="photos">

                                    <div class="row g-3" id="photoGrid">
                                        <?php foreach ($photos as $index => $photo): ?>
                                            <?php
                                            $id       = (string) $photo["id"];
                                            $isCover  = $coverPhotoId === $id;
                                            $alt      = $photoInput[$id]["alt"] ?? $photo["alt_text"];
                                            $caption  = $photoInput[$id]["caption"] ?? $photo["caption"];
                                            $errs     = $photoErrors[$id] ?? [];
                                            ?>
                                            <div class="col-sm-6 col-lg-4 col-xxl-3 photo-col">
                                                <div class="photo-tile h-100">
                                                    <div class="photo-thumb">
                                                        <img src="<?php echo e(adminImg($photo["thumb_url"])); ?>" class="photo-img" alt="" loading="lazy">
                                                        <span class="photo-drag" title="Drag to reorder" aria-hidden="true"><i class="bi bi-grip-vertical"></i></span>
                                                        <?php if ($isCover): ?>
                                                            <span class="cover-badge"><i class="bi bi-star-fill"></i> Cover</span>
                                                        <?php endif; ?>
                                                        <?php if (trim($photo["alt_text"] ?? "") === ""): ?>
                                                            <span class="alt-missing">Needs description</span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="p-3">
                                                        <input type="hidden" name="photos[<?php echo e($id); ?>][order]" value="<?php echo $index; ?>" class="photo-order">

                                                        <label for="alt<?php echo e($id); ?>" class="form-label admin-label">Description</label>
                                                        <input type="text" id="alt<?php echo e($id); ?>" name="photos[<?php echo e($id); ?>][alt]"
                                                            class="form-control admin-input form-control-sm<?php echo isset($errs["alt"]) ? " is-invalid" : ""; ?>"
                                                            value="<?php echo e($alt); ?>" maxlength="150" placeholder="What's in the photo?">
                                                        <?php if (isset($errs["alt"])): ?><div class="invalid-feedback d-block"><?php echo e($errs["alt"]); ?></div><?php endif; ?>

                                                        <label for="caption<?php echo e($id); ?>" class="form-label admin-label mt-2">Caption <span class="optional">(optional)</span></label>
                                                        <input type="text" id="caption<?php echo e($id); ?>" name="photos[<?php echo e($id); ?>][caption]"
                                                            class="form-control admin-input form-control-sm<?php echo isset($errs["caption"]) ? " is-invalid" : ""; ?>"
                                                            value="<?php echo e($caption); ?>" maxlength="200" placeholder="Shown under the photo">
                                                        <?php if (isset($errs["caption"])): ?><div class="invalid-feedback d-block"><?php echo e($errs["caption"]); ?></div><?php endif; ?>

                                                        <div class="d-flex flex-wrap gap-1 mt-3">
                                                            <button type="button" class="btn btn-tile move-left" aria-label="Move photo earlier"><i class="bi bi-arrow-left"></i></button>
                                                            <button type="button" class="btn btn-tile move-right" aria-label="Move photo later"><i class="bi bi-arrow-right"></i></button>
                                                            <?php if (!$isCover): ?>
                                                                <button type="submit" name="set_cover" value="<?php echo e($id); ?>" class="btn btn-tile">
                                                                    <i class="bi bi-star"></i> Make cover
                                                                </button>
                                                            <?php endif; ?>
                                                            <button type="submit" name="delete_photo" value="<?php echo e($id); ?>" class="btn btn-tile btn-tile-danger ms-auto confirm-delete" aria-label="Delete photo">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Save bar -->
                                    <div class="save-bar d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                                        <small class="save-status" id="saveStatus">No unsaved photo changes</small>
                                        <div class="d-flex gap-2">
                                            <a href="galleryEdit.php?id=<?php echo urlencode($itemId); ?>#photos" class="btn btn-cancel">Discard Changes</a>
                                            <button type="submit" class="btn btn-save">
                                                <i class="bi bi-check-lg"></i> Save Photo Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </main>
    </div>

    <?php include __DIR__ . '/include/footer.php' ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        // ---------- Unsaved changes warning ----------
        let hasChanges = <?php echo $saveError !== "" ? "true" : "false"; ?>;
        let submitting = false;
        const saveStatus = document.getElementById("saveStatus");

        function markChanged(form) {
            hasChanges = true;
            // The save bar belongs to the photos form, so only update it for photo changes
            if (saveStatus && (!form || form.id === "photosForm")) {
                saveStatus.textContent = "You have unsaved photo changes";
                saveStatus.classList.add("unsaved");
            }
        }

        document.querySelectorAll(".track-changes").forEach(function(form) {
            form.addEventListener("input", function() {
                markChanged(form);
            });
            form.addEventListener("submit", function() {
                submitting = true;
            });
        });

        window.addEventListener("beforeunload", function(event) {
            if (hasChanges && !submitting) {
                event.preventDefault();
                event.returnValue = "";
            }
        });

        // ---------- Photo order ----------
        const photoGrid = document.getElementById("photoGrid");
        const photosForm = document.getElementById("photosForm");

        if (photoGrid) {
            // Drag to reorder (if the SortableJS library loaded; the arrow buttons work either way)
            if (window.Sortable) {
                Sortable.create(photoGrid, {
                    handle: ".photo-thumb",
                    animation: 150,
                    ghostClass: "photo-ghost",
                    onEnd: function() {
                        markChanged(photosForm);
                    }
                });
            }

            // Arrow buttons (for keyboard users and touch screens)
            photoGrid.addEventListener("click", function(event) {
                const button = event.target.closest(".move-left, .move-right");
                if (!button) return;

                const col = button.closest(".photo-col");
                if (button.classList.contains("move-left") && col.previousElementSibling) {
                    photoGrid.insertBefore(col, col.previousElementSibling);
                } else if (button.classList.contains("move-right") && col.nextElementSibling) {
                    photoGrid.insertBefore(col.nextElementSibling, col);
                } else {
                    return;
                }
                button.focus();
                markChanged();
            });

            // Save the on-screen order into the hidden fields
            photosForm.addEventListener("submit", function(event) {
                if (event.submitter && event.submitter.classList.contains("confirm-delete")) {
                    if (!confirm("Delete this photo? This can't be undone.")) {
                        event.preventDefault();
                        submitting = false;
                        return;
                    }
                }
                photoGrid.querySelectorAll(".photo-order").forEach(function(input, index) {
                    input.value = index;
                });
            });
        }

        const uploadButton = document.getElementById("uploadButton");

        if (uploadButton) {
            const fileInput = document.getElementById("photoFiles");
            const statusBox = document.getElementById("uploadStatus");
            const bar = document.getElementById("uploadBar");
            const message = document.getElementById("uploadMessage");
            const errorList = document.getElementById("uploadErrors");
            const doneLink = document.getElementById("uploadDone");
            const maxBytes = 15 * 1024 * 1024;
            const allowed = ["image/jpeg", "image/png", "image/webp"];

            function addError(fileName, text) {
                const li = document.createElement("li");
                li.textContent = fileName + ": " + text;
                errorList.appendChild(li);
            }

            uploadButton.addEventListener("click", async function() {
                const files = Array.from(fileInput.files);

                if (files.length === 0) {
                    fileInput.focus();
                    statusBox.classList.remove("d-none");
                    message.textContent = "Choose one or more photos first.";
                    return;
                }

                if (hasChanges && !confirm("You have unsaved changes. Uploading will reload the page and lose them. Continue?")) {
                    return;
                }

                uploadButton.disabled = true;
                fileInput.disabled = true;
                statusBox.classList.remove("d-none");
                errorList.innerHTML = "";
                doneLink.classList.add("d-none");

                let uploaded = 0;

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    message.textContent = "Uploading " + (i + 1) + " of " + files.length + ": " + file.name;
                    bar.style.width = Math.round((i / files.length) * 100) + "%";

                    // Quick checks before sending
                    if (!allowed.includes(file.type)) {
                        addError(file.name, "Choose a JPG, PNG or WebP image.");
                        continue;
                    }
                    if (file.size > maxBytes) {
                        addError(file.name, "Choose a photo under 15 MB.");
                        continue;
                    }

                    const formData = new FormData();
                    formData.append("photo", file);
                    formData.append("item_id", <?php echo json_encode($itemId); ?>);
                    formData.append("csrf_token", <?php echo json_encode($_SESSION["csrf_token"]); ?>);

                    try {
                        const response = await fetch("galleryUpload.php", {
                            method: "POST",
                            body: formData,
                            credentials: "same-origin"
                        });
                        let data;
                        try {
                            data = await response.json();
                        } catch (parseError) {
                            data = {
                                ok: false,
                                error: "Your login may have expired. Log in again and retry."
                            };
                        }

                        if (data.ok) {
                            uploaded++;
                        } else {
                            addError(file.name, data.error || "Upload failed. Try again.");
                        }
                    } catch (networkError) {
                        addError(file.name, "The upload was interrupted. Check your connection and try again.");
                    }
                }

                bar.style.width = "100%";
                const failed = files.length - uploaded;

                if (failed === 0) {
                    message.textContent = "All photos uploaded. Loading them now...";
                    submitting = true;
                    window.location.href = "galleryEdit.php?id=" + encodeURIComponent(<?php echo json_encode($itemId); ?>) + "&msg=uploaded&count=" + uploaded + "#photos";
                    return;
                }

                message.textContent = uploaded + " of " + files.length + " photos uploaded. These didn't:";
                uploadButton.disabled = false;
                fileInput.disabled = false;
                fileInput.value = "";
                if (uploaded > 0) {
                    doneLink.classList.remove("d-none");
                }
            });
        }
    </script>

</body>

</html>