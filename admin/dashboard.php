<?php
require_once 'include/auth.php';
include '../supabase.php';

$activePage = "dashboard";

// ---------- Donations: total raised + pending audits ----------
$result = supabaseRequest("GET", "donations", null, "?select=amount,status");

$totalRaised  = 0;
$pendingCount = 0;
if ($result["code"] === 200 && is_array($result["data"])) {
    foreach ($result["data"] as $row) {
        $totalRaised += floatval($row["amount"] ?? 0);
        if (($row["status"] ?? "") === "pending_review") {
            $pendingCount++;
        }
    }
}

$goal = 20000;
$percent = $goal > 0 ? min(100, round(($totalRaised / $goal) * 100)) : 0;

// ---------- Recent donations (latest 5) ----------
$recentResult = supabaseRequest(
    "GET",
    "donations",
    null,
    "?select=created_at,donor_name,amount,donation_type,status&order=created_at.desc&limit=5"
);

$recentDonations = [];
if ($recentResult["code"] === 200 && is_array($recentResult["data"])) {
    $recentDonations = $recentResult["data"];
}

// ---------- Upcoming events ----------
$today = date("Y-m-d");
$eventsResult = supabaseRequest(
    "GET",
    "events",
    null,
    "?select=title,event_date&event_date=gte." . $today . "&order=event_date.asc"
);

$eventCount = 0;
$nextEvent  = null;
if ($eventsResult["code"] === 200 && is_array($eventsResult["data"])) {
    $eventCount = count($eventsResult["data"]);
    $nextEvent  = $eventsResult["data"][0] ?? null;
}

// ---------- Volunteers ----------
$volResult = supabaseRequest("GET", "volunteers", null, "?select=status");

$approvedVolunteers = 0;
$pendingVolunteers  = 0;
if ($volResult["code"] === 200 && is_array($volResult["data"])) {
    foreach ($volResult["data"] as $row) {
        if (($row["status"] ?? "") === "pending") {
            $pendingVolunteers++;
        } else {
            $approvedVolunteers++;
        }
    }
}

// ---------- Status badge labels ----------
$statusBadges = [
    "completed"      => ["label" => "Completed",      "class" => "status-completed"],
    "active"         => ["label" => "Active",         "class" => "status-active"],
    "pending_review" => ["label" => "Pending Review", "class" => "status-pending"]
];

// First name for the welcome message
$firstName = explode(" ", $adminName)[0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | Phill Anthropy Console</title>
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

            <!-- Page Heading -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                <div>
                    <h1 class="admin-title">Console Dashboard</h1>
                    <p class="admin-subtitle mb-0">Welcome back <?php echo htmlspecialchars($firstName); ?>. Here is your current real-time fundraising telemetry.</p>
                </div>
                <small class="sys-version text-uppercase">Sys Version 2.4 Secure</small>
            </div>

            <!-- Stat Cards -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <small class="stat-label text-uppercase">Total Capital Raised</small>
                            <div class="stat-value">$<?php echo number_format($totalRaised, 0); ?></div>
                            <span class="stat-tag tag-green"><?php echo $percent; ?>% of seasonal goal</span>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <small class="stat-label text-uppercase">Active Local Events</small>
                            <div class="stat-value"><?php echo $eventCount; ?> <?php echo $eventCount === 1 ? "Event" : "Events"; ?></div>
                            <?php if ($nextEvent): ?>
                                <span class="stat-tag tag-orange">
                                    Next: <?php echo htmlspecialchars($nextEvent["title"] ?? ""); ?>
                                    <?php echo date("M j", strtotime($nextEvent["event_date"])); ?>
                                </span>
                            <?php else: ?>
                                <span class="stat-tag tag-grey">No upcoming events</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <small class="stat-label text-uppercase">Volunteer Stewards</small>
                            <div class="stat-value"><?php echo $approvedVolunteers; ?> Stewards</div>
                            <span class="stat-tag tag-green">+<?php echo $pendingVolunteers; ?> pending check</span>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <small class="stat-label text-uppercase">Pending Audits</small>
                            <?php if ($pendingCount > 0): ?>
                                <div class="stat-value text-alert"><?php echo $pendingCount; ?> <?php echo $pendingCount === 1 ? "Donation" : "Donations"; ?></div>
                                <span class="stat-tag tag-red">Needs quick review</span>
                            <?php else: ?>
                                <div class="stat-value">0 Donations</div>
                                <span class="stat-tag tag-green">All clear</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card panel-card mb-4">
                <div class="card-body">
                    <h2 class="panel-title mb-3">Direct Administration Actions</h2>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="homepage-content.php" class="btn btn-action">
                            <i class="bi bi-pencil-square"></i> Edit Homepage Text
                        </a>
                        <a href="events.php?action=add" class="btn btn-action">
                            <i class="bi bi-calendar-plus"></i> Add New Event
                        </a>
                        <a href="faqs.php" class="btn btn-action">
                            <i class="bi bi-question-circle"></i> Update FAQs
                        </a>
                        <a href="settings.php#goal" class="btn btn-action">
                            <i class="bi bi-bullseye"></i> Update Fundraising Goal
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Donations -->
            <div class="card panel-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="panel-title mb-0">Recent Donations Ledger</h2>
                        <a href="export-donations.php" class="btn btn-export">
                            <i class="bi bi-download"></i> Export to CSV
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table ledger-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Donor Name</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentDonations)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No donations yet.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($recentDonations as $donation): ?>
                                    <?php
                                    $status = $donation["status"] ?? "";
                                    $badge  = $statusBadges[$status] ?? ["label" => $status, "class" => "status-default"];
                                    ?>
                                    <tr>
                                        <td class="ledger-date"><?php echo date("M d, Y", strtotime($donation["created_at"])); ?></td>
                                        <td class="ledger-donor"><?php echo htmlspecialchars($donation["donor_name"] ?? "Anonymous"); ?></td>
                                        <td class="ledger-amount">$<?php echo number_format(floatval($donation["amount"] ?? 0), 2); ?></td>
                                        <td class="ledger-type"><?php echo htmlspecialchars($donation["donation_type"] ?? ""); ?></td>
                                        <td><span class="status-badge <?php echo $badge["class"]; ?>"><?php echo htmlspecialchars($badge["label"]); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <?php include 'include/footer.php' ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>