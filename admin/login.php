<?php
require __DIR__ . '/include/session.php';
require __DIR__ . '/../supabase.php';

// Already logged in? Go straight to the dashboard
if (!empty($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

// CSRF token: proves the form was submitted from this page
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Lockout settings
$maxAttempts    = 5;
$lockoutSeconds = 5 * 60;

$email = "";
$error = "";

// Messages from logout / timeout redirects
$notice = "";
if (isset($_GET["logged_out"])) {
    $notice = "You've been logged out.";
} elseif (isset($_GET["expired"])) {
    $notice = "Your session expired after 30 minutes of inactivity. Log in again to continue.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $token    = $_POST["csrf_token"] ?? "";

    $lockedUntil = $_SESSION["lockout_until"] ?? 0;

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        $error = "Your form expired. Refresh the page and try again.";
    } elseif ($lockedUntil > time()) {
        $minutes = (int) ceil(($lockedUntil - time()) / 60);
        $error = "Too many failed attempts. Try again in " . $minutes . " " . ($minutes === 1 ? "minute" : "minutes") . ".";
    } elseif ($email === "" || $password === "") {
        $error = "Enter your email and password.";
    } else {
        // Log in with Supabase Auth
        $result = supabaseLogin($email, $password);

        $user    = $result["data"]["user"] ?? null;
        $isAdmin = ($user["app_metadata"]["role"] ?? "") === "admin";

        if ($result["code"] === 200 && $user && $isAdmin) {
            // Success: new session ID stops session fixation attacks
            session_regenerate_id(true);

            $_SESSION["admin_id"]      = $user["id"];
            $_SESSION["admin_email"]   = $user["email"];
            $_SESSION["admin_name"]    = $user["user_metadata"]["name"] ?? $user["email"];
            $_SESSION["admin_role"]    = "Admin";
            $_SESSION["admin_photo"]   = $user["user_metadata"]["photo"] ?? "../assets/images/testphoto.jpeg";
            $_SESSION["access_token"]  = $result["data"]["access_token"];
            $_SESSION["refresh_token"] = $result["data"]["refresh_token"] ?? "";
            $_SESSION["last_activity"] = time();

            unset($_SESSION["login_attempts"], $_SESSION["lockout_until"], $_SESSION["csrf_token"]);

            header("Location: dashboard.php");
            exit;
        }

        if ($result["code"] === 0) {
            // Couldn't reach Supabase at all - don't count it as a failed attempt
            $error = "The login server couldn't be reached. Check your connection and try again.";
        } elseif ($result["code"] === 429) {
            $error = "Too many login attempts. Wait a few minutes and try again.";
        } else {
            // Failed: count the attempt
            $_SESSION["login_attempts"] = ($_SESSION["login_attempts"] ?? 0) + 1;

            if ($_SESSION["login_attempts"] >= $maxAttempts) {
                $_SESSION["lockout_until"]  = time() + $lockoutSeconds;
                $_SESSION["login_attempts"] = 0;
                $error = "Too many failed attempts. Try again in 5 minutes.";
            } else {
                // Same message for wrong email or wrong password, on purpose
                $error = "That email and password don't match. Check them and try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login | Phill Anthropy Console</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../style/admin.css" rel="stylesheet">
</head>

<body class="admin-body login-body">

    <main class="d-flex align-items-center justify-content-center flex-grow-1 px-3 py-5">
        <div class="card login-card">
            <div class="card-body">

                <!-- Brand -->
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="brand-icon"><i class="bi bi-x-circle"></i></span>
                    <span class="login-brand">Phill Anthropy Console</span>
                </div>

                <h1 class="login-title mb-1">Admin Login</h1>
                <p class="admin-subtitle mb-4">Sign in to manage donations, events and site content.</p>

                <!-- Notice (logged out / expired) -->
                <?php if ($notice !== "" && $error === ""): ?>
                    <div class="alert login-notice d-flex gap-2 align-items-start" role="status">
                        <i class="bi bi-info-circle"></i>
                        <small><?php echo htmlspecialchars($notice); ?></small>
                    </div>
                <?php endif; ?>

                <!-- Error -->
                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger d-flex gap-2 align-items-start" role="alert">
                        <i class="bi bi-exclamation-circle"></i>
                        <small><?php echo htmlspecialchars($error); ?></small>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control login-input"
                            value="<?php echo htmlspecialchars($email); ?>"
                            autocomplete="username" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control login-input"
                                autocomplete="current-password" required>
                            <button type="button" class="btn login-input password-toggle" id="passwordToggle"
                                aria-label="Show password" aria-pressed="false">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login w-100">
                        Log In <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="../index.php" class="back-link"><i class="bi bi-arrow-left"></i> Back to website</a>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/include/footer.php' ?>

    <!-- Show / hide password -->
    <script>
        const passwordInput = document.getElementById("password");
        const passwordToggle = document.getElementById("passwordToggle");

        passwordToggle.addEventListener("click", function() {
            const showing = passwordInput.type === "text";
            passwordInput.type = showing ? "password" : "text";
            passwordToggle.setAttribute("aria-pressed", showing ? "false" : "true");
            passwordToggle.setAttribute("aria-label", showing ? "Show password" : "Hide password");
            passwordToggle.innerHTML = showing ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
    </script>

</body>

</html>