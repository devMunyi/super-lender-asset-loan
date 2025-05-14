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


    curl_close($curl);
    ////----Returning balance
    return $credits;
}
