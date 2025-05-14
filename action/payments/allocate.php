<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../php_functions/secondary-functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}
$user_id = $userd['uid'];
$week_ago12 = datesub($date, 0, 2, 0);
//---Check if agent has made more than 5 wrong allocations in the last 7 days
$wrong = countotal('o_events',"tbl='o_users' AND event_by='$user_id' AND date(event_date) >= '$week_ago12'  AND event_details LIKE '%Wrong blind allocation%'","uid");

if($wrong > 9){
    echo errormes("Too many failed blind allocations. Please contact the admin");

    //----Disable account
    $upd = updatedb('o_users',"status=3","uid='$user_id'");
    if($upd == 1){
        // clear_session
        $session_keydest = updatedb('o_tokens',"status=0, expiry_date='$fulldate'","userid='".$user_id."' AND status=1");

        $event = "Staff account disabled because of too many failed blind allocations";
        store_event('o_users', $user_id,"$event");
    }
    exit();
}



$amount = trim($_POST['amount']);
$transaction_code = trim($_POST['transaction_code']);
$mobile_number = make_phone_valid($_POST['mobile_number']);
$loan_id = ($_POST['loan_id']);
$payment_date = $_POST['payment_date'];
$branch_id = 0;
$collector = 0;

////////////////////////



if($amount > 0){}
else{
    die(errormes("Amount is required"));
    exit();
}
if(validate_phone($mobile_number) != 1){
    die(errormes("Mobile number invalid"));
    exit();
}
if(input_length($transaction_code, 10) != 1){
    die(errormes("Transaction code is required"));
    exit();
}
if(input_length($payment_date, 5) != 1){
    die(errormes("Transaction date is required"));
    exit();
}
if($loan_id > 0){}
else{
    die(errormes("Loan ID is required"));
    exit();
}



///----Check if transaction exists
$trans = fetchonerow('o_incoming_payments',"amount='$amount' AND transaction_code='$transaction_code' AND payment_date='$payment_date' AND status=1 AND loan_id=0","uid, payment_date, mobile_number");
if($trans['uid'] > 0){
    $pid = $trans['uid'];
    $mobile_no = $trans['mobile_number'];

    if(input_length($mobile_no, 5) == 0){
        die(errormes("The payment is missing a phone number"));
        exit();
    }

    if(phoneNumberMatch($mobile_no, $mobile_number) == 0){
        $reason = "Phone mismatch ($mobile_number, $mobile_no)";
        wrong_allocation($userd['uid']);
        die(errormes("You have not added all the details correctly"));
        exit();
    }

 $payment_date_f = $trans['payment_date'];
 if($payment_date_f != $payment_date){
     $reason = "Date mismatch ($payment_date, $payment_date_f)";
     wrong_allocation($userd['uid']);
     die(errormes("You have not added all the details correctly"));
     exit();
 }
 if(intval(datediff3($payment_date_f, $date)) > 7){
     $reason = "Payment too old ($payment_date)";
     wrong_allocation($userd['uid']);
     die(errormes("The payment is too old, please forward it to tech support"));
     exit();
 }

}
else{
    wrong_allocation($userd['uid']);
    $reason = "Transaction does not exist";
    wrong_allocation($userd['uid']);
    echo errormes("The transaction does not exist");
    exit();
}

///----Loan Details
$loan = fetchonerow('o_loans',"uid='$loan_id'","uid, account_number, current_agent, customer_id, current_branch");
if($loan['uid']> 0){
    $mobile_number = $loan['account_number'];
    $collector = intval($loan['current_agent']);
    $branch_id = $loan['current_branch'];
}
else{
    echo errormes("The loan ID does not exist");
    exit();
}


///----Customer ID from mobile number
$customer_det = fetchonerow('o_customers',"primary_mobile='$mobile_number'","uid, primary_product");

$customer_id = $customer_det["uid"] ?? 0;
$primary_product = $customer_det["primary_product"] ?? 1;




$update_flds = "customer_id= '$customer_id', branch_id = '$branch_id', collected_by='$collector',  mobile_number=\"$mobile_number\", loan_id = '$loan_id',   comments=\"Blind Allocation\"";
$update = updatedb('o_incoming_payments',$update_flds,"transaction_code = '$transaction_code'");
if ($update == 1) {
    echo sucmes('Payment Updated Successfully');

    if($loan_id > 0) {
        recalculate_loan($loan_id);


        // updatedb("o_loans", "loan_balance = $balance", "uid = $loan_id");
        $event = "Payment updated by [".$userd['name']."(".$userd['uid'].")] through a blind allocation. Details Customer_id:$customer_id, branch_id: $branch_id, mobile number: $mobile_number, Amount: $amount, transaction: $transaction_code, Loan id: $loan_id, payment_date: $payment_date";
        store_event('o_incoming_payments', $pid,"$event");

    }

    $proceed = 1;
} else {
    echo errormes('Error Updating Payment'.$update);
    exit();
}


function wrong_allocation($staff){
    global $amount;
    global $transaction_code;
    global $loan_id;
    global $payment_date;
    global $reason;
    global $pid;
    global $userd;

    $user_id = $userd['uid'];


    if($pid > 0){
        ////----Check wrong allocations for account
        $wrong = countotal('o_events',"tbl='o_incoming_payments' AND fld='$pid' AND event LIKE '%Wrong blind allocation (Amount%'","uid");
        if($wrong > 3) {


            $upd = updatedb('o_incoming_payments', "status=5", "uid='$pid'");
            if ($upd == 1) {
                $event = "Payment disabled because of a wrong blind allocation by [" . $userd['name'] . "(" . $userd['uid'] . ")]";
                store_event('o_incoming_payments', $pid, "$event");
            }
        }
    }

    $event = "Wrong blind allocation (Amount: $amount, Transaction_code: $transaction_code, Loan id: $loan_id, Payment_date: $payment_date) Reason: $reason";
    store_event('o_users', $staff,"$event");
}



?>

<script>
    if('<?php echo $proceed; ?>'){
        setTimeout(function () {
            reload();
        },1500);
    }
</script>
