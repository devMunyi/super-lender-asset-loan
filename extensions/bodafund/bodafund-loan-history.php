<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();
$_SESSION['db_name'] = 'bodafund_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


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
    $loan_status_color = table_to_obj('o_loan_statuses',"uid > 0","100","uid","color_code");
    $loans = fetchtable('o_loans',"customer_id='".$session_d['customer_id']."' AND status!=0 AND disbursed=1","uid","desc","1000","uid, loan_balance, loan_amount, given_date ,disbursed, final_due_date, paid, status");
   $all_loans_array = array();
   $loan_count = 0;
    while($l = mysqli_fetch_array($loans)){
        ////----Has an outstanding loan
        $has_loan = 1;
        $one_loan = array();
        $one_loan['uid'] = $l['uid'];
        $one_loan['loan_balance'] = round($l['loan_balance'], 0);
        $one_loan['loan_amount'] = round($l['loan_amount'],0);
        $one_loan['final_due_date'] = $l['final_due_date'];
        $one_loan['given_date'] = $l['given_date'];
        $one_loan['status'] = $l['status'];
        $one_loan['paid'] = $l['paid'];
        $one_loan['state'] = $loan_statuses[$l['status']];
        $one_loan['state_code'] = $loan_status_color[$l['status']];


        $all_loans_array[$loan_count] = $one_loan;
        $loan_count = $loan_count + 1;
    }


    $result_ = 1;
    $details_ = json_encode(json_encode($all_loans_array));
    $result_code = 111;

    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");

}



