<?php
session_start();
include_once '../configs/20200902.php';
include_once("../php_functions/functions.php");
include_once("../php_functions/secondary-functions.php");
$email = trim($_POST['email']);   ///////////Email could be phone
unset($_SESSION['archives']);
$_SESSION['db_name'] = $db_;
include_once("../configs/conn.inc");

$password = trim($_POST['password']);
$deviceid = $_POST['deviceid'];
$browsername = $_POST['browsername'];
$IPAddress = $_POST['IPAddress'];
$OS = $_POST['OS'];

$email_valid = emailOk($email);
$password_valid = input_length($password, 6);


if ($email_valid == 0) {
    ////-----Check if its a number
    if ((validate_phone(make_phone_valid($email))) == 1) {
    } else {
        echo errormes("Email or Mobile number is invalid");
        $result_ = 0;
        die();
    }
}
if ($password_valid == 0) {
    echo errormes("Password Invalid");
    die();
}

if ($password_valid == 1) {
    $userrecord = fetchonerow("o_users", "email='$email'", "uid, status, pass1, company, phone, login_trials, two_fa_enforced");


    $userid = intval($userrecord['uid']);
    $status = $userrecord['status'];
    $trials = $userrecord['login_trials'];

    if ($trials > 2) {
        $remaining = 6 - $trials . " trials remaining";
    }

    if ($userid > 0) {
        if ($status != 1) {
            $result_ = 0;
            echo errormes("Account is disabled. Please contact us");
        } else {
            ///////--------------Company verification
            $company = $userrecord['company'];
            $comp = company_details($company);
            $comp_id = $comp['uid'];
            if ($comp_id > 0) {
                $_SESSION['company_details'] = $comp;
            } else {
                die(errormes("Company details missing from your profile $company"));
            }


            ////////////Password verification
            $databasepass = $userrecord['pass1'];
            $thesalt = fetchRow('o_passes', "user='$userid'", 'pass');

            ////apendsalt to inputted password
            $fullpass = $thesalt . $password;
            $encpass = hash('SHA256', $fullpass);
            ////fetch user pass from db


            if ($encpass == $databasepass) {

                $token = generateToken($userid, $deviceid, $browsername, $IPAddress, $OS);
                if (strlen($token) == 64) {
                    $result_ = 1;
                    $details_ = $token;


                    //-----Check if OTP is enforced
                    if ($OTP_enforced == 1 && $userrecord['two_fa_enforced'] == 1) {
                        $otp = generateRandomNumber(5);
                        $upd = updatedb('o_users', "otp_='$otp'", "uid='$userid'");
                        if ($upd == 1) {
                            /* $phone = $userrecord['phone'];
                            $fds = array('message_body','created_by', 'phone', 'queued_date','status');
                            $vals = array("Your OTP is $otp","$userid", "$phone", "$fulldate",'2');
                            $create = addtodb('o_sms_outgoing', $fds, $vals);
                            //die(errormes()$create);
                            send_sms_bulk($phone, "Your OTP is $otp"); */

                            $redirect_url = "two-factor";
                            if($use_passkey == 1){
                                $redirect_url = "2fa?ut=".encurl($userid);
                            }

                            $enc_userid = encurl($userid);
                            $_SESSION['one-factor'] = 1;
                            echo sucmes("Please wait ...");
                            echo "<meta http-equiv=\"refresh\" content=\"0; URL=$redirect_url\"/>";
                        }
                    } else {
                        $_SESSION['o-token'] = $token;
                        $upd = updatedb('o_users', "login_trials=0", "uid='$userid'");
                        echo sucmes("Success! we are taking you to the dashboard...");
                        echo "<meta http-equiv=\"refresh\" content=\"2; URL=index\"/>";
                    }
                } else {
                    // echo $token."Error generating token";
                    $result_ = 0;
                    echo error("Unable to generate a security token. Please click login again"); ///Unable to generate token
                }
            } else {
                $result_ = 0;
                echo errormes("Password mismatch. $remaining");
                if ($trials > 5) {
                    ///----Lock account
                    $upd = updatedb('o_users', "login_trials = 0, status=3", "uid='$userid'");
                    $event = "Account locked for too many password fails";
                    store_event('o_users', $userid, "$event");
                }
                $upd = updatedb('o_users', "login_trials = login_trials + 1", "uid='$userid'");
            }
        }
    } else {
        $result_ = 0;
        echo errormes("Email or Mobile number does not exist.");
    }
}
