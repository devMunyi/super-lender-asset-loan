<?php
session_start();
include_once("../../../vendor/autoload.php");
include_once '../../../configs/20200902.php';
include_once ("../../../php_functions/functions.php");
include_once ("../../../configs/conn.inc");
include_once("../../../php_functions/airtel-ug.php");
include_once("../../../configs/airtel-ug.php");


$userd = session_details();
if($userd == null){
    // exit(errormes("Your session is invalid. Please re-login"));

}

$loan_id_enc = intval($_POST['loan_id']);
$loan_id = decurl($_POST['loan_id']);


///////----------------Validation
if($loan_id > 0) {}
else{

    exit(errormes("Loan code needed"));
}

$loan_d = fetchonerow('o_loans',"uid=$loan_id", "loan_code");
$loan_code = trim($loan_d['loan_code'] ?? "");

if($loan_code == ""){
    exit(errormes("Loan code not found"));
}

$resp = b2cTransactionEnquiry($loan_code);
$resp = json_decode($resp, true);
/*
expected response in json format:
{
  "data": {
    "transaction": {
      "id": "APJWL8RVIBWG",
      "message": "Success",
      "status": "TS"
    }
  },
  "status": {
    "response_code": "DP00900001001",
    "code": "200",
    "success": true,
    "result_code": "ESB000010",
    "message": "SUCCESS"
  }
}

*/

// extract status from response
$status = $resp['data']['transaction']['status'] ?? "";
$message = $resp['status']['message'] ?? "";

// send response back to client side
echo(json_encode(array("status"=>$status, "loan_id"=>$loan_id_enc, "loan_code"=>$loan_code, "message"=>$message)));
