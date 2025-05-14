<?php

session_start();
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$addonId = 8;
$total = 0;

$all_loans = fetchtable('o_loans', "status!=0 AND disbursed = 1 AND product_id = 2 AND DATEDIFF('$date', given_date) >= 8 AND  paid = 0", "uid", "desc", "1000000", "uid, given_date, loan_amount");
echo "Total loans: " . mysqli_num_rows($all_loans) . "<br/>";
while ($l = mysqli_fetch_array($all_loans)) {

    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $loan_amount = $l['loan_amount'];
    $amount = (5 / 100) * $loan_amount;

    $exists = checkrowexists('o_loan_addons', "loan_id='$uid' AND addon_id = $addonId AND status=1");
    
    if ($exists == 1) {
        echo "Addon exists for loan $uid <br/>";
        continue;
        // $save = updatedb('o_loan_addons', "addon_amount = '$amount', added_date = '$fulldate', status = 1", "loan_id='$uid' AND addon_id = 8 AND status = 1");

        // if ($save == 1 && $recalculate_loan == true) {
        //     recalculate_loan($uid);
        // }
    } else {

        $fds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
        $vals = array($uid, $addonId, $amount, "$fulldate", 1);
        $added = addtodb('o_loan_addons', $fds, $vals);

        if ($added == 1) {
            recalculate_loan($uid, true);
        }
    }

    if ($added == 1) {
        store_event('o_loans', $uid, "Afterdue Interest Addon Added/Updated ($exists) with amount $amount by system cron service");
        echo "Addon added for loan $uid <br/>";
    }else {
        echo "Error adding addon for loan $uid <br/>";
    }

    $total = $total + 1;
    echo "Applied $uid, Addon amount: $amount <br/>";
}

echo "Total: $total <br/>";
