<?php
session_start();
include_once '../configs/20200902.php';
$_SESSION['db_name'] = 'maria_simple';
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);


function send_money2($msisdn, $amount, $loan_id=0){
    global $fulldate;
    global $server2;
    global $api_server;
    if ((input_length($api_server, 5)) == 0) {
        $server = $server2;
    } else {
        $server = $api_server;
    }
    $platform = company_settings();
    $company_id = $platform['company_id'];
    if ($loan_id > 0) {
        ////////-------------If loan ID is provided, check if loan has already been disbursed
        $loan_state = fetchonerow('o_loans', "uid='$loan_id'", "disburse_state, customer_id");
        $disbursed_state = $loan_state['disburse_state'];
        $customer_id = $loan_state['customer_id'];

        if ($disbursed_state != 'NONE') {
            return "Loan already disbursed!";
        } else {
            // $upd = updatedb('o_loans',"disburse_state='INITIATED'","uid='$loan_id'");
            ///---Proceed
        }
    }
    //echo $server;
    //die();
    $consumerKey = fetchrow('o_mpesa_configs', "uid='4'", "property_value");
    $consumerSecret = fetchrow('o_mpesa_configs', "uid='5'", "property_value");

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


    $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest'; //url b2c

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token)); //setting custom header

    $InitiatorName = fetchrow('o_mpesa_configs', "uid='7'", "property_value");;
    $SecurityCredential = fetchrow('o_mpesa_configs', "uid='3'", "property_value");;
    $Amount = $amount;
    $PartyA = fetchrow('o_mpesa_configs', "uid='1'", "property_value");;
    $PartyB = $msisdn;
    $Remarks = "L$loan_id";
    $QueueTimeOutURL = $server . '/apis/mpesa_queue_timeout.php';
    $ResultURL = $server . '/apis/b2c-notice.php?c=' . $company_id . '&r=' . $loan_id;
    $Occasion = '';

    if ($loan_id > 0) {
        updatedb('o_loans', "disburse_state='INITIATED', disbursed=1", "uid='$loan_id'");
        $total_loans = total_customer_loans($customer_id);
    }

    $curl_post_data = array(
        //Fill in the request parameters with valid values
        'InitiatorName' => $InitiatorName, //This is the credential/username used to authenticate the transaction request.
        'SecurityCredential' => $SecurityCredential, //Base64 encoded string of the B2C short code and password,
        'CommandID' => 'PromotionPayment', //Unique command for each transaction type
        'Amount' => $Amount, //The amount being transacted
        'PartyA' => $PartyA, //Organization’s shortcode initiating the transaction.
        'PartyB' => $PartyB, //Phone number receiving the transaction
        'Remarks' => $Remarks, //Comments that are sent along with the transaction.
        'QueueTimeOutURL' => $QueueTimeOutURL, //The timeout end-point that receives a timeout response.
        'ResultURL' => $ResultURL, //The end-point that receives the response of the transaction
        'Occasion' => $Occasion //optional
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    $curl_response = curl_exec($curl);
    print_r($curl_response . $ResultURL);

    $json = $curl_response;
    $j = json_decode($curl_response);
    $state = $j->ResponseCode;
    $desc = $j->ResponseDescription;
    $errorMessage = $j->errorMessage;

    if ($state == '0') {
        //  $more_comment = $current_comment . "<br/>Loan processing started on $fulldate  ";
        //$update_loan = updatedb('s_loans', "disburse_state='', comments='$more_comment'", "uid='$uid'");
        if ($loan_id > 0) {
            updatedb('o_loans', "disburse_state='INITIATED', disbursed=1", "uid='$loan_id'");
        }
    } else {

        //  $more_comment = $current_comment . "<br/>Error! $errorMessage $fulldate ";
        //  $update_loan = updatedb('s_loans', "response_code='$state', comments='$more_comment'", "uid='$uid'");
        //  echo $update_loan;
        if ($loan_id > 0) {
            updatedb('o_loans', "disburse_state='FAILED', disbursed=0", "uid='$loan_id'");
        }
    }
    if ($loan_id > 0) {
        if (input_length($errorMessage, 5) == 1) {
            $errors = ",Errors: $errorMessage";
        } else {
            $errors = "No errors";
        }
        store_event('o_loans', $loan_id, "API Request started on $fulldate with result-> State: $state,  Desc: $desc, $errors. Result sent to $ResultURL");
    }
    return "$state,  $desc, $errorMessage";


}


$send = send_money(254701547942, 10);
include_once("../configs/close_connection.inc");