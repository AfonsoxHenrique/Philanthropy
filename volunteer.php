<?php
include 'supabase.php';

// Safe output helper (skipped if another file already has one)
if (!function_exists("e")) {
    function e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
    }
}

// Volunteer roles (shown as cards and in the dropdown)
$roles = [
    [
        "title" => "Weekly Sorting Crew",
        "time"  => "2-4 hrs per event",
        "text"  => "Help sort, label and pack school supplies, winter jackets and sports gear at our storage space."
    ],
    [
        "title" => "After-School Tutor",
        "time"  => "2 hrs per week",
        "text"  => "Help primary school students with maths and reading using workbooks we provide."
    ],
    [
        "title" => "Event Marshal",
        "time"  => "Flexible",
        "text"  => "Welcome guests, hand out water, help with parking and keep things running smoothly at fun runs and fundraising events."
    ]
];

// Why volunteer list
$perks = [
    ["icon" => "bi-star",          "text" => "No police or Working with Children Check needed for the sorting crew"],
    ["icon" => "bi-person-check",  "text" => "We help tutors apply for their Working with Children Check"],
    ["icon" => "bi-heart",         "text" => "Volunteer hours signed off for school and community programs"]
];

// ===== Handle form submit =====
$errors = [];
$old    = ["name" => "", "email" => "", "phone" => "", "role" => $roles[0]["title"], "availability" => ""];
$sent   = isset($_GET["sent"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($old as $key => $value) {
        $old[$key] = trim($_POST[$key] ?? "");
    }
    $agreed     = isset($_POST["agree"]);
    $roleTitles = array_column($roles, "title");

    // Validation
    if ($old["name"] === "") {
        $errors["name"] = "Enter your full name.";
    }
    if (!filter_var($old["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email address, e.g. name@example.com.";
    }
    if (!preg_match('/^[0-9 +()-]{8,20}$/', $old["phone"])) {
        $errors["phone"] = "Enter a phone number, e.g. 0412 345 678.";
    }
    if (!in_array($old["role"], $roleTitles, true)) {
        $errors["role"] = "Choose a role from the list.";
    }
    if (strlen($old["availability"]) > 500) {
        $errors["availability"] = "Keep your notes under 500 characters.";
    }
    if (!$agreed) {
        $errors["agree"] = "Tick the box to agree to the Privacy Policy.";
    }

    if (empty($errors)) {
        $newVolunteer = [
            "name"         => $old["name"],
            "email"        => $old["email"],
            "phone"        => $old["phone"],
            "role"         => $old["role"],
            "availability" => $old["availability"],
            "status"       => "pending"
        ];

        $insert = supabaseRequest("POST", "volunteers", $newVolunteer, "");

        if ($insert["code"] === 200 || $insert["code"] === 201) {
            header("Location: volunteer.php?sent=1#volunteerForm");
            exit;
        } else {
            $errors["form"] = "Your form couldn't be sent. Please try again, or email us if it keeps happening.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Volunteer | Phill Anthropy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="volunteerHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Give Your Time</small>
            <h1 class="mt-2 mb-3">Volunteer With Us</h1>
            <p class="mx-auto">Our projects run on community support. Help us sort school supplies,
                run events or tutor students.</p>
        </div>
    </section>

    <!-- Why Join Section -->
    <section id="volunteerIntro" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <div class="row align-items-center g-5">
                <div class="col-md-6">
                    <img src="assets/images/volunteer_team.jpg" class="img-fluid story-img" alt="Volunteers packing school supplies into boxes">
                </div>
                <div class="col-md-6">
                    <small class="eyebrow text-uppercase">Why Join the Team</small>
                    <h2 class="mt-2 mb-4">Your Hands Build the Future</h2>
                    <p>Donations buy school supplies and healthy lunches, but volunteers get them into students' hands.
                        You'll join a friendly team of more than 50 local volunteers who care about our schools.</p>
                    <p>We provide training, flexible scheduling and a supportive team. It's a great fit for students
                        needing community hours, parents who want to help, and local retirees.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section id="roles" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <small class="eyebrow text-uppercase d-block text-center">Where You Fit In</small>
            <h2 class="text-center mt-2 mb-5">Open Roles This Term</h2>

            <div class="row g-4 pb-4">
                <?php foreach ($roles as $role): ?>
                    <!-- Role card -->
                    <div class="col-md-4">
                        <div class="card role-card h-100">
                            <div class="card-body p-4">
                                <span class="role-time mb-3"><?php echo e($role["time"]); ?></span>
                                <h5 class="card-title"><?php echo e($role["title"]); ?></h5>
                                <p class="card-text"><?php echo e($role["text"]); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Form Section -->
    <section id="volunteerForm" class="homeSec pb-5">
        <div class="container-fluid">
            <div class="row g-5">
                <div class="col-lg-6">
                    <h2 class="mb-3">Submit an Expression of Interest</h2>
                    <p class="volunteer-lead">Fill in this short form and our volunteer coordinator will contact you
                        within two business days to arrange an introduction.</p>
                    <ul class="list-unstyled perk-list mt-4">
                        <?php foreach ($perks as $perk): ?>
                            <li><i class="bi <?php echo $perk["icon"]; ?>" aria-hidden="true"></i><?php echo e($perk["text"]); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="col-lg-6">
                    <div class="card form-card p-4 p-md-5">

                        <?php if ($sent): ?>
                            <!-- Success message -->
                            <div class="form-success p-4 text-center" role="status">
                                <i class="bi bi-check-circle fs-2 d-block mb-2" aria-hidden="true"></i>
                                <strong>Thanks for your interest!</strong>
                                <p class="mb-0 mt-2">Our volunteer coordinator will contact you within two business days.</p>
                            </div>
                        <?php else: ?>

                            <?php if (isset($errors["form"])): ?>
                                <div class="alert alert-danger" role="alert"><?php echo e($errors["form"]); ?></div>
                            <?php endif; ?>

                            <form method="post" action="volunteer.php#volunteerForm" novalidate>
                                <!-- Full name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control contact-input <?php echo isset($errors["name"]) ? "is-invalid" : ""; ?>"
                                        id="name" name="name" value="<?php echo e($old["name"]); ?>" autocomplete="name" required>
                                    <?php if (isset($errors["name"])): ?>
                                        <div class="invalid-feedback"><?php echo e($errors["name"]); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control contact-input <?php echo isset($errors["email"]) ? "is-invalid" : ""; ?>"
                                        id="email" name="email" value="<?php echo e($old["email"]); ?>" autocomplete="email" required>
                                    <?php if (isset($errors["email"])): ?>
                                        <div class="invalid-feedback"><?php echo e($errors["email"]); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Phone -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control contact-input <?php echo isset($errors["phone"]) ? "is-invalid" : ""; ?>"
                                        id="phone" name="phone" value="<?php echo e($old["phone"]); ?>" autocomplete="tel" required>
                                    <?php if (isset($errors["phone"])): ?>
                                        <div class="invalid-feedback"><?php echo e($errors["phone"]); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Preferred role -->
                                <div class="mb-3">
                                    <label for="role" class="form-label">Preferred Role</label>
                                    <select class="form-select contact-input <?php echo isset($errors["role"]) ? "is-invalid" : ""; ?>" id="role" name="role">
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo e($role["title"]); ?>" <?php echo $old["role"] === $role["title"] ? "selected" : ""; ?>>
                                                <?php echo e($role["title"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors["role"])): ?>
                                        <div class="invalid-feedback"><?php echo e($errors["role"]); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Availability -->
                                <div class="mb-3">
                                    <label for="availability" class="form-label">Availability &amp; Notes</label>
                                    <textarea class="form-control contact-input <?php echo isset($errors["availability"]) ? "is-invalid" : ""; ?>"
                                        id="availability" name="availability" rows="3" maxlength="500"
                                        placeholder="e.g. Available Thursday mornings or weekend events"><?php echo e($old["availability"]); ?></textarea>
                                    <?php if (isset($errors["availability"])): ?>
                                        <div class="invalid-feedback"><?php echo e($errors["availability"]); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Privacy consent -->
                                <div class="form-check mb-4">
                                    <input class="form-check-input contact-check <?php echo isset($errors["agree"]) ? "is-invalid" : ""; ?>"
                                        type="checkbox" id="agree" name="agree" required>
                                    <label class="form-check-label consent-text" for="agree">
                                        I agree to the <a href="privacypolicy.php">Privacy Policy</a> and to being contacted about volunteering.
                                    </label>
                                    <?php if (isset($errors["agree"])): ?>
                                        <div class="invalid-feedback"><?php echo e($errors["agree"]); ?></div>
                                    <?php endif; ?>
                                </div>

                                <button type="submit" class="btn btn-send w-100">Submit Expression of Interest</button>
                            </form>
                        <?php endif; ?>
                    </div>
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
