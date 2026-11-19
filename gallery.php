<?php
include 'supabase.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$milestones = [];
$loadError  = "";

$result = supabaseRequest(
    "GET",
    "gallery_items",
    null,
    "?select=date_label,title,image_url,alt_text,body,sort_order&is_published=eq.true&order=sort_order.asc"
);

if ($result["code"] === 200 && is_array($result["data"])) {
    foreach ($result["data"] as $row) {
        $body = $row["body"] ?? [];
        if (is_string($body)) {
            $decoded = json_decode($body, true);
            $body = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($body)) {
            $body = [];
        }

        $milestones[] = [
            "date"  => $row["date_label"] ?? "",
            "title" => $row["title"] ?? "",
            "image" => $row["image_url"] ?? "",
            "alt"   => $row["alt_text"] ?? "",
            "text"  => $body
        ];
    }
} else {
    $loadError = "Gallery could not be loaded right now. Please try again later.";
}
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
    <?php include 'include/navbar.php' ?>

    <section id="galleryHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Our Journey So Far</small>
            <h1 class="mt-2 mb-3">Gallery</h1>
            <p class="mx-auto gallery-intro">
                Thanks to the wonderful cooperation of local teachers, we have been able to complete
                three successful rounds of deliveries at Casili Elementary School.
            </p>
        </div>
    </section>

    <section id="gallery" class="homeSec pb-5">
        <div class="container-fluid">
            <?php if ($loadError !== ""): ?>
                <div class="alert alert-warning text-center"><?php echo e($loadError); ?></div>
            <?php elseif (empty($milestones)): ?>
                <p class="text-center text-muted">No gallery items have been published yet.</p>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($milestones as $item): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card gallery-card h-100">
                                <img src="<?php echo e($item["image"]); ?>"
                                     class="gallery-img"
                                     alt="<?php echo e($item["alt"]); ?>"
                                     loading="lazy">
                                <div class="card-body p-4">
                                    <p class="gallery-date mb-2"><?php echo e($item["date"]); ?></p>
                                    <h3 class="card-title mb-3"><?php echo e($item["title"]); ?></h3>
                                    <?php foreach ($item["text"] as $line): ?>
                                        <p class="gallery-text"><?php echo e($line); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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