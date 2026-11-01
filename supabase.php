<?php
$supabaseUrl = "https://xjbfwzciupgpwgiqaonc.supabase.co";  // ← put your real URL here
$supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhqYmZ3emNpdXBncHdnaXFhb25jIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTIxMDA0MzcsImV4cCI6MjEwNzY3NjQzN30.kJXpbfRnwpnW31pn_Nwooq9JyJodqSDCbV_fH-7DWA4";                        // ← put your real anon key here

function supabaseRequest($method, $table, $data = null, $query = "")
{
    global $supabaseUrl, $supabaseKey;

    $url = $supabaseUrl . "/rest/v1/" . $table . $query;

    $headers = [
        "apikey: " . $supabaseKey,
        "Authorization: Bearer " . $supabaseKey,
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

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

function supabaseLogin($email, $password)
{
    $url = 'https://xjbfwzciupgpwgiqaonc.supabase.co' . '/auth/v1/token?grant_type=password';

    $data = [
        'email' => $email,
        'password' => $password
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhqYmZ3emNpdXBncHdnaXFhb25jIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTIxMDA0MzcsImV4cCI6MjEwNzY3NjQzN30.kJXpbfRnwpnW31pn_Nwooq9JyJodqSDCbV_fH-7DWA4',
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return ["code" => 0, "data" => null];
    }

    return ["code" => $httpCode, "data" => json_decode($response, true)];
}
