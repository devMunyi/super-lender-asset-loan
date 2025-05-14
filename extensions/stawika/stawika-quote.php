<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();
$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

//////-----Loan history

$recent_quote = fetchmaxid('o_campaign_messages',"uid = 1","uid, message");
$quote_id = $recent_quote['uid'];
$quote_message = $recent_quote['message'];

if($quote_id > 0) {
    $result_ = 1;
    $details_ = '"' . $quote_message . '"';
    $result_code = 111;
}
else{
    $result_ = 0;
    $details_ = '""';
    $result_code = 101;
}

echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");







