<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();


$_SESSION['db_name'] = 'zidicash_db';
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");



/////----Signup
////

$full_name = trim($data['full_name']);
$dob = trim($data['dob']);
$primary_phone = make_phone_valid(trim($data['primary_phone']));
$enc_phone = hash('sha256', $primary_phone);
$national_id = trim($data['national_id']);
$pin = trim($data['pin']);
$email_address = trim($data['email_address']);
$gender = $data['gender'];
$company_name = trim($data['company_name']);
$net_income = trim($data['net_income']);
$device_id = trim($data['device_id']);


$date_diff = datediff($dob, $date);


if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((input_length($full_name, 5)) == 0){
    $result_ = 0;
    $details_ = '"Name too short"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((input_length($dob, 10)) == 0){
    $result_ = 0;
    $details_ = '"Please enter your date of birth"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if($date_diff < 6570 || $date_diff > 365000 ){
    $result_ = 0;
    $details_ = '"You need to be at least 18 years to signup. Enter Birth Date"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((validate_phone($primary_phone)) == 0){
    $result_ = 0;
    $details_ = '"Phone number is invalid"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((input_between(6, 10, $national_id)) == 0){
    $result_ = 0;
    $details_ = '"National ID is invalid"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}

if($gender!='M' AND $gender!='F'){
    $result_ = 0;
    $details_ = '"Please specify your Gender"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if($net_income < 4000){
    $result_ = 0;
    $details_ = '"Please specify net income"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((emailOk($email_address)) == 0){
    $result_ = 0;
    $details_ = '"Email is invalid"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((input_length($dob, 5)) == 0){
    $result_ = 0;
    $details_ = '"Please enter your company name"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}

if((strlen($pin)) != 4){
    $result_ = 0;
    $details_ = '"Please enter a 4 digit PIN"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}



////---------------------Check if phone exists
$phone_exists = checkrowexists('o_customers',"primary_mobile='$primary_phone'");
if($phone_exists == 1){
    $result_ = 0;
    $details_ = '"The phone number exists"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
////---------------------Check if national_id exists
$id_exists = checkrowexists('o_customers',"national_id='$national_id'");
if($id_exists == 1){
    $result_ = 0;
    $details_ = '"National ID Exists"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}



/////--------------------Check if email exists
$email_exists = checkrowexists('o_customers',"email_address='$email_address'");
if($email_exists == 1){
    $result_ = 0;
    $details_ = '"Account with similar email exists"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}



////----Create User
$limit = $net_income / 6;
if($limit > 15000){
    $limit = 15000;
}
elseif ($limit < 3000){
    $limit = 3000;
}

$limit = round($limit / 500) * 500;

$sec_ = '{"1": "'.addslashes($company_name).'", "12": "'.$net_income.'"}';



$fds = array('full_name','primary_mobile','phone_number_provider','enc_phone','email_address','national_id','gender','dob','added_by','added_date','branch','primary_product','loan_limit','sec_data','pin_','device_id','flag','status');
$vals = array("".addslashes($full_name)."","$primary_phone","1","$enc_phone","$email_address","$national_id","$gender","$dob","1","$fulldate","1","1","$limit",''.$sec_.'',"".md5($pin)."","$device_id","8","1");
$create = addtodb('o_customers', $fds, $vals);
if($create == 1){
    $result_ = 1;
    $details_ = '"Success signing up"';

   // $send = send_via_digivas(make_phone_valid($primary_phone), "Your new PIN is ".$new_pin, 0)."<br/>";
    $fds = array('phone','message_body','queued_date','sent_date','created_by','status');
    $vals = array(make_phone_valid($primary_phone),"Welcome to Zidi, $full_name. We are excited to have you onboard. In case of any questions. Contact us on 0714439868", "$fulldate","$fulldate","1","1");
    $save_ = addtodb('o_sms_outgoing', $fds, $vals);

    $result_code = 111;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
else{
    $result_ = 0;
    $details_ = '"Error signing up"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();

}
include_once("../configs/close_connection.inc");