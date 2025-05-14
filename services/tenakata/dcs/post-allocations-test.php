<?php

include_once("../../vendor/autoload.php");
include_once("../../configs/conn.inc");
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\Utils;

// Sample data with valid and invalid inputs
$testData = [
    ['LOAN_AMOUNT' => 1, 'PHONE_1' => '1234567890', 'ACCOUNT_NUMBER' => 'trial1'], // Valid
    ['FULL_NAMES' => 'trial2', 'LOAN_AMOUNT' => 2, 'PHONE_1' => 'invalid_phone', 'ACCOUNT_NUMBER' => 'trial2'], // Invalid phone
    ['LOAN_AMOUNT' => 'NaN', 'PHONE_1' => '0987654321', 'ACCOUNT_NUMBER' => 'trial3'], // Invalid loan amount
    ['FULL_NAMES' => 'trial4', 'LOAN_AMOUNT' => 4, 'PHONE_1' => '1234509876', 'ACCOUNT_NUMBER' => 'trial4'], // Valid
];

$client = new Client();  
$headers = [
    'Authorization' => "Bearer $dcsystems_token"
];

$promises = [];
$successfulAccounts = []; // Track successful account numbers
$failedAccounts = [];     // Track failed account numbers

foreach ($testData as $data) {
    $options = [
        'headers' => $headers,
        'multipart' => [
            [
                'name' => 'FULL_NAMES',
                'contents' => $data['FULL_NAMES']
            ],
            [
                'name' => 'LOAN_AMOUNT',
                'contents' => $data['LOAN_AMOUNT']
            ],
            [
                'name' => 'PHONE_1',
                'contents' => $data['PHONE_1']
            ],
            [
                'name' => 'ACCOUNT_NUMBER',
                'contents' => $data['ACCOUNT_NUMBER']
            ]
        ]
    ];

    $request = new Request('POST', 'https://tenakata.dcssystems.xyz/api/upload-casefile');
    
    $promises[] = $client->sendAsync($request, $options)->then(
        function ($response) use ($data, &$successfulAccounts, &$failedAccounts) {
            $resposeBody = json_decode($response->getBody(), true);
            $loan_account = $resposeBody['loan_account'] ?? '';
            $original_loan_account = $data['ACCOUNT_NUMBER'] ?? '';

            if($loan_account && $loan_account == $original_loan_account) {
                $successfulAccounts[] = $loan_account;
                echo "Pushed account {$data['ACCOUNT_NUMBER']} to successful accounts<br>";
            } else {
                $failedAccounts[] = $loan_account;
            }

            echo "Success for account {$data['ACCOUNT_NUMBER']}: ==> " . json_encode($resposeBody) ."<br>";
        },
        function ($exception) use ($data, &$failedAccounts) {
            $failedAccounts[] = $data['ACCOUNT_NUMBER'];
            echo "Failed for account {$data['ACCOUNT_NUMBER']}: " . $exception->getMessage() . "<br>";
        }
    );
}

// Use Utils::settle to handle all promises and wait for them to complete
$results = Utils::settle($promises)->wait();

// Print results
echo "Successful accounts: " . implode(', ', $successfulAccounts) . "<br>";
echo "Failed accounts: " . implode(', ', $failedAccounts) . "<br>";
?>
