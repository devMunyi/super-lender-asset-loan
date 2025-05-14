<?php
session_start();
if (isset($_GET['limit'])) {
    $limit = $_GET['limit'];
} else {
    $limit = 20;
}

//echo "Start <br/>";

include_once("../php_functions/functions.php");
$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");


include_once("../configs/conn.inc");


$apiKey = "43EZKGYLA5C8UHRP";
$apiSecret = "cd335fb30b9ec179f9e3961416199a88294cda6fea0085e0d247c0039aacc63e";
/*
function get_bearer($key,$secret){
    return base64_encode($key.$secret);
}
echo get_bearer($apiKey,$apiSecret);


die();
*/
echo send_via_digivas2('254716330450', "Test message","0");




function send_via_digivas2($number, $message, $unique){
    $curl = curl_init();

    $cred = table_to_obj('o_digivas_credentials',"status=1","20","key_","value_");
    $client_id = $cred['client_id'];
    $product_id = $cred['product_id'];
    $token = $cred['bearer'];

    echo "$client_id, $product_id, $token <br/>";

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
  //  $upd = updatedb('o_summaries',"value_='$credits'","name='SMS_BALANCE'");
    return $response;
}

//echo "End <br/>";

include_once("../configs/close_connection.inc");
