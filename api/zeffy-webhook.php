<?php
include __DIR__ . '/../supabase.php';

$payload = json_decode(file_get_contents("php://input"), true);
$payment = $payload["data"] ?? $payload;

$id     = (string)($payment["id"] ?? "");
$status = strtolower((string)($payment["status"] ?? ""));

// Always acknowledge
if ($id === "") {
    http_response_code(200);
    echo "ignored";
    exit;
}

// Deleted payment
if (($payload["type"] ?? "") === "payment.deleted" || $status === "deleted") {
    supabaseRequest("DELETE", "donations", null, "?zeffy_payment_id=eq." . urlencode($id));
    http_response_code(200);
    echo "deleted";
    exit;
}

// Only store successful payments
if ($status !== "succeeded") {
    http_response_code(200);
    echo "ignored";
    exit;
}

$amount = floatval($payment["amount"] ?? 0) / 100;

$first = trim((string)($payment["buyer"]["first_name"] ?? ""));
$last  = trim((string)($payment["buyer"]["last_name"] ?? ""));
$name  = trim($first . " " . $last);
if ($name === "") {
    $name = "Anonymous";
}

$type = "one-time";
if (!empty($payment["items"][0]["recurrence_interval"])) {
    $interval = strtolower((string)$payment["items"][0]["recurrence_interval"]);
    $type = ($interval === "onetime") ? "one-time" : $interval;
}

$row = [
    "zeffy_payment_id" => $id,
    "amount"           => $amount,
    "donor_name"       => $name,
    "email"            => $payment["buyer"]["email"] ?? null,
    "donation_type"    => $type,
    "status"           => "completed"
];

supabaseRequest("POST", "donations", $row);

http_response_code(200);
echo "ok";