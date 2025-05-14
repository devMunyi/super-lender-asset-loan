<?php

$expected_http_method = 'GET';
include_once("../../vendor/autoload.php");
// include_once("../../configs/allowed-ips-or-origins.php");
include_once("../../configs/conn.inc");
if ($has_archive == 1) {
    include_once("../../configs/archive_conn.php");
}
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");
include_once("../../php_functions/jwtAuthenticator.php");
include_once("../../php_functions/functions.php");

// $data = json_decode(file_get_contents('php://input'), true);
$transaction_code = $_GET["transcode"] ?? "";
$limit = $_GET["limit"] ?? 10;

if (empty(trim($transaction_code))) {
    sendApiResponse(400, "Transaction code is required!");
}

////----You have customer ID

$payment_statuses = table_to_obj('o_payment_statuses', "uid > 0", "50", "uid", "name");
$users = table_to_obj('o_users', "uid > 0", "10000", "uid", "name");
$payments = fetchtable("o_incoming_payments", "transaction_code=\"$transaction_code\" AND status=1", "uid", "desc", "100", "uid, amount, comments, mobile_number, transaction_code, loan_id, loan_balance, payment_date, record_method, added_by, status");


$payload = array();
$payments_count = 0;
while ($p = mysqli_fetch_array($payments)) {

    if ($payments_count >= $limit) {
        break;
    }

    $one_payment = array();
    $one_payment['uid'] = $p['uid'];
    $one_payment['amount'] = doubleval($p['amount']);
    $one_payment['comments'] = $p['comments'];
    $one_payment['mobile_number'] = $p['mobile_number'];
    $one_payment['transaction_code'] = $p['transaction_code'];
    $one_payment['loan_id'] = $p['loan_id'];
    $one_payment['loan_balance'] = doubleval($p['loan_balance']);
    $one_payment['payment_date'] = $p['payment_date'];
    $one_payment['record_method'] = $p['record_method'];
    $one_payment['added_by'] = $users[$p['added_by']];
    $one_payment['status'] = $payment_statuses[$p['status']];

    array_push($payload, $one_payment);
    $payments_count += 1;
}


if ($has_archive == 1 && $payments_count < $limit) {

    $query = "SELECT uid, amount, comments, mobile_number, transaction_code, loan_id, loan_balance, payment_date, record_method, added_by, status FROM o_incoming_payments WHERE transaction_code='$transaction_code' AND status = 1 ORDER BY `uid` DESC LIMIT 100";

    try {

        $payments = mysqli_query($con1, $query);

        if (!$payments) {
            throw new Exception("Query failed: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        $http_status_code = 500;
        $message =  $e->getMessage(); 
        sendApiResponse($http_status_code, "Something Went Wrong!");
        exit();
    }

    while ($p = mysqli_fetch_array($payments)) {

        if ($payments_count >= $limit) {
            break;
        }

        $one_payment = array();
        $one_payment['uid'] = $p['uid'];
        $one_payment['amount'] = doubleval($p['amount']);
        $one_payment['comments'] = $p['comments'];
        $one_payment['mobile_number'] = $p['mobile_number'];
        $one_payment['transaction_code'] = $p['transaction_code'];
        $one_payment['loan_id'] = $p['loan_id'];
        $one_payment['loan_balance'] = doubleval($p['loan_balance']);
        $one_payment['payment_date'] = $p['payment_date'];
        $one_payment['record_method'] = $p['record_method'];
        $one_payment['added_by'] = $users[$p['added_by']];
        $one_payment['status'] = $payment_statuses[$p['status']];

        array_push($payload, $one_payment);
        $payments_count += 1;
    }
}

$message = "OK";
$http_status_code = 200;
sendApiResponse2($http_status_code, $payments_count, "OK", $payload);
