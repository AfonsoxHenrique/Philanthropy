<?php
include 'supabase.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function initials($name)
{
    $clean = str_replace("Dr. ", "", $name);
    $words = preg_split('/\s+/', trim($clean));
    $first = $words[0] ?? "";
    $last  = $words[count($words) - 1] ?? "";
    return strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
}

// Defaults if DB is offline
$copy = [
    "about_hero_title"    => "About Us",
    "about_hero_text"     => "Phill Anthropy Pty Ltd is an Australian-based charity dedicated to providing children in Cebu, Philippines, with the essential tools they need to learn and thrive.",
    "about_story_title"   => "Supporting classrooms in Cebu",
    "about_story_p1"      => "We coordinate everything from Australia, working directly with local elementary school teachers in Cebu to find out what their classrooms need most. By listening to educators first, we ensure that the resources we send go straight into the classrooms where they will make the most meaningful impact.",
    "about_story_p2"      => "Making a lasting difference takes time and consistency. Instead of providing one-off donations, we focus on building long-term relationships with our partner schools so we can continue to support them year after year.",
    "about_journey_title" => "Our Journey So Far",
    "about_journey_text"  => "We believe that the best way to help is through steady, deliberate steps. We stay by the side of our partner schools to ensure their classrooms are looked after for the long haul.",
    "about_team_eyebrow"  => "The Leadership",
    "about_team_title"    => "Meet Our Organizers"
];

$settingsResult = supabaseRequest(
    "GET",
    "site_settings",
    null,
    "?select=setting_key,setting_value&setting_key=like.about_%"
);

if (($settingsResult["code"] ?? 0) === 200 && is_array($settingsResult["data"] ?? null)) {
    foreach ($settingsResult["data"] as $row) {
        $key = $row["setting_key"] ?? "";
        if ($key !== "" && array_key_exists($key, $copy)) {
            $copy[$key] = $row["setting_value"] ?? $copy[$key];
        }
    }
}

$organizers = [];
$teamResult = supabaseRequest(
    "GET",
    "team_members",
    null,
    "?select=name,role,bio,image_url&is_published=eq.true&order=sort_order.asc"
);

if (($teamResult["code"] ?? 0) === 200 && is_array($teamResult["data"] ?? null)) {
    foreach ($teamResult["data"] as $row) {
        $organizers[] = [
            "name"  => $row["name"] ?? "",
            "role"  => $row["role"] ?? "",
            "bio"   => $row["bio"] ?? "",
            "image" => $row["image_url"] ?? ""
        ];
    }
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
    <?php include 'include/navbar.php' ?>

    <section id="aboutHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Who We Are</small>
            <h1 class="mt-2 mb-3"><?php echo e($copy["about_hero_title"]); ?></h1>
            <p class="mx-auto about-intro"><?php echo e($copy["about_hero_text"]); ?></p>
        </div>
    </section>

    <section id="aboutStory" class="homeSec pb-5">
        <div class="container-fluid">
            <div class="about-card mx-auto">
                <h2 class="mb-4"><?php echo e($copy["about_story_title"]); ?></h2>
                <p><?php echo e($copy["about_story_p1"]); ?></p>
                <p class="mb-0"><?php echo e($copy["about_story_p2"]); ?></p>
            </div>
        </div>
    </section>

    <section id="journey" class="text-center">
        <div class="container-fluid">
            <h2 class="mb-3"><?php echo e($copy["about_journey_title"]); ?></h2>
            <p class="mx-auto mb-0"><?php echo e($copy["about_journey_text"]); ?></p>
        </div>
    </section>

    <section id="team" class="homeSec pb-5">
        <div class="container-fluid">
            <small class="eyebrow text-uppercase d-block text-center"><?php echo e($copy["about_team_eyebrow"]); ?></small>
            <h2 class="text-center mt-2 mb-5"><?php echo e($copy["about_team_title"]); ?></h2>

            <div class="row g-4 justify-content-center">
                <?php if (empty($organizers)): ?>
                    <p class="text-center text-muted">Team details coming soon.</p>
                <?php endif; ?>

                <?php foreach ($organizers as $person): ?>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card team-card h-100">
                            <?php
                            $img = $person["image"] ?? "";
                            $hasLocalFile = $img !== "" && !preg_match('#^https?://#', $img) && file_exists(__DIR__ . "/" . $img);
                            $hasRemote    = $img !== "" && preg_match('#^https?://#', $img);
                            ?>
                            <?php if ($hasLocalFile || $hasRemote): ?>
                                <img src="<?php echo e($img); ?>" class="team-img" alt="<?php echo e($person["name"]); ?>">
                            <?php else: ?>
                                <div class="team-avatar" aria-hidden="true">
                                    <span class="team-initials"><?php echo e(initials($person["name"])); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column p-4">
                                <h5 class="card-title mb-1"><?php echo e($person["name"]); ?></h5>
                                <small class="team-role text-uppercase mb-2"><?php echo e($person["role"]); ?></small>
                                <p class="card-text"><?php echo e($person["bio"]); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'include/footer.php' ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener("scroll", function() {
            const navbar = document.querySelector(".custom-navbar");
            if (navbar) navbar.classList.toggle("scrolled", window.scrollY > 50);
        });
    </script>
</body>

</html>