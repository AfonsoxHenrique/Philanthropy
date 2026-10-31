<?php
include 'supabase.php';

// Contact details
$contactEmail   = "phillanthropy.au@gmail.com";
$contactPhone   = "+61 459 694 894";
$contactAddress = "44-46 Canterbury Road, Blackburn South, VIC";

// Dropdown options
$subjects = [
    "Volunteer Interest & Opportunities",
    "School Supplies Drive",
    "Physical Safety Grant Application",
    "Wellness Event Suggestion",
    "General Enquiry"
];

// Form values (kept so the user doesn't retype after an error)
$fullName = "";
$email    = "";
$subject  = "";
$message  = "";
$errors   = [];

// Show the success message after the redirect
$sent = isset($_GET["sent"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $subject  = $_POST["subject"] ?? "";
    $message  = trim($_POST["message"] ?? "");
    $agreed   = isset($_POST["agree"]);

    // Validation
    if ($fullName === "") {
        $errors[] = "Enter your full name.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address, like name@example.com.";
    }
    if (!in_array($subject, $subjects, true)) {
        $errors[] = "Choose an enquiry subject.";
    }
    if ($message === "") {
        $errors[] = "Write a message so we know how to help.";
    }
    if (!$agreed) {
        $errors[] = "Tick the box to agree to the terms and privacy policy.";
    }

    // Save to Supabase
    if (empty($errors)) {
        $data = [
            "full_name" => $fullName,
            "email"     => $email,
            "subject"   => $subject,
            "message"   => $message
        ];

        $result = supabaseRequest("POST", "contact_messages", $data);

        if ($result["code"] === 200 || $result["code"] === 201) {
            // Redirect so refreshing the page doesn't send the message twice
            header("Location: contacts.php?sent=1");
            exit;
        } else {
            $errors[] = "Your message couldn't be sent. Try again, or email us at " . $contactEmail . ".";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us | Phill Antrophy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Page Hero -->
    <section id="contactHero" class="d-flex align-items-center justify-content-center pageHero">
        <div class="container-fluid text-center">
            <small class="eyebrow text-uppercase">Connect With Us</small>
            <h1 class="mt-2 mb-3">Get In Touch</h1>
            <p class="mx-auto">Want to collaborate on a school supplies drive, apply for physical safety grants,
                or suggest a wellness event? Let us build together.</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <div class="row g-5">

                <!-- Contact information -->
                <div class="col-lg-6">
                    <h2 class="mb-3">Contact Information</h2>
                    <p class="mb-4">We are directly accessible at our Spring Heights physical headquarters or via secure
                        communication channels below.</p>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle"><i class="bi bi-envelope"></i></div>
                        <div>
                            <small class="contact-label text-uppercase d-block">Email Address</small>
                            <a href="mailto:<?php echo $contactEmail; ?>" class="contact-value"><?php echo $contactEmail; ?></a>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle"><i class="bi bi-telephone"></i></div>
                        <div>
                            <small class="contact-label text-uppercase d-block">Phone Number</small>
                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contactPhone); ?>" class="contact-value"><?php echo $contactPhone; ?></a>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle"><i class="bi bi-geo-alt"></i></div>
                        <div>
                            <small class="contact-label text-uppercase d-block">Headquarters Location</small>
                            <span class="contact-value"><?php echo $contactAddress; ?></span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <small class="social-label text-uppercase d-block mb-3">Follow Our Direct Streams</small>
                    <div class="d-flex gap-2">
                        <a href="#" class="social-btn" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-btn" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-btn" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <!-- Contact form -->
                <div class="col-lg-6">
                    <div class="card form-card">
                        <div class="card-body p-4">

                            <!-- Success message -->
                            <?php if ($sent): ?>
                                <div class="alert form-success d-flex gap-3 align-items-start mb-4" role="alert">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                    <div>
                                        <strong class="d-block">Thank you!</strong>
                                        <small>Your enquiry has been securely logged. Our trust will contact you shortly.</small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Error messages -->
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger mb-4" role="alert">
                                    <strong class="d-block mb-1">Your message wasn't sent:</strong>
                                    <ul class="mb-0 ps-3">
                                        <?php foreach ($errors as $error): ?>
                                            <li><small><?php echo htmlspecialchars($error); ?></small></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <h3 class="form-title mb-4">Send a Secure Message</h3>

                            <form method="POST" action="contacts.php" novalidate>
                                <div class="mb-3">
                                    <label for="fullName" class="form-label">Your Full Name</label>
                                    <input type="text" id="fullName" name="full_name" class="form-control contact-input"
                                        value="<?php echo htmlspecialchars($fullName); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control contact-input"
                                        value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Enquiry Subject</label>
                                    <select id="subject" name="subject" class="form-select contact-input" required>
                                        <option value="" disabled <?php echo $subject === "" ? "selected" : ""; ?>>Choose a subject</option>
                                        <?php foreach ($subjects as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $subject === $option ? "selected" : ""; ?>>
                                                <?php echo htmlspecialchars($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message Content</label>
                                    <textarea id="message" name="message" rows="5" class="form-control contact-input" required><?php echo htmlspecialchars($message); ?></textarea>
                                </div>

                                <div class="form-check mb-4">
                                    <input type="checkbox" id="agree" name="agree" class="form-check-input contact-check" required>
                                    <label for="agree" class="form-check-label">
                                        <small>I agree to the general <a href="terms.php">terms</a> and the <a href="privacypolicy.php">privacy policies</a> of Phill Anthropy.</small>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-send w-100">Send Message</button>
                            </form>
                        </div>
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