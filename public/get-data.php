<?php
// Allow CORS for front-end access
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Target URL (Google Apps Script endpoint)
$url = "https://script.google.com/macros/s/AKfycbzPYrZPxHPOYGd91KmUf-koCmMh6ybIl0vYdmrqeBF3QcypNq2xrwCgXezgyDYQWmRU/exec";

// Init cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Execute request
$response = curl_exec($ch);

// Handle failure
if ($response === false) {
  http_response_code(500);
  echo json_encode([
    "error" => "cURL request failed",
    "details" => curl_error($ch)
  ]);
} else {
  echo $response;
}

curl_close($ch);
