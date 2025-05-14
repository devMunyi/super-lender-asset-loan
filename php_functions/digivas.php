<?php

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
        CURLOPT_POSTFIELDS =>'{
        "unique_ref":"'.$unique.'",
        "clientId":"94",
        "dlrEndpoint":"https://example.com/test",
        "productId":"125",
        "msisdn":"'.$number.'",
        "message":"'.$message.'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer NkRKOEU0M01ZWDJLSFFMWmVlZjk5MWQ0ZDhiNWM2ZTI2NGU0MTYwMzlmZGNlMmUwMjgwMzQwOTRiNjE0YThkMmEzMjlkNDY5NjZhNDg5MjY=',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    $data = json_decode($response, true);
    $credits = $data['credits'];


    curl_close($curl);
    ////----Returning balance
    return $credits;
}

function send_via_digivas($number, $message, $unique){
    $curl = curl_init();

    $cred = table_to_obj('o_digivas_credentials',"status=1","20","key_","value_");
    $client_id = $cred['client_id'];
    $product_id = $cred['product_id'];
    $token = $cred['bearer'];

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.digivas.co.ke/vas/api/Bulk_SMS',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
        "unique_ref":"'.$unique.'",
        "clientId":"'.$client_id.'",
        "dlrEndpoint":"https://example.com/test",
        "productId":"'.$product_id.'",
        "msisdn":"'.$number.'",
        "message":"'.$message.'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$token.'',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    $data = json_decode($response, true);
    $credits = $data['credits'];


    curl_close($curl);
    ////----Returning balance
    $upd = updatedb('o_summaries',"value_='$credits'","name='SMS_BALANCE'");
    return $credits;
}