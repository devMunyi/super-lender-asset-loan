<?php

// set required business information
$business_name = $sec['16'] ? $sec['16'] : "";
$biz_category = trim($sec['43']) == '--Select One' ? "" : trim($sec['43']);
$biz_type = $sec['47'];
$core_business = "$biz_category, $biz_type";
if (trim($core_business) == ",") {
    $core_business = "";
}

// biz information as an array
$data = [
    "customer_id" => $customer_id,
    "business_name" => $business_name,
    "core_business" => $core_business,
    "updated_by" => $userd['name']
];

tenakata_biz_info_callback($data, "UPDATE_EXISTING", "POST");


function tenakata_biz_info_callback($data, $action, $method)
{
    global $callback_token;
    global $callback_key;
    global $tkt_server_biz_info_url;
    $curl = curl_init(); // initialize curl
    $customer_id = $data['customer_id'];

    $payload = [
        "customer_id" => $customer_id,
        "business_name" => $data['business_name'],
        "core_business" => $data['core_business'],
        "updated_by" => $data['updated_by']
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
            CURLOPT_URL => "$tkt_server_biz_info_url",
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

        // execute curl
        $result = curl_exec($curl);

        // send POST api request by executing curl
        if ($result === false) {
            $err = curl_error($curl);
            $message = "Tenakata Remote Server API call to Sync Customer Business Information FAILED, Curl Error => ($err)";
            store_event('o_customers', $customer_id, "$message, $tkt_server_biz_info_url");
            exit();
        } else {
            // $message = "Tenakata Remote Server API call to Sync Customer Business Information Initiated";
            // store_event('o_customers', $customer_id, "$message, $tkt_server_biz_info_url");

            $resp_arr = json_decode($result, true);
            $status = $resp_arr["status"] ?? null;
            $message = $resp_arr["message"] ?? "";

            if ($status == 200) {
                $message = "Customer Business INFO was Synced with Tenakata Remote Server ($message)";
                store_event('o_customers', $customer_id, "$message, $tkt_server_biz_info_url, http_status_code: $status");
            } else {

                $message = "Customer Business INFO was NOT Synced with Tenakata Remote Server ($message).";
                store_event('o_customers', $customer_id, "$message, $tkt_server_biz_info_url, http_status_code: $status");
            }
        }
    } catch (Exception $e) {
        $http_status_code = 500;
        $message = $e->getMessage();
        store_event('o_customers', $customer_id, "$message, http_status_code: $http_status_code$e");
        // sendApiResponse($http_status_code, "Something Went Wrong!");
    } finally {
        curl_close($curl);
    }
}
