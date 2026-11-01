<?php
$supabaseUrl = "https://xjbfwzciupgpwgiqaonc.supabase.co";  // ← put your real URL here
$supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhqYmZ3emNpdXBncHdnaXFhb25jIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTIxMDA0MzcsImV4cCI6MjEwNzY3NjQzN30.kJXpbfRnwpnW31pn_Nwooq9JyJodqSDCbV_fH-7DWA4";                        // ← put your real anon key here

function supabaseRequest($method, $table, $data = null, $query = "") {
    global $supabaseUrl, $supabaseKey;

    $url = rtrim($supabaseUrl, '/') . "/rest/v1/" . $table . $query;

    $headers = [
        "apikey: " . $supabaseKey,
        "Authorization: Bearer " . $supabaseKey,
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_SSL_VERIFYPEER => false, 
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 15,
    ]);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    return [
        "code"  => $httpCode,
        "data"  => json_decode($response, true),
        "error" => $curlError,
        "url"   => $url
    ];
}

function supabaseLogin($email, $password) {
    global $supabaseUrl, $supabaseKey;

    $url = rtrim($supabaseUrl, '/') . "/auth/v1/token?grant_type=password";

    $headers = [
        "apikey: " . $supabaseKey,
        "Content-Type: application/json"
    ];

    $payload = json_encode([
        "email"    => $email,
        "password" => $password
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    return [
        "code"  => $httpCode,
        "data"  => json_decode($response, true),
        "error" => $curlError,
        "url"   => $url
    ];
}
