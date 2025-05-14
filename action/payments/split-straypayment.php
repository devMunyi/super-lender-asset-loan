<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}


$loan_id = $_POST['loan_id'];
$amount = $_POST['amount'];
$parent_payment = $_POST['parent_payment'];
$customer_idd = $_POST['customer_id'];
$payment_for = $_POST['payment_for'];
$recorded_date = $fulldate;
$collected_by = 0;


///////////--------------------Validation
if($amount > 0){}
else{
    die(errormes("Amount required"));
    exit();
}

if($loan_id > 0){}
else
{
    // die(errormes("Loan not selected"));
    // exit();
}

if($parent_payment > 0){}
else{
    die(errormes("Original payment not selected"));
    exit();
}

if($payment_for > 0){
}else{
    exit(errormes("Payment for not selected"));
}


$pp = fetchonerow('o_incoming_payments',"uid='$parent_payment'","*");
$group_id = 0;
$payment_method = $pp['payment_method'];
$transaction_code = $pp['transaction_code'];
$pay_amount = $pp['amount'];
$payment_date = $pp['payment_date'];
$record_method = "MANUAL";
$comments = "Payment Split";
$collected_by = $pp['collected_by'];
$loan_code = $pp['loan_code'];

$total_allocated = totaltable('o_incoming_payments',"split_from='$parent_payment' AND status=1","amount");
if($total_allocated + $amount > $pay_amount){
    die(errormes("The amount you are allocating is higher than total remaining"));
}

$split_from = $pp['uid'];
$added_by = $userd['uid'];
if($loan_id > 0) {
    $loan_det = fetchonerow('o_loans', "uid='$loan_id'", "uid, customer_id, current_branch, account_number, loan_balance");
    $customer_id = $loan_det['customer_id'];
    $branch_id = $loan_det['current_branch'];

    $mobile_number = $loan_det['account_number'];

    // ensure amount to be splitted is not more than loan balance
    $loan_balance = $loan_det['loan_balance'];

    if($amount > $loan_balance){
        exit(errormes("The amount you are allocating is higher than loan balance"));
    }

}
// else{
//     $cust = fetchonerow('o_customers',"uid='$customer_idd'","primary_mobile, branch");
//     $customer_id = $customer_idd;
//     $mobile_number = $cust['primary_mobile'];
//     $branch_id = $cust['branch'];
//     $loan_id = 0;
// }


$can_edit  = permission($userd['uid'],'o_incoming_payments',"0","update_");
if($can_edit == 1) {
   $status = 1;
}
else{
    exit(errormes("You do not have permission to split payment"));
    // $status = 5;
}

$random = generateRandomNumber(3);


$fds = array('customer_id','branch_id','group_id','split_from','payment_method','payment_category','mobile_number','amount','transaction_code','loan_id', 'loan_code', 'payment_date','record_method', 'recorded_date', 'added_by','collected_by','comments','status');
$vals = array("$customer_id","$branch_id", $group_id,"$split_from","$payment_method","$payment_for","$mobile_number","$amount","SP-$random-$transaction_code","$loan_id", "$loan_code","$payment_date","$record_method", "$recorded_date", "$added_by", $collected_by, "$comments","$status");

$create = addtodb('o_incoming_payments',$fds,$vals);
if ($create == 1) {
    echo sucmes('Payment Split Successfully');
    ////----Make the main payment not allocatable
    $update_l = updatedb('o_incoming_payments',"status=2","uid='$split_from'");

    // store event
    $event = "Payment split by [" . $userd['name'] . "(" . $userd['email'] . ")" . "(" . $userd['uid']. ")] on [$fulldate]";
    store_event('o_incoming_payments', $split_from, "$event");
    
    if($loan_id > 0) {
        recalculate_loan($loan_id);

        $ld = fetchmaxid("o_incoming_payments", "status > 0 AND loan_id = $loan_id", "uid");
        $max_pid = $ld["uid"];

        $balance = loan_balance($loan_id);
        updatedb("o_incoming_payments", "loan_balance = $balance", "uid = $max_pid");
        updatedb("o_loans", "loan_balance = $balance", "uid = $loan_id");
        $loan_paid = fetchrow('o_loans',"uid='$loan_id'","paid");
        if($loan_paid == 1){
            //////////////--------------Loan is cleared send cleared message


        }

    }

    $proceed = 1;
} else {
    echo errormes('Error Splitting Payment'.$create);
}



?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        sessionStorage.setItem("payment_splitted", "<?php echo $transaction_code; ?>");
        setTimeout(function () {
            modal_hide();
            gotourl("incoming-payments");
        },500);
    }
</script>
