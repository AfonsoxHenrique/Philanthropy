<?php
function supabaseAuthRefresh()
{
    $refreshToken = $_SESSION["refresh_token"] ?? "";
    if ($refreshToken === "") {
        return false;
    }

    $ch = curl_init('https://xjbfwzciupgpwgiqaonc.supabase.co' . '/auth/v1/token?grant_type=refresh_token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["refresh_token" => $refreshToken]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhqYmZ3emNpdXBncHdnaXFhb25jIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTIxMDA0MzcsImV4cCI6MjEwNzY3NjQzN30.kJXpbfRnwpnW31pn_Nwooq9JyJodqSDCbV_fH-7DWA4',
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = $response !== false ? json_decode($response, true) : null;

    if ($httpCode === 200 && !empty($data["access_token"])) {
        $_SESSION["access_token"]  = $data["access_token"];
        $_SESSION["refresh_token"] = $data["refresh_token"] ?? $refreshToken;
        return true;
    }

    return false;
}

// Same return shape as supabaseRequest(): ["code" => ..., "data" => ...]
function supabaseAdminRequest($method, $table, $data = null, $query = "", $retry = true)
{
    $url = rtrim('https://xjbfwzciupgpwgiqaonc.supabase.co', '/') . '/rest/v1/' . $table . $query;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhqYmZ3emNpdXBncHdnaXFhb25jIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTIxMDA0MzcsImV4cCI6MjEwNzY3NjQzN30.kJXpbfRnwpnW31pn_Nwooq9JyJodqSDCbV_fH-7DWA4',
        'Authorization: Bearer ' . ($_SESSION["access_token"] ?? ""),
        'Content-Type: application/json',
        'Prefer: return=representation'   // send back the saved row so we can confirm it worked
    ]);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 401 && $retry && supabaseAuthRefresh()) {
        return supabaseAdminRequest($method, $table, $data, $query, false);
    }

    if ($response === false) {
        return ["code" => 0, "data" => null];
    }

    return ["code" => $httpCode, "data" => json_decode($response, true)];
}
