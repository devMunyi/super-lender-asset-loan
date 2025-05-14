<?php
session_start();
include_once '../../configs/20200902.php';
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");


$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$loan_id = decurl($_POST['loan_id']);
$with_ni_validation = $_POST['with_ni_validation'];
$action = $_POST['action'];


$change_b2c_validation = permission($userd['uid'], 'o_mpesa_queues', "0", "update_");
if ($change_b2c_validation != 1) {
    exit(errormes("You don't have permission to $action"));
}



///////----------------Validation
if ($loan_id > 0) {
} else {
    exit(errormes("Loan code needed"));
}

$customer_id = intval(fetchrow("o_loans", "uid=$loan_id", "customer_id"));
if($customer_id === 0){
    exit(errormes("Invalid Customer ID Parsed"));
}

$proceed = 0;
$update_b2c_validation = updatedb('o_loans', "with_ni_validation=$with_ni_validation", "uid=$loan_id");
if ($update_b2c_validation == 1) {

    /// handle SKIP_B2C_VALIDATION flag
    $rows = handleSkipB2CValidation($customer_id, $with_ni_validation);


    $event = "Loan B2C validation has been " . ($with_ni_validation == 1 ? "enabled" : "disabled") . " by [" . $userd['name'] . "(" . $userd['email'] . ")] on [$fulldate]";
    store_event('o_loans', $loan_id, "$event");

    $btnText = $with_ni_validation == 1 ? "Disable B2C Validation" : "Enable B2C Validation";
    $message = $with_ni_validation == 1 ? "Success! B2C Validation Enabled" : "Success! B2C Validation Disabled";
    // echo sucmes("Success. $message");
    echo (json_encode(array("status" => 'OK', "loan_id" => $loan_id, "message" => $message, "btnText" => $btnText)));

} else {
    exit(errormes("Oops!.An error occurred. Try again!"));
}

