<?php
$apiKey = '5VP7KRAZGW2FQUBS';
$apiSecret = '59abd2217a34e1678c43586a1cc04f503dace8b7ae3dd2b2ae5a2099948351ff';
$token = base64_encode($apiKey.$apiSecret);
$requestbody = array(
    'unique_ref' => '14', //Your own unique reference number
    'clientId' => '96',
    'dlrEndpoint' => 'https://example.com/test',
    'productId' =>'128',
    'msisdn' => '254716330450',
    'message' => 'SMS test'
);
header('Content-Type: application/json'); // Specify the type of data
$ch = curl_init('https://api.digivas.co.ke/vas/api/Bulk_SMS'); // Initialise cURL
$requestbody = json_encode($requestbody); // Encode the data array into a JSON string
echo $token;
$authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' ,
    $authorization )); // Inject the token into the header
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestbody); // Set the posted fields
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
$result = curl_exec($ch); // Execute the cURL statement
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    var_dump($error_msg);
}
curl_close($ch); // Close the cURL connection
echo $result;