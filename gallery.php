<?php
include 'supabase.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$albums    = [];
$loadError = "";

// Milestones plus their photos in one request
$result = supabaseRequest(
    "GET",
    "gallery_items",
    null,
    "?select=id,date_label,title,image_url,alt_text,body,sort_order,cover_photo_id,"
        . "gallery_photos(id,image_url,thumb_url,alt_text,caption,sort_order)"
        . "&is_published=eq.true&order=sort_order.asc&gallery_photos.order=sort_order.asc"
);

if ($result["code"] === 200 && is_array($result["data"])) {
    foreach ($result["data"] as $row) {
        $body = $row["body"] ?? [];
        if (is_string($body)) {
            $decoded = json_decode($body, true);
            $body = is_array($decoded) ? $decoded : [$body];
        }
        if (!is_array($body)) {
            $body = [];
        }

        $title  = $row["title"] ?? "";
        $photos = [];
        $start  = 0;

        foreach (($row["gallery_photos"] ?? []) as $i => $photo) {
            $alt = trim($photo["alt_text"] ?? "");
            $photos[] = [
                "src"     => $photo["image_url"] ?? "",
                "thumb"   => $photo["thumb_url"] ?? ($photo["image_url"] ?? ""),
                "alt"     => $alt !== "" ? $alt : $title . " (photo " . ($i + 1) . ")",
                "caption" => $photo["caption"] ?? ""
            ];
            if ((string) $photo["id"] === (string) ($row["cover_photo_id"] ?? "")) {
                $start = $i;
            }
        }

        // Older milestones that only have the single image_url
        if (empty($photos) && !empty($row["image_url"])) {
            $photos[] = [
                "src"     => $row["image_url"],
                "thumb"   => $row["image_url"],
                "alt"     => $row["alt_text"] ?? $title,
                "caption" => ""
            ];
        }

        // Nothing to show without photos
        if (empty($photos)) {
            continue;
        }

        $albums[] = [
            "date"   => $row["date_label"] ?? "",
            "title"  => $title,
            "text"   => $body,
            "start"  => $start,
            "photos" => $photos
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
    <link href="style/master.css?v=3" rel="stylesheet">
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
            <?php elseif (empty($albums)): ?>
                <p class="text-center text-muted">No gallery items have been published yet.</p>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($albums as $index => $album): ?>
                        <?php
                        $count = count($album["photos"]);
                        $cover = $album["photos"][$album["start"]];
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card gallery-card h-100">
                                <!-- Clicking the photo opens the slideshow -->
                                <button type="button" class="gallery-open" data-album="<?php echo $index; ?>"
                                    aria-label="View <?php echo $count === 1 ? "the photo" : "all " . $count . " photos"; ?> from <?php echo e($album["title"]); ?>">
                                    <img src="<?php echo e($cover["thumb"]); ?>"
                                        class="gallery-img"
                                        alt=""
                                        loading="lazy">
                                    <span class="gallery-count">
                                        <i class="bi bi-images" aria-hidden="true"></i>
                                        <?php echo $count === 1 ? "View photo" : $count . " photos"; ?>
                                    </span>
                                </button>
                                <div class="card-body p-4">
                                    <p class="gallery-date mb-2"><?php echo e($album["date"]); ?></p>
                                    <h3 class="card-title mb-3"><?php echo e($album["title"]); ?></h3>
                                    <?php foreach ($album["text"] as $line): ?>
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

    <!-- Photo slideshow (filled in by the script below) -->
    <div class="modal fade gallery-lightbox" id="galleryLightbox" tabindex="-1" aria-labelledby="lightboxTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header border-0 align-items-start">
                    <div>
                        <p class="lightbox-date mb-1" id="lightboxDate"></p>
                        <h2 class="modal-title h5 mb-0" id="lightboxTitle"></h2>
                    </div>
                    <span class="lightbox-counter ms-auto me-3" id="lightboxCounter" aria-live="polite"></span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="lightboxCarousel" class="carousel slide">
                        <div class="carousel-inner" id="lightboxSlides"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous photo</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next photo</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo data for the slideshow -->
    <script type="application/json" id="galleryData">
        <?php echo json_encode($albums, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    </script>

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

    <!-- Gallery slideshow -->
    <script>
        (function() {
            const dataEl = document.getElementById("galleryData");
            if (!dataEl) return;

            const albums = JSON.parse(dataEl.textContent);
            const modalEl = document.getElementById("galleryLightbox");
            const carouselEl = document.getElementById("lightboxCarousel");
            const slidesEl = document.getElementById("lightboxSlides");
            const titleEl = document.getElementById("lightboxTitle");
            const dateEl = document.getElementById("lightboxDate");
            const counterEl = document.getElementById("lightboxCounter");
            const controls = carouselEl.querySelectorAll(".carousel-control-prev, .carousel-control-next");

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            let carousel = null;
            let album = null;
            let lastButton = null;

            // Only load the photo being viewed and its neighbours, so big albums open quickly
            function loadSlide(index) {
                const img = slidesEl.children[index] && slidesEl.children[index].querySelector("img");
                if (img && !img.getAttribute("src")) {
                    img.src = img.dataset.src;
                }
            }

            function loadAround(index) {
                const total = slidesEl.children.length;
                [index, index + 1, index - 1].forEach(function(i) {
                    loadSlide((i + total) % total);
                });
            }

            function showCounter(index) {
                counterEl.textContent = album.photos.length > 1 ? (index + 1) + " / " + album.photos.length : "";
            }

            function openAlbum(albumIndex) {
                album = albums[albumIndex];
                titleEl.textContent = album.title;
                dateEl.textContent = album.date;

                if (carousel) {
                    carousel.dispose();
                }
                slidesEl.innerHTML = "";

                // Build slides with textContent so captions can't inject HTML
                album.photos.forEach(function(photo, i) {
                    const slide = document.createElement("div");
                    slide.className = "carousel-item" + (i === album.start ? " active" : "");

                    const figure = document.createElement("figure");
                    figure.className = "lightbox-figure mb-0";

                    const img = document.createElement("img");
                    img.className = "lightbox-img";
                    img.dataset.src = photo.src;
                    img.alt = photo.alt;
                    figure.appendChild(img);

                    if (photo.caption) {
                        const caption = document.createElement("figcaption");
                        caption.className = "lightbox-caption";
                        caption.textContent = photo.caption;
                        figure.appendChild(caption);
                    }

                    slide.appendChild(figure);
                    slidesEl.appendChild(slide);
                });

                const single = album.photos.length < 2;
                controls.forEach(function(control) {
                    control.classList.toggle("d-none", single);
                });

                carousel = new bootstrap.Carousel(carouselEl, {
                    interval: false,
                    ride: false,
                    keyboard: false, // handled below so arrows work anywhere in the popup
                    touch: true,
                    wrap: true
                });

                loadAround(album.start);
                showCounter(album.start);
                modal.show();
            }

            carouselEl.addEventListener("slide.bs.carousel", function(event) {
                loadAround(event.to);
                showCounter(event.to);
            });

            document.querySelectorAll(".gallery-open").forEach(function(button) {
                button.addEventListener("click", function() {
                    lastButton = button;
                    openAlbum(Number(button.dataset.album));
                });
            });

            // Left / right arrow keys change photo
            modalEl.addEventListener("keydown", function(event) {
                if (!carousel || !album || album.photos.length < 2) return;
                if (event.key === "ArrowLeft") {
                    carousel.prev();
                } else if (event.key === "ArrowRight") {
                    carousel.next();
                }
            });

            // Put keyboard focus back on the card that opened the popup
            modalEl.addEventListener("hidden.bs.modal", function() {
                if (lastButton) {
                    lastButton.focus();
                }
            });
        })();
    </script>
</body>

</html>