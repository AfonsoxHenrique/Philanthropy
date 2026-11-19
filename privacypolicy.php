<?php
// NOTE: Draft wording based on OAIC privacy policy guidance.
// The client must review and approve the final text before launch.

include 'supabase.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Defaults if DB is empty
$lastUpdated  = "3 November 2026";
$contactEmail = "phillanthropy.au@gmail.com";
$contactPhone = "+61 459 694 894";
$contactPost  = "44-46 Canterbury Road, Blackburn South, VIC";
$sections     = [];
$loadError    = "";

// Settings
$settingsResult = supabaseRequest(
    "GET",
    "site_settings",
    null,
    "?select=setting_key,setting_value"
);

if ($settingsResult["code"] === 200 && is_array($settingsResult["data"])) {
    foreach ($settingsResult["data"] as $row) {
        $key = $row["setting_key"] ?? "";
        $val = $row["setting_value"] ?? "";
        if ($key === "privacy_last_updated") {
            $lastUpdated = $val;
        }
        if ($key === "contact_email") {
            $contactEmail = $val;
        }
        if ($key === "contact_phone") {
            $contactPhone = $val;
        }
        if ($key === "contact_post") {
            $contactPost = $val;
        }
    }
}

// Sections
$sectionsResult = supabaseRequest(
    "GET",
    "privacy_sections",
    null,
    "?select=section_key,title,body,sort_order&is_published=eq.true&order=sort_order.asc"
);

if ($sectionsResult["code"] === 200 && is_array($sectionsResult["data"])) {
    foreach ($sectionsResult["data"] as $row) {
        $body = $row["body"] ?? [];

        if (is_string($body)) {
            $decoded = json_decode($body, true);
            $body = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($body)) {
            $body = [];
        }

        $sectionKey = $row["section_key"] ?? "";

        if ($sectionKey === "contact" && empty($body)) {
            $body = [[
                "Email: " . $contactEmail,
                "Phone: " . $contactPhone,
                "Post: " . $contactPost
            ]];
        }

        $sections[] = [
            "id"    => $sectionKey,
            "title" => $row["title"] ?? "",
            "body"  => $body
        ];
    }
} else {
    $loadError = "Privacy Policy could not be loaded right now. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Privacy Policy | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?" rel="stylesheet">
</head>

<body data-bs-spy="scroll" data-bs-target="#policyNav" data-bs-root-margin="0px 0px -60%" tabindex="0">
    <?php include 'include/navbar.php' ?>

    <section id="privacyHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Legal &amp; Trust</small>
            <h1 class="mt-2 mb-3">Privacy Policy</h1>
            <p class="mx-auto mb-0">Last updated: <?php echo e($lastUpdated); ?></p>
        </div>
    </section>

    <section id="privacy" class="homeSec">
        <div class="container-fluid">
            <?php if ($loadError !== ""): ?>
                <div class="alert alert-warning text-center"><?php echo e($loadError); ?></div>
            <?php elseif (empty($sections)): ?>
                <p class="text-center text-muted">No privacy policy sections have been published yet.</p>
            <?php else: ?>
                <div class="row g-5">
                    <div class="col-lg-3">
                        <nav id="policyNav" class="policy-nav" aria-label="Privacy Policy sections">
                            <small class="eyebrow text-uppercase d-block mb-3">Sections</small>
                            <?php foreach ($sections as $i => $section): ?>
                                <a class="nav-link" href="#<?php echo e($section["id"]); ?>">
                                    <?php echo ($i + 1) . ". " . e($section["title"]); ?>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    </div>

                    <div class="col-lg-9">
                        <?php foreach ($sections as $i => $section): ?>
                            <article id="<?php echo e($section["id"]); ?>" class="policy-block">
                                <h2><?php echo ($i + 1) . ". " . e($section["title"]); ?></h2>
                                <?php foreach ($section["body"] as $part): ?>
                                    <?php if (is_array($part)): ?>
                                        <ul>
                                            <?php foreach ($part as $item): ?>
                                                <li><?php echo e($item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p><?php echo e($part); ?></p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'include/footer.php' ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener("scroll", function() {
            const navbar = document.querySelector(".custom-navbar");
            if (navbar) {
                navbar.classList.toggle("scrolled", window.scrollY > 50);
            }
        });
    </script>
</body>

</html>