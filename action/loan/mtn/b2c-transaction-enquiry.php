<?php
session_start();
include_once("../../../vendor/autoload.php");
include_once '../../../configs/20200902.php';
include_once ("../../../php_functions/functions.php");
include_once ("../../../configs/conn.inc");
include_once("../../../php_functions/mtn_functions.php");
include_once("../../../configs/mtn_configs.php");


$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
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

$resp = mtn_send_money_status($loan_code);
/*

{"payload":{"amount":"300000","currency":"UGX","financialTransactionId":"25174202902","externalId":"2-43902-1-9014-e812b0af-6005-4e94-b4a5-9497d0e076e9","payee":{"partyIdType":"MSISDN","partyId":"256770949754"},"payerMessage":"Requested Loan","payeeNote":"Congratulations. Your loan of UGX300,000.00 was approved","status":"SUCCESSFUL"},"details":"Error: status code: 200"}

*/

$status = $resp['payload']['status'] ?? "";

if($status == 'SUCCESSFUL'){
    $status = 'TS';
    $message = "Transaction successful";
}else {
    $status = 'TF';
    $message = $resp['payload']['message'] ?? "Transction status unknown";

}

// send response back to client side
echo(json_encode(array("status"=>$status, "loan_id"=>$loan_id_enc, "loan_code"=>$loan_code, "message"=>$message)));
