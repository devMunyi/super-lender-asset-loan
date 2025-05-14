<?php

// files includes
include_once ("../../../configs/conn.inc");
include_once ("../../../php_functions/functions.php");
include_once ("./reusable.php");


$customers = [];
$custs = fetchtable2('o_customers', 'uid > 0', 'uid', 'ASC');

while($c = mysqli_fetch_assoc($custs)){
    $cust_code = $c['customer_code'];
    $customers[$cust_code] = [
        $c['uid']
    ];
}


$file = "../data/waiyaki_customers.csv";
$cf_handle = fopen($file, "r");
$is_first_row = true; // Flag variable to track the first row

// set counters
$updated_counter = 0;
$skipped_counter = 0;

while(($c_data = fgetcsv($cf_handle, 1000000, ",")) !== FALSE){
    if ($is_first_row) {
        $is_first_row = false;
        continue; // Skip the first row and move to the next iteration
    } 
        // CUSTOMER NUMBER(0=>OK),Name(1=>OK),Status(2), Gender(3) ,NATIONAL ID NO(4),DISBURSEMENT NO(5=>OK),ALTERNATIVE PHONE NO(6),
            // GUARANTOR NAME(7),ID NO(8),GUARANTOR CONTACT(9),RELATIONSHIP(10),BUSINESS LOCATION(11),BUSINESS LOCATION MAP(12),
            // RESIDENTIAL LOCATION MAP(13)

        $cust_code  = trim($c_data[0]) ?? 0;
        $b = strtoupper($cust_code[0]);
        $cust_code = customerCode($cust_code, $b);
        $customer_id = $customers[$cust_code][0] ?? 0;
        $business_physical_address = sanitizeAndEscape($c_data[11], $con);
        $business_location_map = sanitizeAndEscape($c_data[12], $con);

        if($b == 'K'){
            $town_or_center = 'Kiambu';
        }elseif($b == 'N'){
            $town_or_center = 'Ngong';
        }elseif($b == 'D'){
            $town_or_center = 'Dagoretti';
        }elseif($b == 'W'){
            $town_or_center = 'Waiyaki Way';
        }else {
            $town_or_center = '';
        }

        if($customer_id > 0) {
            $sec_data = [
                5 => '',
                6 => '',
                7 => '',
                8 => '',
                9 => '',
                10 => '',
                11 => '',
                12 => '',
                13 => '',
                14 => '',
                15 => '',
                44 => $town_or_center,
                16 => '',
                17 => '',
                18 => $business_physical_address,
                50 => $business_location_map,
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
                30 => '',
                31 => '',
                32 => '',
                33 => '',
                34 => '',
                43 => '',
                46 => '',
                48 => '',
                35 => '',
                36 => '',
                37 => '',
                38 => '',
                39 => '',
                40 => '',
                41 => '',
                42 => '',
                45 => '',
                46 => '',
                49 => '',
                51 => '',
            ];
    
            $sec_data_json = json_encode($sec_data);
            $sec_data_json = sanitizeAndEscape($sec_data_json, $con);

            $update = updatedb('o_customers', "sec_data = '$sec_data_json'", "uid = $customer_id");
            echo 'Entry UID: '.$customer_id.' TABLE UPDATE RESPONSE: '.$update .'<br>';
            if($update == 1){
                $updated_counter += 1;
            }else {
                $skipped_counter += 1;
            }
        }

}

echo "UPDATED CUSTOMERS: $updated_counter <br>";
echo "SKIPPED CUSTOMERS:  $skipped_counter <br>";
