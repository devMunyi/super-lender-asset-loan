<?php

include_once("../../../vendor/autoload.php");
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");
include_once("../../../configs/airtel-ug.php");
include_once("../../../php_functions/airtel-ug.php");
if ($has_archive == 1) {
    include_once("../../../configs/archive_conn.php");
}

require_once('../../../php_functions/AfricasTalkingGateway.php');

$from = trim($_POST['from']); // payer phone number
$to = $shortcode = trim($_POST['to']); // Africa's talking shortcode
$text = $keyword = $message = trim($_POST['text']); // amount to pay
$date_ = trim($_POST['date']); // request to pay date
$id = trim($_POST['id']); // unique message id from Africa's talking
$linkId = trim($_POST['linkId']); // This works for onDemand subscription products
$transdate = $fulldate;
$direction = 1;

// log to a file Africa's taking incoming sms
// $result  = "Incoming SMS from $from with text $text";
// $logFile = 'airtel-ussd-request-to-pay-logs.txt';
// $log = fopen($logFile, "a");
// fwrite($log, $result . $fulldate . "\n");
// fclose($log);

$number = $sender_phone = make_phone_valid(ltrim($from, '+'));
if ((strlen($number)) == 12) {
    save_log("Incoming SMS: $from ($number), $to, $text, $date");
    // validate that payment is greater than 0
    $amount = doubleval($text);
    if ($amount > 0) {
        // send stk push to customer phone prompting enter pin to complete payment transaction
        $resp = ug_airtel_ussd_pay($amount, $number);
        if (isset($resp['payload']) && isset($resp['payload']['status']) && $resp['payload']['status'] === 'SUCCESSFUL' && isset($resp['payload']['ref_id'])) {

            // extract reference id
            $ref_id = $resp['payload']['ref_id'];

            // fetch customer using phone number
            $cust = fetchonerow('o_customers', "primary_mobile='$number'", "uid");
            $customer_id = $cust['uid'] ?? 0;

            // store sms reference to o_sms_interaction
            $fds = array('shortcode', 'keyword', 'sender_phone', 'customer_id', 'message', 'transdate', 'direction', 'link_id', 'ref_id', 'status');
            $vals = array("$shortcode", "$keyword", "$sender_phone", $customer_id, "$message", "$transdate", $direction, "$linkId", "$ref_id", 1);

            addtodb('o_sms_interaction', $fds, $vals);

            // $message = "Payment request has been submitted!";
            // feedback($message);

            store_event('o_customers', $customer_id, "Successfully Initiated USSD Payment at $transdate");
            exit();
        }else {
            $message = $resp['message'] ?? "Application Error! Try Again Later";
            feedback($message);
            store_event('o_customers', $customer_id, "Could not Initiate USSD Payment at $transdate with message $message.");
            exit();
        }

    } else {
        $message = "Please Enter a Valid Amount";
        feedback($message);
        exit();
    }
    ////_____________Process SMS

} else {
    // echo "Invalid number";
    save_log("Invalid Number $number:");
    exit();
}

///_____________Process SMS

function feedback($message)
{
    global $number;
    global $linkId;
    send_sms_interactive($number, $message, $linkId);
}
