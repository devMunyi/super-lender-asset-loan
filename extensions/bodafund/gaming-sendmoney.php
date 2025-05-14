<?php
session_start();

include_once("../../php_functions/functions.php");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);


function send_money3($msisdn, $amount, $loan_id=0){
    global $fulldate;
    global $server2;
    if((input_length($server2, 5)) == 0){
        $server = 'https://superlender.co.ke/zidicash/';
    }
    else{
        $server = $server2;
    }
    $platform = company_settings();
    $company_id = $platform['company_id'];

   // echo $server;
    //die();
    $consumerKey = "o9q33r3od3Y0oykNIlJdehJc1kaAL5X0kLWm9vMPLZRGOSgB";
    $consumerSecret = "Zz5oqLm6QyFrMUM3VWQHt2AwGpS6gbjnzNgLhAttLyA1K9bvSqQFsttFM1aJTUZP";

    $headers = ['Content-Type:application/json; charset=utf8'];
    $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_HEADER, false);

    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $result = json_decode($result);
    $access_token = $result->access_token;

    echo "Token".$access_token;


    $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';//url b2c

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token)); //setting custom header

    $InitiatorName = 'HAYAPI';
    $SecurityCredential = 'cht0RZwPIaDrMUNZUMO4Tr9ZDV845FulyOJ1TIuq59PbKSaxv8uSRCvqznmvcU+ToM2hMKBKNqCHUQQV6M/HUKpalNpnnvHyaWl7KMCaVZIx3Iwf5htpJz4CibuQS1bcWolcIhVVpAwqrR7C6qfm3fJUE/etmvtLCrIN22BdIu9PS/cE3BaNYipXvHO1QOKw9PnCX/5IouVF0FYPtGbNgUgGa0d43Je4wU71dBam55eBIZgn4ndruyMvl/YHQqcd9u/e2E2O5Iv6IEM9zk7u1X8X+adBY1JVClSfrey5eYS/iwZSYq/9nbgKZdA8+Hgg1fi3j7PUUGVpUN2VUeZznQ==';
    $Amount = $amount;
    $PartyA =  4954120;
    $PartyB = $msisdn;
    $Remarks = "L$loan_id";
    $QueueTimeOutURL = $server.'/apis/mpesa_queue_timeout.php';
    $ResultURL = $server.'/apis/b2c-notice.php?c='.$company_id.'&r='.$loan_id;
    $Occasion = '';

   // echo $consumerKey;

    $curl_post_data = array(
        //Fill in the request parameters with valid values
        'InitiatorName' => $InitiatorName,//This is the credential/username used to authenticate the transaction request.
        'SecurityCredential' => $SecurityCredential,//Base64 encoded string of the B2C short code and password,
        'CommandID' => 'PromotionPayment',//Unique command for each transaction type
        'Amount' => $Amount,//The amount being transacted
        'PartyA' => $PartyA,//Organization’s shortcode initiating the transaction.
        'PartyB' => $PartyB,//Phone number receiving the transaction
        'Remarks' => $Remarks,//Comments that are sent along with the transaction.
        'QueueTimeOutURL' => $QueueTimeOutURL,//The timeout end-point that receives a timeout response.
        'ResultURL' => $ResultURL,//The end-point that receives the response of the transaction
        'Occasion' => $Occasion //optional
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    $curl_response = curl_exec($curl);
    print_r($curl_response.$ResultURL);

    $json = $curl_response;
    $j = json_decode($curl_response);
    $state = $j->ResponseCode;
    $desc = $j->ResponseDescription;
    $errorMessage = $j->errorMessage;


    echo "$curl_response resp. $ResultURL";


}

function send_stk2($phone, $amount, $account = "")
{
    if ($amount > 5) {
        $amount = floor($amount);
    } else {

        return errormes("Amount is invalid");
    }
    if (validate_phone($phone) != 1) {
        return errormes("Phone is invalid");
    }



    $consumerKey = "o9q33r3od3Y0oykNIlJdehJc1kaAL5X0kLWm9vMPLZRGOSgB";
    $consumerSecret = "Zz5oqLm6QyFrMUM3VWQHt2AwGpS6gbjnzNgLhAttLyA1K9bvSqQFsttFM1aJTUZP";
    $shortcode = 4954120;
    $passkey = '9be2092f37357c4996038784f04c1ef91b0c1f87323ab319d67ce127fccd2dcd';
    $initiator = 'HAYAPI';

    $headers = ['Content-Type:application/json; charset=utf8'];
    $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_HEADER, false);

    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $result = json_decode($result);
    $access_token = $result->access_token;
    //echo 'The access token is: '.$access_token;

    $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'; //sktpush url
    $BusinessShortCode = '' . $shortcode . ''; //shortcode
    $Passkey = '' . $passkey . ''; //passkey
    $InitiatorName = '' . $initiator . '';
    $Timestamp = '20' . date("ymdhis"); //timestamp
    $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp); //password encoded Base64



    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token)); //setting custom header

   // echo $access_token;
    $curl_post_data = array(
        //Fill in the request parameters with valid values
        'BusinessShortCode' => $BusinessShortCode, //The organization shortcode used to receive the transaction.
        'InitiatorName' => $InitiatorName, //This is the credential/username used to authenticate the transaction request.
        'Password' => $Password, //This is generated by base64 encoding BusinessShortcode, Passkey and Timestamp.
        'Timestamp' => $Timestamp, //The timestamp of the transaction in the format yyyymmddhhiiss.
        'TransactionType' => 'CustomerPayBillOnline', //The transaction type to be used for this request.
        'Amount' => '' . $amount . '', //The amount to be transacted.
        'PartyA' => '' . $phone . '', //The MSISDN sending the funds.
        'PartyB' => $BusinessShortCode, //The organization shortcode receiving the funds
        'PhoneNumber' => '' . $phone . '', //The MSISDN sending the funds.
        'CallBackURL' => 'https://www.superlender.co.ke/', //The url to where logs from M-Pesa will be sent to.
        'AccountReference' => '' . $account . '', //Used with M-Pesa PayBills.
        'TransactionDesc' => 'Pay' //A description of the transaction.
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    $curl_response = curl_exec($curl);
    return $curl_response;
}




echo send_stk2(254716330450, 10,"78");
