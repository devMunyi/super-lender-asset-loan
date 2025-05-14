<?php

// files includes
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");
include_once("./reusable.php");

$loanIds = [];

$loans = fetchtable2('o_loans', 'uid > 0', 'uid', 'ASC', 'uid, loan_code');

while ($l = mysqli_fetch_assoc($loans)) {
    $uid_ = $l['uid'];
    $loan_code_ = $l['loan_code'];

    if (!isset($loanIds[$loan_code_])) {
        $loanIds[$loan_code_] = $uid_;
    }
}


$file = "../data/dagoretti_loans.csv";
$open_l = fopen($file, "r");
$is_first_row = true; // Flag variable to track the first row

// set counters
$inserted = 0;
$skipped = 0;
$iteration = 0;

while (($ldata = fgetcsv($open_l, 1000000, ",")) !== FALSE) {
    if ($is_first_row) {
        $is_first_row = false;
        continue; // Skip the first row and move to the next iteration
    }

    // Application ID / Customer Number(0), Loan No.(1), Branch(2), Loan Product(3), Principal Amount(4),
    // Loan Release Date(5), Repayment Date(6), Total Cost(7 ), Total Repaid(8), Loan Interest(9), Initiation Fee(10), Penalties(11)
    // echo json_encode($ldata);
    $cust_code  = trim($ldata[0]) ?? 0;
    $b = strtoupper($cust_code[0]);
    $cust_code = customerCode($cust_code, $b);
    $loan_num = trim($ldata[1]) ?? 0;
    $loan_code = $cust_code . '-' . $loan_num;
    $loan_id = $loanIds[$loan_code] ?? 0;
    $addon_id = 1; // Base interest
    $addon_amount = doubleval(trim($ldata[9])) ?? 0;
    $added_date = "0000-00-00 00:00:00";

    echo "LOAN CODE: ".$loan_code.  " AMOUNT: ".$addon_amount .", LOAN ID: ".$loan_id ."<br>";
    if ($addon_amount > 0 && $loan_id > 0) {
        $fds = array('loan_id', 'addon_id', 'addon_amount', 'added_date');
        $vals = array($loan_id, $addon_id, $addon_amount, "$added_date");
    
        $create = addtodb('o_loan_addons', $fds, $vals);
        echo 'LOAN CODE: ' . $loan_code . ' TABLE INSERT RESPONSE: ' . $create . '<br>';
        if ($create == 1) {
            $inserted += 1;
        } else {
            $skipped += 1;
        }
    }
}

echo "INSERTED INTEREST ADDONS: $inserted <br>";
echo "SKIPPED INTEREST ADDONS: $skipped <br>";
