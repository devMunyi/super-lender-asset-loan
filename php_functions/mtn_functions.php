<?php

// for facilitating mtn b2c money transfer
function mtn_send_money($recipient_msisdn, $amount, $loan_id = 0, $trials = 1, $queue_id = 0)
{
    global $fulldate;
    global $mtn_disb_prod_sub_key;
    global $mtn_api_server;
    global $mtn_currency;
    global $mtnCallbackBaseURL;
    global $mtn_target_env;
    $payload = null; // can either be SUCCESSFUL or FAILED
    $details = null;
    $curl = curl_init(); // initialize curl
    $transc_id = uuidv4_gen(); // generate unique uuid v4 for every transaction

    try {
        $platform = company_settings(); // retrieving platform information from db
        $company_id = $platform['company_id'] ?? 0;

        $callbackUrl = "$mtnCallbackBaseURL/ug-mtn-b2c-notice";

        $token_obj = createMTNAccessToken($loan_id, $transc_id); // create bearer access token
        $access_token = $token_obj["payload"];

        // ensure we have a token
        if ($access_token == null) {
            $payload = "FAILED";
            $details = $token_obj["details"];
            return array("payload" => $payload, "details" => $details);
        }

        if ($loan_id > 0) {
            ////////-------------If loan ID is provided, check if loan has already been disbursed
            $loan_state = fetchonerow('o_loans', "uid=$loan_id", "disburse_state, customer_id");
            $disbursed_state = $loan_state['disburse_state'];
            $customer_id = $loan_state['customer_id'];

            if ($disbursed_state != 'NONE') {
                $payload = "FAILED";
                $details = "Disburse state $disbursed_state. Implying loan disbursement already initiated!";
                return array('payload' => $payload, "details" => $details);
            }
        }

        // Prepare the request body
        $externalId = $company_id . '-' . $loan_id . '-' . $trials . '-' . $queue_id . '-' . $transc_id;
        $stringfied_amount = $mtn_currency . money($amount);
        $requestBody = json_encode(array(
            "amount" => $amount,
            "currency" => $mtn_currency,
            "externalId" => $externalId,
            "payee" => array(
                "partyIdType" => "MSISDN",
                "partyId" => $recipient_msisdn
            ),
            "payerMessage" => "Requested Loan",
            "payeeNote" => "Congratulations. Your loan of $stringfied_amount was approved"
        ));

        // Set the Content-Length header
        $contentLength = strlen($requestBody);
        $requestHeaders = array(
            "X-Callback-Url: $callbackUrl",
            "X-Reference-Id: $transc_id",
            "X-Target-Environment: $mtn_target_env",
            "Content-Type: application/json",
            "Ocp-Apim-Subscription-Key: $mtn_disb_prod_sub_key",
            "Authorization: Bearer $access_token",
            "Content-Length: $contentLength"
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$mtn_api_server/disbursement/v1_0/transfer",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_HTTPHEADER => $requestHeaders,
        ));

        // send POST api request by executing curl
        curl_exec($curl);
        $err = curl_error($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status_code === 202) {
            $payload = "CREATED";
            $details = "disbursement initiated on $fulldate with status code: $status_code. Result to be sent to: $callbackUrl";

            if ($loan_id > 0) {
                // update customer total loans borrowed
                total_customer_loans($customer_id);
            }

            return array('payload' => $payload, "details" => $details);
        } else {

            $payload = "FAILED";
            $details = "disbursement initiation request failed on $fulldate, with status code: $status_code, error message: $err";
            return array('payload' => $payload, "details" => $details);
        }
    } catch (Exception $ex) {
        $payload = "FAILED";
        $details = $ex->getMessage();
        return array('payload' => $payload, "details" => "Error catched: " . $details);
    } finally {
        // close curl
        curl_close($curl);
    }
}



function mtnB2CAccountBalance()
{
    global $mtn_api_server;
    global $mtn_target_env;
    global $mtn_disb_prod_sub_key;
    $payload = null;
    $details = null;
    $curl = curl_init();

    try {

        $token_obj = createMTNAccessToken();
        $access_token = $token_obj["payload"];

        if ($access_token == null) {
            $details = $token_obj["details"];
            return array("payload" => $payload, "details" => $details);
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$mtn_api_server/disbursement/v1_0/account/balance",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "X-Target-Environment: $mtn_target_env",
                "Ocp-Apim-Subscription-Key: $mtn_disb_prod_sub_key",
                "Authorization: Bearer $access_token"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $bal_obj = json_decode($response, true);

        $payload = $bal_obj["availableBalance"];
        $currency = $bal_obj["currency"];

        // balance returned
        if ($payload && $currency) {
            return array("payload" => $payload, "details" => $response);
        } else {
            return array('payload' => $payload, 'details' => $err);
        }
    } catch (Exception $ex) {
        $details = $ex->getMessage();
        return array('payload' => $payload, 'details' => "Error catched: " . $details);
    } finally {
        // close curl
        curl_close($curl);
    }
}

function createMTNAccessToken($loan_id=0, $reference=null)
{
    // Setting variables
    global $mtn_api_server;
    global $mtn_disb_prod_sub_key;
    $payload = null;
    $curl = curl_init();
    $errorMessage = "Error occured in creating access token";
    $err = null;
    $contentLength = 0;

    try {

        // update transaction_code to reference
        if ($loan_id > 0 && $reference){
            $loan_id = intval($loan_id);
            $reference = trim($reference);
            updatedb('o_loans', "loan_code='$reference', transaction_code = '$reference'", "uid=$loan_id");
        }

        $basic_auth_Token = createMTNBasicAuth();
        $requestHeaders = array(
            "Ocp-Apim-Subscription-Key: $mtn_disb_prod_sub_key",
            "Authorization: Basic $basic_auth_Token",
            "Content-Length: $contentLength"
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$mtn_api_server/disbursement/token/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $requestHeaders,
        ));

        // send POST api request by executing curl
        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status_code == 200) {
            $token_obj = json_decode($response, true);
            // echo $token_obj;
            $payload = $token_obj['access_token'];

            return array('payload' => $payload, 'details' => "Error: " . $err . " status code: " . $status_code);
        } else {
            $err = curl_error($curl);
            return array('payload' => $payload, 'details' => $errorMessage . ": " . $err . " status code: " . $status_code);
        }
    } catch (Exception $ex) {
        // Handle exceptions here
        $err = $ex->getMessage();
        return array('payload' => $payload, 'details' => $errorMessage . ": " . $err);
    } finally {
        // close curl
        curl_close($curl);
    }
}


function createMTNBasicAuth()
{
    global $mtn_disb_api_user;
    global $mtn_disb_api_key;
    $auth = $mtn_disb_api_user . ':' . $mtn_disb_api_key;
    return base64_encode($auth);
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

function mtn_queue_loan($amount, $loan_id)
{
    /////----Add loan to queue
    global $fulldate;
    $queue = 0;

    try {
        updatedb('o_loans', "status=2", "uid=$loan_id");
        $fds = array('loan_id', 'amount', 'added_date', 'trials', 'status');
        $vals = array("$loan_id", "$amount", "$fulldate", '0', '1');
        $queue = addtodb('o_mtn_queues', $fds, $vals);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }

    return $queue;
}

function mtn_loan_requeue($loan_id, $queue_id, $feedbackcode = 'Requeued')
{
    global $fulldate;
    $requeued = 0;
    try {
        updatedb('o_loans', "disburse_state='NONE', disbursed=0, status = 2", "uid=$loan_id");
        updatedb('o_mtn_queues', "status=1, feedbackcode='$feedbackcode', requeued_date = '$fulldate'", "uid=$queue_id");
        $requeued = 1;
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }

    return $requeued;
}


function mtn_send_money_status($transc_id = "f1d623a3-dff5-481f-bdf9-ecffa8228cb4")
{
    // set variables
    $curl = curl_init();
    global $mtn_api_server;
    global $mtn_target_env;
    global $mtn_disb_prod_sub_key;
    $payload = null;
    $details = null;
    $err = null;

    try {

        $token_obj = createMTNAccessToken();
        $access_token = $token_obj["payload"];

        if ($access_token == null) {
            $details = $token_obj["details"];
            return array("payload" => $payload, "details" => $details);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$mtn_api_server/disbursement/v1_0/transfer/$transc_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "X-Target-Environment: $mtn_target_env",
                "Ocp-Apim-Subscription-Key: $mtn_disb_prod_sub_key",
                "Authorization: Bearer $access_token"
            ),
        ));

        $response = curl_exec($curl);
        // echo "RESPONSE =>" . $response;
        // var_dump($response);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status_code == 200) {
            $payload = json_decode($response, true);
            // $resp_status = $payload['status'];
            // $reason = $payload['reason'];
            return array('payload' => $payload, 'details' => "Error: " . $err . " status code: " . $status_code);
        } else {
            $err = curl_error($curl);
            return array('payload' => $payload, 'details' => "Error: " . $err . " status code: " . $status_code);
        }
    } catch (Exception $ex) {
        return array('payload' => $payload, 'details' => "Error catched: " . $ex->getMessage());
    } finally {
        curl_close($curl);
    }
}


function updateMtnUtilityBalance()
{
    global $fulldate;
    $bal_resp = mtnB2CAccountBalance();
    $payload = $bal_resp["payload"];

    if ($payload != null) {
        $B2CUtilityAccountAvailableFunds = doubleval($payload);

        if ($B2CUtilityAccountAvailableFunds > 0) {
            updatedb('o_summaries', "value_='$B2CUtilityAccountAvailableFunds', last_update='$fulldate'", "uid=4");
        }
    }
}
