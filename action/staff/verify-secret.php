<?php
session_start();
require("../../vendor/autoload.php");
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? "");
$secret = trim($data['secret'] ?? '');

if (empty($username)) {
    exit(errormes("Please enter a valid username"));
}

if (input_length($secret, 4) == 0) {
    exit(errormes("Please enter a valid secret"));
}

$user_det = fetchmaxid('o_users', "email='$username'", "uid, otp_");
$uid = intval($user_det ['uid'] ?? 0);
if ($uid == 0) {
    exit(errormes("Invalid User ID"));
}

$token = $user_det['otp_'];
$resp = verifyBearerToken($token);
$user = $resp->user;
$username = $user->username;
$secret_decoded = $user->secret;


if($secret_decoded !== $secret){
    exit(errormes("Could Not Complete Request. Please try again!"));
}


$token_det = fetchmaxid("o_tokens", "userid='$uid' AND status=1 AND expiry_date >= '$fulldate'", "token");

if (empty($token_det['token'])) {
    exit(errormes("Invalid token"));
}

///------Passkey is valid, update the user's token
$token = $token_det['token'];
$_SESSION['o-token'] = $token;  // set the token session
unset($_SESSION['one-factor']);  // unset the one-factor session
$upd = updatedb('o_users',"otp_='', login_trials=0","uid='$uid'");

if($upd == 1){
    echo sucmes("Success! Please wait...");
    echo "<meta http-equiv=\"refresh\" content=\"0; URL=index\"/>";
}
else{
    echo errormes("An error occurred, please retry");
}