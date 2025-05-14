<?php

$expected_http_method = 'POST';
include_once("../../../vendor/autoload.php");
include_once ("../../../configs/allowed-ips-or-origins.php");
include_once("../../../configs/conn.inc");
include_once("../../../configs/jwt.php");
include_once("../../../php_functions/jwtAuthUtils.php");
include_once("../../../php_functions/jwtAuthenticator.php");
include_once("../../../php_functions/functions.php");

$data = json_decode(file_get_contents('php://input'), true);
$customer_id = $data["customer_id"];

if ($customer_id > 0) {
}else {
    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse(400, "Customer ID Missing!");
}

////----You have customer ID

try {
    $loan_statuses = table_to_obj('o_loan_statuses', "uid > 0", "100", "uid", "name");
    $loan_status_color = table_to_obj('o_loan_statuses', "uid > 0", "100", "uid", "color_code");
    $loans = fetchtable('o_loans', "customer_id = $customer_id  AND status != 0 AND disbursed = 1", "uid", "desc", "1000", "uid, loan_amount, total_repayable_amount, total_repaid, loan_balance, given_date, disbursed, next_due_date, final_due_date, paid, status");
    $payload = array();
    $loan_count = 0;
    while ($l = mysqli_fetch_array($loans)) {
        ////----Has an outstanding loan
        $has_loan = 1;
        $one_loan = array();
        $one_loan['uid'] = $l['uid'];
        $one_loan['loan_amount'] = doubleval($l['loan_amount']);
        $one_loan['total_repayable_amount'] = doubleval($l['total_repayable_amount']);
        $one_loan['total_repaid'] = doubleval($l['total_repaid']);
        $one_loan['loan_balance'] = doubleval($l['loan_balance']);
        $one_loan['given_date'] = $l['given_date'];
        $one_loan['next_due_date'] = $l['next_due_date'];
        $one_loan['final_due_date'] = $l['final_due_date'];
        // $one_loan['status'] = $l['status'];
        // $one_loan['paid'] = $l['paid'];
        $one_loan['status'] = $loan_statuses[$l['status']];
        // $one_loan['state_code'] = $loan_status_color[$l['status']];


        $payload[] = $one_loan;
        $loan_count += 1;
    }

    $message = "OK";
    $http_status_code = 200;

    store_event_return_void('o_loans', $customer_id, "$message, $http_status_code");
    sendApiResponse2($http_status_code, $loan_count, "OK", $payload);
} catch (Exception $e) {
    $http_status_code = 500;
    $message =  $e->getMessage();

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code$e");
    sendApiResponse($http_status_code, "Something Went Wrong!");
} finally {
    mysqli_close($con);
}
