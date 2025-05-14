<?php
session_start();
if (isset($_GET['limit'])) {
    $limit = $_GET['limit'];
} else {
    $limit = 30;
}

echo "Start <br/>";

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
include_once("../php_functions/digivas.php");
include_once("../php_functions/textsms.php");
include_once("../php_functions/bongasms.php");

// check sms balance
$sms_bal = doubleval(fetchrow('o_summaries', "uid=3", "value_"));
if ($sms_bal <= 20) {
    echo "SMS balance is low. Please top up <br/>";
    exit();  // exit to leave some balance for admin to be notified
}

// select unset messages of type personalized
$unsent = fetchtable('o_sms_outgoing', "status=1 AND date(queued_date) = '$date' AND message_type='PERSONALIZED'", "uid", "asc", "$limit", "uid, phone, message_body, queued_date");

$counter = 0;
while ($un = mysqli_fetch_array($unsent)) {

    $uid = $un['uid'];
    $phone = $un['phone'];
    $message_body = $un['message_body'];
    $queued_date = $un['queued_date'];
    $update_ = updatedb('o_sms_outgoing', "status=2, sent_date='$fulldate'", "uid='$uid'"); ////Mark SMS as sent already

    if ((validate_phone(($phone))) == 1 && input_available($message_body) == 1) {
        if ($sms_provider == 'DIGIVAS') {
            echo send_via_digivas($phone, $message_body, $uid);
        } elseif ($sms_provider == 'TEXTSMS') {
            echo send_via_textsms($phone, $message_body, $uid);
        } elseif ($sms_provider == 'BONGASMS') {
            echo send_via_bonga($phone, $message_body);
        } else {
            echo send_sms_bulk($phone, $message_body);
        }

        echo "[$queued_date]<br/>";

        $counter++;
    }
}
echo "End <br/>";

if ($counter > 0) {
    // update sms balance
    echo updateSmsBalance();
}else {
    echo "No SMS to send <br/>";
}

include_once("../configs/close_connection.inc");