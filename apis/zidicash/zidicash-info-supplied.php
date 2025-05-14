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
    ////----You have supplied documents
    $cid = $session_d['customer_id'];
    $available_files = table_to_obj('o_documents',"status=1 AND tbl='o_customers' AND rec='$cid'","50","category","status");
    $result_ = 1;


    /////////---------------_If client has not applied any loan, or client has an active/cleared loan, don't ask for documents yet
    $latest_loan = fetchmaxid('o_loans',"customer_id='$cid'","uid, status, disbursed");
    $latest_loan_id = $latest_loan['uid'];
    $latest_status = $latest_loan['status'];

    if($latest_loan_id > 0){
        ////-----Has a loan
         ///----Check if client has at least 1 disbursed loan
        $has_disbursed = checkrowexists('o_loans',"customer_id='$cid' AND disbursed=1");
        if($has_disbursed == 1){
             ////Clients to be able to view dashboard
            $result_ = 2;
        }
    }
    else{
        ////----No loan, let them apply first
        ////---------Client to be able to view dashboard
           $result_ = 1;
    }


    $details_ = json_encode($available_files);
    $result_code = 111;

    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");

}



include_once("../configs/close_connection.inc");