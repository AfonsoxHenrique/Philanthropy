<?php
require __DIR__ . '/include/auth.php';
require __DIR__ . '/../supabase.php';
require __DIR__ . '/include/supabaseAdmin.php';

$activePage = "gallery";
$bucket     = "site-images";

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Older photos may use paths like "assets/images/x.jpg"; admin pages are one folder down
function adminImg($url)
{
    if ($url === "" || preg_match('#^(https?:)?//#', $url) || str_starts_with($url, "/")) {
        return $url;
    }
    return "../" . $url;
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}


function loadMilestones()
{
    return supabaseAdminRequest(
        "GET",
        "gallery_items",
        null,
        "?select=id,title,date_label,sort_order,is_published,cover_photo_id,gallery_photos(id,thumb_url)"
            . "&order=sort_order.asc,id.asc&gallery_photos.order=sort_order.asc"
    );
}

$result     = loadMilestones();
$milestones = ($result["code"] === 200 && is_array($result["data"])) ? $result["data"] : [];
$loadError  = $result["code"] === 200 ? "" : "The gallery couldn't be loaded (error " . $result["code"] . "). Refresh to try again.";
$actionError = "";

//Actions: move, show/hide, delete 
if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST" && $loadError === "") {
    $action = $_POST["action"] ?? "";
    $id     = (string) ($_POST["id"] ?? "");
    $ids    = array_map(fn($m) => (string) $m["id"], $milestones);
    $index  = array_search($id, $ids, true);

    if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
        $actionError = "The page expired. Refresh it and try again.";
    } elseif ($index === false) {
        $actionError = "That milestone couldn't be found. It may have been deleted.";
    } elseif ($action === "move_up" || $action === "move_down") {
        $swapWith = $action === "move_up" ? $index - 1 : $index + 1;

        if (isset($milestones[$swapWith])) {
            [$milestones[$index], $milestones[$swapWith]] = [$milestones[$swapWith], $milestones[$index]];

            // Re-number everything 10, 20, 30... and only save the ones that changed
            foreach ($milestones as $i => $milestone) {
                $newOrder = ($i + 1) * 10;
                if ((int) $milestone["sort_order"] !== $newOrder) {
                    supabaseAdminRequest("PATCH", "gallery_items", ["sort_order" => $newOrder], "?id=eq." . urlencode($milestone["id"]));
                }
            }
        }
        header("Location: gallery.php?msg=moved");
        exit;
    } elseif ($action === "toggle") {
        $publish = empty($milestones[$index]["is_published"]);
        $update  = supabaseAdminRequest("PATCH", "gallery_items", ["is_published" => $publish], "?id=eq." . urlencode($id));

        if ($update["code"] === 200 && !empty($update["data"])) {
            header("Location: gallery.php?msg=" . ($publish ? "published" : "hidden"));
            exit;
        }
        $actionError = "That change couldn't be saved (error " . $update["code"] . "). Check your account has the admin role.";
    } elseif ($action === "delete") {
        // Get the storage paths first, because the photo rows are deleted with the milestone
        $photoResult = supabaseAdminRequest("GET", "gallery_photos", null, "?select=storage_path,thumb_path&item_id=eq." . urlencode($id));
        $delete      = supabaseAdminRequest("DELETE", "gallery_items", null, "?id=eq." . urlencode($id));

        if ($delete["code"] === 200 && !empty($delete["data"])) {
            $paths = [];
            foreach (($photoResult["data"] ?? []) as $photo) {
                $paths[] = $photo["storage_path"] ?? "";
                $paths[] = $photo["thumb_path"] ?? "";
            }
            supabaseStorageDelete($bucket, $paths);

            header("Location: gallery.php?msg=deleted");
            exit;
        }
        $actionError = "The milestone couldn't be deleted (error " . $delete["code"] . "). Try again.";
    }
}

$messages = [
    "moved"     => "Order updated.",
    "published" => "Milestone is now showing on the website.",
    "hidden"    => "Milestone is now hidden from the website.",
    "deleted"   => "Milestone and its photos deleted.",
    "missing"   => "That milestone no longer exists."
];
$successMessage = $actionError === "" ? ($messages[$_GET["msg"] ?? ""] ?? "") : "";
$totalPhotos    = array_sum(array_map(fn($m) => count($m["gallery_photos"] ?? []), $milestones));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Gallery | Phill Anthropy Console</title>
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
                    <h1 class="admin-title">Gallery</h1>
                    <p class="admin-subtitle mb-0">
                        <?php echo count($milestones); ?> <?php echo count($milestones) === 1 ? "milestone" : "milestones"; ?>,
                        <?php echo $totalPhotos; ?> <?php echo $totalPhotos === 1 ? "photo" : "photos"; ?>.
                        Visitors see them in this order.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="../gallery.php" target="_blank" rel="noopener" class="btn btn-export">
                        <i class="bi bi-box-arrow-up-right"></i> View Gallery
                    </a>
                    <a href="galleryEdit.php" class="btn btn-save">
                        <i class="bi bi-plus-lg"></i> Add Milestone
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($successMessage !== ""): ?>
                <div class="alert form-alert-success d-flex gap-2 align-items-start" role="status">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?php echo e($successMessage); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($loadError !== "" || $actionError !== ""): ?>
                <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                    <i class="bi bi-exclamation-circle"></i>
                    <div><?php echo e($loadError !== "" ? $loadError : $actionError); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($loadError === ""): ?>
                <div class="card panel-card">
                    <div class="card-body">
                        <?php if (empty($milestones)): ?>
                            <div class="empty-photos text-center">
                                <i class="bi bi-images"></i>
                                <p class="mb-3">No milestones yet.</p>
                                <a href="galleryEdit.php" class="btn btn-save"><i class="bi bi-plus-lg"></i> Add the first milestone</a>
                            </div>
                        <?php else: ?>
                            <ul class="milestone-list list-unstyled mb-0">
                                <?php foreach ($milestones as $i => $milestone): ?>
                                    <?php
                                    $photos     = $milestone["gallery_photos"] ?? [];
                                    $photoCount = count($photos);
                                    $cover      = $photos[0]["thumb_url"] ?? "";
                                    foreach ($photos as $photo) {
                                        if ((string) $photo["id"] === (string) $milestone["cover_photo_id"]) {
                                            $cover = $photo["thumb_url"];
                                        }
                                    }
                                    $published = !empty($milestone["is_published"]);
                                    $id        = (string) $milestone["id"];
                                    ?>

                                    <!-- This is where the photos and titles are displayed -->
                                    <li class="row align-items-center mb-3 milestone-row">
                                        <div class="col-md-4 d-flex flex-column flex-nowrap">
                                            <a href="galleryEdit.php?id=<?php echo urlencode($id); ?>" class="milestone-title"><?php echo e($milestone["title"]); ?></a>
                                            <div class="milestone-meta">
                                                <?php echo e($milestone["date_label"]); ?>
                                                &middot; <?php echo $photoCount; ?> <?php echo $photoCount === 1 ? "photo" : "photos"; ?>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                <span class="status-badge <?php echo $published ? "status-completed" : "status-default"; ?>">
                                                    <?php echo $published ? "Published" : "Hidden"; ?>
                                                </span>
                                                <?php if ($published && $photoCount === 0): ?>
                                                    <span class="status-badge status-pending">No photos, so not shown</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <?php if ($cover !== ""): ?>
                                                <img src="<?php echo e(adminImg($cover)); ?>" class="photo-img" alt="" loading="lazy">
                                            <?php else: ?>
                                                <i class="bi bi-image" aria-hidden="true"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-5 d-flex flex-wrap gap-1 justify-content-md-end">
                                            <form method="POST" action="gallery.php">
                                                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION["csrf_token"]); ?>">
                                                <input type="hidden" name="id" value="<?php echo e($id); ?>">
                                                <button type="submit" name="action" value="move_up" class="btn btn-tile" aria-label="Move <?php echo e($milestone["title"]); ?> up"
                                                    <?php echo $i === 0 ? "disabled" : ""; ?>><i class="bi bi-arrow-up"></i></button>
                                                <button type="submit" name="action" value="move_down" class="btn btn-tile" aria-label="Move <?php echo e($milestone["title"]); ?> down"
                                                    <?php echo $i === count($milestones) - 1 ? "disabled" : ""; ?>><i class="bi bi-arrow-down"></i></button>
                                                <button type="submit" name="action" value="toggle" class="btn btn-tile">
                                                    <i class="bi <?php echo $published ? "bi-eye-slash" : "bi-eye"; ?>"></i>
                                                    <?php echo $published ? "Hide" : "Publish"; ?>
                                                </button>
                                                <button type="submit" name="action" value="delete" class="btn btn-tile btn-tile-danger confirm-delete"
                                                    data-title="<?php echo e($milestone["title"]); ?>" data-count="<?php echo $photoCount; ?>"
                                                    aria-label="Delete <?php echo e($milestone["title"]); ?>"><i class="bi bi-trash"></i></button>
                                            </form>
                                            <a href="galleryEdit.php?id=<?php echo urlencode($id); ?>" class="btn btn-action btn-sm">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <?php include __DIR__ . '/include/footer.php' ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm before deleting a milestone
        document.querySelectorAll(".confirm-delete").forEach(function(button) {
            button.addEventListener("click", function(event) {
                const count = Number(button.dataset.count);
                const photos = count === 1 ? "1 photo" : count + " photos";
                if (!confirm('Delete "' + button.dataset.title + '" and its ' + photos + "? This can't be undone.")) {
                    event.preventDefault();
                }
            });
        });
    </script>

</body>

</html>