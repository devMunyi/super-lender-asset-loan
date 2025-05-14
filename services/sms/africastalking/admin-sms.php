<?php

require_once("../../../vendor/autoload.php");
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");

use AfricasTalking\SDK\AfricasTalking;

// fetch credentials
$credentials = fetchAFTBulkSMSConfigs();

// Set your app credentials
$username = $credentials['username'] ?? '';
$apiKey = $credentials['api_key'] ?? '';
$from = $credentials['bulk_code'] ?? '';

if (empty($username) || empty($apiKey) || empty($from)) {
    die("SMS settings not configured\n");
}

// Initialize the SDK
$AT = new AfricasTalking($username, $apiKey);

// Get the SMS service
$sms = $AT->sms();

// check balance
$bal = doubleval(fetchrow('o_summaries', "uid=1", "value_"));

$message = '';
if ($bal <= 500000 && $bal > 3000) {
    $message = 'Greetings Admin. Superlender B2C Balance is Running Low.';
} else if ($bal <= 3000) {
    $message = 'Greetings Admin! Superlender B2C Balance is Insufficient.';
}else {
    // $message = 'Greetings Admin. B2C balance is sufficient';
    echo "B2C balance is sufficient.";
}

$log_message = "";
if (!empty($message) && !empty($admin_notification_phones)) {
    try {

        // Thats it, hit send and we'll take care of the rest
        $result = $sms->send([
            'to'      => $admin_notification_phones,
            'message' => $message,
            'from'    => $from,
            'enqueue' => 1,
            'bulkSMSMode' => 1
        ]);

        // echo json_encode($result);

        // Check if result is an object and has expected properties
        if (isset($result['status']) && $result['status'] === 'success') {
            $data = $result['data'];
            $sms_data = $data->SMSMessageData;
            $messageResponse = $sms_data->Message;

            $log_message = "At $fulldate, Response Message: $messageResponse \n";

            // write campaign send response to log file
            // $log_message = "At $fulldate, Campaign ID: $campaign_id, Response Message: $messageResponse \n";

            // // Optionally, display recipient details
            // foreach ($sms_data->Recipients as $recipient) {
            //     echo "Recipient Number: {$recipient->number}<br>";
            //     echo "Cost: {$recipient->cost}<br>";
            //     echo "Status: {$recipient->status}<br>";
            //     echo "Message ID: {$recipient->messageId}<br>";
            //     echo "<hr>";
            // }

            updateSmsBalance();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        $log_message = "Error: " . $e->getMessage();
    }
}

$logFile = 'b2c_balance.log';
if (!empty($log_message && !empty($logFile))) {
    file_put_contents($logFile, $log_message, FILE_APPEND);
}

unset($message, $admin_notification_phones, $log_message);