<?php
include 'supabase.php';

// Total raised (same logic as the homepage)
$result = supabaseRequest("GET", "donations", null, "?select=amount");

$totalRaised = 0;
if ($result["code"] === 200 && is_array($result["data"])) {
    foreach ($result["data"] as $row) {
        $totalRaised += floatval($row["amount"] ?? 0);
    }
}

// Core values
$values = [
    [
        "icon"  => "bi-heart",
        "title" => "Compassion",
        "text"  => "Approaching every struggle, food insecurity, or academic lack with an open mind and supportive heart."
    ],
    [
        "icon"  => "bi-shield-check",
        "title" => "Transparency",
        "text"  => "100% of public fundraising is accounted for. We publish regular reports outlining exactly where your dollars go."
    ],
    [
        "icon"  => "bi-people",
        "title" => "Community",
        "text"  => "No school is an island. We connect local small businesses, parents, and schools together to lift children collectively."
    ]
];

// Donation distribution (percentages should add up to 100)
$distribution = [
    ["label" => "School Supplies &amp; Tech",       "percent" => 45, "class" => "dist-green"],
    ["label" => "After-School Development",         "percent" => 25, "class" => "dist-orange"],
    ["label" => "Community Camp, Sports Events",    "percent" => 18, "class" => "dist-blue"],
    ["label" => "Operational Administration",       "percent" => 12, "class" => "dist-grey"]
];

// Organizers
$organizers = [
    [
        "name"  => "Sarah Jenkins",
        "role"  => "Co-Founder &amp; Chair",
        "bio"   => "Parent of two high schoolers. Founded the parent-led initiative after running fundraisers for supply drives.",
        "image" => "assets/images/team_sarah.jpg"
    ],
    [
        "name"  => "Dr. Robert Patel",
        "role"  => "Advisory Principal",
        "bio"   => "Retired middle school principal of 20 years. Advises on student resource needs and teacher safety.",
        "image" => "assets/images/team_robert.jpg"
    ],
    [
        "name"  => "Emily Chen",
        "role"  => "Treasurer &amp; Auditor",
        "bio"   => "Chartered accountant. Makes sure every penny is counted, balanced, and published to our community.",
        "image" => "assets/images/team_emily.jpg"
    ],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About Us | Phill Antrophy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="aboutHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Who We Are</small>
            <h1 class="mt-2 mb-3">About Our Trust</h1>
            <p class="mx-auto">A dedicated charity founded by parents and educators to support families and
                cultivate thriving local learning environments.</p>
        </div>
    </section>

    <!-- Mission Section -->
    <section id="mission" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <small class="eyebrow text-uppercase d-block text-center">The Foundation</small>
            <h2 class="text-center mb-5">Our Mission, Vision &amp; Values</h2>

            <div class="row g-4">
                <?php foreach ($values as $value): ?>
                    <!-- Value card -->
                    <div class="col-md-4">
                        <div class="card value-card h-100">
                            <div class="card-body p-4">
                                <div class="icon-circle mb-3">
                                    <i class="bi <?php echo $value["icon"]; ?>"></i>
                                </div>
                                <h5 class="card-title"><?php echo $value["title"]; ?></h5>
                                <p class="card-text"><?php echo $value["text"]; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Story Section -->
    <section id="story" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <div class="row align-items-center g-5">
                <div class="col-md-6">
                    <small class="eyebrow text-uppercase">How We Started</small>
                    <h2 class="mb-4">Why Phill Anthropy Exists</h2>
                    <p>Phill Anthropy started around a kitchen table in Spring Heights, when a group of parents
                        realised their kids' schools were running fundraisers just to cover pencils and paper.</p>
                    <p>We pooled what we could, bought supplies for one classroom, and published every receipt.
                        Today we support a whole cluster of neighbourhood schools, with the same rule we started with:
                        every dollar is tracked, and every family can see where it went.</p>
                </div>
                <div class="col-md-6 text-end">
                    <img src="assets/images/about_story.jpg" class="img-fluid story-img" alt="Parents and educators planning around a table">
                </div>
            </div>
        </div>
    </section>

    <!-- Distribution Section -->
    <section id="distribution" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <h2 class="mb-3">How Donations Are Distributed</h2>
                    <p class="mb-4">Our commitment is absolute efficiency. Over 85% of incoming capital goes directly to
                        first supply lines and activities. Here is our strategic distribution formula:</p>

                    <?php foreach ($distribution as $item): ?>
                        <!-- Distribution bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small class="dist-label"><?php echo $item["label"]; ?></small>
                                <small class="dist-percent"><?php echo $item["percent"]; ?>%</small>
                            </div>
                            <div class="progress dist-progress mt-1" role="progressbar"
                                aria-label="<?php echo $item["label"]; ?>"
                                aria-valuenow="<?php echo $item["percent"]; ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar <?php echo $item["class"]; ?>" style="width: <?php echo $item["percent"]; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-lg-5">
                    <div class="card transparency-card text-center">
                        <div class="card-body p-4">
                            <h5 class="mb-4">Guaranteed Transparency</h5>
                            <div class="icon-circle icon-circle-lg mx-auto mb-4">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <p class="fst-italic mb-0">"Phill Anthropy maintains an open bank ledger. Any donor can request
                                itemised receipts of purchase for regional schools."</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="d-flex align-items-center justify-content-center">
        <div class="container-fluid">
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="stat-number">$<?php echo number_format(max($totalRaised, 50000), 0); ?>+</div>
                    <small class="stat-label text-uppercase">Total Capital Raised</small>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-number">300+</div>
                    <small class="stat-label text-uppercase">Students Empowered</small>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-number">15</div>
                    <small class="stat-label text-uppercase">Educational Events</small>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-number">50+</div>
                    <small class="stat-label text-uppercase">Volunteer Stewards</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <small class="eyebrow text-uppercase d-block text-center">The Leadership</small>
            <h2 class="text-center mb-5">Meet Our Organizers</h2>

            <div class="row g-4">
                <?php foreach ($organizers as $person): ?>
                    <!-- Organizer card -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card team-card h-100">
                            <img src="<?php echo $person["image"]; ?>" class="team-img" alt="<?php echo $person["name"]; ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-1"><?php echo $person["name"]; ?></h5>
                                <small class="team-role text-uppercase mb-2"><?php echo $person["role"]; ?></small>
                                <p class="card-text"><?php echo $person["bio"]; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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