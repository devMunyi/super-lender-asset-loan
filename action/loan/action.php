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
$action = $_POST['action'];
$status = 1;


$action_name = fetchrow('o_loan_statuses', "uid='$action'", "name");
if($action == 6){
    $loan_action = permission($userd['uid'],'o_loans',"0","REJECT");

    if($loan_action == 1){ 
        // okay
     }else{
        exit(errormes("You don't have permission to change loan status to $action_name"));
    }

}else{
    $loan_action = permission($userd['uid'],'o_loans',"0","update_");

    if($loan_action == 1){ 
        // okay
     }else{
        exit(errormes("You don't have permission to change loan status to $action_name"));
    }
}

// // restrictive status permissions
// $loan_action = permission($userd['uid'],'o_loan_statuses',"0","update_");
// if($loan_action == 1){ 
//     // okay
//  }else{
//     exit(errormes("You don't have permission to change loan status to $action_name"));
// }


///////----------------Validation
if ($loan_id > 0) {
} else {
    exit(errormes("Loan code needed"));
}
$andflds = loan_status($action);
if ($action == 11 || $action == 6) {
    $disbursed = " AND disbursed=1";
} else {
    $disbursed = "";
}



$update_loan_stage = updatedb('o_loans', "status=\"$action\" $andflds $disbursed", "uid=" . decurl($loan_id));

// $update_incoming_payments_status = updatedb("o_incoming_payments", "status = 0", "loan_id=".decurl($loan_id));
if ($update_loan_stage == 1) {
    $proceed = 1;
    echo sucmes("Success");

    $andPaymentRemovedEvent = "";
    // deallcoate payment rejected loan
    if($action == 6 && $loan_id > 0){
        $original_loan = decurl($loan_id);
            updatedb("o_incoming_payments", "loan_balance = 0, loan_id = 0", "loan_id = $original_loan");
            updatedb("o_loans", "loan_balance = 0", "uid = $original_loan");
            $andPaymentRemovedEvent = "Payment deallocated from rejected loan($original_loan).";
    }

    function disburseState($action){
        $disburse_state = "";
        if(in_array($action, [9,10])){
            $disburse_state = "Written Off";
        }

        if($action == 11){
            $disburse_state = "Reversed";
        }

        if($action == 6){
            $disburse_state = "Rejected";
        }
        return $disburse_state;
    }

    // for written off. write off or reversed loan set disbursed = 0;
    if(in_array($action, [6, 9, 10, 11])){
        $disburse_state = disburseState($action);
        $loan_id_dec = decurl($loan_id);
        updatedb("o_loans", "disbursed = 0, disburse_state='$disburse_state'", "uid = $loan_id_dec");
    }

    

    $event = "Loan status changed to $action ($action_name) by [" . $userd['name'] . "(" . $userd['email'] . ")] on [$fulldate] with comment [<i>$comment</i>]".$andPaymentRemovedEvent;
    store_event('o_loans', decurl($loan_id), "$event");
    ////----------------Clear older Loans
    //   $cust_id = fetchrow('o_loans',"uid=".decurl($loan_id)."","customer_id");
    //  $latest_ = fetchmax('o_loans',"customer_id='$cust_id'","given_date","uid");
    // $latest_loan = $latest_['uid'];
    //  $clearall = updatedb('o_loans',"disbursed=1, paid=1, status=5","customer_id='$cust_id' AND uid!='$latest_loan' AND status!=0 AND disbursed=1 AND status!=5");
    ////--------------Clear Older Loans
    // echo sucmes("Clear all other: $clearall");
    $customer_id = fetchrow('o_loans', "uid=" . decurl($loan_id), "customer_id");
    total_customer_loans($customer_id);  //////update the total customer loans

    // if customer is written off or write off set status = 2
    if(in_array($action, [9, 10])){
        updatedb("o_customers", "status = 2", "uid = $customer_id");
    }

    if ($action == 0) {
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