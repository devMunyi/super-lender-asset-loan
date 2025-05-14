<?php
function send_via_bonga($number, $message)
{
    global $bonga_credentials;
    $api_key = $bonga_credentials['API_Key'];
    $client_id = $bonga_credentials['API_Client_ID'];
    $secret = $bonga_credentials['API_Secret'];

    $curl = curl_init();


    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.bongasms.co.ke/send/index.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('apiClientID' => ''.$client_id.'', 'serviceID' => '5757', 'key' => ''.$api_key.'', 'secret' => ''.$secret.'', 'txtMessage' => "$message", 'MSISDN' => "$number"),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;

}


function bongasms_bal()
{
    global $BONGA_SMS_apiClientID;
    global $BONGA_SMS_key;

    $url = "https://app.bongasms.co.ke/api/check-credits?apiClientID=$BONGA_SMS_apiClientID&key=$BONGA_SMS_key";
    
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, true);
}

