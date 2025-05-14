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


// prepare guarantors relationships
$guarantor_relationships = array(
    'mother' => 1,
    'mum' => 1,
    'mom' => 1,
    'father' => 2,
    'dad' => 2,
    'mother in law' => 3,
    'mom in law' => 3,
    'mum in law' => 3,
    'step mother' => 3,
    'father in law' => 4,
    'step father' => 4,
    'dad in law' => 4,
    'son' => 5,
    'daughter' => 6,
    'brother' => 7,
    'bro' => 7,
    'sister' => 8,
    'sis' => 8,
    'sister in law' => 9,
    'sis in law' => 9,
    'brother in law' => 10,
    'bro in law' => 10,
    'husband' => 11,
    'spouse' => 12,
    'wife' => 13,
    'cousin' => 14,
    'niece' => 15,
    'nephew' => 16,
    'aunt' => 17,
    'aunt in law' => 18,
    'uncle' => 19,
    'uncle in law' => 20,
    'friend' => 21,
    'grand mom' => 22,
    'grand father' => 23,
    '' => 24,
    'daughter in law' => 25,
    'son in law' => 26
);

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


    $guarantor_name = sanitizeAndEscape($ct_data[7], $con) ?? '';
    $rel = sanitizeAndEscape($ct_data[10], $con);
    $rel = strtolower($rel);
    $relationship = $guarantor_relationships[$rel] ? $guarantor_relationships[$rel] : 24;
    $cust_code  = trim($ct_data[0]) ?? 0;
    $b = strtoupper($cust_code[0]);
    $cust_code = customerCode($cust_code, $b);
    $customer_id = $customers[$cust_code] ?? 0;
    $national_id = trim($ct_data[8]) ?? '0';
    $mobile_no = trim(($ct_data[9])) ?? '';
    $mobile_no = str_replace([' ', '+'], '', $mobile_no);
    $mobile_no = make_phone_valid($mobile_no);
    $phone_valid = validate_phone($mobile_no);
    if($phone_valid == 0){
        $mobile_no = "";
    }
    $added_date = $fulldate;
    $status = 1;

    if ($customer_id > 0 && strlen($guarantor_name) > 0) {
        $fds = array('guarantor_name', 'customer_id', 'national_id', 'mobile_no', 'added_date', 'relationship', 'status');
        $vals = array("$guarantor_name", $customer_id, "$national_id", "$mobile_no", "$added_date", $relationship, $status);

        $create = addtodb('o_customer_guarantors', $fds, $vals);
        echo 'CUSTOMER ID: ' . $customer_id . ' TABLE INSERT RESPONSE: ' . $create . '<br>';
        if ($create == 1) {
            $inserted += 1;
        } else {
            $skipped += 1;
        }
    }
}

echo "INSERTED GUARANTORS: $inserted <br>";
echo "SKIPPED GUARANTORS: $skipped <br>";
