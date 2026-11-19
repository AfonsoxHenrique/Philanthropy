<?php
include 'supabase.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Load all published questions from the database
$faqs   = [];
$error  = "";
$result = supabaseRequest(
    "GET",
    "faqs",
    null,
    "?select=id,question,answer,sort_order&is_published=eq.true&order=sort_order.asc"
);

if ($result["code"] === 200 && is_array($result["data"])) {
    $faqs = $result["data"];
} else {
    $error = "FAQs could not be loaded right now. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>FAQ | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="faqHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Got Questions?</small>
            <h1 class="mt-2 mb-3">Frequently Asked Questions</h1>
            <p class="mx-auto">Find information about how we use your donations, tax receipts,
                volunteering and how we work with schools.</p>
        </div>
    </section>

    <!-- FAQ Accordion -->
    <section id="faq" class="homeSec">
        <div class="container-fluid">
            <?php if ($error !== ""): ?>
                <div class="alert alert-warning text-center"><?php echo e($error); ?></div>
            <?php elseif (empty($faqs)): ?>
                <p class="text-center text-muted">No FAQs have been published yet.</p>
            <?php else: ?>
                <div class="accordion faq-accordion mx-auto" id="faqAccordion">
                    <?php foreach ($faqs as $i => $faq): ?>
                        <?php $open = ($i === 0); ?>
                        <div class="accordion-item faq-item">
                            <h2 class="accordion-header" id="faqHeading<?php echo $i; ?>">
                                <button class="accordion-button faq-question <?php echo $open ? "" : "collapsed"; ?>"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#faqAnswer<?php echo $i; ?>"
                                    aria-expanded="<?php echo $open ? "true" : "false"; ?>"
                                    aria-controls="faqAnswer<?php echo $i; ?>">
                                    <?php echo e($faq["question"]); ?>
                                </button>
                            </h2>
                            <div id="faqAnswer<?php echo $i; ?>"
                                class="accordion-collapse collapse <?php echo $open ? "show" : ""; ?>"
                                aria-labelledby="faqHeading<?php echo $i; ?>">
                                <div class="accordion-body faq-answer">
                                    <?php echo e($faq["answer"]); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact CTA -->
    <section id="faqContact" class="homeSec text-center">
        <div class="container-fluid">
            <h2 class="mb-3">Still have some unanswered questions?</h2>
            <p class="mx-auto mb-4">Our team is happy to help with anything not covered here.
                Send us a message and we'll reply within 24 hours.</p>
            <a href="contact.php" class="btn btn-send px-4">Contact Our Team</a>
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
