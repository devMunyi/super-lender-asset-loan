<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();
$_SESSION['db_name'] = 'zidicash_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


////------APPLY LOAN


$session_code = $data['session_id'];
$device_id = $data['device_id'];
$amount = $data['amount'];
$product_id = $data['product_id'];


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
if($product_id < 1){
    $result_ = 0;
    $details_ = '"Product not selected. Please contact us"';
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


    /////------Check if customer has an active loan
    $has_loan = checkrowexists('o_loans',"customer_id='".$session_d['customer_id']."' AND disbursed=0 AND paid=0 AND status!=0 AND status in (1,2)");
    if($has_loan == 1){
        $result_ = 0;
        $details_ = '"You have a pending loan, please wait while we review it."';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();
    }
    $has_loan = checkrowexists('o_loans',"customer_id='".$session_d['customer_id']."' AND disbursed=1 AND paid=0 AND status!=0");
    if($has_loan == 1){
        $result_ = 0;
        $details_ = '"You have an existing loan. Please repay it to get a new one"';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();
    }
    ////-----Check if customer account is active
    if($status != 1){
        $result_ = 0;
        $details_ = '"Your Account is inactive, please contact support"';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();
    }
    if($amount > $loan_limit){
        $result_ = 0;
        $details_ = '"Your Allowed limit is '.$loan_limit.'"';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();
    }
    $y = fetchonerow("o_loan_products","uid='$product_id'","*");
    $period = $y['period'];
    $period_units = $y['period_units'];
    $min_amount = $y['min_amount'];
    $max_amount = $y['max_amount'];
    $pay_frequency = $y['pay_frequency'];
    $percent_breakdown = $y['percent_breakdown'];
    $automatic_disburse = $y['automatic_disburse'];
    $added_date = $y['added_date'];
    /// -----Check if amount is allowed
    if($amount < $min_amount || $amount > $max_amount){
        $result_ = 0;
        $details_ = '"The product allows amounts between '.$min_amount.' AND '.$max_amount.'"';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();
    }





    /// -----Create Loan
    $given_date = $date;         ////Initialization
    ////Calculated from product
    $final_due_date = final_due_date($given_date, $period, $period_units);         ////Calculated from product
    $transaction_date = $fulldate;         ////Initialization
    $added_date = $fulldate;
    $loan_stage_d = fetchminid('o_product_stages', "product_id='$primary_product' AND status=1 AND is_final_stage=1", "stage_order, uid");
    $loan_stage = $loan_stage_d['stage_id'];

    $total_instalments = total_instalments($period, $period_units, $pay_frequency);         //////Calculated from product
    $total_instalments_paid = 0.00;  /////Initialization
    $current_instalment = 1;         ////Initialization

    $next_due_date = next_due_date($given_date, $period, $period_units, $pay_frequency);
    ////------Create a Loan
    $fds = array('customer_id', 'account_number', 'product_id', 'loan_amount', 'disbursed_amount','total_repayable_amount','total_repaid','loan_balance', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'given_date', 'next_due_date', 'final_due_date', 'added_by', 'current_branch', 'added_date', 'loan_stage', 'application_mode', 'status');
    $vals = array("$cust_id", "$primary_mobile", "$primary_product", "$amount", "$amount","$amount","0","$amount", "$period", "$period_units", "$pay_frequency", "$pay_frequency", "$total_instalments", "$total_instalments_paid", "$current_instalment", "$given_date", "$next_due_date", "$final_due_date", "1", "$cust_branch", "$added_date", "$loan_stage", "APP", "1");
   // die(json_encode($vals));
    $create = addtodb('o_loans', $fds, $vals);
    if ($create == 1) {
         ////----Send money
        $latest_loan = fetchmaxid('o_loans', "customer_id='$cust_id'", "uid");
        ////////-----------Add Automatic AddOns
        $addons = fetchtable('o_product_addons',"product_id='$primary_product'","addon_id","asc","20","addon_id");
        while($addon = mysqli_fetch_array($addons)){
            $addon_id = $addon['addon_id'];
            $automatic = fetchrow('o_addons',"uid='$addon_id' AND from_day = 0","automatic");
            if($automatic == 1){
                apply_loan_addon_to_Loan($addon_id, $latest_loan['uid'], false);
            }

        }
        mpesa_addon(4, $latest_loan['uid'], false);
        recalculate_loan($latest_loan['uid'], true);

        if(($primary_product == 9 || $primary_product == 1) && $amount >= 50000){
            $auto_disburse = 0;
        }
        if($automatic_disburse == 1) {
           // send_money($primary_mobile, $amount, $latest_loan['uid']);
            ///-------Send money if its qualified
            $latest_lid = $latest_loan['uid'];
            $total_loans = countotal('o_loans',"disbursed='1' AND paid='1' AND customer_id='" . $cust_id . "' AND status!=0 AND given_date >= '2022-04-01' AND uid != '$latest_lid'");   /////---Has cleared at least one loan since the new system
            $days_3 = datesub($date, 0,0, 3);
            $recent_loan = checkrowexists("o_loans", "customer_id='" . $cust_id. "'  AND given_date >= '$days_3' AND uid != '$latest_lid'");  ///Customer has not taken a loan last 3 days. Fraud prevention
            if($total_loans >= 1 AND $recent_loan == 0){
                ///----Customer qualifies for auto disbursement
                  ////----Update loan status to pending disbursement
                  $res = $latest_lid;
                  $update_ = updatedb('o_loans',"status=2","uid='$res'");
                $fds = array('loan_id','amount','added_date','trials','status');
                $vals = array("$latest_lid","$amount","$fulldate",'0','1');
                $queue = addtodb('o_mpesa_queues', $fds, $vals);
                store_event('o_loans', $res, "Queued for automatic processing Result $res");
            }
            else{
                store_event('o_loans', $res, "Not sent automatiacally because (total loans: $total_loans and recent loan: $recent_loan)");
            }


            ///------End of send money if its qualified
        }
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
    store_event('o_customers', $cust_id,"$result_, $details_, $result_code");




}



include_once("../configs/close_connection.inc");