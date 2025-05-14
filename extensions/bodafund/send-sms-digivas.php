<?php
session_start();
$company = $_GET['c'];

    include_once("../configs/20200902.php");
$db = $db_;
$_SESSION['db_name'] = $db;
    include_once("../configs/auth.inc");
    include_once("../php_functions/functions.php");

    $company_d = company_details($company);




        include_once("../configs/conn.inc");


       // echo send_sms_bulk(254716330450, "Testing hello");
//////This file runs once every day to do a trail of things e.g. Send reminders
///---------Send SMS
        $unsent = fetchtable('o_sms_outgoing', "status=1", "uid", "asc", "5", "uid, phone, message_body");
        while ($un = mysqli_fetch_array($unsent)) {

            $uid = $un['uid'];
            $phone = $un['phone'];
            $message_body = $un['message_body'];
            $update_ = updatedb('o_sms_outgoing', "status=2, sent_date='$fulldate'", "uid='$uid'"); ////Mark SMS as sent already

            if ((validate_phone(($phone))) == 1 && input_available($message_body) == 1) {
                echo send_bulk($phone, $message_body, $uid)."<br/>";
            }

        }






function send_bulk($number, $message, $unique)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.digivas.co.ke/vas/api/Bulk_SMS',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
        "unique_ref":"' . $unique . '",
        "clientId":"96",
        "dlrEndpoint":"https://example.com/test",
        "productId":"128",
        "msisdn":"' . $number . '",
        "message":"' . $message . '"
        }',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer NVZQN0tSQVpHVzJGUVVCUzU5YWJkMjIxN2EzNGUxNjc4YzQzNTg2YTFjYzA0ZjUwM2RhY2U4YjdhZTNkZDJiMmFlNWEyMDk5OTQ4MzUxZmY=',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    $data = json_decode($response, true);
    $credits = $data['credits'];

    $upd = updatedb('o_summaries',"value_='$credits'","uid=3");

    //echo $upd.',';
    curl_close($curl);
    ////----Returning balance
    return $credits;
}

