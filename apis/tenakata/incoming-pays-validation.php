<?php
error_reporting(~E_ALL);
session_start();
include_once('../configs/saf-api-notification-ips.php');
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$data  = file_get_contents('php://input');
$company = 7; //==> tenakata id, adjust accordingly 

// write data to file for tesing
// writeToLogFile($data, $company, "c2b-validation-logs.txt");

// Check the 'Host' header to determine the origin
$origin =  $_SERVER['REMOTE_ADDR'];
$responseBody = [
    "ResultCode" => "C2B00012",
    "ResultDesc" => "Rejected",
]; // prepare default validation response body

if (in_array($origin, $saf_notification_ips)) {
    header("Access-Control-Allow-Origin: " . $origin);

    // // Only set Access-Control-Allow-Methods for POST requests
    // if ($requestMethod === $expected_http_method) {
    //     header("Access-Control-Allow-Methods: $expected_http_method");
    // }
} else {
    $responseBody = [
        "ResultCode" => "C2B00016", // other error
        "ResultDesc" => "Rejected",
    ];
    // writeToLogFile("Forbidden Origin: $orign", $company, "c2b-error-log.txt");
    sendJsonResponse($responseBody);
}

// reusable function
function writeToLogFile($data, $company, $logFile = 'validation-logs.txt') {
    // Check if the file exists, if not, create it with read and write permissions

    if (!file_exists($logFile)) {
        $log = fopen($logFile, "a");
    } else {
        $log = fopen($logFile, "a");
    }

    // Write data to the log file
    fwrite($log, $data . 'Company-' . $company . '->' . date('Y-m-d H:i:s') . "\n");

    // Close the file
    fclose($log);
}

// reusable function to send JSON response
function sendJsonResponse($responseBody, $httpStatusCode = 200) {
    http_response_code($httpStatusCode);
    header('Content-Type: application/json');
    echo json_encode($responseBody);
    exit();
}

// Handle errors more gracefully
function handleErrors($responseBody, $message = "") {
    writeToLogFile("Error occurred ", $message, "c2b-error-log.txt");
    sendJsonResponse($responseBody); // Internal Server Error
}

function sanitize_digits($string) {
    return preg_replace('/\D/', '', $string);
}

$result = json_decode(trim($data), true);
$TransAmount = $result['TransAmount'];
$BillRefNumber = $phone_number = sanitize_digits($result['BillRefNumber']);



if ($TransAmount > 0) {
    $company_d = company_details($company);
    if ($company_d['uid'] > 0) {
        $db = $company_d['db_name'];
        $_SESSION['db_name'] = $db;

        if(validate_phone($phone_number) != 1){
            $phone_number = make_phone_valid($phone_number);
        }

        try {
            $customer_det = fetchonerow('o_customers', "national_id='$BillRefNumber' OR primary_mobile='$phone_number'", "uid");
        } catch (Exception $e) {
            $message = $e->getMessage();
            handleErrors($responseBody, $message);
        }

        $customer_id = intval($customer_det['uid']);
        if ($customer_id > 0) {
            // store event
            try {
                $event = store_event('o_customers', $customer_id, 'An Incoming Payment Validated');
            } catch (Exception $e) {
                $message = $e->getMessage();
                handleErrors($responseBody, $message);
            }

            // Accept Transaction 
            sendJsonResponse(["ResultCode" => "0", "ResultDesc" => "Accepted"]);
        } else {
            // Reject transaction
            handleErrors($responseBody, "Customer not found");
        }
    }
} else {

    $responseBody = [
        "ResultCode" => "C2B00013",
        "ResultDesc" => "Rejected",
    ];
    handleErrors($responseBody, "Invalid Transaction Amount");
}
