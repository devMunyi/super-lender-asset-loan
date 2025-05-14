<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$phone = $_POST['phone'];
$amount = $_POST['amount'];
$ref = $_POST['phone'];



///////----------------Validation
if($amount > 5) {}
else{

    die(errormes("Amount is invalid"));
    exit();
}
if(validate_phone($phone) != 1){
    die(errormes("Phone is invalid"));
    exit();
}
$stk = send_stk($phone, $amount, $ref);
echo sucmes("STK Push sent");

//echo sucmes(send_stk($phone, $amount, $ref));
   
?>


