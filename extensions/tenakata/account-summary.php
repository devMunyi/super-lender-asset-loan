<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();
$_SESSION['db_name'] = 'tenakata_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


////------------ACCOUNT SUMMARY


$session_code = $data['session_id'];
$device_id = $data['device_id'];

if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((input_length($session_code, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid Session Code"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}


$session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=1","uid, customer_id");
if($session_d['uid'] < 1){
    $result_ = 0;
    $details_ = '"Session Invalid"';
    $result_code = 107;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
else{
    ////----You have customer ID
    $loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","100","uid","name");
    $cust_details = fetchonerow('o_customers',"uid='".$session_d['customer_id']."'","full_name, primary_mobile, loan_limit, status, primary_product, sec_data");



    ///----Check if has pending loan

    $loan_det = fetchonerow('o_loans',"customer_id='".$session_d['customer_id']."' AND disbursed=0 AND paid=0 AND status NOT in (0, 10, 11) ","uid, loan_amount");
    if($loan_det['uid'] > 0){
        $result_ = 0;
        $details_ = '"You have a pending loan of '.$loan_det['loan_amount'].', please wait while we review it."';
        $result_code = 108;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        die();
        exit();
    }
    //


    $latest_loan = fetchonerow('o_loans',"customer_id='".$session_d['customer_id']."' AND status!=0 AND disbursed=1 AND paid=0","uid, loan_balance, disbursed, final_due_date, status");
    $latest_loan_details = array();
    $product_details = array();
    $savings_total = totaltable('o_incoming_payments',"customer_id = ".$session_d['customer_id']." AND status=1 AND payment_category=4","amount");
    if($latest_loan['uid'] > 0){
        ////----Has an outstanding loan

        $has_loan = 1;
        $latest_loan_details['uid'] = $latest_loan['uid'];
        $latest_loan_details['loan_balance'] = $latest_loan['loan_balance'];
        $latest_loan_details['final_due_date'] = $latest_loan['final_due_date'];
        $latest_loan_details['final_due_date_days'] = fancydate($latest_loan['final_due_date']);
        $latest_loan_details['status'] = $latest_loan['status'];
        $latest_loan_details['state'] = $loan_statuses[$latest_loan['status']];
        $latest_loan_details['savings_total'] = $savings_total;
    }
    else{
        $has_loan = 0;

        ///----No outstanding loan
        /// -----Check the current product

    }

    $prod = fetchonerow('o_loan_products',"uid='".$cust_details['primary_product']."'","uid, name, description, period, period_units, min_amount, max_amount, pay_frequency");
    $product_details['uid'] = $prod['uid'];
    $product_details['name'] = $prod['name'];
    $product_details['description'] = $prod['description'];
    $product_details['period'] = $prod['period'] * $prod['period_units'];
    $product_details['min_amount'] = $prod['min_amount'];
    $product_details['max_amount'] = $prod['max_amount'];
    $product_details['pay_frequency'] = $prod['pay_frequency'];
    $product_details['savings_total'] = $savings_total;

    $product_details['due_date'] = readabledate(dateadd($date, 0, 0, $prod['period'] * $prod['period_units']));





    $limit = $cust_details['loan_limit'];
    $result_ = 1;
    $details_ = json_encode($latest_loan_details);
    $product_ = json_encode($product_details);
    $result_code = 111;

    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"has_loan\":$has_loan, \"product_\":$product_, \"loan_limit\":$limit, \"result_code\":$result_code}");

}



