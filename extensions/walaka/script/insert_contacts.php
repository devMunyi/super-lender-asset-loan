<?php

// files includes
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");
include_once("./reusable.php");

$customers = [];
$custs = fetchtable2('o_customers', 'uid > 0', 'uid', 'ASC', 'uid, customer_code');

while ($c = mysqli_fetch_assoc($custs)) {
    $cust_code = $c['customer_code'];
    $customers[$cust_code] = $c['uid'];
}

$file = "../data/dagoretti_customers.csv";
$open_l = fopen($file, "r");
$is_first_row = true; // Flag variable to track the first row

// set counters
$inserted = 0;
$skipped = 0;
$iteration = 0;

while (($ct_data = fgetcsv($open_l, 1000000, ",")) !== FALSE) {
    if ($is_first_row) {
        $is_first_row = false;
        continue; // Skip the first row and move to the next iteration
    }

    // CUSTOMER NUMBER(0=>OK),Name(1=>OK),Status(2), Gender(3) ,NATIONAL ID NO(4),DISBURSEMENT NO(5=>OK),ALTERNATIVE PHONE NO(6),
    // GUARANTOR NAME(7),ID NO(8),GUARANTOR CONTACT(9),RELATIONSHIP(10),BUSINESS LOCATION(11),BUSINESS LOCATION MAP(12),
    // RESIDENTIAL LOCATION MAP(13)

    $cust_code  = trim($ct_data[0]) ?? 0;
    $b = strtoupper($cust_code[0]);
    $cust_code = customerCode($cust_code, $b);
    $customer_id = $customers[$cust_code] ?? 0;
    $contact_type = 1;
    $value = trim(($ct_data[6])) ?? '';
    $value = str_replace([' ', '+'], '', $value);
    $value = make_phone_valid($value);
    $phone_valid = validate_phone($value);
    if($phone_valid == 0){
        $value = "";
    }
    $status = 1;

    if ($customer_id > 0 && strlen($value) > 0) {
        $fds = array('customer_id', 'contact_type', 'value', 'status');
        $vals = array($customer_id, $contact_type, "$value", $status);

        $create = addtodb('o_customer_contacts', $fds, $vals);
        echo 'CUSTOMER CODE: ' . $cust_code . ' TABLE INSERT RESPONSE: ' . $create . '<br>';
        if ($create == 1) {
            $inserted += 1;
        } else {
            $skipped += 1;
        }
    }
}

echo "INSERTED CONTACTS: $inserted <br>";
echo "SKIPPED CONTACTS: $skipped <br>";
