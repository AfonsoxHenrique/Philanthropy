<?php
require_once 'include/auth.php';
include '../supabase.php';
include '../zeffy_config.php';

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
        // Local Windows only — enable verify in production
        CURLOPT_SSL_VERIFYPEER => false
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

$debug = isset($_GET["debug"]);
$msg   = "";
$err   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" || isset($_GET["run"])) {
    $result = zeffyGet("/payments?limit=100");

    if ($debug) {
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        exit;
    }

    if ($result["code"] !== 200) {
        $err = "Zeffy API error (HTTP " . $result["code"] . "). Check your API key.";
    } else {
        $payments = $result["data"]["data"] ?? [];
        if (!is_array($payments)) {
            $payments = [];
        }

        $inserted = 0;
        $skipped  = 0;

        foreach ($payments as $p) {
            if (!is_array($p)) {
                continue;
            }

            $zeffyId = (string)($p["id"] ?? "");
            if ($zeffyId === "") {
                $skipped++;
                continue;
            }

            // Only successful payments
            $status = strtolower((string)($p["status"] ?? ""));
            if ($status !== "succeeded") {
                $skipped++;
                continue;
            }

            // Zeffy amount is in cents (100 = $1.00)
            $amount = floatval($p["amount"] ?? 0) / 100;

            // Buyer name
            $first = trim((string)($p["buyer"]["first_name"] ?? ""));
            $last  = trim((string)($p["buyer"]["last_name"] ?? ""));
            $donorName = trim($first . " " . $last);
            if ($donorName === "") {
                $donorName = "Anonymous";
            }

            $email = $p["buyer"]["email"] ?? null;

            // Recurrence from line item
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
                // Likely duplicate zeffy_payment_id or RLS/column issue
                $skipped++;
            }
        }

        $msg = "Sync finished. Inserted: {$inserted}. Skipped: {$skipped}.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sync Zeffy | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style/admin.css" rel="stylesheet">
</head>
<body class="admin-body">
<?php include 'include/header.php'; ?>
<div class="admin-wrapper">
    <?php include 'include/sidebar.php'; ?>
    <main class="admin-main">
        <h1 class="admin-title">Sync Zeffy Payments</h1>
        <p class="admin-subtitle">Pull payments from Zeffy into the Supabase donations table.</p>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?php echo e($msg); ?></div>
        <?php endif; ?>
        <?php if ($err): ?>
            <div class="alert alert-danger"><?php echo e($err); ?></div>
        <?php endif; ?>

        <div class="card panel-card">
            <div class="card-body">
                <form method="post">
                    <button type="submit" class="btn btn-action">Sync now</button>
                    <a class="btn btn-export" href="sync-zeffy.php?debug=1">Debug API response</a>
                    <a class="btn btn-export" href="dashboard.php">Back to dashboard</a>
                </form>
                <p class="small text-muted mt-3 mb-0">
                    Amounts are converted from cents. Successful payments only. Duplicates are skipped.
                </p>
            </div>
        </div>
    </main>
</div>
</body>
</html>