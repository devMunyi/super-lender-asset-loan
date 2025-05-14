<?php
session_start();
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


// select all paymenes whose transaction starts with SP-
$rpp = $_GET['rpp'] ?? 10;
$payments = fetchtable('o_incoming_payments', "transaction_code LIKE 'SP-%' AND status=1 AND collected_by = 0", "uid", "DESC", "$rpp", "uid, transaction_code, split_from");


$split_from_uids = []; // eg [1, 2, 3, 4, 5]
while ($payment = mysqli_fetch_assoc($payments)) {
    $split_from_uids[] = $payment['split_from'];
}


// implode split from uids 
$split_from_uids_str = implode(",", $split_from_uids); // eg "1, 2, 3, 4, 5"
$payments2 = fetchtable('o_incoming_payments', "uid IN ($split_from_uids_str) AND collected_by != 0", "uid", "asc", "$rpp", "uid, collected_by, transaction_code");


$split_from_coll_by_kv = []; // [1 => "3", 2 => "2", 3 => "3", 4 => "4", 5 => "5"]
while ($payment2 = mysqli_fetch_assoc($payments2)) {

    // $split_parent_uid = $payment2['uid'];
    // echo "Split Parent uid: $split_parent_uid <br>";

    // $collected_by_uid = $payment2['collected_by'];
    // echo "Collected by: $collected_by_uid <br>";

    // $transaction_code = $payment2['transaction_code'];
    // echo "Transaction code: $transaction_code <br>";

    $split_from_coll_by_kv[$split_parent_uid] = $collected_by_uid;
}


// loop through $split_from_uids and update collected_by 
$updated_count = 0;

// echo json_encode($split_from_coll_by_kv);
foreach ($split_from_coll_by_kv as $split_from => $collected_by) {

    // echo "Updating $split_from to $collected_by <br>";
    $resp  = updatedb('o_incoming_payments', "collected_by='$collected_by'", "split_from='$split_from'");
    if ($resp) {
        $updated_count++;
    }
}

echo "Updated $updated_count records";

