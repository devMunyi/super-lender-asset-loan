<?php

$expected_http_method = 'GET';
include_once("../../../vendor/autoload.php");
include_once ("../../../configs/allowed-ips-or-origins.php");
include_once("../../../configs/conn.inc");
include_once("../../../configs/jwt.php");
include_once("../../../php_functions/jwtAuthUtils.php");
include_once("../../../php_functions/jwtAuthenticator.php");
include_once("../../../php_functions/functions.php");

try {

    $custs = fetchtable('o_customers', "uid > 0", "uid", "ASC", "10000000", "uid, full_name, primary_mobile, national_id, gender, sec_data, added_by");
    $users_names = table_to_obj('o_users', "uid > 0", "1000000", "uid", "name");

    $payload = [];
    $customers_count = 0;
    while ($r = mysqli_fetch_assoc($custs)) {
        $customers_arr = [];
        $customers_arr["uid"] = $r['uid'];
        $customers_arr["full_name"] = $r['full_name'];
        $customers_arr["primary_mobile"] = $r['primary_mobile'];
        $customers_arr["national_id"] = $r['national_id'];
        $customers_arr["gender"] = $r['gender'];
        $added_by = $r['added_by'];
        $added_by = $users_names[$added_by] ?? "";
        $customers_arr["added_by"] = $added_by;
        $sec = $r["sec_data"] ? json_decode($r["sec_data"], true) : array();

        // extracting secondary information
        if (empty($sec)) {
            $business_name = "";
            $core_business = "";
        } else {
            $business_name = $sec['16'] ? $sec['16'] : "";
            $biz_category = trim($sec['43']) == '--Select One' ? "" : trim($sec['43']);
            $biz_type = $sec['47'];
            $core_business = "$biz_category, $biz_type";
            if (trim($core_business) == ",") {
                $core_business = "";
            }
        }

        $customers_arr["business_name"] = $business_name;
        $customers_arr["core_business"] = $core_business;

        $payload[] = $customers_arr;
        $customers_count += 1;
    }

    $message = "OK";
    $http_status_code = 200;
    sendApiResponse2($http_status_code, $customers_count, "$message", $payload);
} catch (Exception $e) {
    $http_status_code = 500;
    $message =  $e->getMessage();
    sendApiResponse($http_status_code, "Something Went Wrong! Retry");
} finally {
    mysqli_close($con);
}
