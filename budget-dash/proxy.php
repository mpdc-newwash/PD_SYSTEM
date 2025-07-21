<?php
// CORS & headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// Read JSON body
$input = file_get_contents("php://input");

if (!$input) {
  http_response_code(400);
  echo "Error: No POST body received";
  exit;
}

// Google Apps Script Web App URL
$target = "https://script.google.com/macros/s/AKfycbzPYrZPxHPOYGd91KmUf-koCmMh6ybIl0vYdmrqeBF3QcypNq2xrwCgXezgyDYQWmRU/exec";

// Send POST to Apps Script using cURL
$ch = curl_init($target);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Content-Length: " . strlen($input)
]);

$response = curl_exec($ch);

// On error
if (curl_errno($ch)) {
  http_response_code(500);
  echo "cURL error: " . curl_error($ch);
} else {
  echo $response;
}

curl_close($ch);
