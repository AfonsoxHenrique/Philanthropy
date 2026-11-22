<?php
require_once 'include/auth.php';
include '../supabase.php';

$activePage = "faqs";

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = "";
$error   = "";

if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    if ($id > 0) {
        $del = supabaseRequest("DELETE", "faqs", null, "?id=eq." . $id);
        if ($del["code"] === 200 || $del["code"] === 204) {
            header("Location: faqs.php?msg=deleted");
            exit;
        }
        $error = "Could not delete that FAQ.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id         = intval($_POST["id"] ?? 0);
    $question   = trim($_POST["question"] ?? "");
    $answer     = trim($_POST["answer"] ?? "");
    $sortOrder  = intval($_POST["sort_order"] ?? 0);
    $published  = isset($_POST["is_published"]);

    if ($question === "" || $answer === "") {
        $error = "Question and answer are required.";
    } else {
        $data = [
            "question"     => $question,
            "answer"       => $answer,
            "sort_order"   => $sortOrder,
            "is_published" => $published
        ];

        if ($id > 0) {
            $result = supabaseRequest("PATCH", "faqs", $data, "?id=eq." . $id);
        } else {
            $result = supabaseRequest("POST", "faqs", $data);
        }

        if (in_array($result["code"], [200, 201], true)) {
            header("Location: faqs.php?msg=saved");
            exit;
        }
        $error = "Could not save the FAQ. Check Supabase policies and try again.";
    }
}

if (isset($_GET["msg"])) {
    if ($_GET["msg"] === "saved") {
        $message = "FAQ saved.";
    }
    if ($_GET["msg"] === "deleted") {
        $message = "FAQ deleted.";
    }
}

$listResult = supabaseRequest(
    "GET",
    "faqs",
    null,
    "?select=id,question,answer,sort_order,is_published&order=sort_order.asc"
);

$faqs = [];
if ($listResult["code"] === 200 && is_array($listResult["data"])) {
    $faqs = $listResult["data"];
}

$editFaq = null;
if (isset($_GET["edit"])) {
    $editId = intval($_GET["edit"]);
    foreach ($faqs as $faq) {
        if (intval($faq["id"] ?? 0) === $editId) {
            $editFaq = $faq;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>FAQs | Phill Anthropy Console</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../style/admin.css" rel="stylesheet">
</head>

<body class="admin-body">
    <?php include 'include/header.php' ?>

    <div class="admin-wrapper">
        <?php include 'include/sidebar.php' ?>

        <main class="admin-main">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                <div>
                    <h1 class="admin-title">Manage FAQs</h1>
                    <p class="admin-subtitle mb-0">Add, edit, reorder or remove questions shown on the public FAQ page.</p>
                </div>
                <a href="dashboard.php" class="btn btn-action">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <?php if ($message !== ""): ?>
                <div class="alert alert-success"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error !== ""): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Form -->
                <div class="col-lg-5">
                    <div class="card panel-card">
                        <div class="card-body">
                            <h2 class="panel-title mb-3">
                                <?php echo $editFaq ? "Edit FAQ" : "Add FAQ"; ?>
                            </h2>

                            <form method="post" action="faqs.php">
                                <input type="hidden" name="id" value="<?php echo e($editFaq["id"] ?? 0); ?>">

                                <div class="mb-3">
                                    <label class="form-label" for="question">Question</label>
                                    <input type="text" class="form-control" id="question" name="question" required
                                           value="<?php echo e($editFaq["question"] ?? ""); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="answer">Answer</label>
                                    <textarea class="form-control" id="answer" name="answer" rows="5" required><?php
                                        echo e($editFaq["answer"] ?? "");
                                    ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="sort_order">Order (lower = higher on page)</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order"
                                           value="<?php echo e($editFaq["sort_order"] ?? count($faqs) + 1); ?>">
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published"
                                        <?php
                                        $pub = $editFaq["is_published"] ?? true;
                                        echo $pub ? "checked" : "";
                                        ?>>
                                    <label class="form-check-label" for="is_published">Published on website</label>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-action">
                                        <i class="bi bi-save"></i>
                                        <?php echo $editFaq ? "Update FAQ" : "Add FAQ"; ?>
                                    </button>
                                    <?php if ($editFaq): ?>
                                        <a href="faqs.php" class="btn btn-export">Cancel edit</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- List -->
                <div class="col-lg-7">
                    <div class="card panel-card">
                        <div class="card-body">
                            <h2 class="panel-title mb-3">All FAQs</h2>

                            <?php if (empty($faqs)): ?>
                                <p class="text-muted mb-0">No FAQs yet. Add the first one on the left.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table ledger-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Order</th>
                                                <th>Question</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($faqs as $faq): ?>
                                                <tr>
                                                    <td><?php echo e($faq["sort_order"] ?? 0); ?></td>
                                                    <td>
                                                        <strong><?php echo e($faq["question"] ?? ""); ?></strong>
                                                        <div class="small text-muted">
                                                            <?php echo e(strlen($faq["answer"] ?? "") > 80 ? substr($faq["answer"], 0, 80) . "…" : ($faq["answer"] ?? "")); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($faq["is_published"])): ?>
                                                            <span class="status-badge status-completed">Published</span>
                                                        <?php else: ?>
                                                            <span class="status-badge status-pending">Hidden</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <a class="btn btn-sm btn-action" href="faqs.php?edit=<?php echo urlencode($faq["id"]); ?>">
                                                            Edit
                                                        </a>
                                                        <a class="btn btn-sm btn-export"
                                                           href="faqs.php?delete=<?php echo urlencode($faq["id"]); ?>"
                                                           onclick="return confirm('Delete this FAQ?');">
                                                            Delete
                                                        </a>
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

    <?php include 'include/footer.php' ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>