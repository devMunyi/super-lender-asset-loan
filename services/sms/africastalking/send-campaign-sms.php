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

/*

running_statuses:
1 => pending
2 => Running/Queued
3 => sent/complete

*/

// fetch for one queued campaign in the order of the oldest
$queued_campaign_query = "SELECT c.uid as campaign_id, cm.message FROM o_campaigns c LEFT JOIN o_campaign_messages cm ON cm.campaign_id = c.uid WHERE c.running_status = 2 AND c.status = 1 AND cm.status = 1 AND cm.type = 'GENERAL' LIMIT 1";

$queued_campaign_result = mysqli_query($con, $queued_campaign_query);

if (mysqli_num_rows($queued_campaign_result) > 0) {
    $queued_campaign = mysqli_fetch_assoc($queued_campaign_result);
    $campaign_id = $queued_campaign['campaign_id'];
    $message = $queued_campaign['message'];


    $phones_array = table_to_array("o_sms_outgoing", "source_record=$campaign_id AND source_tbl='o_campaigns' AND status = 1 AND date(queued_date) = '$date'", "10000000", "phone");

    $recipients = implode(",", $phones_array);

    $log_file = 'campaign_sms.log';

    try {
        // Thats it, hit send and we'll take care of the rest
        $result = $sms->send([
            'to'      => $recipients,
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

            // write campaign send response to log file
            $log_message = "At $fulldate, Campaign ID: $campaign_id, Response Message: $messageResponse \n";

            // // Optionally, display recipient details
            // foreach ($sms_data->Recipients as $recipient) {
            //     echo "Recipient Number: {$recipient->number}<br>";
            //     echo "Cost: {$recipient->cost}<br>";
            //     echo "Status: {$recipient->status}<br>";
            //     echo "Message ID: {$recipient->messageId}<br>";
            //     echo "<hr>";
            // }


            // update running_status to sent/complete on o_campaigns table
            updatedb("o_campaigns", "running_status=3", "uid=$campaign_id");

            // update status to sent on o_sms_outgoing table
            updatedb("o_sms_outgoing", "status=2, sent_date='$fulldate'", "source_record=$campaign_id AND source_tbl='o_campaigns'");

        } else {
            // throw error
            throw new Exception("Error: Unexpected response format.");
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();

        // write campaign send response to log file
        $log_message = "At $fulldate, Campaign ID: $campaign_id, Error Message: " . $e->getMessage() . "\n";
    }
} else {
    echo "No Queued Campaigns";
}

// Check if log message is set and not empty
if (isset($log_message) && strlen(trim($log_message)) > 0) {
    // Write the log message to the log file
    $result = file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);

    // Check if writing was successful
    if ($result === false) {
        echo "Error writing to log file.";
    } else {
        echo "Log entry added successfully.";
    }

    unset($log_message);
}

?>
