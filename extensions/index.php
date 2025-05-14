<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../configs/20200902.php");
$db = $db_;
//include_once(".configs/auth.inc");
include_once("../php_functions/functions.php");

$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");

$offset = $_GET['offset'];
$rpp = $_GET['rpp'];

echo "here";
/*
$loan_statuses = table_to_obj('o_loan_statuses',"uid>0","100","uid","name");

echo "<table>";

$loans = fetchtable('o_loans',"given_date >= '2024-06-01' AND disbursed=1 AND paid=0","uid","asc","100000","uid, loan_amount, total_repayable_amount, status, given_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $loan_amount = $l['loan_amount'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $status = $l['status'];
    $state = $loan_statuses[$status];
    $given_date = $l['given_date'];

    $addons_ = $total_repayable_amount - $loan_amount;
    $perc = ($addons_ / $loan_amount) * 100;

    echo "<tr><td>$uid</td><td>$given_date</td><td>$loan_amount</td><td>$total_repayable_amount</td><td>$perc</td><td>$state</td></tr>";
}
echo "</table>";
*/







/*
$customers = table_to_array('o_customers',"status=1 AND loan_limit<100","100000000","primary_mobile","uid","asc");

$customers_list = implode(',', $customers);

//echo $customers_list;
$latest_limits = array();

$all_loans = fetchtable('o_loans',"account_number in ($customers_list)","uid","asc","1000000","uid, account_number, loan_amount");
while($al = mysqli_fetch_array($all_loans)){

    $uid = $al['uid'];
    $account_number = $al['account_number'];
    $loan_amount = $al['loan_amount'];
    $latest_limits[$account_number] = $loan_amount;

}


foreach ($latest_limits as $account_number => $loan_amount){
    echo "$account_number - $loan_amount <br/>";
    updatedb('o_customers',"loan_limit='$loan_amount'","primary_mobile='$account_number'");

}

*/

$loans = fetchtable('o_loans',"given_date >= '2024-01-01' AND disbursed=1","uid","asc","$offset, $rpp","uid, total_addons, loan_amount, total_repayable_amount, given_date");
while($l = mysqli_fetch_array($loans)) {
    $loan_id = $l['uid'];
    $total_addons = $l['total_addons'];
    $loan_amount = $l['loan_amount'];
    $given_date = $l['given_date'];

    echo $loan_id;

    $interest_addons_total = loan_interest_addons($loan_id);
    if($interest_addons_total > 0){
        ////--- store it ask JSON
        //  $sec = array("INTEREST_AMOUNT"=>$interest_addons_total);
        $andsec = "other_info = JSON_SET(
                IFNULL(other_info, '{}'),
                '$.INTEREST_AMOUNT', '$interest_addons_total')";

        $update = updatedb('o_loans', "$andsec", "uid='$loan_id'");
        echo "Update $update, $loan_id, $given_date <br/>";

    }

}



/*
$loans = fetchtable('o_loans',"given_date >= '2024-10-01' AND disbursed=1 AND paid=0","uid","asc","10000","uid, total_addons, loan_amount, total_repayable_amount");
while($l = mysqli_fetch_array($loans)){
    $loan_id = $l['uid'];
    $total_addons = $l['total_addons'];
    $loan_amount = $l['loan_amount'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $addon_amount = $loan_amount * 0.23;

    echo addon_with_amount_update(3,$loan_id, $addon_amount, 0);
    echo addon_with_amount_update(6,$loan_id, $addon_amount, 0);
    echo addon_with_amount_update(4,$loan_id, 0, 0);
    echo recalculate_loan($loan_id, true);


    //echo "ID:$loan_id, Amount:$loan_amount, Repayable: $total_repayable_amount, Total Addons: $total_addons <br/>";
 //
//   echo updatedb('o_loans',"loan_flag = 0","uid=$loan_id");

    echo "--".$loan_id.',<br/>';

}
*/


/*

// Open the CSV file
$file = fopen('../test/jaza-statement.csv', 'r');

// Skip the header row if it exists
fgetcsv($file);
$from_mpesa_array = array();
// Loop through each row
while (($row = fgetcsv($file)) !== false) {
  $receipt = trim($row[0]);
  $amount = trim(removeNumberSeparators($row[4]));
  if($amount > 0) {

   //   echo $receipt . ", $amount" . "<br/>";
      $from_mpesa_array[$receipt] = $amount;
  }



}

// Close the file
fclose($file);


$loans = fetchtable('o_incoming_payments',"recorded_date > '2024-10-20 00:00:00'","uid","asc","10000","amount, transaction_code");
while($l = mysqli_fetch_array($loans)){
    $am = $l['amount'];
    $transaction_code = $l['transaction_code'];
    $statement_amount = $from_mpesa_array[$transaction_code];

    if (array_key_exists($transaction_code, $from_mpesa_array)) {
        if ($statement_amount != $am) {
            echo "$transaction_code - Original: $statement_amount, New: $am<br/>";
        }
    }
}

*/

echo "last";