<?php

$data = [
    'customer_id' => $cust_id,
    'full_name' => $full_name,
    'primary_mobile' => $primary_mobile,
    'national_id' => $national_id,
    'gender' => $gender,
    'added_by' => $userd['name']
];

tenakata_customer_callback($data, "CREATE_NEW", "POST");

function tenakata_customer_callback($data, $action, $method)
{
    global $callback_token;
    global $callback_key;
    global $tkt_server_cust_info_url;
    $curl = curl_init(); // initialize curl
    $cust_id = $data['customer_id'] ?? 0;

    $payload = [
        "customer_id" => $cust_id,
        "fullname" => $data['full_name'],
        "country_code" => 254,
        "phone_number" => $data['primary_mobile'],
        "national_id" => $data['national_id'],
        "gender" => $data['gender'],
        "supervisor_name" => $data['added_by']
    ];

    $requestBody = json_encode(["action" => $action, "payload" => $payload]);

    try {
        // Set the Content-Length header
        $contentLength = strlen($requestBody);
        $requestHeaders = array(
            "Content-Type: application/json",
            "Content-Length: $contentLength",
            "token: $callback_token",
            "x-api-key: $callback_key"
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$tkt_server_cust_info_url",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_HTTPHEADER => $requestHeaders,
        ));

        // send POST api request by executing curl
        $result = curl_exec($curl);

        if ($result === false) {
            $err = curl_error($curl);
            $message = "Tenakata Remote Server API call to Sync Customer Primary Information FAILED, Curl Error => ($err)";
            store_event('o_customers', $cust_id, "$message, $tkt_server_cust_info_url");
            exit();
        } else {

            // $message = "Tenakata Remote Server API call to Sync Customer Primary Information Initiated";
            // store_event('o_customers', $cust_id, "$message, $tkt_server_cust_info_url");

            $resp_arr = json_decode($result, true);
            $status = $resp_arr["status"] ?? null;
            $message = $resp_arr["message"] ?? null;

            $details = ($status == 200) ? "Customer Primary INFO was Synced with Tenakata Remote Server. Response => ($message)." : "Customer Primary INFO was NOT Synced with Tenakata Remote Server. Response => ($message).";

            store_event('o_customers', $cust_id, "$details, $tkt_server_cust_info_url, http_status_code: $status");
        }
    } catch (Exception $e) {
        $http_status_code = 500;
        $message = $e->getMessage();
        store_event('o_customers', $cust_id, "$message, http_status_code: $http_status_code");
    } finally {
        curl_close($curl);
    }
}
