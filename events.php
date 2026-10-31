<?php
include 'supabase.php';

// Get scheduled events from Supabase
$result = supabaseRequest(
    "GET",
    "events",
    null,
    "?select=*&status=eq.scheduled&order=event_date.asc"
);

$events = [];

if (
    isset($result["code"]) &&
    $result["code"] >= 200 &&
    $result["code"] < 300 &&
    is_array($result["data"])
) {
    $events = $result["data"];
}

// Sample data if no events are available
if (empty($events)) {
    $events = [
        [
            "id" => "sample-1",
            "title" => "District Fun Run Camp: Charity Pair",
            "event_date" => "2026-11-22",
            "start_time" => "08:00",
            "location" => "Community Park",
            "description" => "Join us for a fun-filled morning supporting local families."
        ],
        [
            "id" => "sample-2",
            "title" => "Annual Spring Benefit Concert",
            "event_date" => "2026-11-04",
            "start_time" => "18:30",
            "location" => "Community Hall",
            "description" => "Enjoy talented local musicians while supporting our community."
        ],
        [
            "id" => "sample-3",
            "title" => "Community Garden Planting Day",
            "event_date" => "2026-11-18",
            "start_time" => "09:00",
            "location" => "Community Garden",
            "description" => "Help plant trees and create a greener neighbourhood."
        ],
        [
            "id" => "sample-4",
            "title" => "Books & Backpacks Volunteer Sorting",
            "event_date" => "2026-12-02",
            "start_time" => "10:00",
            "location" => "Community Centre",
            "description" => "Prepare donated school supplies for children in need."
        ]
    ];
}

// Handle RSVP submission
$rsvpMessage = "";
$rsvpType = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_rsvp"])) {
    $eventId = trim($_POST["event_id"] ?? "");
    $fullName = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $attendees = filter_var(
        $_POST["attendees"] ?? null,
        FILTER_VALIDATE_INT
    );

    $validEvent = false;

    foreach ($events as $event) {
        if ((string) ($event["id"] ?? "") === $eventId) {
            $validEvent = true;
            break;
        }
    }

    if (
        !$validEvent ||
        $fullName === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $attendees === false ||
        $attendees < 1 ||
        $attendees > 10
    ) {
        $rsvpMessage = "Please complete all fields correctly.";
        $rsvpType = "danger";
    } elseif (strpos($eventId, "sample-") === 0) {
        $rsvpMessage = "Please connect a real event in Supabase before registering.";
        $rsvpType = "warning";
    } else {
        $rsvpResult = supabaseRequest(
            "POST",
            "event_rsvps",
            [
                "event_id" => $eventId,
                "full_name" => $fullName,
                "email" => $email,
                "attendees" => $attendees
            ],
            ""
        );

        if (
            isset($rsvpResult["code"]) &&
            $rsvpResult["code"] >= 200 &&
            $rsvpResult["code"] < 300
        ) {
            $rsvpMessage = "Your RSVP has been submitted successfully!";
        } else {
            $rsvpMessage = "Unable to submit your RSVP. Please try again.";
            $rsvpType = "danger";
        }
    }
}

// Past activity highlights
$highlights = [
    [
        "title" => "Winter Coat Drive 2026",
        "category" => "Community Support",
        "description" => "Warm clothing collected and distributed to families in need.",
        "image" => "assets/images/winter-coat-drive.jpg"
    ],
    [
        "title" => "Summer Book Drive",
        "category" => "Education",
        "description" => "Books and learning resources donated to local children.",
        "image" => "assets/images/summer-book-drive.jpg"
    ],
    [
        "title" => "Fun Run Autumn Rally",
        "category" => "Fundraising",
        "description" => "A community event bringing families together for a great cause.",
        "image" => "assets/images/fun-run.jpg"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Events | Phill Antrophy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Existing project stylesheet -->
    <link href="style/master.css" rel="stylesheet">

    <style>
        :root {
            --event-green: #285b45;
            --event-dark: #173d2d;
            --event-light: #f0f6f1;
            --event-yellow: #f0c95b;
        }

        body {
            background-color: #fbfaf6;
        }

        /* Small custom styles; Bootstrap handles the layout */
        .events-hero {
            min-height: 240px;
            color: white;
            background-color: var(--event-green);
        }

        .events-hero .eyebrow,
        .section-eyebrow {
            color: #c39a38;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: 1.5px;
        }

        .events-hero .eyebrow {
            color: var(--event-yellow);
        }

        .event-date {
            width: 65px;
            min-width: 65px;
            height: 65px;
            color: white;
            background-color: var(--event-green);
            border-radius: .5rem;
        }

        .event-date .month {
            color: var(--event-yellow);
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .event-date .day {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .event-title,
        .section-title {
            color: var(--event-green);
        }

        .event-card,
        .highlight-card,
        .rsvp-card {
            border: 1px solid #e3ebe4;
            border-radius: .75rem;
        }

        .event-card {
            transition: border-color .2s ease;
        }

        .event-card:hover {
            border-color: #a9c8b3;
        }

        .event-btn,
        .rsvp-btn {
            color: white;
            background-color: var(--event-green);
            border-color: var(--event-green);
        }

        .event-btn:hover,
        .rsvp-btn:hover {
            color: white;
            background-color: var(--event-dark);
            border-color: var(--event-dark);
        }

        .rsvp-section {
            background-color: var(--event-light);
        }

        .highlight-img {
            width: 100%;
            height: 190px;
            object-fit: cover;
        }

        .highlight-tag {
            color: #795b15;
            background-color: #fff3d2;
        }

        .rsvp-card .form-control:focus,
        .rsvp-card .form-select:focus {
            border-color: var(--event-green);
            box-shadow: 0 0 0 .2rem rgba(40, 91, 69, .15);
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <?php include 'include/navbar.php'; ?>

    <!-- Header Section -->
    <section id="hero" class="d-flex align-items-center justify-content-center homeSec events-hero">
        <div class="container-fluid text-center py-5">

            <span class="eyebrow text-uppercase">Community Events</span>

            <h1 class="fw-bold mt-3 mb-3">
                Events &amp; Fundraising Activities
            </h1>

            <p class="mx-auto mb-0" style="max-width: 650px;">
                Join our local community events, volunteer activities and fundraising initiatives.
                Every activity helps us support local schools, families and children.
            </p>

        </div>
    </section>

    <!-- Events Section -->
    <section id="commitment" class="d-flex align-items-center justify-content-center homeSec py-5">
        <div class="container-fluid px-3 px-md-5">

            <div class="text-center mb-4">
                <span class="section-eyebrow text-uppercase">Join the Action</span>

                <h2 class="section-title fw-bold mt-2">
                    <?php echo count($events); ?> Scheduled Public Events
                </h2>
            </div>

            <!-- Event List -->
            <div class="container" style="max-width: 1000px;">
                <div class="row g-3">

                    <?php foreach ($events as $event): ?>
                        <?php
                        $date = !empty($event["event_date"])
                            ? strtotime($event["event_date"])
                            : false;

                        $month = $date ? date("M", $date) : "TBA";
                        $day = $date ? date("d", $date) : "--";

                        $time = !empty($event["start_time"])
                            ? date("g:i A", strtotime($event["start_time"]))
                            : "Time TBA";
                        ?>

                        <div class="col-12">
                            <div class="card event-card shadow-sm">
                                <div class="card-body p-3 p-md-4">

                                    <div class="row align-items-center g-3">

                                        <!-- Date Badge -->
                                        <div class="col-auto">
                                            <div class="event-date d-flex flex-column align-items-center justify-content-center">
                                                <span class="month">
                                                    <?php echo htmlspecialchars($month); ?>
                                                </span>

                                                <span class="day">
                                                    <?php echo htmlspecialchars($day); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Event Details -->
                                        <div class="col">
                                            <h5 class="event-title fw-bold mb-2">
                                                <?php echo htmlspecialchars($event["title"] ?? "Community Event"); ?>
                                            </h5>

                                            <div class="d-flex flex-wrap gap-3 text-secondary small mb-2">
                                                <span>
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?php echo htmlspecialchars($time); ?>
                                                </span>

                                                <span>
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    <?php echo htmlspecialchars($event["location"] ?? "Location TBA"); ?>
                                                </span>
                                            </div>

                                            <p class="text-secondary small mb-0">
                                                <?php echo htmlspecialchars($event["description"] ?? "Join us for this community activity."); ?>
                                            </p>
                                        </div>

                                        <!-- Register Button -->
                                        <div class="col-12 col-md-auto">
                                            <a
                                                href="#rsvp"
                                                class="btn event-btn btn-sm w-100 register-event"
                                                data-event-id="<?php echo htmlspecialchars((string) ($event["id"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                                                Register Now
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>
            </div>

        </div>
    </section>

    <!-- RSVP Section -->
    <section id="projects" class="d-flex align-items-center justify-content-center homeSec rsvp-section py-5">
        <div class="container-fluid px-3">

            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">

                    <div class="card rsvp-card shadow-sm">
                        <div class="card-body p-4">

                            <h4 class="section-title fw-bold mb-2">
                                <i class="bi bi-calendar-check me-2"></i>
                                RSVP for an Event
                            </h4>

                            <p class="text-secondary small mb-4">
                                Reserve your place and be part of our community.
                            </p>

                            <?php if ($rsvpMessage !== ""): ?>
                                <div class="alert alert-<?php echo htmlspecialchars($rsvpType); ?> alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($rsvpMessage); ?>

                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="#projects">

                                <div class="mb-3">
                                    <label for="event_id" class="form-label small fw-semibold">
                                        Select Event
                                    </label>

                                    <select name="event_id" id="event_id" class="form-select" required>
                                        <option value="">Choose an event</option>

                                        <?php foreach ($events as $event): ?>
                                            <option
                                                value="<?php echo htmlspecialchars((string) ($event["id"] ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                                                <?php echo (
                                                    ($_POST["event_id"] ?? "") === (string) ($event["id"] ?? "")
                                                ) ? "selected" : ""; ?>>

                                                <?php echo htmlspecialchars($event["title"] ?? "Community Event"); ?>

                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="full_name" class="form-label small fw-semibold">
                                        Full Name
                                    </label>

                                    <input
                                        type="text"
                                        name="full_name"
                                        id="full_name"
                                        class="form-control"
                                        placeholder="Enter your full name"
                                        maxlength="150"
                                        value="<?php echo htmlspecialchars($_POST["full_name"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label small fw-semibold">
                                        Email Address
                                    </label>

                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        class="form-control"
                                        placeholder="you@example.com"
                                        maxlength="254"
                                        value="<?php echo htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label for="attendees" class="form-label small fw-semibold">
                                        Number Attending
                                    </label>

                                    <select name="attendees" id="attendees" class="form-select" required>
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option
                                                value="<?php echo $i; ?>"
                                                <?php echo ((string) ($_POST["attendees"] ?? "1") === (string) $i) ? "selected" : ""; ?>>
                                                <?php echo $i . ($i === 1 ? " person" : " people"); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <button type="submit" name="submit_rsvp" value="1" class="btn rsvp-btn w-100 py-2">
                                    Submit RSVP
                                </button>

                            </form>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- Highlights Section -->
    <section id="highlights" class="d-flex align-items-center justify-content-center homeSec py-5">
        <div class="container-fluid px-3 px-md-5">

            <div class="text-center mb-4">
                <span class="section-eyebrow text-uppercase">Moments of Success</span>

                <h2 class="section-title fw-bold mt-2">
                    Highlights from Past Activities
                </h2>
            </div>

            <div class="row g-4">

                <?php foreach ($highlights as $highlight): ?>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card highlight-card h-100 shadow-sm">

                            <img
                                src="<?php echo htmlspecialchars($highlight["image"], ENT_QUOTES, "UTF-8"); ?>"
                                class="card-img-top highlight-img"
                                alt="<?php echo htmlspecialchars($highlight["title"], ENT_QUOTES, "UTF-8"); ?>"
                                loading="lazy">

                            <div class="card-body d-flex flex-column">

                                <h5 class="card-title section-title fw-bold">
                                    <?php echo htmlspecialchars($highlight["title"]); ?>
                                </h5>

                                <div class="mb-3">
                                    <span class="badge highlight-tag">
                                        <?php echo htmlspecialchars($highlight["category"]); ?>
                                    </span>
                                </div>

                                <p class="card-text text-secondary small mb-0">
                                    <?php echo htmlspecialchars($highlight["description"]); ?>
                                </p>

                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'include/footer.php'; ?>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Navbar scroll effect
        window.addEventListener("scroll", function() {
            const navbar = document.querySelector(".custom-navbar");

            if (navbar) {
                navbar.classList.toggle("scrolled", window.scrollY > 50);
            }
        });

        // Select the event when Register Now is clicked
        document.querySelectorAll(".register-event").forEach(function(button) {
            button.addEventListener("click", function() {
                const eventSelect = document.getElementById("event_id");

                if (eventSelect) {
                    eventSelect.value = this.dataset.eventId;
                }
            });
        });
    </script>

</body>

</html>