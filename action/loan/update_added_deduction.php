<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

/////-------Permission
$deduction_action = permission($userd['uid'], 'o_loan_deductions', "0", "update_");
if ($deduction_action != 1) {
    exit(errormes("You don't have permission to edit deduction value"));
}
/////--------


$deduction_id = $_POST['uid'];
$deduction_amount = $_POST['amount'];

if ($deduction_id > 0) {
    $loan_deduction_details = fetchonerow('o_loan_deductions', "uid=$deduction_id", "loan_id, deduction_id");
    $loan_id = $loan_deduction_details['loan_id'];
    $deduction = $loan_deduction_details['deduction_id'];
    $deduction_details = fetchonerow('o_deductions', "uid=$deduction", "name");
} else {
    exit(errormes("Invalid Deduction Id"));
}


// has archive and deduction is of archive overpayment
if ($has_archive == 1 && $overpayment_deduction_id == $deduction) {
    include_once("../../configs/archive_conn.php");
    include_once("../../services/util.php");

    $customer_id = fetchonerow('o_loans', "uid='$loan_id'", "customer_id")['customer_id'];
    $sql = "SELECT sum(loan_balance) as total_overpayment FROM o_loans WHERE customer_id = $customer_id AND loan_balance < 0 AND paid = 1 AND disbursed = 1";
    $result = mysqli_query($con2, $sql);

    $row = mysqli_fetch_assoc($result);
    $total_overpayment = abs($row['total_overpayment']);

    if ($deduction_amount != $total_overpayment) {
        exit(errormes("Deduction amount must be equal to $total_overpayment of overpayment"));
    }

    $sql = "SELECT uid, loan_balance FROM o_loans WHERE customer_id = $customer_id AND loan_balance < 0 AND paid = 1 AND disbursed = 1";

    $res = mysqli_query($con2, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $loan_id = $row['uid'];
        $loan_balance = abs($row['loan_balance']);
        $sql = "UPDATE o_incoming_payments SET transaction_code = CONCAT('AOC-', transaction_code), amount = (amount - $loan_balance), loan_balance = 0 WHERE loan_id = $loan_id AND amount >= $loan_balance ORDER BY uid DESC LIMIT 1";

        $result  = mysqli_query($con2, $sql);
        if ($result) {
            recalculate_loan_archive($loan_id, true);
        }
    }
}



$loan_id = fetchrow('o_loan_deductions', "uid='$deduction_id'", "loan_id");

$update = updatedb('o_loan_deductions', "deduction_amount=$deduction_amount, added_date='$fulldate'", "uid='$deduction_id'");
if ($update == 1) {
    echo sucmes("Amount updated");
    recalculate_loan($loan_id, true);
    $proceed = 1;
    store_event('o_loans', $loan_id, "Amount for deduction [" . $deduction_details['name'] . "($deduction)] updated to $deduction_amount by [" . $userd['name'] . "(" . $userd['email'] . ")]");
} else {
    echo errormes("Unable to update amount" . $update);
}


?>

<script>
    if ('<?php echo $proceed ?>') {
        modal_hide();
        loan_addons('<?php echo encurl($loan_id); ?>');

    }
</script>