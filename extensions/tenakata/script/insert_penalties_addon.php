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
    $penaltiesArr = [];
    $penaltiesAddons = [];

    foreach ($paymentsData['data'] as $payData) {
        // set necessary variables
        $loan_id = intval($payData['loan']);
        $penalties = doubleval($payData['penalties']);
        
        $expected_instal_repay_date = new DateTime($payData['date']);
        $expected_instal_repay_date_r = $payData['date'];
        $current_date = new DateTime($date);

        if (isset($expected_instal_repay_date) && $current_date >= $expected_instal_repay_date && $penalties > 0) {
            // fees
            $penaltiesArr = obj_add($penaltiesArr, $loan_id, $penalties);

            // date added expect it to happen following day
            $expected_instal_repay_date->modify('+1 day');
            $applied_date = $expected_instal_repay_date->format('Y-m-d');

            $penaltiesAddons[$loan_id] = [
                $loan_id, // loan_id
                4, // addon_id
                $penaltiesArr[$loan_id], // addon_amount
                0, // added_by
                $applied_date, // added_date
                1 // status
            ];
        }

        $iteration ++;
    }

    foreach ($penaltiesAddons as $n_loan_id => $values) {
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

    echo "INSERTED PENALTIES ADDONS: $inserted <br>";
    echo "SKIPPED PENALTIES ADDONS: $skipped <br>";
}
