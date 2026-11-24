<?php
require_once 'include/auth.php';
include '../supabase.php';
include '../zeffy_config.php';

$activePage = "donations";

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");
}

function zeffyGet($path)
{
    global $zeffyApiKey, $zeffyApiBase;

    $ch = curl_init($zeffyApiBase . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $zeffyApiKey,
            "Accept: application/json"
        ],
        CURLOPT_SSL_VERIFYPEER => false // local only
    ]);

    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);

    return [
        "code"  => $code,
        "data"  => json_decode($raw, true),
        "error" => $err,
        "raw"   => $raw
    ];
}

$msg = "";
$err = "";

// ---------- Sync Zeffy on this page ----------
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "sync_zeffy") {
    $result = zeffyGet("/payments?limit=100");

    if ($result["code"] !== 200) {
        $err = "Zeffy API error (HTTP " . $result["code"] . "). Check your API key.";
    } else {
        $payments = $result["data"]["data"] ?? [];
        if (!is_array($payments)) {
            $payments = [];
        }

        $inserted = 0;
        $skipped  = 0;
        $removed  = 0;

        // All succeeded payment IDs currently in Zeffy
        $zeffyIds = [];

        foreach ($payments as $p) {
            if (!is_array($p)) {
                continue;
            }

            $zeffyId = (string)($p["id"] ?? "");
            if ($zeffyId === "") {
                $skipped++;
                continue;
            }

            $status = strtolower((string)($p["status"] ?? ""));
            if ($status !== "succeeded") {
                $skipped++;
                continue;
            }

            $zeffyIds[] = $zeffyId;

            // Amount is in cents
            $amount = floatval($p["amount"] ?? 0) / 100;

            $first = trim((string)($p["buyer"]["first_name"] ?? ""));
            $last  = trim((string)($p["buyer"]["last_name"] ?? ""));
            $donorName = trim($first . " " . $last);
            if ($donorName === "") {
                $donorName = "Anonymous";
            }

            $email = $p["buyer"]["email"] ?? null;

            $type = "one-time";
            if (!empty($p["items"][0]["recurrence_interval"])) {
                $interval = strtolower((string)$p["items"][0]["recurrence_interval"]);
                $type = ($interval === "onetime") ? "one-time" : $interval;
            }

            $row = [
                "zeffy_payment_id" => $zeffyId,
                "amount"           => $amount,
                "donor_name"       => $donorName,
                "email"            => $email,
                "donation_type"    => $type,
                "status"           => "completed"
            ];

            $insert = supabaseRequest("POST", "donations", $row);

            if (in_array($insert["code"], [200, 201], true)) {
                $inserted++;
            } else {
                $skipped++;
            }
        }

        // ---- Remove local Zeffy rows that no longer exist in Zeffy ----
        $existing = supabaseRequest(
            "GET",
            "donations",
            null,
            "?select=id,zeffy_payment_id&zeffy_payment_id=not.is.null"
        );

        if ($existing["code"] === 200 && is_array($existing["data"])) {
            foreach ($existing["data"] as $local) {
                $localZeffyId = (string)($local["zeffy_payment_id"] ?? "");
                $localId      = $local["id"] ?? null;

                if ($localZeffyId === "" || $localId === null) {
                    continue;
                }

                // If this Zeffy id is not in the latest API list, delete local row
                if (!in_array($localZeffyId, $zeffyIds, true)) {
                    $del = supabaseRequest(
                        "DELETE",
                        "donations",
                        null,
                        "?id=eq." . urlencode((string)$localId)
                    );

                    if (in_array($del["code"], [200, 204], true)) {
                        $removed++;
                    }
                }
            }
        }
        $msg = "Sync finished";
    }
}

// ---------- Load donations (after sync so new rows show) ----------
$result = supabaseRequest(
    "GET",
    "donations",
    null,
    "?select=id,created_at,donor_name,email,amount,donation_type,status,zeffy_payment_id&order=created_at.desc"
);

$donations   = [];
$totalRaised = 0;

if ($result["code"] === 200 && is_array($result["data"])) {
    $donations = $result["data"];
    foreach ($donations as $row) {
        $totalRaised += floatval($row["amount"] ?? 0);
    }
}

$statusBadges = [
    "completed"      => ["label" => "Completed",      "class" => "status-completed"],
    "active"         => ["label" => "Active",         "class" => "status-active"],
    "pending_review" => ["label" => "Pending Review", "class" => "status-pending"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donations | Phill Anthropy Console</title>
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
                <h1 class="admin-title">Donations</h1>
                <p class="admin-subtitle mb-0">
                    All donations stored in the database.
                    Total raised: <strong>$<?php echo number_format($totalRaised, 2); ?></strong>
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form method="post" action="donations.php" class="d-inline">
                    <input type="hidden" name="action" value="sync_zeffy">
                    <button type="submit" class="btn btn-action">
                        <i class="bi bi-arrow-repeat"></i> Sync Zeffy Payments
                    </button>
                </form>
                <a href="dashboard.php" class="btn btn-export">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>

        <?php if ($msg !== ""): ?>
            <div class="alert alert-success"><?php echo e($msg); ?></div>
        <?php endif; ?>
        <?php if ($err !== ""): ?>
            <div class="alert alert-danger"><?php echo e($err); ?></div>
        <?php endif; ?>

        <div class="card panel-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table ledger-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Donor</th>
                                <th>Email</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($donations)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        No donations yet. Use <strong>Sync Zeffy Payments</strong> to import them.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($donations as $donation): ?>
                                <?php
                                $status   = $donation["status"] ?? "";
                                $badge    = $statusBadges[$status] ?? ["label" => $status ?: "—", "class" => "status-default"];
                                $hasZeffy = !empty($donation["zeffy_payment_id"]);
                                ?>
                                <tr>
                                    <td class="ledger-date">
                                        <?php
                                        echo !empty($donation["created_at"])
                                            ? date("M d, Y", strtotime($donation["created_at"]))
                                            : "—";
                                        ?>
                                    </td>
                                    <td class="ledger-donor">
                                        <?php echo e($donation["donor_name"] ?? "Anonymous"); ?>
                                    </td>
                                    <td><?php echo e($donation["email"] ?? "—"); ?></td>
                                    <td class="ledger-amount">
                                        $<?php echo number_format(floatval($donation["amount"] ?? 0), 2); ?>
                                    </td>
                                    <td class="ledger-type">
                                        <?php echo e($donation["donation_type"] ?? "—"); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo e($badge["class"]); ?>">
                                            <?php echo e($badge["label"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($hasZeffy): ?>
                                            <span class="badge text-bg-light">Zeffy</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Manual / site</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'include/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>