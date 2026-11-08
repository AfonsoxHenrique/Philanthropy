<?php
include 'supabase.php';

// Donation ID passed after payment, e.g. donation-confirmed.php?id=123
$donationId = isset($_GET["id"]) ? trim($_GET["id"]) : "";
$donation   = null;

if ($donationId !== "") {
    $result = supabaseRequest("GET", "donations", null, "?id=eq." . urlencode($donationId) . "&select=*");

    if ($result["code"] === 200 && is_array($result["data"]) && count($result["data"]) > 0) {
        $donation = $result["data"][0];
    }
}

// Safe output helper
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Prepare display values (column names must match the donations table)
if ($donation) {
    $amount     = number_format(floatval($donation["amount"] ?? 0), 2);
    $frequency  = (($donation["frequency"] ?? "one-time") === "monthly") ? "Monthly Gift" : "One-Time Gift";
    $dateTime   = !empty($donation["created_at"])
        ? date("F j, Y, g:i A", strtotime($donation["created_at"]))
        : "";
    $dedication = $donation["dedication"] ?? "";
    $email      = $donation["email"] ?? "";
}

// Facebook share link (points to the homepage)
$siteUrl  = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"];
$shareUrl = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($siteUrl);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thank You | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?v=2" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Confirmation Section -->
    <section id="confirmation" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <div class="card confirm-card mx-auto text-center p-4 p-md-5">

                <div class="icon-circle icon-circle-lg mx-auto mb-4">
                    <i class="bi bi-heart"></i>
                </div>

                <h1 class="confirm-title mb-3">Thank You for Your Generosity!</h1>
                <p class="confirm-text mx-auto mb-4">Your donation directly funds supplies, nutrition and activities
                    for students in our local school community.</p>

                <?php if ($donation): ?>
                    <!-- Donation summary -->
                    <div class="confirm-summary text-start mb-4">
                        <div class="summary-row">
                            <span class="summary-label">Donation Amount</span>
                            <span class="summary-amount">$<?php echo e($amount); ?> AUD</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Frequency</span>
                            <span class="summary-value fw-semibold"><?php echo e($frequency); ?></span>
                        </div>
                        <?php if ($dateTime !== ""): ?>
                            <div class="summary-row">
                                <span class="summary-label">Date &amp; Time</span>
                                <span class="summary-value"><?php echo e($dateTime); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($dedication !== ""): ?>
                            <div class="summary-row">
                                <span class="summary-label">Dedicated To</span>
                                <span class="summary-value"><?php echo e($dedication); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($email !== ""): ?>
                        <p class="confirm-receipt mb-4">A receipt has been sent to your email (<?php echo e($email); ?>)</p>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Shown if the donation details can't be loaded -->
                    <p class="confirm-receipt mb-4">Your payment was received. A receipt will be sent to your email shortly.</p>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <a href="index.php" class="btn btn-send w-100">Return to Home</a>
                    </div>
                    <div class="col-sm-6">
                        <a href="<?php echo e($shareUrl); ?>" target="_blank" rel="noopener" class="btn btn-outline-send w-100">
                            <i class="bi bi-share me-2"></i>Share to Facebook
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php include 'include/footer.php' ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scroll Effect -->
    <script>
        window.addEventListener("scroll", function() {
            const navbar = document.querySelector(".custom-navbar");
            navbar.classList.toggle("scrolled", window.scrollY > 50);
        });
    </script>

</body>

</html>
