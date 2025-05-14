<?php 

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
require_once("../../vendor/autoload.php");
require_once("../../configs/conn.inc");
require_once("../../php_functions/functions.php");


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\Utils;  // Use Utils for promise handling

$limit = $_GET['l'] ?? 20;

echo "Limit: $limit <br>";

$DEBT_SUB_TYPES = [
    "30-60 Days" => 1,
    "61-90 Days" => 2,
    "91-120 Days" => 3,
];

$nintyDaysAgo = datesub($date, 0, 0, 90);
$sql = "SELECT c.full_name AS FULL_NAMES, l.loan_amount AS LOAN_AMOUNT, c.primary_mobile AS PHONE_1, l.total_repayable_amount AS AMOUNT_TO_COLLECT, l.uid AS ACCOUNT_NUMBER, b.name AS BANK_BRANCH, l.given_date AS LOAN_START_DATE, l.final_due_date AS FINAL_DUE_DATE FROM o_loans l LEFT JOIN o_customers c ON c.uid = l.customer_id LEFT JOIN o_branches b ON b.uid = l.current_branch WHERE l.paid = 0 
AND l.final_due_date <= '$nintyDaysAgo'
AND l.disbursed = 1 AND l.status = 7 AND (
        JSON_UNQUOTE(JSON_EXTRACT(l.other_info, '$.DCS_SYNCED')) != 1 OR JSON_EXTRACT(l.other_info, '$.DCS_SYNCED') IS NULL) LIMIT $limit";

$queryRes = mysqli_query($con, $sql);                       

if (!$queryRes) {
    die("Error executing query: " . mysqli_error($con));
}

$client = new Client();  
$headers = [
    'Authorization' => "Bearer $dcsystems_token"
];

$promises = [];
$successfulAccounts = [];  // To keep track of successful account numbers
$failedAccounts = [];      // To keep track of failed account numbers

while($row = mysqli_fetch_assoc($queryRes)) {
    $fullNames = $row['FULL_NAMES'] ?? null;
    $loanAmount = $row['LOAN_AMOUNT'] ?? null;
    $phone1 = $row['PHONE_1'] ?? null;
    $amountToCollect = $row['AMOUNT_TO_COLLECT'] ?? null;
    $accountNumber = $row['ACCOUNT_NUMBER'] ?? null;
    $bankBranch = $row['BANK_BRANCH'] ?? null;
    $loanStartDate = $row['LOAN_START_DATE'] ?? null;
    $finalDueDate = $row['FINAL_DUE_DATE'] ?? null;
    $daysPassed = datediff3($finalDueDate, $date);
    $debtSubType = null;

    if ($daysPassed >= 90) {
        $debtSubType = 3;
    }else{
        continue;
    }
    

    // Prepare the options for the API request
    $options = [
        'headers' => $headers,
        'multipart' => [
            [
                'name' => 'FULL_NAMES',
                'contents' => $fullNames
            ],
            [
                'name' => 'LOAN_AMOUNT',
                'contents' => $loanAmount
            ],
            [
                'name' => 'PHONE_1',
                'contents' => $phone1
            ],
            [
                'name' => 'AMOUNT_TO_COLLECT',
                'contents' => $amountToCollect
            ],
            [
                'name' => 'ACCOUNT_NUMBER',
                'contents' => $accountNumber
            ],
            [
                'name' => 'BANK_BRANCH',
                'contents' => $bankBranch
            ],
            [
                'name' => 'LOAN_START_DATE',
                'contents' => $loanStartDate
            ],
            [
                'name' => 'DEBT_SUB_TYPE',
                'contents' => $debtSubType
            ]

        ]
    ];

    // Create a new request without additional options
    $request = new Request('POST', 'https://tenakata.dcssystems.xyz/api/upload-casefile');
    
    // Asynchronously send the request and store the promise
    $promises[] = $client->sendAsync($request, $options)->then(
        function ($response) use ($row, &$successfulAccounts, &$failedAccounts) {
            $resposeBody = json_decode($response->getBody(), true);
            $loanAccount = $resposeBody['loan_account'] ?? '';
            $originalLoanAccount = $row['ACCOUNT_NUMBER'] ?? '';

            if ($loanAccount && $loanAccount == $originalLoanAccount) {
                $successfulAccounts[] = $loanAccount;
                echo "Pushed account {$row['ACCOUNT_NUMBER']} to successful accounts<br>";
            } else {
                $failedAccounts[] = $loanAccount;
            }
           
        },
        function ($exception) use ($accountNumber, &$failedAccounts) {
            // On failure, store the account number in the failed list
            $failedAccounts[] = $accountNumber;
            // echo "Error for account $accountNumber: " . $exception->getMessage() . "\n";
        }
    );
}

// Wait for all promises to resolve using Utils::settle
$results = Utils::settle($promises)->wait();

// set log variable
$log = "";
// set DCS_SYNCED key to 1 for all successful accounts
if (count($successfulAccounts) > 0) {
    $successfulAccountsStr = implode(',', $successfulAccounts);
    $updateSql = "UPDATE o_loans SET other_info = CASE WHEN other_info IS NULL THEN '{\"DCS_SYNCED\": 1}' ELSE JSON_SET(other_info, '$.DCS_SYNCED', 1) END WHERE uid IN ($successfulAccountsStr);";

    echo "Update SQL: $updateSql <br>";
    $updateRes = mysqli_query($con, $updateSql);

    if (!$updateRes) {
        die("Error updating DCS_SYNCED key: " . mysqli_error($con));
    }


    // update all accounted payments so far to have DCS_SYNCED key set to 1
    $updatePaysSql = "UPDATE o_incoming_payments 
                   SET other_info = CASE WHEN other_info IS NULL THEN '{\"DCS_SYNCED\": 1}' 
                   ELSE JSON_SET(other_info, '$.DCS_SYNCED', 1) END 
                   WHERE loan_id IN ($successfulAccountsStr);";

    echo "Update SQL: $updatePaysSql<br>";
    $updatePaysRes = mysqli_query($con, $updatePaysSql);


    if (!$updatePaysRes) {
        die("Error updating successful payments: " . mysqli_error($con));
    }


    // log the successful accounts
    $log .= "Successful accounts: " . implode(', ', $successfulAccounts) . " ==> $fulldate". "\n";

}

if (count($failedAccounts) > 0) {
    $log .= "Failed accounts: " . implode(', ', $failedAccounts) . " ==> $fulldate " . "\n";
}

// write to log file
$filename = "post-allocations.log";
if($log){
    file_put_contents($filename, $log, FILE_APPEND);
}


echo "Successful accounts: " . implode(', ', $successfulAccounts) . "</br>";
echo "Failed accounts: " . implode(', ', $failedAccounts) . "</br>";

// close the connection
mysqli_close($con);
?>
