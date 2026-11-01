<?php
require __DIR__ . '/session.php';

// Log out after 30 minutes of no activity
$timeoutSeconds = 30 * 60;

if (isset($_SESSION["last_activity"]) && time() - $_SESSION["last_activity"] > $timeoutSeconds) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php?expired=1");
    exit;
}

if (empty($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$_SESSION["last_activity"] = time();

// Details shown in the top bar
$adminName  = $_SESSION["admin_name"] ?? "Admin";
$adminRole  = $_SESSION["admin_role"] ?? "Admin";
$adminPhoto = $_SESSION["admin_photo"] ?? "../assets/images/testphoto.jpeg";
