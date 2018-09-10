<?php

/**
 * A demonstration of the Electroneum Vendor PHP API to poll for payment confirmation.
 */

// Load the ETN vendor class.
require_once("../src/Vendor.php");
require_once("../src/Exception/VendorException.php");

// Create the vendor object.
$vendor = new \Electroneum\Vendor\vendor('key_live_1234567890abcdefghijklm', 'sec_live_zxyxwvutsrqponmlkjihgfedcba0987654321zxyxwvutsrqponmlkj');

try {
    // Generate the payload.
    $payload = [
        'vendor_address' => 'etn-it-0abc123def456',
        'payment_id' => '7ce25b4dc0'
    ];

    // Check for confirmation.
    $result = $vendor->checkPaymentPoll(json_encode($payload));

    // Output the result.
    echo "<h1>Poll for Confirmation of Payment</h1>";
    if ($result['status'] == 1) {
        echo "<p>Payment sent for " . $result['amount'] . " ETN</p>";
    } elseif (!empty($result['message'])) {
        echo "<p>" . $result['message'] . "</p>";
    } else {
        echo "<p>There was an unknown error.</p>";
    }
} catch (\Electroneum\Vendor\Exception\VendorException $error) {
    echo $error;
}

// You could use jQuery to poll a script doing the above every 1000ms
//<script>
//    var interval = 1000;
//    function doPoll() {
//        $.ajax({
//            type: 'POST',
//            url: 'poll.php',
//            data: {'paymentId': '7ce25b4dc0'},
//            dataType: 'json',
//            success: function (data) {
//                if (data.status == 0) {
//                    // No payment, poll again
//                    setTimeout(doPoll, interval);
//                } else {
//                    // Hide the QR code and show confirmation ...
//                }
//            }
//        });
//    }
//    setTimeout(doPoll, interval);
//</script>
