<?php
require __DIR__ . '/session.php';

// Clear everything in the session
$_SESSION = [];

// Delete the session cookie from the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", [
        "expires"  => time() - 3600,
        "path"     => $params["path"],
        "domain"   => $params["domain"],
        "secure"   => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => $params["samesite"]
    ]);
}

session_destroy();

header("Location: ../login.php?logged_out=1");
exit;
