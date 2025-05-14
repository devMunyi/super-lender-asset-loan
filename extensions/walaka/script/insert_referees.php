<?php 

// files includes
include_once ("../../../configs/conn.inc");
include_once ("../../../php_functions/functions.php");
include_once ("./reusable.php");

$customers = [];
$custs = fetchtable2('o_customers', 'uid > 0', 'uid', 'ASC', 'uid, customer_code');

while($c = mysqli_fetch_assoc($custs)){
    $cust_code = $c['customer_code'];
    $customers[$cust_code] = $c['uid'];
}

// prepare referees relationships
$ref_relationships = array(
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


$file = "../data/dagoretti_referees.csv";;
$open_l = fopen($file, "r");
$is_first_row = true; // Flag variable to track the first row


// set counters
$inserted = 0;
$skipped = 0;
$iteration = 0;

while(($ref_data = fgetcsv($open_l, 1000000, ",")) !== FALSE){
    if ($is_first_row) {
        $is_first_row = false;
        continue; // Skip the first row and move to the next iteration
    }

    // Customer Number/ Application ID(0), Referee Name(1), RELATIONSHIP(2), PHONE NUMBER(3)
    $cust_code  = trim($ref_data[0]) ?? 0;
    $b = strtoupper($cust_code[0]);
    $cust_code = customerCode($cust_code, $b);
    $customer_id = $customers[$cust_code] ?? 0;
    $added_date = $fulldate;
    $referee_name = sanitizeAndEscape($ref_data[1], $con);
    $rel = sanitizeAndEscape($ref_data[2], $con);
    $rel = strtolower($rel);
    $relationship = $ref_relationships[$rel] ?? 24;
    $mobile_no = trim(($ref_data[3])) ?? '';
    $mobile_no = str_replace([' ', '+'], '', $mobile_no);
    $mobile_no = make_phone_valid($mobile_no);
    $phone_valid = validate_phone($mobile_no);

    if($phone_valid == 0){
        $mobile_no = "";
    }

    $status = 1;

    if($customer_id > 0 && strlen($referee_name) > 0){
        $fds = array('customer_id','added_date','referee_name', 'mobile_no', 'relationship', 'status');
        $vals = array($customer_id, "$added_date", "$referee_name", "$mobile_no", $relationship, $status);
        
        $create = addtodb('o_customer_referees' ,$fds, $vals);
        echo 'CUSTOMER CODE: '.$cust_code .' TABLE INSERT RESPONSE: '.$create .'<br>'; 
        if($create == 1)
        {
            $inserted += 1;
        }
        else
        {
            $skipped += 1;
        }
    }
}

echo "INSERTED REFEREES: $inserted <br>";
echo "SKIPPED REFEREES: $skipped <br>";