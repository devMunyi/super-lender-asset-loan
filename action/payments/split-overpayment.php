<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$new_loan_id = $_POST['new_loan_id'];
$init_loan_id = $_POST['init_loan_id'];
$new_loan_split_amt = $_POST['amount'];
$parent_payment = $_POST['parent_payment'];
$customer_idd = $_POST['customer_id'];
$payment_for = $_POST['payment_for'];
$recorded_date = $fulldate;
$collected_by = 0;


///////////--------------------Validation
if ($init_loan_id > 0) {
} else {
    exit(errormes("Current Loan ID not selected"));
}
if ($new_loan_split_amt > 0) {
} else {
    exit(errormes("Amount required"));
}
if ($parent_payment > 0) {
} else {
    exit(errormes("Original payment not selected"));
}

if($payment_for > 0){
}else{
    exit(errormes("Payment for not selected"));
}

$cur_bal_ = fetchmaxid('o_loans', "uid='$init_loan_id'", "loan_balance");
if(doubleval($cur_bal_['loan_balance']) < 0){
    // validate new loan split amount
    if($new_loan_split_amt > abs($cur_bal_['loan_balance'])){
        exit(errormes("Amount to be allocated to new loan is higher than the current loan overpayment"));
    }
}else {
    exit(errormes("Current Loan doesn't have an overpayment"));
}


$pp = fetchonerow('o_incoming_payments', "uid='$parent_payment'", "*");
$group_id = $pp['group_id'] ? $pp['group_id'] : 0;
$payment_method = $pp['payment_method'];
$transaction_code = $pp['transaction_code'];
$pay_amount = $pp['amount'];
$init_loan_split_amt = $pay_amount - $new_loan_split_amt;
$init_payment_date =  $pp['payment_date'];
$record_method = "MANUAL";
$comments = "Overpayment Split";
$collected_by = $pp['collected_by'];
$current_loan_id = $pp['loan_id'];
$loan_code = $pp['loan_code'];

$total_allocated = totaltable('o_incoming_payments', "split_from='$parent_payment' AND status=1", "amount");
if ($total_allocated + $new_loan_split_amt > $pay_amount) {
    exit(errormes("The amount you are allocating is higher than total remaining"));
}

$group_id = $pp['group_id'];
$split_from = $pp['uid'];
$added_by = $userd['uid'];
if ($init_loan_id > 0) {
    $loan_det = fetchonerow('o_loans', "uid='$init_loan_id'", "uid, customer_id, current_branch, account_number");
    $customer_id = $loan_det['customer_id'];
    $branch_id = $loan_det['current_branch'];
    $mobile_number = $loan_det['account_number'];
} else {
    $cust = fetchonerow('o_customers', "uid='$customer_idd'", "primary_mobile, branch");
    $customer_id = $customer_idd;
    $mobile_number = $cust['primary_mobile'];
    $branch_id = $cust['branch'];
    $init_loan_id = 0;
}


$can_edit  = permission($userd['uid'], 'o_incoming_payments', "0", "update_");
if ($can_edit == 1) {
    $status = 1;
} else {
    exit(errormes("You do not have permission to split payment"));
    // $status = 5;
}


// Begin handle of first split to be allocated back to the initial loan
$random = generateRandomNumber(3);

$fds = array('customer_id', 'branch_id', 'group_id', 'split_from', 'payment_method', 'payment_category', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'loan_code', 'payment_date', 'record_method', 'recorded_date', 'added_by', 'collected_by', 'comments', 'status');
$vals = array("$customer_id", "$branch_id", "$group_id", "$split_from", "$payment_method", 1, "$mobile_number", "$init_loan_split_amt", "SP-$random-$transaction_code", $init_loan_id, "$loan_code", "$init_payment_date", "$record_method", "$recorded_date", "$added_by", $collected_by, "$comments", $status);


$create_split1_payment = addtodb('o_incoming_payments', $fds, $vals);
if ($create_split1_payment == 1) {
    // echo sucmes('Payment Split Successfully');
    ////----Update parent payment
    $update_l = updatedb('o_incoming_payments', "status = 2, loan_id = 0", "uid='$split_from'");

    // store event
    $event = "Payment split by [" . $userd['name'] . "(" . $userd['email'] . ")" . "(" . $userd['uid']. ")] on [$fulldate]";
    store_event('o_incoming_payments', $split_from, "$event");

    if ($init_loan_id > 0) {
        recalculate_loan($init_loan_id);

        $ld = fetchmaxid("o_incoming_payments", "status > 0 AND loan_id = $init_loan_id", "uid");
        $max_pid = $ld["uid"];

        $balance = loan_balance($init_loan_id);
        updatedb("o_incoming_payments", "loan_balance = $balance", "uid = $max_pid");
        updatedb("o_loans", "loan_balance = $balance", "uid = $init_loan_id");
        $loan_paid = fetchrow('o_loans', "uid='$init_loan_id'", "paid");
        if ($loan_paid == 1) {
            //////////////--------------Loan is cleared send cleared message
        }
    }

    $proceed = 1;
} else {
    echo errormes('Error Splitting Payment' . $create_split1_payment);
}

///=== End handle of first split to be allocated back to the initial loan


// Begin handle of second split to be allocated to the new loan
$random2 = generateRandomNumber(3);

$fds = array('customer_id', 'branch_id', 'group_id', 'split_from', 'payment_method', 'payment_category', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'loan_code', 'payment_date', 'record_method', 'recorded_date', 'added_by', 'collected_by', 'comments', 'status');
$vals = array("$customer_id", "$branch_id", "$group_id", "$split_from", "$payment_method", "$payment_for", "$mobile_number", "$new_loan_split_amt", "SP-$random2-$transaction_code", $new_loan_id, "$loan_code", "$init_payment_date", "$record_method", "$recorded_date", "$added_by", $collected_by, "$comments", $status);


$create_split2_payment = addtodb('o_incoming_payments', $fds, $vals);
if ($create_split2_payment == 1) {
    echo sucmes('Payment Split Successfully');
    ////----Update parent payment
    // $update_l = updatedb('o_incoming_payments', "status = 2, loan_id = 0", "uid='$split_from'");

    if ($new_loan_id > 0) {
        recalculate_loan($new_loan_id);

        $ld = fetchmaxid("o_incoming_payments", "status > 0 AND loan_id = $new_loan_id", "uid");
        $max_pid = $ld["uid"];

        $balance = loan_balance($new_loan_id);
        updatedb("o_incoming_payments", "loan_balance = $balance", "uid = $max_pid");
        updatedb("o_loans", "loan_balance = $balance", "uid = $new_loan_id");
        $loan_paid = fetchrow('o_loans', "uid='$new_loan_id'", "paid");
        if ($loan_paid == 1) {
            //////////////--------------Loan is cleared send cleared message


        }
    }

    $proceed2 = 1;
} else {
    echo errormes('Error Splitting Payment' . $create_split2_payment);
}

///=== End handle of second split to be allocated to the new loan

?>

<script>
    if ('<?php echo $proceed; ?>' === "1" && '<?php echo $proceed2; ?>' === "1") {
        sessionStorage.setItem("payment_splitted", "<?php echo $transaction_code; ?>");
        setTimeout(function() {
            modal_hide();
            gotourl("incoming-payments");
        }, 500);
    }
</script>