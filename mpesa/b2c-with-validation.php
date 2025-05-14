<?php

function getAccessToken()
{

    $consumerKey = fetchrow('o_mpesa_configs', "uid='4'", "property_value");
    $consumerSecret = fetchrow('o_mpesa_configs', "uid='5'", "property_value");

    try {
        $headers = ['Content-Type: application/json; charset=utf8'];
        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);

        $result = curl_exec($curl);
        // $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($result, true);

        if (isset($result['access_token'])) {
            return [
                'status'  => 1,
                'payload' => $result['access_token'],
                'message' => 'Success',
            ];
        } else {
            throw new Exception('Invalid Access Token - Access token not found in the response.');
        }
    } catch (Exception $e) {
        // Handle the exception, e.g., log or rethrow if needed
        // error_log("Exception: " . $e->getMessage());
        return [
            'status' => 0,
            "payload" => null,
            'message' => "Error: " . $e->getMessage()
        ];
    } finally {
        curl_close($curl);
    }
}



function send_money_with_nid_validation($msisdn, $national_id, $loan_id, $amount)
{
    // initialize necessary variables
    global $environment;
    global $fulldate;
    global $server2;
    global $api_server;
    global $sl_key;
    $state = null;
    $desc = null;
    $errorMessage = null;
    $errors = null;

    if ((input_length($api_server, 5)) == 0) {
        $server = $server2;
    } else {
        $server = $api_server;
    }


    try {

        $platform = company_settings();
        $company_id = $platform['company_id'];
        if ($loan_id > 0) {
            ////////-------------If loan ID is provided, check if loan has already been disbursed
            $loan_state = fetchonerow('o_loans', "uid='$loan_id'", "disburse_state, customer_id");
            $disbursed_state = $loan_state['disburse_state'];
            $customer_id = $loan_state['customer_id'];
    
            if ($disbursed_state != 'NONE') {
                throw new Exception('Loan already disbursed!');
            }
        }

        // ====== generate access token
        $access_token_resp = getAccessToken();
        $access_token_status = $access_token_resp['status'] ?? 0;
        $access_token_payload = $access_token_resp['payload'] ?? null;
        $access_token_message = $access_token_resp['message'] ?? 'Invalid Access Token - Failed to obtain access token!';

        if ($access_token_status == 0) {
            throw new Exception($access_token_message);
        }

        // preparing to send a b2c payment request
        $url = ($environment == 'sandbox') ? 'https://sandbox.safaricom.co.ke/mpesa/b2cvalida
    te/v2/paymentrequest' : 'https://api.safaricom.co.ke/mpesa/b2cvalidate/v2/paymentrequest';
        // =========== setting headers
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token_payload)); //setting custom header
        // ============ End setting headers


        //  ========== preparing payload/request body
        $InitiatorName = fetchrow('o_mpesa_configs', "uid='7'", "property_value");;
         $SecurityCredential_enc = fetchrow('o_mpesa_configs', "uid='3'", "property_value"); /// Its encrypted now
         $SecurityCredential = decryptStringSecure($SecurityCredential_enc, $sl_key);
        $Amount = $amount;
        $PartyA = fetchrow('o_mpesa_configs', "uid='1'", "property_value");;
        $PartyB = $msisdn;
        $Remarks = "L$loan_id";
        $QueueTimeOutURL = $server . '/apis/mpesa_queue_timeout.php';
        $ResultURL = $server . '/apis/b2c-notice.php?c=' . $company_id . '&r=' . $loan_id;
        $Occasion = '';
        // $IDType = substr($national_id, 0, 2);
        // $IDNumber = substr($national_id, 2);
        $IDType = '01';
        $IDNumber = trim($national_id);
        // handle case of id with 1 leading zero
        $IDNumber = ltrim($IDNumber, '0');

        // handle case of id with 2 leading zeros
        $IDNumber = ltrim($IDNumber, '0');

        // handle case of ids with empty spaces
        $IDNumber = str_replace(' ', '', $IDNumber);
        $OriginatorConversationID = uuid_gen();

        // validate national_id
        if (strlen($national_id) < 6 || !preg_match('/^[0-9]+$/', $national_id)) {
            throw new Exception("Invalid National ID");
        }

        $data_string = json_encode(array(
            //Fill in the request parameters with valid values
            'OriginatorConversationID'=> $OriginatorConversationID,
            'InitiatorName' => $InitiatorName, //This is the credential/username used to authenticate the transaction request.
            'SecurityCredential' => $SecurityCredential, //Base64 encoded string of the B2C short code and password,
            'CommandID' => 'BusinessPayment', //Unique command for each transaction type
            'Amount' => $Amount, //The amount being transacted
            'PartyA' => $PartyA, //Organization’s shortcode initiating the transaction.
            'PartyB' => $PartyB, //Phone number receiving the transaction
            'Remarks' => $Remarks, //Comments that are sent along with the transaction.
            'QueueTimeOutURL' => $QueueTimeOutURL, //The timeout end-point that receives a timeout response.
            'ResultURL' => $ResultURL, //The end-point that receives the response of the transaction
            'Occasion' => $Occasion, //optional
            'IDType' => $IDType,
            'IDNumber' => $IDNumber
        ));


        // $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        /// ================ End preparing payload/request body
        $curl_response = curl_exec($curl);
        // print_r($curl_response.$ResultURL);
        $j = json_decode($curl_response);
        $state = $j->ResponseCode;
        $desc = $j->ResponseDescription;
        $errorMessage = $j->errorMessage;

        if ($state == '0') {
            if ($loan_id > 0) {
                updatedb('o_loans', "transaction_date='$fulldate',disburse_state='INITIATED', disbursed=1", "uid='$loan_id'");
                total_customer_loans($customer_id);
            }
        } else {
            if ($loan_id > 0) {
                updatedb('o_loans', "disburse_state='FAILED', disbursed=0", "uid='$loan_id'");
            }

            throw new Exception("Error: " . $errorMessage);
        }

        if ($loan_id > 0) {
            if (input_length($errorMessage, 5) == 1) {
                $errors = ",Errors: $errorMessage";
            } else {
                $errors = "No errors";
            }
        }
        return "$state,  $desc, $errorMessage";
    } catch (Exception $e) {
        $errorMessage = $errors = $e->getMessage();
        return "$state,  $desc, $errorMessage";
    } finally {
        curl_close($curl);
        store_event('o_loans', $loan_id, "API Request with Validation started on $fulldate with result-> State: $state, ConversationID: $OriginatorConversationID, Desc: $desc, $errors. Result sent to $ResultURL");
    }
}

function reject_loan($loan_id)
{
    // update the FAILED disburse_state and status 6(rejected);
    updatedb("o_loans", "disburse_state = 'REJECTED', disbursed = 0, status = 6", "uid = $loan_id");

    // detach payment(s)
    updatedb("o_incoming_payments", "loan_id = 0", "loan_id = $loan_id");

    // store event
    store_event('o_loans', $loan_id, "Loan Auto Rejected by the System!");
}

function mpesa_loan_requeue($loan_id, $feedbackcode = 'Requeued', $queueType = 'DEF')
{
    global $fulldate;
    $message = "";
    // $requeued = 0;
    try {
        updatedb('o_loans', "disburse_state = 'NONE', disbursed = 0, status = 2", "uid = $loan_id");
        updatedb('o_mpesa_queues', "status = 1, feedbackcode = '$feedbackcode', requeued_date = '$fulldate', queue_type='$queueType'", "loan_id = $loan_id");
        // $requeued = 1;
        $message = "Loan requeued for automatic resend.";
        store_event('o_loans', $loan_id, "$message");
    } catch (Exception $ex) {
        $message = $ex->getMessage();
    }
}
