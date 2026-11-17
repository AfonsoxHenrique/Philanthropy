<?php
// Organizers (replace with the client's real organizers)
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
    ]
];

// Initials for when there's no approved photo (e.g. "Dr. Robert Patel" -> "RP")
function initials($name)
{
    $words = explode(" ", str_replace("Dr. ", "", $name));
    return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About Us | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?v=2" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="aboutHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Who We Are</small>
            <h1 class="mt-2 mb-3">About Us</h1>
            <p class="mx-auto about-intro">Phill Anthropy Pty Ltd is an Australian-based charity dedicated to providing children in
                Cebu, Philippines, with the essential tools they need to learn and thrive.</p>
        </div>
    </section>

    <!-- Story -->
    <section id="aboutStory" class="homeSec pb-5">
        <div class="container-fluid">
            <div class="about-card mx-auto">
                <h2 class="mb-4">Supporting classrooms in Cebu</h2>
                <p>We coordinate everything from Australia, working directly with local elementary school teachers in Cebu to
                    find out what their classrooms need most. By listening to educators first, we ensure that the resources we
                    send go straight into the classrooms where they will make the most meaningful impact.</p>
                <p class="mb-0">Making a lasting difference takes time and consistency. Instead of providing one-off donations,
                    we focus on building long-term relationships with our partner schools so we can continue to support them
                    year after year.</p>
            </div>
        </div>
    </section>

    <!-- Journey -->
    <section id="journey" class="text-center">
        <div class="container-fluid">
            <h2 class="mb-3">Our Journey So Far</h2>
            <p class="mx-auto mb-0">We believe that the best way to help is through steady, deliberate steps. We stay by the
                side of our partner schools to ensure their classrooms are looked after for the long haul.</p>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="homeSec pb-5">
        <div class="container-fluid">
            <small class="eyebrow text-uppercase d-block text-center">The Leadership</small>
            <h2 class="text-center mt-2 mb-5">Meet Our Organizers</h2>

            <div class="row g-4 justify-content-center">
                <?php foreach ($organizers as $person): ?>
                    <!-- Organizer card -->
                    <div class="col-sm-6 col-lg-4">
                        <div class="card team-card h-100">
                            <?php if (file_exists(__DIR__ . "/" . $person["image"])): ?>
                                <img src="<?php echo $person["image"]; ?>" class="team-img" alt="<?php echo $person["name"]; ?>">
                            <?php else: ?>
                                <!-- No approved photo yet: show initials -->
                                <div class="team-avatar" aria-hidden="true">
                                    <span class="team-initials"><?php echo initials($person["name"]); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column p-4">
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