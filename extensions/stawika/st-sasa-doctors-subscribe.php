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

////------APPLY LOAN


$session_code = $data['session_id'];
$device_id = $data['device_id'];
$amount = $data['amount'];
$package_id = $data['package_id'];
$type = $data['type'];
$product_id = 13;


if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
if((input_length($session_code, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid Session Code"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
if($amount < 50){
    $result_ = 0;
    $details_ = '"Please enter a Valid amount"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
if($package_id < 1){
    $result_ = 0;
    $details_ = '"Please select a package"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}



$session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=1","uid, customer_id");
if($session_d['uid'] < 1){
    $result_ = 0;
    $details_ = '"Session Invalid"';
    $result_code = 107;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
else{
    $cust_det = fetchonerow('o_customers',"uid=".$session_d['customer_id']."","primary_product, loan_limit, status, primary_mobile, branch");
    $primary_product = $cust_det['primary_product'];
    $loan_limit = $cust_det['loan_limit'];
    $status = $cust_det['status'];
    $cust_id = $session_d['customer_id'];
    $primary_mobile = $cust_det['primary_mobile'];
    $cust_branch = $cust_det['branch'];


    ////----Add user to Sasa doctors group
    $customer_exists = checkrowexists('o_group_members', "group_id='4' AND customer_id='$cust_id'");
    if ($customer_exists == 1) {
        ///------Activate
        $save = updatedb('o_group_members', "status=1, added_date='$fulldate'", "group_id='4' AND customer_id='$cust_id'");
    } else {
        $fds = array('group_id', 'customer_id', 'added_date', 'added_by', 'status');
        $vals = array("4", "$cust_id", "$fulldate", "0", "1");
        $save = addtodb('o_group_members', $fds, $vals);
    }

    if($type == 'LOAN') {
        if ($save == 1) {
            $give_loan = give_loan($cust_id, $product_id, $amount, 'APP');
            if ($give_loan > 0) {
                ////----Give loan
                $result_ = 1;
                $details_ = '"Your request has been submitted successfully. Please wait"';
                $result_code = 111;
                echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");


            } else {
                $result_ = 0;
                $details_ = '"An internal error occurred. Please try again or contact us"';
                $result_code = 105;
                echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            }
        } else {
            $result_ = 0;
            $details_ = '"An internal error occurred. Please try again or contact us"';
            $result_code = 105;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        }
    }
    else if($type == 'CASH'){

        if ($save == 1) {

            ////----This client has opted to pay cash
           ////----Customer valid, existing Sasa Doctor Loan
            ///-----Create a loan due today with no addons
            $fds = array('customer_id','account_number', 'product_id', 'loan_amount', 'disbursed_amount','total_repayable_amount','total_repaid','loan_balance', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'given_date', 'next_due_date', 'final_due_date', 'added_by','current_lo','current_co', 'current_branch', 'added_date', 'loan_stage', 'application_mode', 'status');
            $vals = array("$cust_id","$primary_mobile", "$product_id", "$amount", "$amount","$amount","0","$amount", "$period", "$period_units", "$pay_frequency", "$payment_breakdown", "$total_instalments", "$total_instalments_paid", "$current_instalment", "$given_date", "".move_to_monday($next_due_date)."", "".move_to_monday($final_due_date)."", "$added_by", "$current_lo","$current_co","$branch", "$added_date", "$loan_stage", "$application_mode", "1");
            $create = addtodb('o_loans', $fds, $vals);
            // updatedb("o_customers", "primary_product = $product_id", "uid = $customer_id");
            if ($create == 1) {

            }
            else{

            }
            ///-----Ask client to pay the loan
            ///-----

        }

    }


    store_event('o_customers', $cust_id,"$result_, $details_, $result_code");




}



