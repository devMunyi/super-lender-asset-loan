<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'];

include_once ("../php_functions/functions.php");
include_once ("../configs/auth.inc");

if(emailOk($email) == 1){
   $member = member_details($email);
   $uid = $member['uid'];
   if($uid > 0){
    $company = $member['member_company'];
    $company_d = company_details($company);
    if($company_d['uid'] > 0){
        $result_ = 1;
        $details = $company_d['db_name'];


    }
    else{
        $result_ = 0;
        $details = "Company not found";
    }
   }
   else{
       $result_ = 0;
       $details = "Email not found";
   }
}
else{
    $result_ = 0;
    $details = "Email is invalid";
}


echo json_encode("{\"result_\":$result_,\"details_\":\"$details\"}");