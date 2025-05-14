<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$offset = $_GET['offset'];
$rpp = $_GET['rpp'];

if(isset($_GET['start_date']) && isset($_GET['end_date']) && isset($_GET['rpp']) && isset($_GET['offset'])) {

    $payments = fetchtable('o_incoming_payments',"date(recorded_date) BETWEEN '$start_date' AND '$end_date' AND customer_id=0 AND loan_id=0 AND record_method LIKE '%UPLOAD%' AND status=1","uid","asc","$offset, $rpp","uid, mobile_number, comments");
    while($p = mysqli_fetch_array($payments)){
        $uid = $p['uid'];
        $mobile_number = $p['mobile_number'];
        $comments = $p['comments'];


        $string = $comments;

// The regular expression explained:
// - 'from\s+'      : matches the word "from" followed by one or more whitespace characters.
// - '([\d*]+)'     : captures one or more digits or asterisks (the phone number) into group 1.
// - '\s+-\s+'      : matches " - " (with optional spaces around the hyphen).
// - '([A-Za-z]+)'  : captures one or more letters (the name) into group 2.
        if (preg_match('/from\s+([\d*]+)\s+-\s+([A-Za-z]+)/', $string, $matches)) {
            $phone = $matches[1];
            $name  = $matches[2];
          /// echo "$uid Phone: $phone, ";
          ///  echo "Name: $name <br/>";

            $half_phone = str_replace("***", "%", $phone);

            ////------Check if a phone like this pattern exists
            $loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND account_number LIKE '$half_phone'","uid","asc","10","uid, customer_id, account_number");
            while($l = mysqli_fetch_array($loans)){
                $lid = $l['uid'];
                $customer_id = $l['customer_id'];
                $account_number = $l['account_number'];
                ////-----Check if the name matches too
                $name_match = trim($name);
                $cust = fetchonerow('o_customers',"full_name LIKE '%$name_match%' AND uid='$customer_id'","uid");
                if($cust['uid'] > 0) {
                    echo "Full match - Phone $account_number, Loan_id: $lid, Payment_id: $uid <br/>";
                    $upd = updatedb('o_incoming_payments',"customer_id='$customer_id', mobile_number='$account_number', loan_id='$lid'","uid='$uid'");
                    if($upd == 1){
                        echo "Updated $uid<br/>";
                        store_event('o_incoming_payments', $uid,"Uploaded Payment allocated automatically by pattern matching", 1);
                        //----Log
                    }
                }
            }


        } else {
           /// echo "No match found.";
        }


    }




    include_once("../configs/close_connection.inc");
}