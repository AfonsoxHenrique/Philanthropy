<?php
// Filter categories (key => label shown on the pill)
$categories = [
    "events"     => "Local Events",
    "campaigns"  => "Seasonal Campaigns",
    "deliveries" => "School Deliveries"
];

// Gallery items
$galleryItems = [
    [
        "title"    => "Spring Backpack Drive",
        "date"     => "March 2026",
        "category" => "deliveries",
        "image"    => "assets/images/gallery_backpack.jpg"
    ],
    [
        "title"    => "Community Garden Day",
        "date"     => "April 2025",
        "category" => "events",
        "image"    => "assets/images/gallery_garden.jpg"
    ],
    [
        "title"    => "Annual Spring Benefit Concert",
        "date"     => "April 2025",
        "category" => "events",
        "image"    => "assets/images/gallery_concert.jpg"
    ],
    [
        "title"    => "After-School Hot Fuel Program",
        "date"     => "February 2025",
        "category" => "campaigns",
        "image"    => "assets/images/gallery_hotfuel.jpg"
    ],
    [
        "title"    => "Creative Arts Funding Drop",
        "date"     => "January 2026",
        "category" => "deliveries",
        "image"    => "assets/images/gallery_arts.jpg"
    ],
    [
        "title"    => "District Charity Fun Run",
        "date"     => "November 2025",
        "category" => "events",
        "image"    => "assets/images/gallery_funrun.jpg"
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Gallery | Phill Antrophy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="galleryHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">In The Frame</small>
            <h1 class="mt-2 mb-3">Our Community Gallery</h1>
            <p class="mx-auto">Visual moments showing the real, direct impact of your charity drives,
                neighborhood school supplies, and community gardens.</p>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">

            <!-- Filter pills -->
            <div class="d-flex justify-content-center mb-5">
                <div class="gallery-filter d-flex flex-wrap justify-content-center gap-1">
                    <button type="button" class="btn filter-btn active" data-filter="all" aria-pressed="true">All Photos</button>
                    <?php foreach ($categories as $key => $label): ?>
                        <button type="button" class="btn filter-btn" data-filter="<?php echo $key; ?>" aria-pressed="false"><?php echo $label; ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Photo grid -->
            <div class="row g-4">
                <?php foreach ($galleryItems as $item): ?>
                    <!-- Gallery card -->
                    <div class="col-sm-6 col-lg-4 gallery-item" data-category="<?php echo $item["category"]; ?>">
                        <div class="card gallery-card h-100">
                            <img src="<?php echo $item["image"]; ?>" class="gallery-img" alt="<?php echo $item["title"]; ?>">
                            <div class="card-body">
                                <h5 class="card-title mb-1"><?php echo $item["title"]; ?></h5>
                                <small class="gallery-meta text-uppercase">
                                    <?php echo $item["date"]; ?> &bull; <?php echo $categories[$item["category"]] ?? ""; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Shown when a filter has no photos -->
            <p id="galleryEmpty" class="text-center mt-4 d-none">No photos in this category yet. Check back after our next event.</p>
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
    <!-- Gallery Filter -->
    <script>
        const filterButtons = document.querySelectorAll(".filter-btn");
        const galleryItems = document.querySelectorAll(".gallery-item");
        const emptyMessage = document.getElementById("galleryEmpty");

        filterButtons.forEach(function(button) {
            button.addEventListener("click", function() {
                const filter = button.dataset.filter;
                let visibleCount = 0;

                // Highlight the clicked pill
                filterButtons.forEach(function(btn) {
                    btn.classList.remove("active");
                    btn.setAttribute("aria-pressed", "false");
                });
                button.classList.add("active");
                button.setAttribute("aria-pressed", "true");

                // Show or hide cards
                galleryItems.forEach(function(item) {
                    const show = filter === "all" || item.dataset.category === filter;
                    item.classList.toggle("d-none", !show);
                    if (show) visibleCount++;
                });

                emptyMessage.classList.toggle("d-none", visibleCount > 0);
            });
        });
    </script>

</body>

</html>