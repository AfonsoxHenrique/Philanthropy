<?php

include 'supabase.php';

// Safe output helper (skipped if another file already has one)
if (!function_exists("e")) {
    function e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
    }
}

// ===== Fundraising goal settings =====
$goalAmount = 20000;
$goalEnds   = "15 April 2027";

// ===== Handle form submit =====
$errors = [];
$old    = [
    "frequency"  => "one-time",
    "amount"     => "50",
    "name"       => "",
    "email"      => "",
    "dedication" => ""
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old["frequency"]  = ($_POST["frequency"] ?? "") === "monthly" ? "monthly" : "one-time";
    $old["amount"]     = trim($_POST["amount"] ?? "");
    $old["name"]       = trim($_POST["name"] ?? "");
    $old["email"]      = trim($_POST["email"] ?? "");
    $old["dedication"] = trim($_POST["dedication"] ?? "");
    $agreed            = isset($_POST["agree"]);

    // Validation
    $amount = floatval($old["amount"]);
    if (!is_numeric($old["amount"]) || $amount < 1) {
        $errors["amount"] = "Enter an amount of $1 or more.";
    } elseif ($amount > 100000) {
        $errors["amount"] = "For donations over $100,000, please contact our team.";
    }
    if ($old["name"] === "") {
        $errors["name"] = "Enter your name.";
    }
    if (!filter_var($old["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email address, e.g. name@example.com.";
    }
    if (strlen($old["dedication"]) > 200) {
        $errors["dedication"] = "Keep the dedication under 200 characters.";
    }
    if (!$agreed) {
        $errors["agree"] = "Tick the box to agree to the Privacy Policy.";
    }

    if (empty($errors)) {
        // TODO: Send the donor to the payment gateway here (task 4.1).
        // Only save the donation after the payment is confirmed.
        $newDonation = [
            "amount"     => round($amount, 2),
            "frequency"  => $old["frequency"],
            "name"       => $old["name"],
            "email"      => $old["email"],
            "dedication" => $old["dedication"]
        ];

        $insert = supabaseRequest("POST", "donations", $newDonation, "");

        if ($insert["code"] === 200 || $insert["code"] === 201) {
            $newId = $insert["data"][0]["id"] ?? "";
            header("Location: donation-confirmed.php" . ($newId !== "" ? "?id=" . urlencode($newId) : ""));
            exit;
        } else {
            $errors["form"] = "Your donation couldn't be saved. Please try again, or contact us if it keeps happening.";
        }
    }
}

// ===== Fundraising progress (same logic as the homepage) =====
$result = supabaseRequest("GET", "donations", null, "?select=amount");

$totalRaised   = 0;
$donationCount = 0;
if ($result["code"] === 200 && is_array($result["data"])) {
    foreach ($result["data"] as $row) {
        $totalRaised += floatval($row["amount"] ?? 0);
    }
    $donationCount = count($result["data"]);
}
$percentFunded = $goalAmount > 0 ? min(100, round(($totalRaised / $goalAmount) * 100)) : 0;

// ===== Impact list =====
$impacts = [
    ["amount" => 10,  "text" => "Buys a sketchbook, colours and a brush set for the creative arts program."],
    ["amount" => 25,  "text" => "Supplies a warm winter coat for a child in need."],
    ["amount" => 50,  "text" => "Provides after-school tutoring kits and maths app licences for five students."],
    ["amount" => 100, "text" => "Funds healthy fruit and lunch top-ups for a whole primary class."]
];

// ===== Preset amounts =====
$presetAmounts = [10, 25, 50, 100];

// ===== Accepted payment methods =====
$paymentMethods = [
    ["icon" => "bi-credit-card",         "label" => "Visa"],
    ["icon" => "bi-credit-card-2-front", "label" => "Mastercard"],
    ["icon" => "bi-paypal",              "label" => "PayPal"],
    ["icon" => "bi-apple",               "label" => "Apple Pay"],
    ["icon" => "bi-google",              "label" => "Google Pay"]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Donate | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="donateHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Support a Child</small>
            <h1 class="mt-2 mb-3">Make a Difference Today</h1>
            <p class="mx-auto">Your donation goes directly to school supplies, healthy meals and
                safe activities for students in our local schools.</p>
        </div>
    </section>

    <section id="donate" class="homeSec">
        <div class="container-fluid">

            <!-- Fundraising Progress (same card as the homepage) -->
            <div class="card fundraising-card p-4 mb-5">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <small class="fund-label text-uppercase">Current fundraising status</small>
                        <div class="mt-1">
                            <span class="fund-raised">$<?php echo number_format($totalRaised, 0); ?></span>
                            <span class="fund-goal">raised of $<?php echo number_format($goalAmount, 0); ?> goal</span>
                        </div>
                    </div>
                    <span class="badge rounded-pill fund-badge"><?php echo $percentFunded; ?>% Funded</span>
                </div>

                <div class="progress fund-progress my-4" role="progressbar" aria-label="Fundraising progress"
                    aria-valuenow="<?php echo $percentFunded; ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar fund-bar" style="width: <?php echo $percentFunded; ?>%"></div>
                </div>

                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <small class="fund-note">Supported by <?php echo $donationCount; ?> donations from families and local businesses</small>
                    <small class="fund-deadline">Goal ends: <?php echo e($goalEnds); ?></small>
                </div>
            </div>

            <div class="row g-5">
                <!-- Impact List -->
                <div class="col-lg-6">
                    <h2 class="impact-title mb-4">How Your Donation Helps Students</h2>
                    <?php foreach ($impacts as $impact): ?>
                        <!-- Impact item -->
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <span class="impact-amount">$<?php echo $impact["amount"]; ?></span>
                            <p class="impact-text mb-0"><?php echo e($impact["text"]); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Donation Form -->
                <div class="col-lg-6">
                    <div class="card form-card p-4 p-md-5">
                        <h2 class="form-title mb-4">Choose Your Gift</h2>
                        <div>
                        <div data-zeffy-embed data-form-url="/embed/donation-form/donate-to-help-schools-in-philippines"></div>
                        <div data-zeffy-embed-fallback style="display:none;">
                            <div style="position:relative;overflow:hidden;height:450px;width:100%;"><iframe title='Donation form powered by Zeffy' style='position: absolute; border: 0; top:0;left:0;bottom:0;right:0;width:100%;height:100%' data-zeffy-embed-src='https://www.zeffy.com/embed/donation-form/donate-to-help-schools-in-philippines' allowpaymentrequest allowTransparency="true"></iframe></div>
                        </div>
                        <script
                            src="https://www.zeffy.com/embed/v2/zeffy-embed.js"
                            onerror="document.querySelectorAll('[data-zeffy-embed-fallback]').forEach(function(el){el.style.display='block';el.querySelectorAll('iframe[data-zeffy-embed-src]').forEach(function(f){f.src=f.getAttribute('data-zeffy-embed-src');});});">
                        </script>
                        </div>
                        <?php if (isset($errors["form"])): ?>
                            <div class="alert alert-danger" role="alert"><?php echo e($errors["form"]); ?></div>
                        <?php endif; ?>

                            <div class="secure-note mb-4">
                                <i class="bi bi-lock me-2" aria-hidden="true"></i>Payments are processed securely and your card details are never stored on this website.
                            </div>
                        </form>
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
    <!-- Donation Form -->
    <script>
        const amountInput = document.getElementById("amount");
        const amountButtons = document.querySelectorAll(".amount-btn");
        const submitButton = document.getElementById("donateSubmit");
        const frequencyInputs = document.querySelectorAll("input[name='frequency']");

        // Update the button text with the amount and frequency
        function updateSubmitText() {
            const amount = parseFloat(amountInput.value) || 0;
            const monthly = document.getElementById("freqMonthly").checked;
            const label = monthly ? "Start Monthly Donation of $" : "Complete Donation of $";
            submitButton.innerHTML = label + amount.toFixed(2) + ' <i class="bi bi-heart ms-2" aria-hidden="true"></i>';
        }

        // Preset amount buttons fill in the amount field
        amountButtons.forEach(function(button) {
            button.addEventListener("click", function() {
                amountButtons.forEach(function(b) { b.classList.remove("active"); });
                button.classList.add("active");
                amountInput.value = button.dataset.amount;
                updateSubmitText();
            });
        });

        // Typing a custom amount clears the preset selection
        amountInput.addEventListener("input", function() {
            amountButtons.forEach(function(b) {
                b.classList.toggle("active", parseFloat(b.dataset.amount) === parseFloat(amountInput.value));
            });
            updateSubmitText();
        });

        frequencyInputs.forEach(function(input) {
            input.addEventListener("change", updateSubmitText);
        });

        updateSubmitText();
    </script>

</body>

</html>
