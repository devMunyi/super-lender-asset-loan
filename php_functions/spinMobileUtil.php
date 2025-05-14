<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;

function statusQuerySm($scoreType, $uniqueId, $decrypter) {
    $client = new Client();

    $authorizationToken = getAccessTokenSm();
    if (!$authorizationToken) {
        return null;
    }
    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => $authorizationToken
    ];

    $body = json_encode([
        'score_type' => $scoreType,
        'unique_id' => $uniqueId
    ]);

    if(!empty(trim($decrypter))) {
        // add decrypter to the body
        $body = json_encode([
            'score_type' => $scoreType,
            'unique_id' => $uniqueId,
            'decrypter' => trim($decrypter)
        ]);
    }

    // echo "body : $body<br>";

    $apiBaseURL = getAPIBaseURLSm();
    $request = new Request('POST', "$apiBaseURL/analytics/status-query/", $headers, $body);
    

    $response = $client->sendAsync($request)->wait();
    return json_decode($response->getBody(), true);
    
}

function analysisQuerySm($scoreType, $uniqueId, $decrypter) {
    $client = new Client();
    $authorizationToken = getAccessTokenSm();
    if (!$authorizationToken) {
        return null;
    }

    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => $authorizationToken
    ];
    $body = json_encode([
        'score_type' => $scoreType,
        'unique_id' => $uniqueId
    ]);

    if(!empty(trim($decrypter))) {
        $body = json_encode([
            'score_type' => $scoreType,
            'unique_id' => $uniqueId,
            'decrypter' => trim($decrypter)
        ]);
    }

    $apiBaseURL = getAPIBaseURLSm();
    $request = new Request('POST', "$apiBaseURL/analytics/analysis-query/", $headers, $body);

    $response = $client->sendAsync($request)->wait();
    return json_decode($response->getBody(), true);
}

function getAPIBaseURLSm()
{
    global $environment;
    return ($environment == 'staging') ? 'https://stage-radicrunch.spinmobile.co/api' : 'https://api.spinmobile.co/api';
}


/**
 * Get Spin Mobile access token
 * 
 * @return string|null
 */
function getAccessTokenSm(): ?string
{
    global $spin_consumer_key;
    global $spin_consumer_secret;

    $client = new Client();

    $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ];

    $body = json_encode([
        'consumer_key' => $spin_consumer_key,
        'consumer_secret' => $spin_consumer_secret
    ]);

    $apiBaseURL = getAPIBaseURLSm();

    $request = new Request('POST', "$apiBaseURL/analytics/auth/", $headers, $body);
    $response = $client->sendAsync($request)->wait();

    if ($response->getStatusCode() !== 200) {
        return null;
    }

    $data = json_decode($response->getBody(), true);

    return $data['token'] ?? null;
}

$documentTypes = [
    'MPESA' => 'MPESA Statement',
    'BANK' => 'Bank Statement',
    'TILL' => 'MPESA Till',
    'PAYBILL' => 'MPESA Paybill	PAYBILL'
];

// spin/analysis types
$spinTypes = [
    'INDIVIDUAL' => 'Individual',
    'COMBINED' => 'Combined'
];

class DocumentType
{
    const MPESA = 'MPESA';
    const BANK = 'BANK';
    const TILL = 'TILL';
    const PAYBILL = 'PAYBILL';
}

/*
    * Banks key-value pairs
    * key is the bank code
    * value is the bank name
    */
$banks = [
    "MPESA" => "MPESA",
    "CENTENARY" => "Centenary Bank",
    "UBA" => "UBA Bank",
    "COOP" => "Co-operative Bank",
    "KCB" => "Kenya Commercial Bank",
    "HFC" => "HFC Bank",
    "Equity" => "Equity Bank",
    "NBK" => "NATIONAL Bank",
    "NIC" => "NIC Bank",
    "SBM" => "SBM Bank",
    "SIDIAN" => "SIDIAN Bank",
    "DTB" => "DTB Bank",
    "CREDIT" => "CREDIT Bank",
    "FAULU" => "FAULU Bank",
    "SCB" => "Standard Chartered Bank",
    "RAFIKI" => "Rafiki MFB",
    "KINGDOM" => "Kingdom Bank",
    "FAULU" => "Faulu MFB",
    "CREDIT" => "Credit Bank",
    "FORTUNE" => "Fortune Bank",
    "NCBA" => "NCBA Bank",
    "NCBALoop" => "NCBA Loop",
    "HABIB" => "Habib Bank",
    "PRIME" => "Prime Bank",
    "INM" => "I&M Bank",
    "STANBIC" => "Stanbic Bank",
    "GT" => "GT Bank",
    "FAMILY" => "Family Bank",
    "POSTBANK" => "Post Bank",
    "UNI" => "UBI MFB",
    "SMEP" => "SMEP",
    "HF" => "HF Bank",
    "KWFT" => "Kenya Women MFB",
    "ABSA" => "ABSA Bank",
    "BOA" => "Bank of Africa",
    "Gulf" => "Gulf",
    "TNSACCO" => "TNSACCO",
    "WINAS" => "WINAS",
    "CONSOLBANK" => "Consolidated Bank",
    "CARITAS" => "CARITAS"

];


/**
    * Upload a document to Spin Mobile
    * $documentType: The type of document to upload (e.g., 'MPESA', 'BANK', 'TILL', 'PAYBILL').
    * $documentPath: The path to the document to upload.
    * $remoteIdentifier: The unique identifier of the document.
    * $authorizationToken: The authorization token to use for the request.
    * $bank_code: The bank code of the document.
    * $decrypter: The decrypter to use for the document.
    * $additionalFields: Additional fields to include in the request.
    * @return string|null, the string will be id of the uploaded document

 */

/*

sample expected ouput: 
{
    "code": "100.000.000",
    "message": "Upload successful",
    "data": {
        "id": "3fd14b58-8fab-4b51-802a-20d12826b871"
    }
}
*/
function uploadDocumentSm(
    $documentType,
    $documentPath,
    $remoteIdentifier,
    $authorizationToken,
    $bank_code,
    $decrypter = '',
    $additionalFields = []
) {

    global $spin_organization_code;
    $client = new Client();
    if($bank_code == 'MPESA'){
        $bank_code = '';
    }

    $headers = [
        'Authorization' => $authorizationToken
    ];

    $multipart = array_merge([
        ['name' => 'document_type', 'contents' => "$documentType"],
        ['name' => 'organization_code', 'contents' => "$spin_organization_code"],
        ['name' => 'bank_code', 'contents' => "$bank_code"],
        ['name' => 'sender', 'contents' => 'Spin_LMS_API'],
        ['name' => 'decrypter', 'contents' => "$decrypter"],
        ['name' => 'remote_identifier', 'contents' => $remoteIdentifier],
        [
            'name'     => 'document',
            'contents' => Utils::tryFopen($documentPath, 'r'),
            'filename' => basename($documentPath),
            'headers'  => ['Content-Type' => mime_content_type($documentPath)]
        ]
    ], $additionalFields);

    $options = ['multipart' => $multipart];

    $apiBaseURL = getAPIBaseURLSm();
    $endpoint = "$apiBaseURL/analytics/e-statement/upload/";
    $request = new Request('POST', $endpoint, $headers);
    $response = $client->sendAsync($request, $options)->wait();

    if ($response->getStatusCode() !== 200) {
        return null;
    }


    return json_decode($response->getBody(), true);

}

