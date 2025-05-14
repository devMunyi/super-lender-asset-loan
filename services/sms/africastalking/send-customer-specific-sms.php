<?php

require_once("../../../vendor/autoload.php");
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\Utils;

// Start tracking execution time
$startTime = microtime(true);

// Fetch credentials
$credentials = fetchAFTBulkSMSConfigs();
$username = $credentials['username'] ?? '';
$apiKey = $credentials['api_key'] ?? '';
$from = $credentials['bulk_code'] ?? '';

// Validate credentials
if (empty($username) || empty($apiKey) || empty($from)) {
    die("SMS settings not configured\n");
}
// Fetch unsent SMS
$date = date('Y-m-d');
$unsent = fetchtable(
    'o_sms_outgoing',
    "status=1 AND date(queued_date) = '$date' AND (source_tbl != 'o_campaigns' OR source_tbl IS NULL)",
    "uid",
    "asc",
    "1000",
    "uid, phone, message_body"
);

if (!$unsent) {
    die("No unsent SMS found.\n");
}

$client = new Client();
$promises = [];
$successfulSMS = [];
$successfulSMSPhones = [];
$failedSMS = [];
$failedSMSPhones = [];

$totalSMS = mysqli_num_rows($unsent);

// Process SMS
while ($row = mysqli_fetch_assoc($unsent)) {
    $uid = $row['uid'];
    $phone = $row['phone'];
    $messageBody = $row['message_body'];

    if (validate_phone($phone) == 1 && input_available($messageBody) == 1) {
        $options = [
            'headers' => [
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'username' => $username,
                'message' => $messageBody,
                'to' => $phone,
                'from' => $from,
                'enqueue' => 1,
                'bulkSMSMode' => 1
            ],
        ];

        $request = new Request('POST', 'https://api.africastalking.com/version1/messaging');
        $promises[] = $client->sendAsync($request, $options)->then(
            function ($response) use ($uid, $phone, &$successfulSMS, &$successfulSMSPhones) {
                $responseBody = json_decode($response->getBody(), true);
                if (isset($responseBody['SMSMessageData']['Recipients'][0]['status']) && 
                    $responseBody['SMSMessageData']['Recipients'][0]['status'] == 'Success') {
                    $successfulSMS[] = $uid;
                    $successfulSMSPhones[] = $phone; // Track successful phone
                } else {
                    throw new Exception("SMS failed for UID: $uid");
                }
            },
            function ($exception) use ($uid, $phone, &$failedSMS, &$failedSMSPhones) {
                $failedSMS[] = $uid;
                $failedSMSPhones[] = $phone; // Track failed phone
            }
        );
    } else {
        echo "Invalid phone or message for UID: $uid\n";
        $failedSMS[] = $uid;
        $failedSMSPhones[] = $phone; // Track invalid phone
    }
}

// Process in batches of 100 promises
$batchSize = 100;
$batches = array_chunk($promises, $batchSize);

foreach ($batches as $batch) {
    // Resolve the current batch of promises
    Utils::settle($batch)->wait();
}

// Update status for successful SMS
if (!empty($successfulSMS)) {
    $successfulUIDs = implode(',', $successfulSMS);
    $updateSQL = "UPDATE o_sms_outgoing SET status = 2 WHERE uid IN ($successfulUIDs)";
    mysqli_query($con, $updateSQL) or die("Error updating successful SMS: " . mysqli_error($con));
}

// Sample log sms
$log = "At $fulldate, Message Sent " . count($successfulSMS) . "/" . $totalSMS . "\n";

// Concatenate the failed phones
$log .= ",Failed Phones: " . implode(', ', $failedSMSPhones) . "\n";

// End tracking execution time
$endTime = microtime(true);
$executionTime = $endTime - $startTime; // in seconds

// Add execution time to the log
$log .= "Total Execution Time: " . round($executionTime, 2) . " seconds\n";

// Log to file
// echo "successfulPhones = " . json_encode($successfulSMSPhones) . "<br>";

createFileIfNotExists("./customer-specific-sms.log");
$write_resp  = file_put_contents("./customer-specific-sms.log", $log, FILE_APPEND | LOCK_EX);

if ($write_resp === false) {
    echo "Error writing to log file\n";
}

// echo "$log";

// Close DB connection
mysqli_close($con);
?>
