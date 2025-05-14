<?php
session_start();
include_once("../../configs/conn.inc");
if ($has_archive == 1) {
    include_once("../../configs/archive_conn.php");
}
include_once '../../configs/20200902.php';
include_once("../../php_functions/functions.php");


$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$loan_id = $_POST['loan_id'];
if ($loan_id > 0) {
    $loan_id = decurl($loan_id);
} else {
    exit(errormes("Loan code needed"));
}

$new_status = $_POST['status'];
$current_det = fetchonerow('o_loans', "uid=$loan_id", "status, loan_stage");
$current_status = $current_det['status'];
$loan_stage = $current_det['loan_stage'];


$loanStatusNames = table_to_obj('o_loan_statuses', "uid > 0", "1000", "uid", "name");
$new_status_name = $loanStatusNames[$new_status];

// if $new_status == $current_status
if ($new_status == $current_status) {
    exit(errormes("Loan is already in $new_status_name status"));
}

// $stage_perm = permission($userd['uid'], 'o_loan_stages', $loan_stage, "general_");
// if ($stage_perm != 1) {
//     exit(errormes("You don't have permission to change loan status to $new_status_name"));
// }

$general_perm = permission($userd['uid'], 'o_loan_statuses', "0", "general_");
if ($general_perm != 1) {
    if ($new_status == 6) {
        $loan_action = permission($userd['uid'], 'o_loans', "0", "REJECT");
        if ($loan_action != 1) {
            exit(errormes("You don't have permission to change loan status to " . $loanStatusNames[$new_status]));
        }
    } else if ($new_status == 10) {
        $loan_action = permission($userd['uid'], 'o_loans', "0", "WRITE_OFF");
        if ($loan_action != 1) {
            exit(errormes("You don't have permission to change loan status to " . $loanStatusNames[$new_status]));
        }
    } else if ($new_status == 0) {
        $loan_action = permission($userd['uid'], 'o_loans', "0", "delete_");
        if ($loan_action != 1) {
            exit(errormes("You don't have permission to delete loan"));
        }
    } else if ($new_status  == $current_status) {
        exit(errormes("Loan is already in $new_status_name status"));
    } else {
        $loan_action = permission($userd['uid'], 'o_loan_statuses', $current_status, "update_");
        if ($loan_action != 1) {
            exit(errormes("You don't have permission to change loan status to " . $loanStatusNames[$new_status]));
        }
    }
}




///////----------------Validation
$andflds = loan_status($new_status);
if ($new_status == 11 || $new_status == 6) {
    $disbursed = " AND disbursed=1";
} else {
    $disbursed = "";
}



$update_loan_stage = updatedb('o_loans', "status=\"$new_status\" $andflds $disbursed", "uid=$loan_id");

if ($update_loan_stage == 1) {
    $proceed = 1;
    echo sucmes("Success");

    $andPaymentRemovedEvent = "";
    // deallcoate payment rejected loan
    if ($new_status == 6 && $loan_id > 0 && $ignore_rejected_loan_deallocation != 1) {
        $original_loan = $loan_id;
        updatedb("o_incoming_payments", "loan_balance = 0, loan_id = 0", "loan_id = $original_loan");
        updatedb("o_loans", "loan_balance = 0", "uid = $original_loan");
        $andPaymentRemovedEvent = "Payment deallocated from rejected loan($original_loan).";
    }

    function disburseState($new_status)
    {
        $disburse_state = "";
        if (in_array($new_status, [9, 10])) {
            $disburse_state = "Written Off";
        }

        if ($new_status == 11) {
            $disburse_state = "Reversed";
        }

        if($new_status == 12){
            $disburse_state = "Failed";
        }

        if ($new_status == 6) {
            $disburse_state = "Rejected";
        }

        if(in_array($new_status, [3, 4, 5, 7, 8])){
            $disburse_state = "DELIVERED";
        }

        return $disburse_state;
    }

    // for written off. write off or reversed loan set disbursed = 0;
    $disburse_state = disburseState($new_status);
    if (in_array($new_status, [6, 9, 10, 11, 12])) {
        updatedb("o_loans", "disbursed = 0, disburse_state='$disburse_state'", "uid = $loan_id");
    }else if(in_array($new_status, [3, 4, 5, 7, 8])){
        updatedb("o_loans", "disbursed = 1, disburse_state='$disburse_state'", "uid = $loan_id");
    }

    $event = "Loan status changed to $new_status ($new_status_name) by [" . $userd['name'] . "(" . $userd['email'] . ")] on [$fulldate] with comment [<i>$comment</i>]" . $andPaymentRemovedEvent;
    store_event('o_loans', $loan_id, "$event");

    $customer_id = fetchrow('o_loans', "uid=$loan_id", "customer_id");
    total_customer_loans($customer_id);  //////update the total customer loans

    if (in_array($new_status, [9, 10])) {
        updatedb("o_customers", "status = 2", "uid = $customer_id");
    }

    if ($new_status == 0) {
        $delete = "1";
    }
} else {
    die(errormes("Oops!.An error occured. Try again"));
}




///////------------End of validation
?>
<script>
    modal_hide();
    if ('<?php echo $proceed; ?>') {
        setTimeout(function() {
            if ('<?php echo $delete ?>') {
                gotourl('loans');
            } else {
                reload();
            }
        }, 1000);
    }
</script>