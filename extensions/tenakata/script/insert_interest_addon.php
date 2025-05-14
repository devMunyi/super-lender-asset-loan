<?php

// files includes
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");

// Read the JSON file content
$paymentsJson = file_get_contents('../data/repayments.json');


// Convert JSON data to a PHP array
$paymentsData = json_decode($paymentsJson, true);

$inserted = 0;
$skipped = 0;
$iteration = 0;

if (isset($paymentsData['data']) && is_array($paymentsData['data'])) {

    // set arrays to use
    $interestArr = [];
    $interestAddons = [];

    foreach ($paymentsData['data'] as $payData) {
        // set necessary variables
        $loan_id = intval($payData['loan']);
        $interest = doubleval($payData['interest']);
        
        $expected_instal_repay_date = new DateTime($payData['date']);
        $expected_instal_repay_date_r = $payData['date'];
        $current_date = new DateTime($date);

        if (isset($expected_instal_repay_date) && $current_date >= $expected_instal_repay_date && $interest > 0) {
            // interest
            $interestArr = obj_add($interestArr, $loan_id, $interest);

            // date added expect it to happen following day
            $expected_instal_repay_date->modify('+1 day');
            $applied_date = $expected_instal_repay_date->format('Y-m-d');

            $interestAddons[$loan_id] = [
                $loan_id, // loan_id
                1, // addon_id
                $interestArr[$loan_id], // addon_amount
                0, // added_by
                $applied_date, // added_date
                1 // status
            ];
        }

        $iteration ++;
    }

    foreach ($interestAddons as $n_loan_id => $values) {
        list($loan_id, $addon_id, $addon_amount, $added_by, $added_date, $status) = $values;

        // do db insert
        if($loan_id > 0){
            $fds = array('loan_id','addon_id','addon_amount','added_by','added_date','status');
            $vals = array($loan_id, $addon_id, $addon_amount, $added_by, $added_date,"$status");
            $create = addtodb('o_loan_addons',$fds,$vals);
            echo 'LOAN UID: '.$loan_id .' TABLE INSERT RESPONSE: '.$create .'<br>'; 
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

    echo "INSERTED INTEREST ADDONS: $inserted <br>";
    echo "SKIPPED INTEREST ADDONS: $skipped <br>";
}
