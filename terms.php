<?php
// NOTE: Draft Terms of Use wording. The client must review and approve
// the final text before launch (the team does not provide legal advice).

$lastUpdated  = "3 November 2026";
$contactEmail = "phillanthropy.au@gmail.com";
$contactPhone = "+61 459 694 894";
$contactPost  = "44-46 Canterbury Road, Blackburn South, VIC";

// Terms sections (id is used for the sidebar links)
$sections = [
    [
        "id"    => "acceptance",
        "title" => "Accepting These Terms",
        "body"  => [
            "These Terms of Use apply to the Phill Anthropy website, run by Phill Anthropy Ltd (ABN 85 675 428 896). By using this website, you agree to these terms. If you don't agree, please don't use the website."
        ]
    ],
    [
        "id"    => "use",
        "title" => "Using the Website",
        "body"  => [
            "You agree to use this website only for lawful purposes. You must not:",
            [
                "Submit false, misleading or someone else's information in our forms.",
                "Try to access parts of the website or data you're not authorised to use.",
                "Upload anything harmful, such as viruses or spam."
            ]
        ]
    ],
    [
        "id"    => "donations",
        "title" => "Donations",
        "body"  => [
            "All donations are voluntary. A receipt is emailed to you after each successful payment.",
            "Donations are generally not refundable. If you've made a mistake, such as donating the wrong amount, please contact us within 14 days and we'll do our best to help.",
            "You can change or cancel a monthly donation at any time by contacting us."
        ]
    ],
    [
        "id"    => "volunteering",
        "title" => "Volunteering and Events",
        "body"  => [
            "Submitting a volunteer or event form is an expression of interest only and doesn't guarantee a place. Some volunteer roles that involve working with children require a valid Working with Children Check."
        ]
    ],
    [
        "id"    => "content",
        "title" => "Website Content",
        "body"  => [
            "We try to keep the information on this website accurate and up to date, but we can't guarantee it's always complete or current. Fundraising figures and event details may change.",
            "All logos, text and images on this website belong to Phill Anthropy or are used with permission. Please don't copy or reuse them without asking us first."
        ]
    ],
    [
        "id"    => "links",
        "title" => "Links to Other Websites",
        "body"  => [
            "This website may link to other websites, such as our payment provider and social media pages. We're not responsible for the content or privacy practices of those websites."
        ]
    ],
    [
        "id"    => "privacy",
        "title" => "Privacy",
        "body"  => [
            "We handle your personal information as described in our <a href=\"privacypolicy.php\">Privacy Policy</a>."
        ]
    ],
    [
        "id"    => "liability",
        "title" => "Limitation of Liability",
        "body"  => [
            "To the extent allowed by law, Phill Anthropy is not liable for any loss or damage arising from your use of this website. Nothing in these terms limits your rights under the Australian Consumer Law."
        ]
    ],
    [
        "id"    => "changes",
        "title" => "Changes to These Terms",
        "body"  => [
            "We may update these terms from time to time. The \"Last updated\" date at the top of this page shows when they were last changed."
        ]
    ],
    [
        "id"    => "law",
        "title" => "Governing Law",
        "body"  => [
            "These terms are governed by the laws of Victoria, Australia."
        ]
    ],
    [
        "id"    => "contact",
        "title" => "Contact Us",
        "body"  => [
            [
                "Email: " . $contactEmail,
                "Phone: " . $contactPhone,
                "Post: " . $contactPost
            ]
        ]
    ]
];
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
            <p class="mx-auto mb-0">Last updated: <?php echo $lastUpdated; ?></p>
        </div>
    </section>

    <section id="terms" class="homeSec">
        <div class="container-fluid">
            <div class="row g-5">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <nav id="policyNav" class="policy-nav" aria-label="Terms of Use sections">
                        <small class="eyebrow text-uppercase d-block mb-3">Sections</small>
                        <?php foreach ($sections as $i => $section): ?>
                            <a class="nav-link" href="#<?php echo $section["id"]; ?>"><?php echo ($i + 1) . ". " . $section["title"]; ?></a>
                        <?php endforeach; ?>
                    </nav>
                </div>

                <!-- Policy content -->
                <div class="col-lg-9">
                    <?php foreach ($sections as $i => $section): ?>
                        <!-- Policy section -->
                        <article id="<?php echo $section["id"]; ?>" class="policy-block">
                            <h2><?php echo ($i + 1) . ". " . $section["title"]; ?></h2>
                            <?php foreach ($section["body"] as $part): ?>
                                <?php if (is_array($part)): ?>
                                    <ul>
                                        <?php foreach ($part as $item): ?>
                                            <li><?php echo $item; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p><?php echo $part; ?></p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </article>
                    <?php endforeach; ?>
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
