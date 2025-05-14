<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


$userd = session_details();
$added_by = $userd['uid'];
$file_name = $_FILES['file_']['name'];
$file_size = $_FILES['file_']['size'];
$file_tmp = $_FILES['file_']['tmp_name'];
$upload_location = '../../mpesa_uploads/';
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];


$allowed_formats = "csv";
$allowed_formats_array = array('csv');

if(!validate_date($start_date)){
    die(errormes("Invalid start date"));
}
if(!validate_date($end_date)){
    die(errormes("Invalid end date"));
}


if($file_size > 10){
    if((file_type($file_name, $allowed_formats_array)) == 0){
        die(errormes("This file format is not allowed. Only CSV files"));
    }
}else{
    die(errormes("File not attached or has invalid size"));
}


/////------Pick payments
$pay_amounts_array = array();
$payment_dates_array = array();
$no_trans_code_array = array();
$mpesa_uids_array = array();
$all_mpesa_codes_system = array();
///
$pays = fetchtable('o_incoming_payments',"payment_date BETWEEN '$start_date' AND '$end_date' AND status=1","uid","asc","10000000","uid, amount, transaction_code, payment_date");
while($p = mysqli_fetch_array($pays)){
    $uid = $p['uid'];
    $amount = $p['amount'];
    $transaction_code = $p['transaction_code'];
    $payment_date = $p['payment_date'];
    if(input_length($transaction_code, 10)) {
        $pay_amounts_array[$transaction_code] = $amount;
        $payment_dates_array[$transaction_code] = $payment_date;
        $mpesa_uids_array[$transaction_code] = $uid;
        array_push($all_mpesa_codes_system, $transaction_code);
    }
    else{
        $no_trans_code_array[$uid] = $amount;
    }

}



////---
$handle = fopen($file_tmp, "r");
$i = 0;

$upload = upload_file($file_name, $file_tmp, $upload_location);
if($upload === 0){
	echo errormes("Error uploading file, please retry");
	exit();
}
$saved = $skipped = $failed = 0;

$open = fopen("../../mpesa_uploads/".$upload, "r");
$data = fgetcsv($open, 10000000, ",");

echo "<table class='tablex text-black' id='table1'>";
echo "<thead><tr><th>Transaction Code</th><th>Mpesa Amount</th><th>System Amount</th><th>Amount Result</th><th>Mpesa Date</th><th>System Date</th><th>Date Result</th><th>General Comment</th><th>UID</th></tr></thead>";

echo "<tbody>";
$all_mpesa_codes = array();

while(($data2 = fgetcsv($open, 100000, ",")) !== FALSE){
    $mpesa_code = trim($data2[0]);
    $completed_time = trim($data2[1]);
    $details = trim(str_replace("'", "", $data2[3]));
    $transaction_status = trim($data2[4]);
    $amount = trim((int)str_replace(',', '', $data2[5]));
    $cost = trim($data2[6]);
    $till_balance = trim($data2[7]);   //////or paybill balance
    $account = trim($data2[12]);
    if (strtotime($completed_time) == false) {
        $date_valid = 0;
    } else {
        $date_valid = 1;
    }

    if ($amount > 0 && $date_valid == 1) {
        $d = datefromdatetime2($completed_time);
        $formatted_date = dateformatchange($d);
        array_push($all_mpesa_codes, $mpesa_code);

        $system_amount = $pay_amounts_array[$mpesa_code];
        $system_date = $payment_dates_array[$mpesa_code];
        $mpesa_uid = $mpesa_uids_array[$mpesa_code];

        if (array_key_exists($mpesa_code, $pay_amounts_array)) {
            $comment = "";
            if($system_amount != $amount){
                $amount_comment = "Amount Mismatch";
            }
            else{
                $amount_comment = "Ok";
            }
            if($system_date != $formatted_date){
                $date_comment = "Date Mismatch";
            }
            else{
                $date_comment = "Ok";
            }
        } else {
            $comment = "Not in System";
            $amount_comment = "N/A";
            $date_comment = "N/A";
        }






        echo "<tr><td>$mpesa_code</td><td>$amount</td><td>$system_amount</td><td>$amount_comment</td><td>$formatted_date</td><td>$system_date</td><td>$date_comment</td><td>$comment</td><td>$uid</td></tr>";
    }

}

////----Find payments in system but not in M-Pesa
///
$pays = fetchtable('o_incoming_payments',"payment_date BETWEEN '$start_date' AND '$end_date' AND status=1","uid","asc","10000000","uid, amount, transaction_code, payment_date");
while($p = mysqli_fetch_array($pays)){
    $uid = $p['uid'];
    $amount = $p['amount'];
    $transaction_code = $p['transaction_code'];
    $payment_date = $p['payment_date'];
    $comment_ = "Not exist in Mpesa";

    if (in_array($transaction_code, $all_mpesa_codes) && input_length($transaction_code, 10) == 1) {
        ////----Skip existing
    } else {
        echo "<tr><td>$transaction_code</td><td></td><td>$amount</td><td></td><td></td><td>$payment_date</td><td></td><td>$comment_</td><td>$uid</td></tr>";
    }

}
///
/// -----
echo "</tbody>";
echo "</table>";

unlink($upload_location.$upload);

?>
<script>
    $(document).ready(function() {
        $('#table1').DataTable({
            dom: 'Blfrtip', // Add buttons, length menu (l), filter (f), etc.
            pageLength: 20, // Set default page length to 20
            lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ], // Define rows per page options
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    });

</script>
