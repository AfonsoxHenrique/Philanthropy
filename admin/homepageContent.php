<?php
require __DIR__ . '/include/auth.php';
require __DIR__ . '/../supabase.php';
require __DIR__ . '/include/supabaseAdmin.php';

$activePage = "homepage";

// Escape text for HTML output
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Shows a field's error message under the input
function fieldError($errors, $key)
{
    return isset($errors[$key]) ? '<div class="invalid-feedback d-block">' . e($errors[$key]) . '</div>' : "";
}

// Adds Bootstrap's red border to a field with an error
function invalidClass($errors, $key)
{
    return isset($errors[$key]) ? " is-invalid" : "";
}

// Icons the admin can pick for each program card
$iconOptions = [
    "bi-book"        => "Book",
    "bi-pc-display"  => "Computer",
    "bi-wifi"        => "Wi-Fi",
    "bi-backpack"    => "Backpack",
    "bi-pencil"      => "Pencil",
    "bi-basket"      => "Food basket",
    "bi-palette"     => "Art",
    "bi-trophy"      => "Sports",
    "bi-heart"       => "Heart",
    "bi-people"      => "People"
];

// Hero image upload settings
$imageBucket   = "site-images";
$maxImageBytes = 5 * 1024 * 1024; // 5 MB
$allowedImages = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp"
];
$defaultHeroImage = "../assets/images/index_background.jpg";

// Text fields: key => [label, max length, required, hint]
$textFields = [
    "hero_title"    => ["Hero heading",       80,  true,  "The big heading at the top of the homepage."],
    "hero_subtitle" => ["Hero intro paragraph", 300, true, "A short paragraph under the heading."],
    "hero_image_alt" => ["Image description", 125, true,  "Describe the photo for screen readers, e.g. \"Students holding new backpacks outside their school\"."],
    "mission_line"  => ["Mission statement",  200, true,  "A single sentence describing what the trust does."],
    "promise_title" => ["Promise heading",    100, true,  "Shown above the \"See how donations are used\" link."],
    "quote"         => ["Featured quote",     250, false, "An approved quote from a parent or volunteer. Leave empty to hide the quote box. Don't add quote marks."],
    "cta_title"     => ["Call to action heading", 100, true, "The heading in the green section above the footer."],
    "cta_text"      => ["Call to action text",    150, true, "One short line under that heading."]
];

// Load current content 
$loadResult = supabaseRequest("GET", "homepage_content", null, "?select=*&id=eq.1");

$content   = null;
$loadError = "";
if ($loadResult["code"] === 200 && is_array($loadResult["data"]) && !empty($loadResult["data"])) {
    $content = $loadResult["data"][0];
} else {
    $loadError = "The homepage content couldn't be loaded (error " . $loadResult["code"] . "). Check that the homepage_content table exists and has a row with id 1.";
}

// Form values start as what's saved in Supabase
$form = [];
foreach ($textFields as $key => $field) {
    $form[$key] = $content[$key] ?? "";
}
$form["programs"] = is_array($content["programs"] ?? null) ? $content["programs"] : [];
$form["impact"]   = is_array($content["impact"] ?? null) ? $content["impact"] : [];

// Always show 3 program slots and 3 impact slots
for ($i = 0; $i < 3; $i++) {
    $form["programs"][$i] = array_merge(["icon" => "bi-book", "title" => "", "meta" => "", "text" => ""], $form["programs"][$i] ?? []);
    $form["impact"][$i]   = array_merge(["number" => "", "label" => ""], $form["impact"][$i] ?? []);
}

// CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$errors    = [];
$saveError = "";
$saved     = isset($_GET["saved"]);

// Save changes 
if ($_SERVER["REQUEST_METHOD"] === "POST" && $content !== null) {
    // If the upload is bigger than PHP's post_max_size, PHP throws away the whole form
    if (empty($_POST) && (int) ($_SERVER["CONTENT_LENGTH"] ?? 0) > 0) {
        $saveError = "That image is too large for the server to accept. Choose an image under 5 MB.";
    } elseif (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
        $saveError = "Your form expired. Refresh the page and make your changes again.";
    } else {
        // Text fields
        foreach ($textFields as $key => [$label, $max, $required]) {
            $value = trim($_POST[$key] ?? "");
            $form[$key] = $value;

            if ($required && $value === "") {
                $errors[$key] = "Enter the " . strtolower($label) . ".";
            } elseif (mb_strlen($value) > $max) {
                $errors[$key] = "Keep this under " . $max . " characters. It's " . mb_strlen($value) . " now.";
            }
        }

        // Program cards
        for ($i = 0; $i < 3; $i++) {
            $program = [
                "icon"  => $_POST["programs"][$i]["icon"] ?? "",
                "title" => trim($_POST["programs"][$i]["title"] ?? ""),
                "meta"  => trim($_POST["programs"][$i]["meta"] ?? ""),
                "text"  => trim($_POST["programs"][$i]["text"] ?? "")
            ];
            $form["programs"][$i] = $program;

            if (!array_key_exists($program["icon"], $iconOptions)) {
                $errors["programs.$i.icon"] = "Choose an icon from the list.";
            }
            if ($program["title"] === "") {
                $errors["programs.$i.title"] = "Enter a program title.";
            } elseif (mb_strlen($program["title"]) > 60) {
                $errors["programs.$i.title"] = "Keep the title under 60 characters.";
            }
            if (mb_strlen($program["meta"]) > 80) {
                $errors["programs.$i.meta"] = "Keep this under 80 characters.";
            }
            if ($program["text"] === "") {
                $errors["programs.$i.text"] = "Enter a short description.";
            } elseif (mb_strlen($program["text"]) > 160) {
                $errors["programs.$i.text"] = "Keep the description under 160 characters.";
            }
        }

        // Impact numbers
        for ($i = 0; $i < 3; $i++) {
            $stat = [
                "number" => trim($_POST["impact"][$i]["number"] ?? ""),
                "label"  => trim($_POST["impact"][$i]["label"] ?? "")
            ];
            $form["impact"][$i] = $stat;

            if ($stat["number"] === "") {
                $errors["impact.$i.number"] = "Enter a number, like 300+.";
            } elseif (mb_strlen($stat["number"]) > 10) {
                $errors["impact.$i.number"] = "Keep this under 10 characters.";
            }
            if ($stat["label"] === "") {
                $errors["impact.$i.label"] = "Enter a label.";
            } elseif (mb_strlen($stat["label"]) > 40) {
                $errors["impact.$i.label"] = "Keep the label under 40 characters.";
            }
        }

        // Hero image (optional: only when a new file is chosen)
        $upload      = $_FILES["hero_image"] ?? null;
        $uploadError = $upload["error"] ?? UPLOAD_ERR_NO_FILE;
        $newImageUrl = "";
        $imageMime   = "";

        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            $errors["hero_image"] = "That image is larger than the server allows (" . ini_get("upload_max_filesize") . "). Choose a smaller image.";
        } elseif ($uploadError !== UPLOAD_ERR_OK && $uploadError !== UPLOAD_ERR_NO_FILE) {
            $errors["hero_image"] = "The image didn't upload properly. Try choosing it again.";
        } elseif ($uploadError === UPLOAD_ERR_OK) {
            // Check the real file type, not just the file name
            $imageMime = (new finfo(FILEINFO_MIME_TYPE))->file($upload["tmp_name"]);

            if ($upload["size"] > $maxImageBytes) {
                $errors["hero_image"] = "Choose an image under 5 MB. This one is " . round($upload["size"] / 1048576, 1) . " MB.";
            } elseif (!isset($allowedImages[$imageMime]) || getimagesize($upload["tmp_name"]) === false) {
                $errors["hero_image"] = "Choose a JPG, PNG or WebP image.";
            }
        }

        // Upload the image only once everything else is valid, so failed saves don't leave stray files
        if (empty($errors) && $uploadError === UPLOAD_ERR_OK) {
            // A new unique name each time, so browsers don't show the old cached image
            $fileName = "hero/hero-" . date("Ymd-His") . "-" . bin2hex(random_bytes(4)) . "." . $allowedImages[$imageMime];
            $uploadResult = supabaseStorageUpload($imageBucket, $fileName, $upload["tmp_name"], $imageMime);

            if ($uploadResult["code"] === 200) {
                $newImageUrl = $uploadResult["url"];
            } else {
                $errors["hero_image"] = "The image couldn't be uploaded to Supabase (error " . $uploadResult["code"] . "). Check the site-images bucket and its admin upload policy.";
            }
        }

        // Save to Supabase
        if (empty($errors)) {
            $data = [];
            foreach ($textFields as $key => $field) {
                $data[$key] = $form[$key];
            }
            if ($newImageUrl !== "") {
                $data["hero_image"] = $newImageUrl;
            }
            $data["programs"]        = $form["programs"];
            $data["impact"]          = $form["impact"];
            $data["updated_at"]      = date("c");
            $data["updated_by"]      = $_SESSION["admin_id"];
            $data["updated_by_name"] = $adminName;

            $result = supabaseAdminRequest("PATCH", "homepage_content", $data, "?id=eq.1");

            if ($result["code"] === 200 && is_array($result["data"]) && count($result["data"]) > 0) {
                // Redirect so refreshing doesn't resubmit the form
                header("Location: homepageContent.php?saved=1");
                exit;
            } elseif ($result["code"] === 200) {
                // Supabase said OK but changed no rows: RLS blocked the update
                $saveError = "Supabase didn't save the changes. Check that your account has the admin role and the table has the admin update policy.";
            } elseif ($result["code"] === 401) {
                $saveError = "Your login has expired. Log out, log back in, then try saving again.";
            } elseif ($result["code"] === 0) {
                $saveError = "Supabase couldn't be reached. Check your connection and try again. Your changes are still in the form.";
            } else {
                $saveError = "Your changes couldn't be saved (error " . $result["code"] . "). Try again, and your changes are still in the form.";
            }
        } else {
            $saveError = "Fix the " . (count($errors) === 1 ? "highlighted field" : count($errors) . " highlighted fields") . ", then save again.";
        }
    }
}

// "Last saved" info
$lastSaved = "";
if (!empty($content["updated_at"])) {
    $savedAt = new DateTime($content["updated_at"]);
    $savedAt->setTimezone(new DateTimeZone(date_default_timezone_get()));
    $lastSaved = "Last saved " . $savedAt->format("j M Y, g:i a");
    if (!empty($content["updated_by_name"])) {
        $lastSaved .= " by " . $content["updated_by_name"];
    }
}

// Image shown in the preview
$currentHeroImage = !empty($content["hero_image"]) ? $content["hero_image"] : $defaultHeroImage;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Homepage Content | Phill Anthropy Console</title>
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

            <!-- Page Heading -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="admin-title">Homepage Content</h1>
                    <p class="admin-subtitle mb-0">Edit the text visitors see on the homepage. Changes go live as soon as you save.</p>
                    <?php if ($lastSaved !== ""): ?>
                        <small class="last-saved d-block mt-1"><i class="bi bi-clock-history"></i> <?php echo e($lastSaved); ?></small>
                    <?php endif; ?>
                </div>
                <a href="../index.php" target="_blank" rel="noopener" class="btn btn-export">
                    <i class="bi bi-box-arrow-up-right"></i> View Homepage
                </a>
            </div>

            <!-- Saved message -->
            <?php if ($saved && $saveError === ""): ?>
                <div class="alert form-alert-success d-flex gap-2 align-items-start" role="status">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>Changes saved. The homepage now shows the new content.</div>
                </div>
            <?php endif; ?>

            <!-- Load / save errors -->
            <?php if ($loadError !== ""): ?>
                <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                    <i class="bi bi-exclamation-circle"></i>
                    <div><?php echo e($loadError); ?></div>
                </div>
            <?php elseif ($saveError !== ""): ?>
                <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                    <i class="bi bi-exclamation-circle"></i>
                    <div><?php echo e($saveError); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($content !== null): ?>
                <form method="POST" action="homepageContent.php" id="contentForm" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION["csrf_token"]); ?>">

                    <!-- Hero -->
                    <div class="card panel-card mb-4">
                        <div class="card-body">
                            <h2 class="panel-title mb-1">Hero</h2>
                            <p class="panel-hint mb-4">The first thing visitors see. The Donate Now button stays the same.</p>

                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <?php [$label, $max, $required, $hint] = $textFields["hero_title"]; ?>
                                    <div class="mb-3">
                                        <label for="hero_title" class="form-label admin-label"><?php echo e($label); ?></label>
                                        <input type="text" id="hero_title" name="hero_title"
                                            class="form-control admin-input<?php echo invalidClass($errors, "hero_title"); ?>"
                                            value="<?php echo e($form["hero_title"]); ?>" maxlength="<?php echo $max; ?>" required>
                                        <small class="field-hint"><?php echo e($hint); ?></small>
                                        <?php echo fieldError($errors, "hero_title"); ?>
                                    </div>

                                    <?php [$label, $max, $required, $hint] = $textFields["hero_subtitle"]; ?>
                                    <div>
                                        <label for="hero_subtitle" class="form-label admin-label"><?php echo e($label); ?></label>
                                        <textarea id="hero_subtitle" name="hero_subtitle" rows="4"
                                            class="form-control admin-input<?php echo invalidClass($errors, "hero_subtitle"); ?>"
                                            maxlength="<?php echo $max; ?>" required><?php echo e($form["hero_subtitle"]); ?></textarea>
                                        <small class="field-hint"><?php echo e($hint); ?></small>
                                        <?php echo fieldError($errors, "hero_subtitle"); ?>
                                    </div>
                                </div>

                                <div class="col-lg-5">
                                    <label for="hero_image" class="form-label admin-label">Hero image</label>
                                    <div class="image-preview mb-2">
                                        <img src="<?php echo e($currentHeroImage); ?>" id="heroPreview" alt="Current hero image">
                                        <span class="image-badge" id="heroBadge">Current image</span>
                                    </div>
                                    <input type="file" id="hero_image" name="hero_image"
                                        class="form-control admin-input<?php echo invalidClass($errors, "hero_image"); ?>"
                                        accept="image/jpeg,image/png,image/webp">
                                    <small class="field-hint">JPG, PNG or WebP, under 5 MB. A wide photo (about 1600 × 1000 px) works best. Leave empty to keep the current image.</small>
                                    <?php echo fieldError($errors, "hero_image"); ?>
                                    <?php if (isset($errors["hero_image"]) === false && !empty($errors)): ?>
                                        <small class="field-hint reselect-note">If you chose a new image, choose it again. Browsers clear file fields after an error.</small>
                                    <?php endif; ?>

                                    <?php [$label, $max, $required, $hint] = $textFields["hero_image_alt"]; ?>
                                    <div class="mt-3">
                                        <label for="hero_image_alt" class="form-label admin-label"><?php echo e($label); ?></label>
                                        <input type="text" id="hero_image_alt" name="hero_image_alt"
                                            class="form-control admin-input<?php echo invalidClass($errors, "hero_image_alt"); ?>"
                                            value="<?php echo e($form["hero_image_alt"]); ?>" maxlength="<?php echo $max; ?>" required>
                                        <small class="field-hint"><?php echo e($hint); ?></small>
                                        <?php echo fieldError($errors, "hero_image_alt"); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mission -->
                    <div class="card panel-card mb-4">
                        <div class="card-body">
                            <h2 class="panel-title mb-4">Mission</h2>
                            <?php [$label, $max, $required, $hint] = $textFields["mission_line"]; ?>
                            <label for="mission_line" class="form-label admin-label"><?php echo e($label); ?></label>
                            <textarea id="mission_line" name="mission_line" rows="2"
                                class="form-control admin-input<?php echo invalidClass($errors, "mission_line"); ?>"
                                maxlength="<?php echo $max; ?>" required><?php echo e($form["mission_line"]); ?></textarea>
                            <small class="field-hint"><?php echo e($hint); ?></small>
                            <?php echo fieldError($errors, "mission_line"); ?>
                        </div>
                    </div>

                    <!-- Programs -->
                    <div class="card panel-card mb-4">
                        <div class="card-body">
                            <h2 class="panel-title mb-1">Our Programs</h2>
                            <p class="panel-hint mb-4">The three program cards. Each card's "Learn more" link goes to the Gallery.</p>

                            <div class="row g-4">
                                <?php for ($i = 0; $i < 3; $i++): ?>
                                    <?php $program = $form["programs"][$i]; ?>
                                    <div class="col-lg-4">
                                        <div class="program-slot h-100">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <span class="slot-icon"><i class="bi <?php echo e($program["icon"]); ?>" id="iconPreview<?php echo $i; ?>" aria-hidden="true"></i></span>
                                                <strong class="slot-title">Card <?php echo $i + 1; ?></strong>
                                            </div>

                                            <div class="mb-3">
                                                <label for="programIcon<?php echo $i; ?>" class="form-label admin-label">Icon</label>
                                                <select id="programIcon<?php echo $i; ?>" name="programs[<?php echo $i; ?>][icon]"
                                                    class="form-select admin-input icon-select<?php echo invalidClass($errors, "programs.$i.icon"); ?>"
                                                    data-preview="iconPreview<?php echo $i; ?>">
                                                    <?php foreach ($iconOptions as $icon => $iconLabel): ?>
                                                        <option value="<?php echo $icon; ?>" <?php echo $program["icon"] === $icon ? "selected" : ""; ?>><?php echo $iconLabel; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php echo fieldError($errors, "programs.$i.icon"); ?>
                                            </div>

                                            <div class="mb-3">
                                                <label for="programTitle<?php echo $i; ?>" class="form-label admin-label">Title</label>
                                                <input type="text" id="programTitle<?php echo $i; ?>" name="programs[<?php echo $i; ?>][title]"
                                                    class="form-control admin-input<?php echo invalidClass($errors, "programs.$i.title"); ?>"
                                                    value="<?php echo e($program["title"]); ?>" maxlength="60" required>
                                                <?php echo fieldError($errors, "programs.$i.title"); ?>
                                            </div>

                                            <div class="mb-3">
                                                <label for="programMeta<?php echo $i; ?>" class="form-label admin-label">Detail line <span class="optional">(optional)</span></label>
                                                <input type="text" id="programMeta<?php echo $i; ?>" name="programs[<?php echo $i; ?>][meta]"
                                                    class="form-control admin-input<?php echo invalidClass($errors, "programs.$i.meta"); ?>"
                                                    value="<?php echo e($program["meta"]); ?>" maxlength="80">
                                                <small class="field-hint">e.g. 30 computers delivered · January 2026</small>
                                                <?php echo fieldError($errors, "programs.$i.meta"); ?>
                                            </div>

                                            <div>
                                                <label for="programText<?php echo $i; ?>" class="form-label admin-label">Description</label>
                                                <textarea id="programText<?php echo $i; ?>" name="programs[<?php echo $i; ?>][text]" rows="3"
                                                    class="form-control admin-input<?php echo invalidClass($errors, "programs.$i.text"); ?>"
                                                    maxlength="160" required><?php echo e($program["text"]); ?></textarea>
                                                <?php echo fieldError($errors, "programs.$i.text"); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Promise -->
                    <div class="card panel-card mb-4">
                        <div class="card-body">
                            <h2 class="panel-title mb-4">Our Promise</h2>
                            <?php [$label, $max, $required, $hint] = $textFields["promise_title"]; ?>
                            <label for="promise_title" class="form-label admin-label"><?php echo e($label); ?></label>
                            <input type="text" id="promise_title" name="promise_title"
                                class="form-control admin-input<?php echo invalidClass($errors, "promise_title"); ?>"
                                value="<?php echo e($form["promise_title"]); ?>" maxlength="<?php echo $max; ?>" required>
                            <small class="field-hint"><?php echo e($hint); ?></small>
                            <?php echo fieldError($errors, "promise_title"); ?>
                        </div>
                    </div>

                    <!-- Impact -->
                    <div class="card panel-card mb-4">
                        <div class="card-body">
                            <h2 class="panel-title mb-1">Our Impact</h2>
                            <p class="panel-hint mb-4">Three headline numbers. Use the client's real figures.</p>

                            <div class="row g-4">
                                <?php for ($i = 0; $i < 3; $i++): ?>
                                    <?php $stat = $form["impact"][$i]; ?>
                                    <div class="col-md-4">
                                        <div class="program-slot">
                                            <div class="mb-3">
                                                <label for="impactNumber<?php echo $i; ?>" class="form-label admin-label">Number</label>
                                                <input type="text" id="impactNumber<?php echo $i; ?>" name="impact[<?php echo $i; ?>][number]"
                                                    class="form-control admin-input<?php echo invalidClass($errors, "impact.$i.number"); ?>"
                                                    value="<?php echo e($stat["number"]); ?>" maxlength="10" required>
                                                <?php echo fieldError($errors, "impact.$i.number"); ?>
                                            </div>
                                            <div>
                                                <label for="impactLabel<?php echo $i; ?>" class="form-label admin-label">Label</label>
                                                <input type="text" id="impactLabel<?php echo $i; ?>" name="impact[<?php echo $i; ?>][label]"
                                                    class="form-control admin-input<?php echo invalidClass($errors, "impact.$i.label"); ?>"
                                                    value="<?php echo e($stat["label"]); ?>" maxlength="40" required>
                                                <?php echo fieldError($errors, "impact.$i.label"); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <div class="mt-4">
                                <?php [$label, $max, $required, $hint] = $textFields["quote"]; ?>
                                <label for="quote" class="form-label admin-label"><?php echo e($label); ?> <span class="optional">(optional)</span></label>
                                <textarea id="quote" name="quote" rows="2"
                                    class="form-control admin-input<?php echo invalidClass($errors, "quote"); ?>"
                                    maxlength="<?php echo $max; ?>"><?php echo e($form["quote"]); ?></textarea>
                                <small class="field-hint"><?php echo e($hint); ?></small>
                                <?php echo fieldError($errors, "quote"); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Call to Action -->
                    <div class="card panel-card mb-4">
                        <div class="card-body">
                            <h2 class="panel-title mb-1">Call to Action</h2>
                            <p class="panel-hint mb-4">The Donate and Volunteer buttons stay the same.</p>

                            <?php foreach (["cta_title", "cta_text"] as $key): ?>
                                <?php [$label, $max, $required, $hint] = $textFields[$key]; ?>
                                <div class="mb-3">
                                    <label for="<?php echo $key; ?>" class="form-label admin-label"><?php echo e($label); ?></label>
                                    <input type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>"
                                        class="form-control admin-input<?php echo invalidClass($errors, $key); ?>"
                                        value="<?php echo e($form[$key]); ?>" maxlength="<?php echo $max; ?>" required>
                                    <small class="field-hint"><?php echo e($hint); ?></small>
                                    <?php echo fieldError($errors, $key); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Save bar -->
                    <div class="save-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <small class="save-status" id="saveStatus">No unsaved changes</small>
                        <div class="d-flex gap-2">
                            <a href="homepageContent.php" class="btn btn-cancel">Discard Changes</a>
                            <button type="submit" class="btn btn-save">
                                <i class="bi bi-check-lg"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

        </main>
    </div>

    <?php include __DIR__ . '/include/footer.php' ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Icon preview + unsaved changes warning -->
    <script>
        const contentForm = document.getElementById("contentForm");

        if (contentForm) {
            const saveStatus = document.getElementById("saveStatus");
            let hasChanges = <?php echo $saveError !== "" ? "true" : "false"; ?>;
            let submitting = false;

            function markChanged() {
                hasChanges = true;
                saveStatus.textContent = "You have unsaved changes";
                saveStatus.classList.add("unsaved");
            }

            if (hasChanges) {
                markChanged();
            }

            contentForm.addEventListener("input", markChanged);

            // Update the icon preview when a different icon is picked
            document.querySelectorAll(".icon-select").forEach(function(select) {
                select.addEventListener("change", function() {
                    document.getElementById(select.dataset.preview).className = "bi " + select.value;
                    markChanged();
                });
            });

            // Preview a newly chosen hero image before saving
            const heroInput = document.getElementById("hero_image");
            const heroPreview = document.getElementById("heroPreview");
            const heroBadge = document.getElementById("heroBadge");
            const originalHeroSrc = heroPreview.src;

            heroInput.addEventListener("change", function() {
                const file = heroInput.files[0];
                if (file && file.type.startsWith("image/")) {
                    heroPreview.src = URL.createObjectURL(file);
                    heroBadge.textContent = "New image (not saved yet)";
                    heroBadge.classList.add("is-new");
                } else {
                    heroPreview.src = originalHeroSrc;
                    heroBadge.textContent = "Current image";
                    heroBadge.classList.remove("is-new");
                }
                markChanged();
            });

            contentForm.addEventListener("submit", function() {
                submitting = true;
            });

            // Warn before leaving with unsaved changes
            window.addEventListener("beforeunload", function(event) {
                if (hasChanges && !submitting) {
                    event.preventDefault();
                    event.returnValue = "";
                }
            });
        }
    </script>

</body>

</html>