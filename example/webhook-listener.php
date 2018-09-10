<?php

/**
 * A demonstration of the Electroneum Vendor PHP API to listen for an incoming webhook.
 */

// Load the ETN vendor class.
require_once("../src/Vendor.php");
require_once("../src/Exception/VendorException.php");

// Create the vendor object.
$vendor = new \Electroneum\Vendor\vendor('key_live_1234567890abcdefghijklm', 'sec_live_zxyxwvutsrqponmlkjihgfedcba0987654321zxyxwvutsrqponmlkj');

try {
    // Get the payload and signature from an incoming webhook.
    $payload = @file_get_contents('php://input');
    $signature = @$_SERVER['HTTP_ETN_SIGNATURE'];

    // OVERRIDE: Use the generated test webhook data from https://my.electroneum.com/user/vendor.
    //$payload = "";
    //$signature = "";

    // Verify the signature.
    echo "<h1>Listen for a Webhook</h1>";
    if ($vendor->verifySignature($payload, $signature)) {
        // Signature passed.
        http_response_code(200);
        echo "<p>Webhook signature verification: pass</p>";

        // Log and process the transaction.
        $payload = json_decode($payload);
        echo "<p>Payment received for " . $payload['amount'] . " ETN for payment-id " . $payload['payment_id'] . ".</p>";
    } else {
        // Signature failed.
        http_response_code(401);
        echo "<p>Webhook signature verification: fail</p>";
    }
} catch (\Electroneum\Vendor\Exception\VendorException $error) {
    echo $error;
}
