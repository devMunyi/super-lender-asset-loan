<?php

$expected_http_method = 'GET';
include_once("../../vendor/autoload.php");
// include_once("../../configs/allowed-ips-or-origins.php");
include_once("../../configs/conn.inc");
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");
include_once("../../php_functions/jwtAuthenticator.php");
include_once("../../php_functions/functions.php");

$loan_id = $_GET["loan_id"] ?? 0;

if ($loan_id <= 0) {
    sendApiResponse(400, '', '404', "Load ID Missing!");
    exit();
}

$loan_exists = checkrowexists('o_loans', "uid = $loan_id AND disbursed = 1");

if ($loan_exists == 0) {
    if ($has_archive == 1) {
        try {
            $query = "SELECT uid FROM o_loans WHERE uid = $loan_id AND disbursed = 1";
            $loan_exists = mysqli_query($con1, $query);
            $count = mysqli_num_rows($loan_exists);
            if ($count == 0) {
                http_response_code(404);
                exit();
            }
        } catch (Exception $e) {
            sendApiResponse(500, "Something Went Wrong!");
            exit();
        }
    } else {
        http_response_code(404);
        exit();
    }
}

$loan_det = fetchonerow('o_loans', "uid = $loan_id AND disbursed = 1", "loan_balance");
$loan_balance = doubleval($loan_det['loan_balance']);

if ($loan_balance <= 0) {
    if ($has_archive == 1) {
        try {
            $query = "SELECT loan_balance FROM o_loans WHERE uid = $loan_id AND disbursed = 1";
            $result = mysqli_query($con, $query);
            $loan_det = mysqli_fetch_assoc($result);
            $loan_balance = doubleval($loan_det['loan_balance']);
        } catch (Exception $e) {
            sendApiResponse(500, "Something Went Wrong!");
            exit();
        }
    } else {
        http_response_code(404);
        exit();
    }
}

sendApiResponse(200, "", "OK", $loan_balance);

// explicit close of connection at the end of script
include_once("../../configs/close_connection.inc");