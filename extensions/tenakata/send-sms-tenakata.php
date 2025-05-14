<?php
session_start();
$company = $_GET['c'];

    include_once("../configs/20200902.php");
$db = $db_;
$_SESSION['db_name'] = $db;
    include_once("../configs/auth.inc");
    include_once("../php_functions/functions.php");
    include_once("tenakata_sms.php");

    $company_d = company_details($company);


        include_once("../configs/conn.inc");


       // echo send_sms_bulk(254716330450, "Testing hello");
//////This file runs once every day to do a trail of things e.g. Send reminders
///---------Send SMS
     $balance = 0;
        $unsent = fetchtable('o_sms_outgoing', "status=1", "uid", "asc", "5", "uid, phone, message_body");
        while ($un = mysqli_fetch_array($unsent)) {


            $uid = $un['uid'];
            $phone = $un['phone'];
            $message_body = $un['message_body'];
            $update_ = updatedb('o_sms_outgoing', "status=2, sent_date='$fulldate'", "uid='$uid'"); ////Mark SMS as sent already

            if ((validate_phone(($phone))) == 1 && input_available($message_body) == 1) {
                $res = sendSMS($phone, $message_body)."<br/>";


            }

        }






