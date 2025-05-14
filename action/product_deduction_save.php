<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$deduction_id = $_POST['deduction_id'];
$product_id = $_POST['product_id'];
$action = $_POST['action'];

$proceed = 0;
$event_details = null;
$product_name = fetchrow("o_loan_products", "uid=$product_id", "name");
$deduction_name = fetchrow("o_deductions", "uid=$deduction_id", "name");

// Function to generate event details

if ($action == 'ADD') {
    // Check permission
    $permi = permission($userd['uid'], 'o_product_deductions', 0, 'create_');
    if ($permi != 1) {
        exit(errormes("You do not have permission to Add Product Deductions"));
    }

    $exists = checkrowexists("o_product_deductions", "product_id='$product_id' AND deduction_id='$deduction_id'");
    if ($exists == 1) {
        // Update status to 1
        $update = updatedb('o_product_deductions', "status=1", "product_id='$product_id' AND deduction_id='$deduction_id'");
        if ($update == 1) {
            $feedback = sucmes('Success Adding Deduction');
            $proceed = 1;
        } else {
            $feedback = errormes('Error Adding Deduction');
        }
    } else {
        // Insert new record
        $fds = array('deduction_id', 'product_id', 'date_added', 'status');
        $vals = array("$deduction_id", "$product_id", "$fulldate", "1");
        $create = addtodb('o_product_deductions', $fds, $vals);
        if ($create == 1) {
            $feedback = sucmes('Success Adding Deduction');
            $proceed = 1;
        } else {
            $feedback = errormes('Error Adding Deduction');
        }
    }

} elseif ($action == 'REMOVE') {
    // Check permission
    $permi = permission($userd['uid'], 'o_product_deductions', 0, 'update_');
    if ($permi != 1) {
        exit(errormes("You do not have permission to Update Product Deductions"));
    }

    // Update status to 0
    $update = updatedb('o_product_deductions', "status=0", "product_id='$product_id' AND deduction_id='$deduction_id'");
    if ($update == 1) {
        $feedback = sucmes('Success Removing Deduction');
        $proceed = 1;
    } else {
        $feedback = errormes('Error Removing Deduction');
    }
}

$final_state = fetchrow("o_product_deductions", "product_id='$product_id' AND deduction_id='$deduction_id'", "status");

// Generate event details
if ($proceed == 1) {
    $action_text = ($action == 'ADD') ? 'added to' : 'removed from';
    $event_details = "Deduction $deduction_name $action_text Product $product_name by ".$userd['name']." (".$userd['uid'].")";
    store_event('o_product_deductions', $product_id, "$event_details");
}

echo json_encode("{\"result_\":$proceed,\"final_\":$final_state}");
?>
