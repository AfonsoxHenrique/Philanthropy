<?php
// Volunteer sign-ups are paused until the client has upcoming events.
// The previous form version is in Git history if it's needed again.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Volunteer | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?v=2" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Coming Soon -->
    <section id="volunteerSoon" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Volunteer With Us</small>
            <h1 class="mt-3 mb-4 mx-auto">Stay tuned for upcoming volunteer events</h1>
            <p class="mx-auto mb-0">We&rsquo;ll share opportunities to get involved here.</p>
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