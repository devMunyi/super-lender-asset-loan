<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$offset = $_GET['offset'];
$rpp = $_GET['rpp'];

if(isset($_GET['start_date']) && isset($_GET['end_date']) && isset($_GET['rpp']) && isset($_GET['offset'])){




    $loan_enc_array = array();
    $loan_full_numbers = array();
    $loan_customers = array();
    $loan_branches = array();
    $loan_agents = array();
    ////------ALl open loans
    $open_loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND given_date >= '2023-01-01'","uid","asc","10000000","uid, account_number, customer_id, current_branch, current_agent, current_co");
    while($o = mysqli_fetch_array($open_loans)){
        $uid = $o['uid'];
        $account_number = $o['account_number'];
        $customer_id = $o['customer_id'];
        $current_branch = $o['current_branch'];
        $current_agent = $o['current_agent'];
        $current_co = $o['current_co'];

        if($current_agent == 0){
            $current_agent = $current_co;
        }


        $loan_branches[$uid] = $current_branch;
        $loan_customers[$uid] = $customer_id;
        $loan_agents[$uid] = $current_agent;
        $loan_full_numbers[$uid] = $account_number;
        $masked = maskString($account_number, 6, 3);
        $loan_enc_array[$uid] = $masked;

    }


    $loan_enc_array_final = removeDuplicateValues($loan_enc_array);  /////Unique loan numbers
   // echo json_encode( $loan_enc_array_final, true);

    $payments = fetchtable('o_incoming_payments',"loan_id=0 AND date(recorded_date) BETWEEN '$start_date' AND '$end_date' AND mobile_number LIKE '%***%' AND status=1 AND record_method LIKE '%UPLOAD%'","uid","asc","$offset, $rpp","uid, mobile_number, amount, transaction_code");
    while($p = mysqli_fetch_array($payments)){
        $puid = $p['uid'];
        $mobile_number = $p['mobile_number'];
        $amount = $p['amount'];
        $transaction_code = $p['transaction_code'];

        ////----Search loan ID by phone
        $matchingKey = searchKeyByValue($loan_enc_array_final, $mobile_number);

        if ($matchingKey !== null) {

            $current_branch_ = $loan_branches[$matchingKey] ;
            $agent = $loan_agents[$matchingKey];
            $customer_id_ = $loan_customers[$matchingKey];
            $account_number_ = $loan_full_numbers[$matchingKey];

            echo "Found {$mobile_number}: {Loan: $matchingKey} Branch $current_branch_, Customer_id: $customer_id_, Account:  $account_number_, PUID: $puid [$transaction_code], COllected by: $agent<br/>";
            ///-----Found
            $update_pay = updatedb('o_incoming_payments',"customer_id='$customer_id_', branch_id='$current_branch_', loan_id='$matchingKey', mobile_number='$$account_number_'","uid='$puid'");
            if($update_pay == 1){
                echo "..Success Updating..";
                recalculate_loan($matchingKey);
                $event = "Payment fixed and allocated to loan by system manual process: Original $mobile_number -> $account_number_";
                store_event('o_incoming_payments', $puid,"$event");
            }
            else{
                echo "..Error Updating";
            }

        } else {
            echo "Value {$matchingKey} not found in the array. </br>";
        }


    }





}
else{
    echo "start_date and end_date not set";
}








function removeDuplicateValues($associativeArray) {
    $seenValues = [];
    $resultArray = [];

    foreach ($associativeArray as $key => $value) {
        // Check if the value has been seen before
        if (!in_array($value, $seenValues)) {
            // Add the key-value pair to the result array
            $resultArray[$key] = $value;
            // Add the value to the list of seen values
            $seenValues[] = $value;
        }
    }

    return $resultArray;
}

function searchKeyByValue($associativeArray, $searchValue) {
    foreach ($associativeArray as $key => $value) {
        if ($value === $searchValue) {
            return $key;
        }
    }

    // If the value is not found, you can return a specific value or handle it as needed.
    return null;
}

include_once("../configs/close_connection.inc");