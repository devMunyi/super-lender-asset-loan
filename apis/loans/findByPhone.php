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

$customer_phone = $_GET["phone_number"] ?? "";
$limit = $_GET["limit"] ?? 10;

$customer_phone = make_phone_valid($customer_phone);
if (validate_phone($customer_phone) != 1) {
    sendApiResponse(400, "Invalid Phone Number!");
}

// fetch customer id based on customer_phone
$cust_ = fetchonerow('o_customers', "primary_mobile = '$customer_phone'", "uid, full_name");
$customer_id = $cust_['uid'] ?? 0;
$customer_name = $cust_['full_name'] ?? "";

if ($customer_id > 0) {
} else {
    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse(400, "Customer Not Found!");
}

////----You have customer ID

$loan_statuses = table_to_obj('o_loan_statuses', "uid > 0", "100", "uid", "name");
$loan_status_color = table_to_obj('o_loan_statuses', "uid > 0", "100", "uid", "color_code");
$loans = fetchtable('o_loans', "customer_id = $customer_id  AND status != 0 AND disbursed = 1", "uid", "desc", "100", "uid, loan_amount, total_repayable_amount, total_repaid, loan_balance, given_date, disbursed, next_due_date, final_due_date, paid, status");
$payload = array();
$loan_count = 0;
while ($l = mysqli_fetch_array($loans)) {

    if ($loan_count >= $limit) {
        break;
    }

    $one_loan = array();
    $one_loan['uid'] = $l['uid'];
    $one_loan['customer_name'] = $customer_name;
    $one_loan['loan_amount'] = doubleval($l['loan_amount']);
    $one_loan['total_repayable_amount'] = doubleval($l['total_repayable_amount']);
    $one_loan['total_repaid'] = doubleval($l['total_repaid']);
    $one_loan['loan_balance'] = doubleval($l['loan_balance']);
    $one_loan['given_date'] = $l['given_date'];
    $one_loan['next_due_date'] = $l['next_due_date'];
    $one_loan['final_due_date'] = $l['final_due_date'];
    $one_loan['status'] = $loan_statuses[$l['status']];

    $payload[] = $one_loan;
    $loan_count += 1;
}


if ($has_archive == 1 && $loan_count < $limit) {

    $query = "SELECT uid, loan_amount, total_repayable_amount, total_repaid, loan_balance, given_date, disbursed, next_due_date, final_due_date, paid, `status` FROM o_loans WHERE customer_id = $customer_id  AND status != 0 AND disbursed = 1 ORDER BY uid DESC LIMIT 100";

    try {
        $loans = mysqli_query($con1, $query);
    } catch (Exception $e) {
        $http_status_code = 500;
        sendApiResponse($http_status_code, "Something Went Wrong!");
        exit();
    }

    while ($l = mysqli_fetch_array($loans)) {

        if ($loan_count >= $limit) {
            break;
        }

        ////----Has an outstanding loan
        $has_loan = 1;
        $one_loan = array();
        $one_loan['uid'] = $l['uid'];
        $one_loan['customer_name'] = $customer_name;
        $one_loan['loan_amount'] = doubleval($l['loan_amount']);
        $one_loan['total_repayable_amount'] = doubleval($l['total_repayable_amount']);
        $one_loan['total_repaid'] = doubleval($l['total_repaid']);
        $one_loan['loan_balance'] = doubleval($l['loan_balance']);
        $one_loan['given_date'] = $l['given_date'];
        $one_loan['next_due_date'] = $l['next_due_date'];
        $one_loan['final_due_date'] = $l['final_due_date'];
        $one_loan['status'] = $loan_statuses[$l['status']];

        $payload[] = $one_loan;
        $loan_count += 1;
    }
}

$http_status_code = 200;
sendApiResponse2($http_status_code, $loan_count, "OK", $payload);

include_once("../../configs/close_connection.inc");