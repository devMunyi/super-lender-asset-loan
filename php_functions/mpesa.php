<?php 

function get_mpesa_access_token()
{
    try {
        $mpesa_configs = fetchonerow('o_mpesa_configs', "uid=1", "consumer_key, consumer_secret");
        $consumerKey = $mpesa_configs['consumer_key'] ?? '';
        $consumerSecret = $mpesa_configs['consumer_secret'] ?? '';

        if(empty($consumerKey) || empty($consumerSecret)){
            throw new Exception("Consumer key or secret not found");
        }

        $headers = ['Content-Type:application/json; charset=utf8'];
        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);

        $result = curl_exec($curl);
        // $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // echo "status ====>".$status ."<br>";
        $result = json_decode($result);

        // print_r($result);
        $access_token = $result->access_token;

        return $access_token;
    } catch (Exception $e) {
        // Handle any exceptions that might occur
        echo 'Exception caught: ' . $e->getMessage();
        return null; // or handle the exception in a different way
    }finally{
        curl_close($curl);
    }
}


function send_money_v2($msisdn, $amount, $loan_id = 0, $mpesa_configs = [])
{
    global $fulldate;
    global $api_server;
    global $api_server2;
    global $server2;
    global $sl_key;

    try {
        if((input_length($api_server2, 5)) == 1){
            $api_server = $api_server2;
        }
        if ((input_length($api_server, 5)) == 0) {
            $server = $server2;
        } else {
            $server = $api_server;
        }

        $platform = company_settings();
        $company_id = $platform['company_id'];

        if ($loan_id > 0) {
            $loan_state = fetchonerow('o_loans', "uid='$loan_id'", "disburse_state");
            $disbursed_state = $loan_state['disburse_state'];

            if ($disbursed_state != 'NONE') {
                return "Loan already disbursed!";
            }
        }

        $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
        $curl = curl_init($url);

        $PartyA = trim($mpesa_configs['property_value'] ?? '');
        $InitiatorName = trim($mpesa_configs['initiator_name'] ?? '');
        $SecurityCredential_enc = trim($mpesa_configs['security_credential'] ?? '');
        $SecurityCredential = decryptStringSecure($SecurityCredential_enc, $sl_key);
        $enc_token = trim($mpesa_configs['enc_token'] ?? '');
        $enc_token_key = trim($mpesa_configs['enc_token_key'] ?? '');

        
        if (empty($InitiatorName) || empty($SecurityCredential) || empty($PartyA) || empty($enc_token) || empty($enc_token_key)) {
            throw new Exception("B2C configuration(s) missing!");
        }

        // Decrypt the access token
        $access_token = decryptString($enc_token, $enc_token_key);
        $PartyB = $msisdn;
        $Remarks = "L$loan_id";
        $QueueTimeOutURL = $server . '/apis/mpesa_queue_timeout';
        $ResultURL = $server . '/apis/b2c-notice?c=' . $company_id . '&r=' . $loan_id;
        $Occasion = ''; 
        $OriginatorConversationID = uuid_gen();


        $curl_post_data = [
            'OriginatorConversationID' => $OriginatorConversationID,
            'InitiatorName' => $InitiatorName,
            'SecurityCredential' => $SecurityCredential,
            'CommandID' => 'PromotionPayment',
            'Amount' => $amount,
            'PartyA' => $PartyA,
            'PartyB' => $PartyB,
            'Remarks' => $Remarks,
            'QueueTimeOutURL' => $QueueTimeOutURL,
            'ResultURL' => $ResultURL,
            'Occasion' => $Occasion
        ];

        // echo "Curl post data: " . json_encode($curl_post_data) . "<br>";

        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));

        $curl_response = curl_exec($curl);
        $j = json_decode($curl_response);
        $state = $j->ResponseCode;
        $desc = $j->ResponseDescription;
        $errorMessage = $j->errorMessage;

        if ($state == '0') {
            if ($loan_id > 0) {
                updatedb('o_loans', "disburse_state='INITIATED', disbursed=1, loan_code='$OriginatorConversationID'", "uid='$loan_id'");
            }
        } else {
            if ($loan_id > 0) {
                updatedb('o_loans', "disburse_state='FAILED', disbursed=0", "uid='$loan_id'");
            }
        }

        if ($loan_id > 0) {
            $errors = (input_length($errorMessage, 3) == 1) ? ",Errors: $errorMessage" : "No errors";
            store_event('o_loans', $loan_id, "API Request started on $fulldate with result-> State: $state, ConversationID: $OriginatorConversationID, Desc: $desc, $errors. Result to be sent to-> $ResultURL");
        }

        return "$state,  $desc, $errorMessage";
    } catch (Exception $e) {
        // Handle exception, log it, etc.
        return "An error occurred: " . $e->getMessage();
    } finally {
        // check if the curl resource is still open and close it
        curl_close($curl);
    }
}


function send_money_with_nid_validation_v2($msisdn, $national_id, $loan_id, $amount, $mpesa_configs = [])
{
    // initialize necessary variables
    global $environment;
    global $fulldate;
    global $server2;
    global $api_server;
    global $api_server2;
    global $sl_key;
    $state = null;
    $desc = null;
    $errorMessage = null;
    $errors = null;

    if((input_length($api_server2, 5)) == 1){
        $api_server = $api_server2;
    }
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

        $PartyA = trim($mpesa_configs['property_value'] ?? '');
        $InitiatorName = trim($mpesa_configs['initiator_name'] ?? '');
        $SecurityCredential_enc = trim($mpesa_configs['security_credential'] ?? '');
        $SecurityCredential = decryptStringSecure($SecurityCredential_enc, $sl_key);
        $enc_token = trim($mpesa_configs['enc_token'] ?? '');
        $enc_token_key = trim($mpesa_configs['enc_token_key'] ?? '');
        $access_token = decryptString($enc_token, $enc_token_key);

        
        if (empty($InitiatorName) || empty($SecurityCredential) || empty($PartyA) || empty($enc_token) || empty($enc_token_key)) {
            throw new Exception("B2C configuration(s) missing!");
        }

        // preparing to send a b2c payment request
        $url = ($environment == 'sandbox') ? 'https://sandbox.safaricom.co.ke/mpesa/b2cvalida
    te/v2/paymentrequest' : 'https://api.safaricom.co.ke/mpesa/b2cvalidate/v2/paymentrequest';
        // =========== setting headers
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token)); //setting custom header
        // ============ End setting headers


        //  ========== preparing payload/request body
        $Amount = $amount;
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



// Encryption function
function encryptString($plaintext, $key = null)
{
    // Generate a random 256-bit encryption key if not provided
    if (!$key) {
        $key = openssl_random_pseudo_bytes(32);
    }

    // Generate an initialization vector (IV) for encryption
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));

    // Encrypt the plaintext
    $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    // Combine the IV and ciphertext
    $encrypted = base64_encode($iv . $ciphertext);

    return $encrypted;
}

// Decryption function
function decryptString($encrypted, $key = null)
{
    // Check if the key is provided
    if (!$key) {
        return "Encryption key is required for decryption.";
    }

    // Decode the encrypted string
    $encrypted = base64_decode($encrypted);

    // Extract the IV and ciphertext
    $iv = substr($encrypted, 0, openssl_cipher_iv_length('AES-256-CBC'));
    $ciphertext = substr($encrypted, openssl_cipher_iv_length('AES-256-CBC'));

    // Decrypt the ciphertext
    $plaintext = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    return $plaintext;
}


function generateEncryptionKey($bytes = 32)
{
    return base64_encode(openssl_random_pseudo_bytes($bytes));
}


function update_b2c_tkn_and_enc_key($b2c_tkn, $b2c_tkn_enc_key) {
    global $con;
    global $fulldate;
    $success = 0;
    $stmt = null;

    try {
        // Start a transaction
        mysqli_autocommit($con, FALSE);

        // Prepare the SQL statement
        $stmt = mysqli_prepare($con, "UPDATE o_mpesa_configs SET enc_token = ?, enc_token_key = ?, last_update = ? WHERE uid = 1");

        // Bind the parameters
        mysqli_stmt_bind_param($stmt, "sss", $b2c_tkn, $b2c_tkn_enc_key, $fulldate);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Commit the transaction
            mysqli_commit($con);
            $success = 1;
        } else {
            throw new Exception("Error updating token: " . mysqli_stmt_error($stmt));
        }
    } catch (Exception $e) {
        // Handle exceptions here
        echo "Error: " . $e->getMessage() . "<br/>";
        // Rollback the transaction
        mysqli_rollback($con);
    } finally {
        // Set autocommit back to true
        mysqli_autocommit($con, TRUE);

        // Close the statement
        if ($stmt !== null) {
            mysqli_stmt_close($stmt);
        }
    }

    return $success;
}


function checkMpesaB2CTnxStatus($mpesa_configs, $loan_id, $OriginalConversationID) {


    try{

        global $api_server; 
        global $server2;
    
        if ((input_length($api_server, 5)) == 0) {
            $server = $server2;
        } else {
            $server = $api_server;
        }
    
        $platform = company_settings();
        $company_id = $platform['company_id'];
        $Initiator = $mpesa_configs['initiator_name'] ?? '';
        $SecurityCredential = $mpesa_configs['security_credential'] ?? '';
        $PartyA = $mpesa_configs['property_value'] ?? '';
        $enc_token = $mpesa_configs['enc_token'] ?? '';
        $enc_token_key = $mpesa_configs['enc_token_key'] ?? '';
        $accessToken = decryptString($enc_token, $enc_token_key);
        $QueueTimeOutURL = $server . '/apis/mpesa_queue_timeout';
        $ResultURL = $server . '/apis/b2c-notice?c=' . $company_id . '&r=' . $loan_id;
        
    
        $url = 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ];
        
        $post_data = [
            'Initiator' => "$Initiator",
            'SecurityCredential' => "$SecurityCredential",
            'CommandID' => 'TransactionStatusQuery',
            'OriginalConversationID' => "$OriginalConversationID",
            "PartyA" => "$PartyA",
            'IdentifierType' => '4',
            'ResultURL' => "$ResultURL",
            'QueueTimeOutURL' => "$QueueTimeOutURL",
            'Remarks' => 'OK',
            'Occasion' => 'OK'
        ];
        
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($post_data),
            CURLOPT_HTTPHEADER => $headers,
        ]);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            throw new Exception("Curl Error: " . $error);
        } else {
            return json_decode($response, true);
        }
    }catch(Exception $e){
        echo "Error: " . $e->getMessage() . "<br/>";
        return null;
    }
}

function update_b2c_tkn($b2c_tkn) {
    global $con;
    global $fulldate;
    $success = 0;
    $stmt = null;

    try {
        // Start a transaction
        mysqli_autocommit($con, FALSE);

        // Prepare the SQL statement
        $stmt = mysqli_prepare($con, "UPDATE o_mpesa_configs SET r_token = ?, last_update = ? WHERE uid = 1");

        // Bind the parameters
        mysqli_stmt_bind_param($stmt, "ss", $b2c_tkn, $fulldate);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Commit the transaction
            mysqli_commit($con);
            $success = 1;
        } else {
            throw new Exception("Error updating token: " . mysqli_stmt_error($stmt));
        }
    } catch (Exception $e) {
        // Handle exceptions here
        echo "Error: " . $e->getMessage() . "<br/>";
        // Rollback the transaction
        mysqli_rollback($con);
    } finally {
        // Set autocommit back to true
        mysqli_autocommit($con, TRUE);

        // Close the statement
        if ($stmt !== null) {
            mysqli_stmt_close($stmt);
        }
    }

    return $success;
}