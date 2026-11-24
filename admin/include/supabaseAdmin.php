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

    //delete before hosting it
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //delete before hosting it
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["refresh_token" => $refreshToken]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhqYmZ3emNpdXBncHdnaXFhb25jIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTIxMDA0MzcsImV4cCI6MjEwNzY3NjQzN30.kJXpbfRnwpnW31pn_Nwooq9JyJodqSDCbV_fH-7DWA4',
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

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

    //delete before hosting it
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //delete before hosting it
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

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

    if ($httpCode === 401 && $retry && supabaseAuthRefresh()) {
        return supabaseAdminRequest($method, $table, $data, $query, false);
    }

    if ($response === false) {
        return ["code" => 0, "data" => null];
    }

    return ["code" => $httpCode, "data" => json_decode($response, true)];
}

function supabaseStorageUpload($bucket, $path, $localFile, $mimeType, $retry = true)
{
    $base = rtrim('https://xjbfwzciupgpwgiqaonc.supabase.co', '/');

    $ch = curl_init($base . '/storage/v1/object/' . $bucket . '/' . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($localFile));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    //delete before hosting it
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //delete before hosting it
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhqYmZ3emNpdXBncHdnaXFhb25jIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTIxMDA0MzcsImV4cCI6MjEwNzY3NjQzN30.kJXpbfRnwpnW31pn_Nwooq9JyJodqSDCbV_fH-7DWA4',
        'Authorization: Bearer ' . ($_SESSION["access_token"] ?? ""),
        'Content-Type: ' . $mimeType,
        'Cache-Control: max-age=31536000'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    //Refresh expired token and try again
    if (($httpCode === 401 || $httpCode === 403) && $retry && supabaseAuthRefresh()) {
        return supabaseStorageUpload($bucket, $path, $localFile, $mimeType, false);
    }

    if ($response === false || $httpCode !== 200) {
        return ["code" => $httpCode, "url" => ""];
    }

    return ["code" => 200, "url" => $base . '/storage/v1/object/public/' . $bucket . '/' . $path];
}

function supabaseStorageDelete($bucket, array $paths, $retry = true)
{
    $paths = array_values(array_filter($paths));
    if (empty($paths)) {
        return ["code" => 200];
    }

    $ch = curl_init(rtrim('https://xjbfwzciupgpwgiqaonc.supabase.co', '/') . '/storage/v1/object/' . $bucket);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["prefixes" => $paths]));

    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    //delete before hosting it
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //delete before hosting it

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhqYmZ3emNpdXBncHdnaXFhb25jIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTIxMDA0MzcsImV4cCI6MjEwNzY3NjQzN30.kJXpbfRnwpnW31pn_Nwooq9JyJodqSDCbV_fH-7DWA4',
        'Authorization: Bearer ' . ($_SESSION["access_token"] ?? ""),
        'Content-Type: application/json'
    ]);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (($httpCode === 401 || $httpCode === 403) && $retry && supabaseAuthRefresh()) {
        return supabaseStorageDelete($bucket, $paths, false);
    }

    return ["code" => $httpCode];
}
