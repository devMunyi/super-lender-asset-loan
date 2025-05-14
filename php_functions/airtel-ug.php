<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

///// ============== Begin Utility Functions

function getAirtelApiHost()
{
    global $environment;
    return ($environment == 'staging') ? 'https://openapiuat.airtel.africa' : 'https://openapi.airtel.africa';
}

function remove_msisdn_country_code($msisdn)
{
    return substr(trim($msisdn), 3);
}

function validate_msisdn($msisdn, $length = 9)
{
    return (int)(strlen($msisdn) == $length);
}

// uuid version 4 generator
function uuidv4_gen()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}


function generateRefId()
{
    $new_string = str_replace("-", "", uuidv4_gen());
    return strtoupper($new_string);
}


function generateRefIdV2()
{
    return strtoupper(generateRandomString(12));
}

function generateEncodedPin()
{
    global $prod_pin;
    global $prod_public_key;
    $payload = null;

    // Get keys from a string 
    $publicKeyString = <<<PK
    -----BEGIN PUBLIC KEY-----
    $prod_public_key
    -----END PUBLIC KEY-----
    PK;

    // Load public key
    $publicKey = openssl_pkey_get_public(array($publicKeyString, ""));
    if (!$publicKey) {
        return buildResponse('Public key NOT Correct', null, $payload);
    }

    // encrypt pin with public key
    if (!openssl_public_encrypt($prod_pin, $encryptedWithPublic, $publicKey)) {
        return buildResponse('Error encrypting with public key', null, $payload);
    }

    $payload = base64_encode($encryptedWithPublic);

    return buildResponse('OK', null, $payload);
}

function createAirtelUGAccessToken($loan_id=0, $reference=null)
{

    // Setting variables
    global $client_id;
    global $client_secret;
    $airtel_api_server = getAirtelApiHost();
    $payload = null;
    $message = null;
    $details = null;

    try {

        // update transaction_code to reference
        if ($loan_id > 0 && $reference){
            $loan_id = intval($loan_id);
            $reference = trim($reference);
            updatedb('o_loans', "loan_code='$reference', transaction_code = '$reference'", "uid=$loan_id");
        }
        

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'Cookie' => 'visid_incap_2967769=O0r84Ko2RvCLVWlT1d7lRH8j/2QAAAAAQUIPAAAAAADhNIMl4Oz3fBv9oP7Qcxtk'
        ];

        $body = json_encode([
            "client_id" => "$client_id",
            "client_secret" => "$client_secret",
            "grant_type" => "client_credentials"
        ]);

        $request = new Request('POST', "$airtel_api_server/auth/oauth2/token", $headers, $body);
        $res = $client->sendAsync($request)->wait();
        $responseBody = $details = json_decode($res->getBody(), true);

        $payload = $responseBody['access_token'] ?? null;

        if ($payload) {
            $message = 'OK';
            return buildResponse($message, $details, $payload);
        } else {
            $message = 'Error occurred in generating access token';
            throw new Exception($message);
            // return buildResponse($message, $details, $payload);
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        return buildResponse($message, $details, $payload);
    }
}

// function createAirtelUGAccessToken()
// {
//     $deferred = new Deferred();

//     // Setting variables
//     global $client_id;
//     global $client_secret;
//     $airtel_api_server = getAirtelApiHost();
//     $payload = null;
//     $message = null;
//     $details = null;

//     $browser = new Browser();

//     $headers = [
//         'Content-Type' => 'application/json',
//         'Cookie' => 'visid_incap_2967769=O0r84Ko2RvCLVWlT1d7lRH8j/2QAAAAAQUIPAAAAAADhNIMl4Oz3fBv9oP7Qcxtk'
//     ];

//     $body = json_encode([
//         "client_id" => "$client_id",
//         "client_secret" => "$client_secret",
//         "grant_type" => "client_credentials"
//     ]);

//     $browser->post("$airtel_api_server/auth/oauth2/token", $headers, $body)
//         ->then(
//             function ($res) use (&$details, &$payload, &$message, $deferred) {
//                 $responseBody = json_decode($res->getBody(), true);

//                 $payload = $responseBody['access_token'] ?? null;

//                 if ($payload) {
//                     $message = 'OK';
//                     $details = $responseBody;
//                     $deferred->resolve(buildResponse($message, $details, $payload));
//                 } else {
//                     $message = 'Error occurred in generating access token';
//                     $deferred->reject(new Exception($message));
//                 }
//             }
//         );

//     return $deferred->promise();
// }



function generateHashedRespBody($data): string
{
    global $hash_key;
    $s = hash_hmac('sha256', json_encode($data), $hash_key, true);
    return base64_encode($s);
}

function isCallbackAuthentic($compare_hash, $data): int
{
    $gen_hash = generateHashedRespBody($data);
    return (int)($compare_hash === $gen_hash);
}

function buildResponse($message, $details = null, $payload = 'FAILED')
{
    return ['payload' => $payload, 'message' => $message, "details" => $details];
}

function buildResponse2($reference, $message, $details = null, $status = 'FAILED')
{
    $payload = ['status' => $status, 'ref_id' => "$reference"];
    return ['payload' => $payload, 'message' => $message, 'details' => $details];
}

function updateUgAirtelUtilityBal($payload)
{
    global $fulldate;

    if ($payload !== null) {
        $B2CUtilityAccountAvailableFunds = doubleval($payload);

        if ($B2CUtilityAccountAvailableFunds > 0) {
            updatedb('o_summaries', "value_='$B2CUtilityAccountAvailableFunds', last_update='$fulldate'", "uid=5");
        }
    }
}


function airtelUGB2CBalanceEnquiry()
{
    $payload = null;
    $details = null;

    try {
        $payload = $details = fetchrow('o_summaries', "name='AIRTEL_UG_UTILITY_BALANCE'", "value_");
    } catch (Exception $e) {
        // $e->getMessage();
        $details = $e->getMessage();
    }

    return [
        'payload' => $payload,
        'details' => $details
    ];
}


function updateUgAirtelB2CBalance($payload)
{
    global $fulldate;

    if ($payload !== null) {
        $C2BUtilityAccountAvailableFunds = doubleval($payload);

        if ($C2BUtilityAccountAvailableFunds > 0) {
            updatedb('o_summaries', "value_='$C2BUtilityAccountAvailableFunds', last_update='$fulldate'", "name='AIRTEL_UG_PAYBILL_BALANCE'");
        }
    }
}

///////// ================ End Utility functions



///// ====> Send STK/USSD Push function

function ug_airtel_ussd_pay($amount, $msisdn)
{
    // variables
    $airtel_api_server = getAirtelApiHost();
    $details = null;
    $message = null;
    $reference = generateRefIdV2();


    try {

        // strip out country code
        $msisdn = remove_msisdn_country_code($msisdn);

        // ensure phone is valid
        $phone_valid = validate_msisdn($msisdn);
        if ($phone_valid == 0) {
            $message = "Phone Number Validation Error!";
            throw new Exception($message);
            // return buildResponse2($reference, $message, $details);
        }

        // generate encoded pin
        $pin_result = generateEncodedPin();
        $pin = $pin_result['payload'];

        if ($pin == null) {
            $message = $pin_result['message'] ?? "Error occured in generating encryption pin!";
            throw new Exception($message);
            // return buildResponse2($reference, $message, $details);
        }

        $result = createAirtelUGAccessToken();
        $access_token = $result['payload'];

        if ($access_token == null) {
            $message = $result['message'] ?? "Error creating access token!";
            $details = $result['details'] ?? null;
            throw new Exception($message);
            // return buildResponse2($reference, $message, $details);
        }

        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'X-Country' => 'UG',
            'X-Currency' => 'UGX',
            'Authorization' => "Bearer $access_token"
        ];

        $body = json_encode([
            "reference" => "$reference",
            "subscriber" => [
                "country" => "UG",
                "currency" => "UGX",
                "msisdn" => "$msisdn"
            ],
            "transaction" => [
                "amount" => $amount,
                "country" => "UG",
                "currency" => "UGX",
                "id" => "$reference"
            ]
        ]);

        $request = new Request('POST', "$airtel_api_server/merchant/v1/payments/", $headers, $body);
        $res = $client->sendAsync($request)->wait();
        $responseBody = $details = json_decode($res->getBody(), true);

        $success = $responseBody['status']['success'] ?? false;
        $status_code = $responseBody['status']['code'] ?? null;
        $message = $responseBody['status']['message'] ?? "Something Went Wrong in Consuming Airtel Payments USSD Push API";

        if ($success && $status_code === '200') {
            return buildResponse2($reference, $message, $details, 'SUCCESSFUL');
        } else {
            throw new Exception($message);
            // return buildResponse2($reference, $message, $details);
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        return buildResponse2($reference, $message, $details);
    }
}


//// ==> Send Money Function

function airtel_ug_send_money($recipient_msisdn, $recipient_name, $amount, $loan_id = 0)
{
    $airtel_api_server = getAirtelApiHost();
    $payload = null; // can either be SUCCESSFUL or FAILED
    $details = null;
    $message = null;
    $reference = generateRefIdV2();
    $status = null;
    $response_code = null;

    try {

        // strip out country code
        $recipient_msisdn = remove_msisdn_country_code($recipient_msisdn);

        // ensure phone is valid
        $phone_valid = validate_msisdn($recipient_msisdn);
        if ($phone_valid === 0) {
            $message = "Phone Number Validation Error!";

            $payload = [
                'status' => $status,
                'response_code' => $response_code
            ];
            // throw new Exception($message);
            return buildResponse($message, $details, $payload);
        }

        // generate encoded pin
        $pin_result = generateEncodedPin();
        $pin = $pin_result['payload'];

        if ($pin === null) {
            $message = $pin_result['message'];
            $payload = [
                'status' => $status,
                'response_code' => $response_code
            ];
            // throw new Exception($message);
            return buildResponse($message, $details, $payload);
        }

        // generate access token
        $result = createAirtelUGAccessToken($loan_id, $reference);
        $access_token = $result['payload'];

        if ($access_token === null) {
            $message = $result['message'] ?? 'Error creating access token!';
            $details = $result['details'] ?? null;
            $payload = [
                'status' => $status,
                'response_code' => $response_code
            ];
            // throw new Exception($message);
            return buildResponse($message, $details, $payload);
        }

        if ($loan_id > 0) {
            ////////-------------If loan ID is provided, check if loan has already been disbursed
            $loan_state = fetchonerow('o_loans', "uid=$loan_id", "disburse_state, customer_id");
            $disbursed_state = $loan_state['disburse_state'] ?? '';
            $customer_id = $loan_state['customer_id'] ?? 0;

            if ($disbursed_state != 'NONE') {
                $message = "Disburse state $disbursed_state. Please confirm if 
                fund is already disbursed.";
                $payload = [
                    'status' => $status,
                    'response_code' => $response_code
                ];
                // throw new Exception($message);
                return buildResponse($message, $details, $payload);
            }
        } else {
            $payload = [
                'status' => $status,
                'response_code' => $response_code
            ];
            return buildResponse("Valid Loan ID is required!", $details, $payload);
        }

        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
            'X-Country' => 'UG',
            'X-Currency' => 'UGX',
            'Authorization' => "Bearer $access_token",
            'Cookie' => 'visid_incap_2967769=O0r84Ko2RvCLVWlT1d7lRH8j/2QAAAAAQUIPAAAAAADhNIMl4Oz3fBv9oP7Qcxtk'
        ];

        $body = json_encode([
            "payee" => [
                "currency" => "UGX",
                "msisdn" => "$recipient_msisdn",
                "name" => "$recipient_name"
            ],
            "reference" => "$reference",
            "pin" => "$pin",
            "transaction" => [
                "amount" => $amount,
                "id" => "$reference",
                "type" => "B2C"
            ]
        ]);

        $request = new Request('POST', "$airtel_api_server/standard/v1/disbursements/", $headers, $body);
        $res = $client->sendAsync($request)->wait();
        $responseBody = $details = json_decode($res->getBody(), true);

        $success = $responseBody['status']['success'] ?? false;
        $status_code = $responseBody['status']['code'] ?? null;
        $response_code = $responseBody['status']['response_code'];
        $message = $responseBody['status']['message'] ?? "Something Went Wrong in Consuming Airtel Disbursement API";

        if ($success && $status_code === '200') {
            if ($loan_id > 0) {
                // update customer total loans borrowed
                total_customer_loans($customer_id);
            }
            $payload = [
                'status' => 'SUCCESSFUL',
                'response_code' => $response_code
            ];
            return buildResponse($message, $details, $payload);
        } else {
            // throw new Exception($message);
            $payload = [
                'status' => 'FAILED',
                'response_code' => $response_code
            ];
            return buildResponse($message, $details, $payload);
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $payload = [
            'status' => $status,
            'response_code' => $response_code
        ];
        return buildResponse($message, $details, $payload);
    }
}

//// ==> C2B Balance Enquiry Function

function airtelUGC2BBalanceEnquiry()
{
    $airtel_api_server = getAirtelApiHost();
    $payload = null;
    $message = null;
    $details = null;

    try {

        // generate access token
        $result = createAirtelUGAccessToken();
        $access_token = $result['payload'];

        if ($access_token == null) {
            $message = $result['message'] ?? "Error creating access token!";
            $details = $result['details'] ?? null;
            throw new Exception($message);
            // return buildResponse($message, $details);
        }

        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'X-Country' => 'UG',
            'X-Currency' => 'UGX',
            'Authorization' => "Bearer $access_token",
            'Cookie' => 'incap_ses_1019_2967769=A3gpRzlgv3PlzhPwSjgkDhjKAmUAAAAAX8z5/U01F0x+FrvTZTnrFQ==; nlbi_2967769=jhkcAObgczmNlCFumeq1mAAAAAAX01CtXiwXkiTzlTNHxG0f; visid_incap_2967769=O0r84Ko2RvCLVWlT1d7lRH8j/2QAAAAAQUIPAAAAAADhNIMl4Oz3fBv9oP7Qcxtk'
        ];
        $request = new Request('GET', "$airtel_api_server/standard/v1/users/balance", $headers);
        $res = $client->sendAsync($request)->wait();
        $responseBody = $details = json_decode($res->getBody(), true);

        $success = $responseBody['status']['success'] ?? false;
        $status_code = $responseBody['status']['code'] ?? null;
        $message = $responseBody['status']['message'] ?? "Something Went Wrong in Consuming Airtel Balance Enquiry API";
        if ($success && $status_code === '200') {
            $payload = doubleval($responseBody['data']['balance']);
            return buildResponse($message, $details, $payload);
        } else {
            throw new Exception($message);
            // return buildResponse($message, $details);
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        return buildResponse($message, $details);
    }
}



//// ==>  User Information Enquiry Function
function airtelUGUserEnquiry($msisdn)
{
    $airtel_api_server = getAirtelApiHost();
    $payload = null;
    $message = null;
    $details = null;

    try {

        // strip out country code
        $msisdn = remove_msisdn_country_code($msisdn);

        // ensure phone is valid
        $phone_valid = validate_msisdn($msisdn);
        if ($phone_valid == 0) {
            $message = "Phone Number Validation Error!";
            throw new Exception($message);
            // return buildResponse($message, $details);
        }

        // generate access token
        $result = createAirtelUGAccessToken();
        $access_token = $result['payload'];

        if ($access_token == null) {
            $message = $result['message'] ?? "Error creating access token!";
            $details = $access_token['details'] ?? null;
            throw new Exception($message);
            // return buildResponse($message, $details);
        }

        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'X-Country' => 'UG',
            'X-Currency' => 'UGX',
            'Authorization' => "Bearer $access_token",
            'Cookie' => 'incap_ses_1019_2967769=uhxMAoxu6nJMHyrwSjgkDkXYAmUAAAAA+3bjl3uF/T6Rn9W4V71Rgw==; nlbi_2967769=jhkcAObgczmNlCFumeq1mAAAAAAX01CtXiwXkiTzlTNHxG0f; visid_incap_2967769=O0r84Ko2RvCLVWlT1d7lRH8j/2QAAAAAQUIPAAAAAADhNIMl4Oz3fBv9oP7Qcxtk'
        ];
        $request = new Request('GET', "$airtel_api_server/standard/v1/users/$msisdn", $headers);
        $res = $client->sendAsync($request)->wait();
        $responseBody = $details = json_decode($res->getBody(), true);

        $success = $responseBody['status']['success'] ?? false;
        $status_code = $responseBody['status']['code'] ?? null;
        $message = $responseBody['status']['message'] ?? "Something Went Wrong in Consuming Airtel User Enquiry API";
        if ($success && $status_code === '200') {
            $payload = $responseBody['data'];
            return buildResponse($message, $details, $payload);
        } else {
            throw new Exception($message);
            // return buildResponse($message, $details);
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        return buildResponse($message, $details);
    }
}



// b2c transaction enquiry
function b2cTransactionEnquiry($transaction_id)
{
    try {
        $airtel_api_server = getAirtelApiHost();
        $client = new Client();
        // generate access token
        $result = createAirtelUGAccessToken();
        $access_token = $result['payload'];

        if ($access_token == null) {
            $message = $result['message'] ?? "Error creating access token!";
            // $details = $access_token['details'] ?? null;
            throw new Exception($message);
            // return buildResponse($message, $details);

        }

        // echo "Access Token: $access_token\n";

        $headers = [
            'Content-Type' => 'application/json',
            'X-Country' => 'UG',
            'X-Currency' => 'UGX',
            'Authorization' => "Bearer $access_token"
        ];

        $request = new Request('GET', "$airtel_api_server/standard/v1/disbursements/$transaction_id", $headers);
        $res = $client->sendAsync($request)->wait();
        // echo $res;
        return $res->getBody();
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}



//// ==>  Loan queuing function
function airtel_ug_queue_loan($amount, $loan_id)
{
    /////----Add loan to queue
    global $fulldate;
    $queue = 0;

    try {
        updatedb('o_loans', "status=2", "uid=$loan_id");
        $fds = array('loan_id', 'amount', 'added_date', 'trials', 'status');
        $vals = array("$loan_id", "$amount", "$fulldate", '0', '1');
        $queue = addtodb('o_airtel_ug_queues', $fds, $vals);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }

    return $queue;
}

///// ===> Loan re-queing function
function airtel_ug_loan_requeue($loan_id, $queue_id, $feedbackcode = 'Requeued')
{

    if (strlen($feedbackcode) > 40 || !$feedbackcode) {
        $feedbackcode = 'Requeued';
    }

    global $fulldate;
    updatedb('o_loans', "disburse_state='NONE', disbursed=0, status = 2", "uid=$loan_id");
    $requeued = updatedb('o_airtel_ug_queues', "status=1, feedbackcode='$feedbackcode', requeued_date = '$fulldate'", "uid=$queue_id");

    return $requeued;
}


function airtelB2CGetBalFromMessage($message)
{
    // Define the regular expression pattern to match the balance value
    $pattern = '/Your bal: UGX ([\d,]+)/';

    // Perform a regular expression match
    if (preg_match($pattern, $message, $matches)) {
        // Extracted balance value is in $matches[1]
        $balanceString = $matches[1];

        // Remove commas from the balance string and convert it to a double
        $balance = (float) str_replace(',', '', $balanceString);

        // Return the balance
        return $balance;
    } else {
        // Return null if balance is not found
        return null;
    }
}
