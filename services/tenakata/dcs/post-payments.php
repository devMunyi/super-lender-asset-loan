<?php 

require_once("../../vendor/autoload.php");
require_once("../../configs/conn.inc");
require_once("../../php_functions/functions.php");

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\Utils;  // Use Utils for promise handling


$limit = $_GET['l'] ?? 10;

echo "Limit: $limit <br>";

$nintyDaysAgo = datesub($date, 0, 0, 90);

$sql = "SELECT ip.payment_date, ip.amount AS confirmed_amount, ip.loan_id AS account_number, ip.uid AS payment_id, ip.transaction_code, l.final_due_date 
        FROM o_incoming_payments ip 
        LEFT JOIN o_loans l ON l.uid = ip.loan_id 
        WHERE ip.status = 1 
        AND ip.loan_id > 0 
        AND (JSON_UNQUOTE(JSON_EXTRACT(ip.other_info, '$.DCS_SYNCED')) != 1 
        OR JSON_EXTRACT(ip.other_info, '$.DCS_SYNCED') IS NULL) 
        AND l.status = 7
        AND l.final_due_date <= '$nintyDaysAgo'
        LIMIT $limit";

$queryRes = mysqli_query($con, $sql);

if (!$queryRes) {
    die("Error executing query: " . mysqli_error($con));
}

$client = new Client();
$headers = [
    'Authorization' => "Bearer $dcsystems_token"
];

$promises = [];
$successfulPayments = [];  // To keep track of successful payment IDs
$failedPayments = [];      // To keep track of failed payment IDs

while ($row = mysqli_fetch_assoc($queryRes)) {
    $paymentDate = $row['payment_date'];
    $confirmedAmount = $row['confirmed_amount'];
    $accountNumber = $row['account_number'];
    $paymentId = $row['payment_id'];
    $transactionCode = $row['transaction_code'];
    $finalDueDate = $row['final_due_date'] ?? null;

    $daysPassed = datediff3($finalDueDate, $date);
    $debtSubType = null;

    if ($daysPassed >= 90) {
        $debtSubType = 3;
    } else {
        continue;
    }

    // Prepare the options for the API request
    $options = [
        'headers' => $headers,
        'multipart' => [
            [
                'name' => 'payment_date',
                'contents' => $paymentDate
            ],
            [
                'name' => 'confirmed_amount',
                'contents' => $confirmedAmount
            ],
            [
                'name' => 'account_number',
                'contents' => $accountNumber
            ],
            [
                'name' => 'transaction_code',
                'contents' => $transactionCode
            ]
        ]
    ];

    // Prepare the request
    $request = new Request('POST', 'https://tenakata.dcssystems.xyz/api/post-payments');
    
    // Send the request asynchronously
    $promise = $client->sendAsync($request, $options)->then(
        function ($response) use ($paymentId, &$successfulPayments, &$failedPayments) {    
            $responseBody = (string)$response->getBody();
            echo "Response body: $responseBody<br>";
            if (strpos($responseBody, 'Loan Account was not found') !== false) {
                $failedPayments[] = $paymentId;
            }else{
                // echo "Payment posted successfully for payment ID $paymentId<br>";
                $successfulPayments[] = $paymentId;
            }
        },
        function ($exception) use ($paymentId, &$failedPayments) {
            $failedPayments[] = $paymentId;
            echo "Failed to post payment for payment ID $paymentId: " . $exception->getMessage() . "<br>";
        }
    );

    $promises[] = $promise;
}

// Wait for all the requests to complete
$responses = Utils::settle($promises)->wait();


// set log variable
$logData = "";

// Set DCS_SYNCED key to 1 for all successful payments
if (count($successfulPayments) > 0) {
    $successfulPaymentsStr = implode(",", $successfulPayments);
    $updateSql = "UPDATE o_incoming_payments 
                   SET other_info = CASE WHEN other_info IS NULL THEN '{\"DCS_SYNCED\": 1}' 
                   ELSE JSON_SET(other_info, '$.DCS_SYNCED', 1) END 
                   WHERE uid IN ($successfulPaymentsStr);";

    // echo "Update SQL: $updateSql<br>";
    $updateRes = mysqli_query($con, $updateSql);


    if (!$updateRes) {
        die("Error updating successful payments: " . mysqli_error($con));
    }

    // Log the successful payments
    $logData .= "Successful payments: " . implode(", ", $successfulPayments) . " ==> $fulldate". "\n";
}

// Log the failed payments
if (count($failedPayments) > 0) {
    $logData .= "Failed payments: " . implode(", ", $failedPayments) . " ==> $fulldate". "\n";
}


// write to log file
$filename = "post-payments.log";
if($logData){
    file_put_contents($filename, $logData, FILE_APPEND);
}


echo "Successful payments: " . implode(", ", $successfulPayments) . "<br>";
echo "Failed payments: " . implode(", ", $failedPayments) . "<br>";

// Close the connection
mysqli_close($con);