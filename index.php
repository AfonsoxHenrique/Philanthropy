<?php
include 'supabase.php';

// ===== Fundraising goal settings (keep the same as donationPage.php) =====
$goalAmount = 20000;
$goalEnds   = "15 April 2027";

// ===== Fundraising progress =====
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

// These defaults are only used if Supabase can't be reached
$content = [
    "hero_title"    => "Helping Local Students Thrive",
    "hero_subtitle" => "School essentials. Brighter futures in Cebu.",
    "mission_line"  => "We fund school supplies and technology for students in Cebu, Philippines.",
    "programs"      => [
        ["icon" => "bi-book",       "title" => "Textbooks & Stationery", "meta" => "First delivery · April 2024",                "text" => "Books and supplies for Cebu classrooms."],
        ["icon" => "bi-pc-display", "title" => "Classroom Computers",    "meta" => "30 computers delivered · January 2026",     "text" => "Technology to support classroom learning."],
        ["icon" => "bi-wifi",       "title" => "School Internet Access", "meta" => "Added access points · September 2026",      "text" => "Wider internet coverage across the school."]
    ],
    "promise_title" => "Every dollar is tracked and reported.",
    "impact"        => [
        ["number" => "300+", "label" => "Students supported"],
        ["number" => "50+",  "label" => "Volunteers"],
        ["number" => "15",   "label" => "Events"]
    ],
    "quote"         => "",
    "cta_title"     => "Join Us — Every Contribution Makes a Difference",
    "cta_text"      => "Give a little. Help local children thrive."
];

$contentResult = supabaseRequest("GET", "homepage_content", null, "?select=*&id=eq.1");

if ($contentResult["code"] === 200 && is_array($contentResult["data"]) && !empty($contentResult["data"])) {
    $row = $contentResult["data"][0];
    foreach ($content as $key => $default) {
        if (!array_key_exists($key, $row) || $row[$key] === null) {
            continue;
        }
        if (is_array($default) === is_array($row[$key])) {
            $content[$key] = $row[$key];
        }
    }
}

$programs = $content["programs"];
$impact   = $content["impact"];
$quote    = trim($content["quote"]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Home | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?v=2" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Hero -->
    <section id="homeHero" class="d-flex align-items-center">
        <div class="container-fluid">
            <h1 class="mb-3"><?php echo htmlspecialchars($content["hero_title"]); ?></h1>
            <p class="hero-sub mb-4"><?php echo htmlspecialchars($content["hero_subtitle"]); ?></p>
            <a href="donationPage.php" class="btn cta-btn cta-primary">Donate Now</a>
        </div>
    </section>

    <!-- Mission -->
    <section id="homeMission" class="text-center">
        <div class="container-fluid">
            <p class="mission-line mx-auto mb-0"><?php echo htmlspecialchars($content["mission_line"]); ?></p>
        </div>
    </section>

    <!-- Programs -->
    <section id="programs" class="homeSec">
        <div class="container-fluid">
            <h2 class="text-center mb-5">Our Programs</h2>

            <div class="row g-4">
                <?php foreach ($programs as $program): ?>
                    <!-- Program card -->
                    <div class="col-md-4">
                        <div class="card program-card h-100">
                            <div class="card-body p-4">
                                <div class="program-icon mb-4">
                                    <i class="bi <?php echo htmlspecialchars($program["icon"] ?? ""); ?>" aria-hidden="true"></i>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($program["title"] ?? ""); ?></h5>
                                <?php if (!empty($program["meta"])): ?>
                                    <p class="program-meta mb-3"><?php echo htmlspecialchars($program["meta"]); ?></p>
                                <?php endif; ?>
                                <p class="program-text"><?php echo htmlspecialchars($program["text"] ?? ""); ?></p>
                                <a href="gallery.php" class="program-link">Learn more <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Fundraising progress -->
            <div class="card fundraising-card p-4 mt-5">
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
                    <small class="fund-deadline">Goal ends: <?php echo $goalEnds; ?></small>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Promise -->
    <section id="promise" class="text-center">
        <div class="container-fluid">
            <h2 class="mb-3"><?php echo htmlspecialchars($content["promise_title"]); ?></h2>
            <a href="faq.php" class="promise-link">See how donations are used <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    </section>

    <!-- Our Impact -->
    <section id="impact" class="homeSec pb-5">
        <div class="container-fluid">
            <h2 class="text-center mb-5">Our Impact</h2>

            <div class="row g-4 text-center">
                <?php foreach ($impact as $stat): ?>
                    <div class="col-md-4">
                        <div class="impact-number"><?php echo htmlspecialchars($stat["number"] ?? ""); ?></div>
                        <p class="impact-label mb-0"><?php echo htmlspecialchars($stat["label"] ?? ""); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($quote !== ""): ?>
                <!-- Quote -->
                <div class="impact-quote mx-auto mt-5 text-center">
                    <p class="mb-0">&ldquo;<?php echo htmlspecialchars($quote); ?>&rdquo;</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section id="callToAction" class="text-center py-5">
        <div class="container-fluid py-4">
            <h2 class="mb-3"><?php echo htmlspecialchars($content["cta_title"]); ?></h2>
            <p class="mb-4"><?php echo htmlspecialchars($content["cta_text"]); ?></p>
            <div class="d-flex justify-content-center flex-wrap gap-3">
                <a href="donationPage.php" class="btn cta-btn cta-primary">Donate</a>
                <a href="volunteer.php" class="btn cta-btn cta-secondary cta-outline">Volunteer With Us</a>
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