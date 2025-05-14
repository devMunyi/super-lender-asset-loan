<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$permi = permission($userd['uid'], 'o_incoming_payments', "0", "create_");
if ($permi != 1) {
    die(errormes("You don't have permission to save payment"));
    exit();
}

$payment_method = $_POST['payment_method'];
$mobile_number = make_phone_valid($_POST['mobile_number']);
$amount = $_POST['amount'];
$transaction_code = trim($_POST['transaction_code']);
$payment_for = trim($_POST['payment_for']);
$loan_id = $_POST['loan_id'];
$payment_date = $_POST['payment_date'];
$record_method = $_POST['record_method'];
$comments = sanitizeAndEscape($_POST['comments'], $con);
$status = $_POST['status'];
$added_by = $userd['uid'];
$group_id = $_POST['group_id'];
$customer_id = 0;
$branch_id = 0;


////////////////////////

if ($payment_method == 4) {
    $transaction_code = "N/A";
} else {
    if (input_length($transaction_code, 3) == 1) {
        $exists = checkrowexists('o_incoming_payments', "transaction_code=\"$transaction_code\"");

        if ($exists == 1) {
            die(errormes("Transaction code exists"));
            exit();
        }
    } else {
        //////------Invalid user ID
        die(errormes("Please enter transaction code"));
        exit();
    }
}


if ($amount > 0) {
} else {
    die(errormes("Amount is required"));
    exit();
}


if ($loan_id > 0) {
    $exists = checkrowexists('o_loans', "uid = '$loan_id' AND status != 0");
    if ($exists == 0) {
        // die(errormes("The loan code doesn't exist"));
        // exit();
    } else {
        $customer_id = fetchrow('o_loans', "uid=$loan_id", "customer_id");
        if ($customer_id > 0) {
            $customer_det = fetchonerow("o_customers", "uid=$customer_id", "branch, primary_product");
            $branch_id = $customer_det['branch'] ?? 0;
            $primary_product = $customer_det['primary_product'] ?? 1;
        }
    }
} else {
    // die(errormes("Please enter loan code"));
    // exit();
}

if ((input_length($payment_date, 10)) == 0) {
    die(errormes("Payment date required"));
    exit();
}
if ($payment_method == 0) {
    die(errormes("Payment method required"));
    exit();
}


$fds = array('customer_id', 'branch_id', 'group_id', 'payment_method', 'payment_category', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'payment_date', 'record_method', 'added_by', 'comments', 'status');
$vals = array("$customer_id", "$branch_id", "$group_id", "$payment_method", "$payment_for", "$mobile_number", "$amount", "$transaction_code", "$loan_id", "$payment_date", "$record_method", "$added_by", "$comments", "$status");
$create = addtodb('o_incoming_payments', $fds, $vals);
echo "create $create";
if ($create == 1) {
    echo sucmes('Payment Recorded Successfully');
    if ($loan_id > 0) {
        recalculate_loan($loan_id, true);

        $ld = fetchmaxid("o_incoming_payments", "status > 0 AND loan_id = $loan_id", "uid");
        $max_pid = $ld["uid"];
        $balance = doubleval(loan_balance($loan_id));

        /////-------Check the after save script
        $primary_product = $primary_product ? $primary_product : 1;
        $scr = after_script($primary_product, "SPLIT_PAYMENT");

        if ($scr !== 0) {
            include_once("../../$scr");
        }else {
            updatedb("o_incoming_payments", "loan_balance = $balance", "uid = $max_pid");
        }

        ////-------End of check after save script

        $loan_paid = fetchrow('o_loans', "uid= $loan_id", "paid");
        if ($loan_paid == 1) {
            //////////////--------------Loan is cleared send cleared message


        }
    }

    $proceed = 1;
} else {
    echo errormes('Error Recording Payment');
}
?>

<script>
    if ('<?php echo $proceed; ?>') {
        setTimeout(function() {
            reload();
        }, 1500);
    }
</script>