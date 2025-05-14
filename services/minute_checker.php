<?php
session_start();
if (isset($_GET['limit'])) {
    $limit = $_GET['limit'];
} else {
    $limit = 20;
}
include_once("../configs/conn.inc");
echo "Start <br/>";

include_once("../php_functions/functions.php");
$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");
include_once("../php_functions/digivas.php");
include_once("../php_functions/textsms.php");
include_once("../php_functions/bongasms.php");


//send_via_bonga(254716330450, "Test Message");
//die('Done');


include_once("../configs/conn.inc");
$unsent = fetchtable('o_sms_outgoing', "status=1 AND date(queued_date) = '$date' ", "uid", "asc", "$limit", "uid, phone, message_body, queued_date");
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
    }
}
echo "End <br/>";

include_once("../configs/close_connection.inc");
