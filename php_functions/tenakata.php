<?php
function tenakata_customer_callback($data, $action, $method)
{
    global $callback_token;
    global $callback_key;
    global $tkt_server_cust_info_url;
    $curl = curl_init(); // initialize curl
    $customer_id = $data['customer_id'];

    $payload = [
        "customer_id" => $customer_id,
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
        curl_exec($curl);
        $err = curl_error($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status_code == 200) {
            $message = "Customer Primary INFO was sent to Tenakata Remote Server via an API call.";
            store_event('o_customers', $customer_id, "$message, $tkt_server_cust_info_url, http_status_code: $status_code");
        } else {

            $message = "Customer Primary INFO was NOT sent to Tenakata Remote Server via an API call.";
            store_event('o_customers', $customer_id, "$message, $tkt_server_cust_info_url, http_status_code: $status_code, Error: $err");
        }
    } catch (Exception $e) {
        $http_status_code = 500;
        $message = $e->getMessage();
        store_event('o_customers', $customer_id, "$message, http_status_code: $http_status_code");
        // sendApiResponse($http_status_code, "Something Went Wrong!");
    } finally {
        curl_close($curl);
    }
}



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

        // send POST api request by executing curl
        $result = curl_exec($curl);
        $resp_arr = json_decode($result, true);
        $status = $resp_arr["status"] ?? null;
        $message = $resp_arr["message"] ?? "";

        // $err = curl_error($curl);
        // $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status == "200") {
            $message = "Customer Business INFO was sent to Tenakata Remote Server ($message)";
            store_event('o_customers', $customer_id, "$message, $tkt_server_biz_info_url, http_status_code: $status");
        } else {

            $message = "Customer Business INFO was NOT sent to Tenakata Remote Server ($message).";
            store_event('o_customers', $customer_id, "$message, $tkt_server_biz_info_url, http_status_code: $status");
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
