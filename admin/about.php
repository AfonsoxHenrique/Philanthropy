<?php
require_once 'include/auth.php';
include '../supabase.php';

// Prefer admin helper if available
if (file_exists(__DIR__ . '/include/supabaseAdmin.php')) {
    require_once __DIR__ . '/include/supabaseAdmin.php';
}

$activePage = "about";

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");
}

function adminReq($method, $table, $data = null, $query = "")
{
    if (function_exists("supabaseAdminRequest")) {
        return supabaseAdminRequest($method, $table, $data, $query);
    }
    return supabaseRequest($method, $table, $data, $query);
}

$aboutKeys = [
    "about_hero_title"    => "Hero title",
    "about_hero_text"     => "Hero text",
    "about_story_title"   => "Story title",
    "about_story_p1"      => "Story paragraph 1",
    "about_story_p2"      => "Story paragraph 2",
    "about_journey_title" => "Journey title",
    "about_journey_text"  => "Journey text",
    "about_team_eyebrow"  => "Team eyebrow",
    "about_team_title"    => "Team section title"
];

$msg = "";
$err = "";

// ---------- Save About text ----------
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["form"] ?? "") === "copy") {
    $ok = true;
    foreach ($aboutKeys as $key => $label) {
        $value = trim($_POST[$key] ?? "");
        // Upsert-style: try update, if none then insert
        $patch = adminReq(
            "PATCH",
            "site_settings",
            ["setting_value" => $value],
            "?setting_key=eq." . urlencode($key)
        );

        $updated = ($patch["code"] === 200 && is_array($patch["data"]) && count($patch["data"]) > 0);

        if (!$updated) {
            $ins = adminReq("POST", "site_settings", [
                "setting_key"   => $key,
                "setting_value" => $value
            ]);
            if (!in_array($ins["code"], [200, 201], true)) {
                $ok = false;
            }
        }
    }
    if ($ok) {
        header("Location: about.php?msg=copy");
        exit;
    }
    $err = "Could not save About text. Check RLS policies on site_settings.";
}

// ---------- Save / add team member ----------
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["form"] ?? "") === "team") {
    $id        = intval($_POST["id"] ?? 0);
    $name      = trim($_POST["name"] ?? "");
    $role      = trim($_POST["role"] ?? "");
    $bio       = trim($_POST["bio"] ?? "");
    $imageUrl  = trim($_POST["image_url"] ?? "");
    $sortOrder = intval($_POST["sort_order"] ?? 0);
    $published = isset($_POST["is_published"]);

    if ($name === "" || $role === "") {
        $err = "Name and role are required.";
    } else {
        $data = [
            "name"         => $name,
            "role"         => $role,
            "bio"          => $bio,
            "image_url"    => $imageUrl,
            "sort_order"   => $sortOrder,
            "is_published" => $published
        ];

        if ($id > 0) {
            $result = adminReq("PATCH", "team_members", $data, "?id=eq." . $id);
        } else {
            $result = adminReq("POST", "team_members", $data);
        }

        if (in_array($result["code"], [200, 201], true)) {
            header("Location: about.php?msg=team");
            exit;
        }
        $err = "Could not save team member (error " . ($result["code"] ?? "?") . ").";
    }
}

// ---------- Delete team member ----------
if (isset($_GET["delete_team"])) {
    $id = intval($_GET["delete_team"]);
    if ($id > 0) {
        $del = adminReq("DELETE", "team_members", null, "?id=eq." . $id);
        if (in_array($del["code"], [200, 204], true)) {
            header("Location: about.php?msg=deleted");
            exit;
        }
        $err = "Could not delete team member.";
    }
}

if (isset($_GET["msg"])) {
    $messages = [
        "copy"    => "About page text saved.",
        "team"    => "Team member saved.",
        "deleted" => "Team member deleted."
    ];
    $msg = $messages[$_GET["msg"]] ?? "";
}

// ---------- Load copy ----------
$copy = [];
foreach ($aboutKeys as $key => $label) {
    $copy[$key] = "";
}

$settings = adminReq("GET", "site_settings", null, "?select=setting_key,setting_value&setting_key=like.about_%");
if (($settings["code"] ?? 0) === 200 && is_array($settings["data"] ?? null)) {
    foreach ($settings["data"] as $row) {
        $k = $row["setting_key"] ?? "";
        if (isset($copy[$k])) {
            $copy[$k] = $row["setting_value"] ?? "";
        }
    }
}

// ---------- Load team ----------
$team = [];
$teamResult = adminReq(
    "GET",
    "team_members",
    null,
    "?select=id,name,role,bio,image_url,sort_order,is_published&order=sort_order.asc"
);
if (($teamResult["code"] ?? 0) === 200 && is_array($teamResult["data"] ?? null)) {
    $team = $teamResult["data"];
}

// Edit one member?
$editMember = null;
if (isset($_GET["edit_team"])) {
    $editId = intval($_GET["edit_team"]);
    foreach ($team as $m) {
        if (intval($m["id"] ?? 0) === $editId) {
            $editMember = $m;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../style/admin.css" rel="stylesheet">
</head>
<body class="admin-body">
<?php include 'include/header.php'; ?>

<div class="admin-wrapper">
    <?php include 'include/sidebar.php'; ?>

    <main class="admin-main">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
            <div>
                <h1 class="admin-title">About Us</h1>
                <p class="admin-subtitle mb-0">Edit the public About page text and organisers.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="../about.php" target="_blank" class="btn btn-export">
                    <i class="bi bi-box-arrow-up-right"></i> View page
                </a>
                <a href="dashboard.php" class="btn btn-export">Dashboard</a>
            </div>
        </div>

        <?php if ($msg !== ""): ?>
            <div class="alert alert-success"><?php echo e($msg); ?></div>
        <?php endif; ?>
        <?php if ($err !== ""): ?>
            <div class="alert alert-danger"><?php echo e($err); ?></div>
        <?php endif; ?>

        <!-- About text -->
        <div class="card panel-card mb-4">
            <div class="card-body">
                <h2 class="panel-title mb-3">Page text</h2>
                <form method="post" action="about.php">
                    <input type="hidden" name="form" value="copy">
                    <div class="row g-3">
                        <?php foreach ($aboutKeys as $key => $label): ?>
                            <div class="col-12 <?php echo str_contains($key, 'title') || str_contains($key, 'eyebrow') ? 'col-md-6' : ''; ?>">
                                <label class="form-label" for="<?php echo e($key); ?>"><?php echo e($label); ?></label>
                                <?php if (str_contains($key, 'text') || str_contains($key, '_p')): ?>
                                    <textarea class="form-control" id="<?php echo e($key); ?>" name="<?php echo e($key); ?>" rows="3"><?php echo e($copy[$key]); ?></textarea>
                                <?php else: ?>
                                    <input type="text" class="form-control" id="<?php echo e($key); ?>" name="<?php echo e($key); ?>"
                                           value="<?php echo e($copy[$key]); ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-action">
                            <i class="bi bi-save"></i> Save page text
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <!-- Team form -->
            <div class="col-lg-5">
                <div class="card panel-card">
                    <div class="card-body">
                        <h2 class="panel-title mb-3">
                            <?php echo $editMember ? "Edit organiser" : "Add organiser"; ?>
                        </h2>
                        <form method="post" action="about.php">
                            <input type="hidden" name="form" value="team">
                            <input type="hidden" name="id" value="<?php echo e($editMember["id"] ?? 0); ?>">

                            <div class="mb-3">
                                <label class="form-label" for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo e($editMember["name"] ?? ""); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="role">Role</label>
                                <input type="text" class="form-control" id="role" name="role" required
                                       value="<?php echo e($editMember["role"] ?? ""); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="bio">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo e($editMember["bio"] ?? ""); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="image_url">Image path or URL</label>
                                <input type="text" class="form-control" id="image_url" name="image_url"
                                       placeholder="assets/images/team_sarah.jpg"
                                       value="<?php echo e($editMember["image_url"] ?? ""); ?>">
                                <small class="text-muted">Local path or full https URL. Leave empty for initials avatar.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="sort_order">Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order"
                                       value="<?php echo e($editMember["sort_order"] ?? (count($team) + 1)); ?>">
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published"
                                    <?php
                                    $pub = $editMember["is_published"] ?? true;
                                    echo $pub ? "checked" : "";
                                    ?>>
                                <label class="form-check-label" for="is_published">Published on website</label>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-action">
                                    <i class="bi bi-save"></i>
                                    <?php echo $editMember ? "Update" : "Add"; ?> organiser
                                </button>
                                <?php if ($editMember): ?>
                                    <a href="about.php" class="btn btn-export">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Team list -->
            <div class="col-lg-7">
                <div class="card panel-card">
                    <div class="card-body">
                        <h2 class="panel-title mb-3">Organisers</h2>
                        <?php if (empty($team)): ?>
                            <p class="text-muted mb-0">No team members yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table ledger-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($team as $m): ?>
                                            <tr>
                                                <td><?php echo e($m["sort_order"] ?? 0); ?></td>
                                                <td>
                                                    <strong><?php echo e($m["name"] ?? ""); ?></strong>
                                                    <div class="small text-muted">
                                                        <?php
                                                        $bio = $m["bio"] ?? "";
                                                        echo e(strlen($bio) > 60 ? substr($bio, 0, 60) . "…" : $bio);
                                                        ?>
                                                    </div>
                                                </td>
                                                <td><?php echo e($m["role"] ?? ""); ?></td>
                                                <td>
                                                    <?php if (!empty($m["is_published"])): ?>
                                                        <span class="status-badge status-completed">Published</span>
                                                    <?php else: ?>
                                                        <span class="status-badge status-pending">Hidden</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-nowrap">
                                                    <a class="btn btn-sm btn-action" href="about.php?edit_team=<?php echo urlencode($m["id"]); ?>">Edit</a>
                                                    <a class="btn btn-sm btn-export"
                                                       href="about.php?delete_team=<?php echo urlencode($m["id"]); ?>"
                                                       onclick="return confirm('Delete this organiser?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'include/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>