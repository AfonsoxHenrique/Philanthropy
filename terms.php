<?php
// NOTE: Draft Terms of Use wording. The client must review and approve
// the final text before launch (the team does not provide legal advice).

include 'supabase.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Defaults if the database is empty or offline
$lastUpdated  = "3 November 2026";
$contactEmail = "phillanthropy.au@gmail.com";
$contactPhone = "+61 459 694 894";
$contactPost  = "44-46 Canterbury Road, Blackburn South, VIC";
$sections     = [];
$loadError    = "";

// ----- Settings (last updated + contact) -----
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
        if ($key === "terms_last_updated") {
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

// ----- Terms sections -----
$sectionsResult = supabaseRequest(
    "GET",
    "terms_sections",
    null,
    "?select=section_key,title,body,sort_order&is_published=eq.true&order=sort_order.asc"
);

if ($sectionsResult["code"] === 200 && is_array($sectionsResult["data"])) {
    foreach ($sectionsResult["data"] as $row) {
        $body = $row["body"] ?? [];

        // Supabase may return JSON as a string or already decoded
        if (is_string($body)) {
            $decoded = json_decode($body, true);
            $body = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($body)) {
            $body = [];
        }

        $sectionKey = $row["section_key"] ?? "";

        // Build contact section from settings if body is empty
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
    $loadError = "Terms of Use could not be loaded right now. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Terms of Use | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?" rel="stylesheet">
</head>

<body data-bs-spy="scroll" data-bs-target="#policyNav" data-bs-root-margin="0px 0px -60%" tabindex="0">
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="termsHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Legal &amp; Trust</small>
            <h1 class="mt-2 mb-3">Terms of Use</h1>
            <p class="mx-auto mb-0">Last updated: <?php echo e($lastUpdated); ?></p>
        </div>
    </section>

    <section id="terms" class="homeSec">
        <div class="container-fluid">
            <?php if ($loadError !== ""): ?>
                <div class="alert alert-warning text-center"><?php echo e($loadError); ?></div>
            <?php elseif (empty($sections)): ?>
                <p class="text-center text-muted">No terms sections have been published yet.</p>
            <?php else: ?>
                <div class="row g-5">
                    <!-- Sidebar -->
                    <div class="col-lg-3">
                        <nav id="policyNav" class="policy-nav" aria-label="Terms of Use sections">
                            <small class="eyebrow text-uppercase d-block mb-3">Sections</small>
                            <?php foreach ($sections as $i => $section): ?>
                                <a class="nav-link" href="#<?php echo e($section["id"]); ?>">
                                    <?php echo ($i + 1) . ". " . e($section["title"]); ?>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    </div>

                    <!-- Policy content -->
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
                                        <?php if (($section["id"] ?? "") === "privacy"): ?>
                                            <p>
                                                We handle your personal information as described in our
                                                <a href="privacypolicy.php">Privacy Policy</a>.
                                            </p>
                                        <?php else: ?>
                                            <p><?php echo e($part); ?></p>
                                        <?php endif; ?>
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