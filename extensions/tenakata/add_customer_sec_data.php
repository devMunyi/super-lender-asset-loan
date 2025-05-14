<?php

// files includes
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

// Read the JSON file content
$customersJson = file_get_contents('customers.json');

// Convert JSON data to a PHP array
$customersArr = json_decode($customersJson, true);

if (isset($customersArr['data']) && is_array($customersArr['data'])) {
    
    // set arrays to use
    $updated_counter = 0;
    $skipped_counter = 0;

    foreach ($customersArr['data'] as $cust) {
        // prepare secondary data to added as a json value referencing customer/member id
        $uid = $cust['id'] ?? 0;
        $estate = $cust['estate'] ?? '';
        $house_no = $cust['floor'] ?? '';
        $geo_loc = $cust['geo_location'] ?? $cust['location'] ?? $cust['region'] ?? $cust['constituency'] ?? '';
        $town_or_center = $cust['location'] ?? $cust['region'] ?? $cust['business_town'] ?? '';
        $postal_address = $cust['postal_address'] ?? '';
        $county_or_state = $cust['county_or_state'] ?? '';
        $business_name = $cust['business_name'] ?? '';
        $business_physical_address = $cust['business_physical_address'] ?? $cust['business_town'] ?? '';
        $reg_cert_no = $cust['registration_certificate_no'] ?? 0;
        $referred_by = $cust['introduced_by_member'] ?? $cust['introduced_by_member'] ?? '';

        if($uid > 0) {
            $sec_data = [
                5 => '',
                6 => '',
                7 => $estate,
                8 => $house_no,
                9 => '',
                10 => '',
                11 => '',
                12 => '',
                13 => '',
                14 => $geo_loc,
                15 => '',
                44 => $town_or_center,
                52 => $postal_address,
                53 => $county_or_state,
                16 => $business_name,
                17 => '',
                18 => $business_physical_address,
                20 => '',
                21 => '',
                22 => '',
                23 => '',
                24 => '',
                25 => '',
                26 => '',
                27 => '',
                28 => '',
                29 => '',
                30 => $reg_cert_no,
                31 => '',
                32 => '',
                33 => '',
                34 => '',
                43 => '',
                46 => '',
                48 => '',
                50 => $geo_loc,
                35 => '',
                36 => '',
                37 => '',
                38 => '',
                39 => '',
                40 => '',
                41 => '',
                42 => '',
                45 => '',
                46 => $referred_by,
                49 => '',
                51 => '',

            ];
    
            $sec_data_json = json_encode($sec_data);

            $update = updatedb('o_customers', "sec_data = '$sec_data_json'", "uid = $uid");
            echo 'Entry UID: '.$uid .' TABLE UPDATE RESPONSE: '.$update .'<br>';
            if($update == 1){
                $updated_counter += 1;
            }else {
                $skipped_counter += 1;
            }
            
        }
    }
    
    echo "UPDATED CUSTOMERS: $updated_counter <br>";
    echo "SKIPPED CUSTOMERS:  $skipped_counter <br>";

}else {
    echo "Error: Unable to read customers data." . PHP_EOL;
}


function custom_escape_string($value) {
    // Add more characters to the list if needed
    $special_chars = array("'", '"', '\\');
    $replace_chars = array("\'", '\"', '\\\\');

    return str_replace($special_chars, $replace_chars, $value);
}


?>
