<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$userd = session_details();
$otp = trim($_POST['otp']);   ///////////Email could be phone


include_once("../configs/conn.inc");
//include_once ("../configs/auth.inc");


if (input_length($otp, 4) == 0) {
    echo errormes("Please enter a valid OTP as received");
    die();
}


$userd = session_details();

$user_id = $userd['uid'];
$trials = $userd['login_trials'];

if($trials > 2){
    $remaining = 6-$trials. " trials remaining";
}

if($trials > 5){
    ///----Lock account
    $upd = updatedb('o_users',"login_trials = 0, status=2","uid='$user_id'");
    $event = "Account locked for too many password fails";
    store_event('o_users', $user_id, "$event");
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=login\"/>";
}

$c_otp = fetchrow('o_users',"uid='$user_id'","otp_");
if($otp == $c_otp){
    ///------OTP is valid
    $upd = updatedb('o_users',"otp_=''","uid='$user_id'");
    $upd = updatedb('o_users',"login_trials=0","uid='$user_id'");
    if($upd == 1){
        echo sucmes("Success...");
        echo "<meta http-equiv=\"refresh\" content=\"0; URL=index\"/>";
    }
    else{
        echo errormes("An error occurred, please retry");
    }
}
else{
    echo errormes("Invalid OTP.$remaining");
    $upd = updatedb('o_users',"login_trials=login_trials+1","uid='$user_id'");
}
