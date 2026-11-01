<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        "lifetime" => 0,                                   // cookie ends when the browser closes
        "path"     => "/",
        "secure"   => !empty($_SERVER["HTTPS"]),           // only send over HTTPS when the site has it
        "httponly" => true,                                // JavaScript can't read the cookie
        "samesite" => "Lax"
    ]);
    session_start();
}
