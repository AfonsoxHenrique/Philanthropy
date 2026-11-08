<?php
// NOTE: Draft wording based on OAIC privacy policy guidance.
// The client must review and approve the final text before launch.

$lastUpdated  = "3 November 2026";
$contactEmail = "phillanthropy.au@gmail.com";
$contactPhone = "+61 459 694 894";
$contactPost  = "44-46 Canterbury Road, Blackburn South, VIC";

// Policy sections (id is used for the sidebar links)
$sections = [
    [
        "id"    => "introduction",
        "title" => "Introduction",
        "body"  => [
            "Phill Anthropy (\"we\", \"our\" or \"the Trust\") respects the privacy of our donors, volunteers and school community members. This policy explains what personal information we collect through this website, how we use and protect it, and what choices you have.",
            "We aim to handle personal information in line with the Privacy Act 1988 (Cth) and the Australian Privacy Principles."
        ]
    ],
    [
        "id"    => "collection",
        "title" => "Information We Collect",
        "body"  => [
            "We only collect information we need. Depending on how you use the website, this may include:",
            [
                "Donation form: your name, email address, donation amount, frequency and any dedication message.",
                "Contact form: your name, email address, subject and message.",
                "Volunteer form: your name, contact details, preferred role and availability.",
                "Event registration: your name, email address and number of people attending."
            ],
            "We collect this information directly from you when you fill in a form on this website."
        ]
    ],
    [
        "id"    => "use",
        "title" => "How We Use Your Information",
        "body"  => [
            "We use your information to process donations and send receipts, reply to enquiries, manage volunteers and events, and report on how funds are used. We do not sell, rent or trade your personal information."
        ]
    ],
    [
        "id"    => "payments",
        "title" => "Payments",
        "body"  => [
            "Payments are handled by our secure payment provider. Your card details go directly to the provider and are never stored on this website or by Phill Anthropy."
        ]
    ],
    [
        "id"    => "storage",
        "title" => "Storage and Security",
        "body"  => [
            "Your information is stored securely with a third-party database provider (Supabase). The website uses HTTPS encryption, and only authorised staff can access personal information through a secure login.",
            "Some of our service providers may store data on servers outside Australia. [Confirm the countries where data is stored before launch.]"
        ]
    ],
    [
        "id"    => "cookies",
        "title" => "Cookies and Analytics",
        "body"  => [
            "This website may use cookies to keep it working properly. [Update this section if analytics tools such as Google Analytics are added.]"
        ]
    ],
    [
        "id"    => "retention",
        "title" => "How Long We Keep It",
        "body"  => [
            "We keep donation records for as long as required for tax and financial reporting. Other information, such as contact messages and event registrations, is deleted when it is no longer needed."
        ]
    ],
    [
        "id"    => "rights",
        "title" => "Your Rights and Consent",
        "body"  => [
            "By using our forms, you agree to us handling your information as described in this policy. You can ask to see, correct or delete the personal information we hold about you at any time by contacting us."
        ]
    ],
    [
        "id"    => "complaints",
        "title" => "Complaints",
        "body"  => [
            "If you're concerned about how we've handled your information, please contact us first so we can try to fix it. If you're not satisfied with our response, you can contact the Office of the Australian Information Commissioner (OAIC) at oaic.gov.au."
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
    <title>Privacy Policy | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?" rel="stylesheet">
</head>

<body data-bs-spy="scroll" data-bs-target="#policyNav" data-bs-root-margin="0px 0px -60%" tabindex="0">
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="privacyHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Legal &amp; Trust</small>
            <h1 class="mt-2 mb-3">Privacy Policy</h1>
            <p class="mx-auto mb-0">Last updated: <?php echo $lastUpdated; ?></p>
        </div>
    </section>

    <section id="privacy" class="homeSec">
        <div class="container-fluid">
            <div class="row g-5">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <nav id="policyNav" class="policy-nav" aria-label="Privacy Policy sections">
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
