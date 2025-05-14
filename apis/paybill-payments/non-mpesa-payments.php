<?php
session_start();

// Get the JSON input data
$data = file_get_contents('php://input');

// Initialize log file (optional)
$logFile = 'log.txt';
// $log = fopen($logFile,"a");
// fwrite($log, $data.date('Y-m-d H:i:s')."\n");
// fclose($log);

// Decode the JSON data
$result = json_decode(trim($data), true);

// Initialize response variables
$response = [
    'result_' => 0,
    'message_' => "Unknown error"
];

// Extract transaction details from the decoded JSON
$TransID = $result['TransID'] ?? null;
$TransTime = $result['TransTime'] ?? null;
$TransAmount = $result['TransAmount'] ?? 0;
$BillRefNumber = $result['BillRefNumber'] ?? null;
$name = $result['FullName'] ?? null;
$MSISDN = $result['phone'] ?? null;

if ($TransAmount > 0) {
    // Include necessary functions and database connection
    include_once("../php_functions/functions.php");
    $_SESSION['db_name'] = $db_;
    include_once("../configs/conn.inc");

    // Fetch customer details
    $customer_det = fetchonerow('o_customers', "primary_mobile='" . make_phone_valid($BillRefNumber) . "' OR national_id='$BillRefNumber'", "uid, branch");
    $customer_id = $customer_det['uid'] ?? 0;
    
    if ($customer_id > 0) {
        $branch_id = $customer_det['branch'];
        $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, product_id, account_number");
        $latest_loan_id = $latest_loan['uid'] ?? 0;
    } else {
        $latest_loan_id = 0;
    }

    // Prepare data for saving to the database
    $payment_method = 3; // Assuming payment method ID is 3
    $fds = ['customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'loan_code', 'payment_date', 'recorded_date', 'record_method', 'added_by', 'comments', 'status'];
    $vals = [
        "$customer_id", 
        "$branch_id", 
        "$payment_method", 
        "$MSISDN", 
        "$TransAmount", 
        "$TransID", 
        false_zero($latest_loan_id), 
        "$BillRefNumber", 
        "$TransTime", 
        date('Y-m-d H:i:s'), // Assuming you want the current date/time
        "API", 
        "0", 
        "From API", 
        "1"
    ];

    // Attempt to save the payment record
    if (addtodb('o_incoming_payments', $fds, $vals) == 1) {
        // Payment saved successfully
        $response['message_'] = "Save Payment for Loan: $latest_loan_id received";
        $response['result_'] = 1;

        if ($latest_loan_id > 0) {
            recalculate_loan($latest_loan_id);
            // Update loan balance and notify user logic here...
            // ...
        }
    } else {
        // Check if transaction already exists
        if (checkrowexists('o_incoming_payments', "transaction_code='$TransID'") == 1) {
            $response['message_'] = "Error! Transaction Code: $TransID already exists";
        } else {
            $response['message_'] = "Error saving payment";
        }
    }
} else {
    // Invalid transaction amount
    $response['message_'] = "Amount invalid";
}

// Set header for JSON response and echo the response
header('Content-Type: application/json');
echo json_encode($response);
?>