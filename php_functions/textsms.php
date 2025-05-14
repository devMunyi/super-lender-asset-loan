<?php

function send_via_textsms($number, $message, $unique)
{

    $cred = table_to_obj('o_sms_settings',"status=1","20","property_name","property_value");
    $apikey = $cred['TEXTSMS_APIKEY'];
    $partnerID = $cred['TEXTSMS_PARTNERID'];
    $senderID = $cred['TEXTSMS_SHORTCODE'];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://sms.textsms.co.ke/api/services/sendsms/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
 "apikey":"'.$apikey.'",
 "partnerID":"'.$partnerID.'",
 "message":"'.$message.'",
 "shortcode":"'.$senderID.'",
 "mobile":"'.$number.'"
}



',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Cookie: PHPSESSID=8nktudf8iv44f21889hfanehv8'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;

}