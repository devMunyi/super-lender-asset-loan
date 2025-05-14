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

$permi = permission($userd['uid'],'o_incoming_payments',"0","general_");
if($permi != 1){
    die(errormes("You don't have permission to upload payment"));
    exit();
}

$till_phone = fetchrow('o_customers',"uid='23'","primary_mobile");
$allowed_formats = "csv";
$allowed_formats_array = explode(",", $allowed_formats);

if($file_size > 10){
    if((file_type($file_name, $allowed_formats_array)) == 0){
        die(errormes("This file format is not allowed. Only $allowed_formats files"));
    }
}else{
    die(errormes("File not attached or has invalid size"));
}


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

///-------------Read customers data before hand
$open2 = fopen("../../mpesa_uploads/".$upload, "r");
$data2 = fgetcsv($open2, 10000000, ",");

$all_phones = array();
$all_accounts = array();
$all_account_phones = array();
$all_mpesa_codes = array();
while(($data2 = fgetcsv($open2, 100000, ",")) !== FALSE){
    $details2 = trim(str_replace("'", "",$data2[3]));
    $mpesa_codex = trim($data2[0]);
    $payer = explode(' ', $details2);
    $phonex = $payer[3];
    $acc = trim($data2[12]);

    if(validate_phone(make_phone_valid($phonex)) == 1){
        array_push($all_phones, make_phone_valid($phonex));
        array_push($all_mpesa_codes, $mpesa_codex);
    }
    if($acc > 100){
        array_push($all_accounts, $acc);
    }

}

//////////------------------Mpesa codes

$mpesa_code_list = implode("','", $all_mpesa_codes);

$existing_codes = table_to_array('o_incoming_payments',"transaction_code in ('$mpesa_code_list')","1000000","transaction_code");
/////////---------------
//echo "<textarea>$mpesa_code_list</textarea>";
//echo implode('*',$existing_codes);
//die();

$phone_list = implode(',', $all_phones);
$account_list = implode(',', $all_accounts);
$customer_ids = array();
$customer_branches = array();

$customer_det = fetchtable('o_customers', "primary_mobile in ($phone_list)","uid","asc","100000", "uid, branch, primary_mobile, primary_product");
while($c = mysqli_fetch_array($customer_det)){
    $cid = $c['uid'];
    $cbranch = $c['branch'];
    $primary_mobile = $c['primary_mobile'];
    $customer_ids[$primary_mobile] = $cid;
    $customer_branches[$cid] = $cbranch;
    $primary_product = $c['primary_product'];
}

// optionally use $scr to handle overpayment splitting
$primary_product = $primary_product ? $primary_product : 1;
$scr = after_script($primary_product, "SPLIT_PAYMENT");

$latest_loan_ids = array();
$latest_collectors = array();

$latest_loans = fetchtable('o_loans',"account_number in ($phone_list) AND disbursed=1 AND paid=0 AND status !=0","uid","asc","100000000","uid, customer_id, current_co, current_agent");
while($ll = mysqli_fetch_array($latest_loans)){
    $lid = $ll['uid'];
    $customer_id = $ll['customer_id'];
    $current_co = $ll['current_co'];
    $current_agent = $ll['current_agent'];
    $collector = $ll['current_co'];

    if($current_agent > 0){

    }
    else{
        $current_agent = $collector;
    }

    $latest_loan_ids[$customer_id] = $lid;
    $latest_collectors[$customer_id] = $current_agent;



}
//
//echo errormes(json_encode($customer_branches));
//echo errormes(json_encode($customer_branches));
//echo errormes(json_encode($latest_loan_ids));
//echo errormes(json_encode($latest_collectors));

//die("");

//--------------End of read customers data beforehand
$affected_loans = array();
$payment_array = array();
while (($data = fgetcsv($open, 100000, ",")) !== FALSE){
	//$data = fgetcsv($handle);
     $mpesa_code = trim($data[0]);

     if(!in_array($mpesa_code, $existing_codes)) {
         $completed_time = trim($data[1]);
         $details = trim(str_replace("'", "", $data[3]));
         $transaction_status = trim($data[4]);
         $amount = trim((int)str_replace(',', '', $data[5]));
         $cost = trim($data[6]);
         $till_balance = trim($data[7]);   //////or paybill balance
         $account = trim($data[12]);
         if (strtotime($completed_time) == false) {
             $date_valid = 0;
         } else {
             $date_valid = 1;
         }


         // echo $data[0].'<br/>';
         // echo "Mpesa Code: $mpesa_code, Completed Time:  $completed_time, Details: $details, Trans Status: $transaction_status, Amount: $amount, Cost: $cost, Till Balance: $till_balance, Account: $account <hr/>";
         ////---------Check if this is a valid transaction
         if ($amount > 0 && $date_valid == 1 && $amount < 100000) {

             // echo "Mpesa Code: $mpesa_code, Completed Time:  $completed_time, Details: $details, Trans Status: $transaction_status, Amount: $amount, Cost: $cost, Till Balance: $till_balance, Account: $account <hr/>";
             $payer = explode(' ', $details);
             $phone = $payer[3];
             if ($phone == 'from') {
                 $phone = $payer[4];
             }

             if (validate_phone(make_phone_valid($phone)) == 0) {
                 $phone = $till_phone;
             }
             // echo ">>>$phone<br/>";
             if (validate_phone(make_phone_valid($phone)) == 1) {
                 ////-----Phone is validated, valid
                 if (input_length($mpesa_code, 5) == 1) {
                     $d = datefromdatetime2($completed_time);
                     $formatted_date = dateformatchange($d);
                     ////////////////////////////////////
                     // echo $formatted_date;
                     //$customer_det = fetchonerow('o_customers', "primary_mobile='" . make_phone_valid($account) . "' OR national_id='$account' OR primary_mobile='" . make_phone_valid($phone) . "'", "uid, branch");
                     $customer_id = $customer_ids[make_phone_valid($phone)];
                     if ($customer_id > 0) {
                         $branch_id = $customer_branches[$customer_id];
                         // $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND given_date < '$formatted_date' AND paid=0", "uid, current_co, current_agent");
                         $latest_loan_id = $latest_loan_ids[$customer_id];
                         $collector = $latest_collectors[$customer_id];

                     } else {
                         $latest_loan_id = 0;
                         $collector = 0;
                     }
                     $payment_method = 3;

                     $fds = array('customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'payment_date', 'recorded_date', 'record_method', 'added_by', 'collected_by', 'comments', 'status');
                     $vals = array("$customer_id", "$branch_id", "$payment_method", make_phone_valid($phone), "$amount", "$mpesa_code", "$latest_loan_id", "$formatted_date", "$fulldate", "UPLOAD ($details)", "$added_by", "$collector", "$details", "1");


                     $save = addtodb('o_incoming_payments', $fds, $vals);
                     if ($save == 1) {
                         $saved = $saved + 1;
                         //  echo "Save Payment for Loan $latest_loan_id: $save";
                         if ($latest_loan_id > 0) {
                             recalculate_loan($latest_loan_id);
                             $recalc = $recalc + 1;
                            // array_push($affected_loans, $latest_loan_id);

                            /////-------Handle overpayment splitting

                            if ($scr !== 0) {

                                $ld = fetchonerow("o_incoming_payments", "transaction_code = '$mpesa_code'", "uid");
                                $max_pid = $ld["uid"];
   
                                $balance = loan_balance($latest_loan_id);
                                // $balanceup = updatedb("o_incoming_payments", "loan_balance = '$balance'", "uid = $max_pid");

                                // availing all expected variables
                                $transaction_code = $mpesa_code;
                                $loan_id = $latest_loan_id;
                                $group_id = 0;
                                $payment_for = 1;
                                $mobile_number = make_phone_valid($phone);
                                $payment_date = $formatted_date;
                                $record_method = "UPLOAD ($details)";
                                $comments = "$details";
                                $status = 1;
                                
                                include_once("../../$scr");
                            }

                             ////-------End of Handling Overpayment splitting
                         }
                     } else {
                         // echo "Save Payment for Loan $latest_loan_id:$save";
                         $skipped = $skipped + 1;
                     }

                 }
             }
         }
     }

}

////////////////////-------------------
//$affected_loans_list = implode(',', $affected_loans);
//$aloans = fetchtable('o_loans',"uid in ($affected_loans_list)","uid","asc","10000","uid, loan_balance");
//while($al = mysqli_fetch_array($aloans)){
//    $alid = $al['uid'];
//    $lbal = $al['loan_balance'];
//    if($lbal > 0){
//        $max_pid = $payment_array[$alid];
//        $balanceup = updatedb("o_incoming_payments", "loan_balance = '$lbal'", "uid = $max_pid");
//    }
//}

///------------------

echo notice("Added: $saved, Skipped: $skipped, Recalc: $recalc");
unlink($upload_location.$upload);

?>