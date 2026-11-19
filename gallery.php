<?php
// Delivery milestones (photos must not show identifiable students or teachers)
$milestones = [
    [
        "date"  => "April 2024",
        "title" => "First textbook delivery",
        "image" => "assets/images/April2024.jpg",
        "alt"   => "Stacks of new English textbooks and boxes of chalk",
        "text"  => [
            "Coordinated our very first delivery of textbooks straight to the local teaching staff."
        ]
    ],
    [
        "date"  => "January 2026",
        "title" => "Introducing Computers",
        "image" => "assets/images/Jan2026.jpg",
        "alt"   => "New desktop computers set up on classroom desks",
        "text"  => [
            "Stepped up our support by delivering 30 computers to be installed across the school.",
            "Provided stationery supplies."
        ]
    ],
    [
        "date"  => "September 2026",
        "title" => "Refreshing Resources",
        "image" => "assets/images/Sep2026.jpg",
        "alt"   => "Certificate of recognition from Casili Elementary School to Phill Anthropy",
        "text"  => [
            "Expanded the school's internet coverage by installing additional access points.",
            "Provided additional stationery supplies."
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Gallery | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css?v=2" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="galleryHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Our Journey So Far</small>
            <h1 class="mt-2 mb-3">Gallery</h1>
            <p class="mx-auto gallery-intro">Thanks to the wonderful cooperation of local teachers, we have been able to complete
                three successful rounds of deliveries at Casili Elementary School.</p>
        </div>
    </section>

    <!-- Gallery -->
    <section id="gallery" class="homeSec pb-5">
        <div class="container-fluid">
            <div class="row g-4">
                <?php foreach ($milestones as $item): ?>
                    <!-- Milestone card -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card gallery-card h-100">
                            <img src="<?php echo $item["image"]; ?>" class="gallery-img" alt="<?php echo $item["alt"]; ?>" loading="lazy">
                            <div class="card-body p-4">
                                <p class="gallery-date mb-2"><?php echo $item["date"]; ?></p>
                                <h3 class="card-title mb-3"><?php echo $item["title"]; ?></h3>
                                <?php foreach ($item["text"] as $line): ?>
                                    <p class="gallery-text"><?php echo $line; ?></p>
                                <?php endforeach; ?>
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