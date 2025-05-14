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

/////----Signup
////

$full_name = trim($data['full_name']);
$dob = trim($data['dob']);
$primary_phone = make_phone_valid(trim($data['primary_phone']));
$national_id = trim($data['national_id']);
$pin = trim($data['pin']);
$email_address = trim($data['email_address']);
$gender = $data['gender'];
$income_source = trim($data['income_source']);
$home_address = trim($data['home_address']);
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
if($income_source < 1){
    $result_ = 0;
    $details_ = '"Please specify income source"';
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
if((input_length($home_address, 5)) == 0){
    $result_ = 0;
    $details_ = '"Home address is required"';
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

$fds = array('full_name','primary_mobile','email_address','physical_address','national_id','gender','dob','added_by','added_date','branch','primary_product','loan_limit','pin_','device_id','flag','status');
$vals = array("".addslashes($full_name)."","$primary_phone","$email_address","".addslashes($home_address)."","$national_id","$gender","$dob","1","$fulldate","2","1","0","".md5($pin)."","$device_id","8","2");
$create = addtodb('o_customers', $fds, $vals);
if($create == 1){
    $result_ = 1;
    $details_ = '"Success signing up"';
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




