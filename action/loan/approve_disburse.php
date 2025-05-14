<?php
session_start();
include_once("../../php_functions/functions.php");
include_once('../../php_functions/mtn_functions.php');
include_once("../../configs/conn.inc");
include_once(".../../php_functions/airtel-ug.php");

if ($has_archive == 1) {
    include_once("../../configs/archive_conn.php");
}

$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}


//die(errormes("Under maintenance"));

$loan_id = $_POST['loan_id'];
$comment = $_POST['comment'];

$status = 1;

///////----------------Validation
if ($loan_id > 0) {
} else {

    exit(errormes("Loan code needed"));
}

$loan_details = fetchonerow('o_loans', "uid='" . decurl($loan_id) . "'", "account_number, product_id, loan_amount, disbursed_amount, disbursed, loan_stage, given_date , final_due_date , next_due_date,customer_id, status");
$mobile_number = $loan_details['account_number'];
// $MSISDN_provider = getProviderByMSISDN($mobile_number);

$disbursed_amount = $loan_details['disbursed_amount'];
$disbursed = $loan_details['disbursed'];
$current_stage = $loan_details['loan_stage'];
$product_id = $loan_details['product_id'];
$customer_id = $loan_details['customer_id'];

$customer = fetchonerow("o_customers", "uid = $customer_id OR primary_mobile = '$mobile_number'", "phone_number_provider");
$mobile_phone_provider = intval($customer['phone_number_provider'] ?? 1);
$MSISDN_provider = getMSISDNProvider($mobile_phone_provider);

$given_date = $loan_details['given_date'];
$final_due_date = $loan_details['final_due_date'];
$next_due_date = $loan_details['next_due_date'];
$loan_stage = $loan_details['loan_stage'];
$days_ago = datediff3($date, $given_date);
$product_details = fetchonerow('o_loan_products', "uid='$product_id'", "name, disburse_method");
$disburse_method = $product_details['disburse_method'];    /////----The method of disbursing this loan


$lstatus = intval($loan_details['status']);
if ($lstatus != 1) {
    $status_name = fetchrow("o_loan_statuses", "uid=$lstatus", "name");
    exit(errormes("Loan is not approved. Current status is $status_name"));
}

$stage_action_permission  = permission($userd['uid'], 'o_loan_stages', "$loan_stage", "general_");
if ($stage_action_permission == 0) {
    exit(errormes("You don't have permission to disburse"));
}

$final_stage = fetchrow('o_product_stages', "product_id='$product_id' AND status=1 AND is_final_stage=1", "stage_id");

$configs_available = 1;
if($cc == 254){
    // this is for kenya
    $configs_available = checkrowexists('o_mpesa_configs', "uid = 5 AND status = 1"); ////For now, we just check if consumer secret is set, this is obviously very lazy coding
}


if ($disburse_method == 2) {
    $update_loan_stage = updatedb('o_loans', "status='2', loan_stage='$final_stage'", "uid=" . decurl($loan_id));
} else {
    $update_loan_stage = updatedb('o_loans', "status='3',disbursed=1, loan_stage='$final_stage'", "uid=" . decurl($loan_id));
    if ($update_loan_stage == 1) {
        echo sucmes("Loan marked as disbursed");
        $proceed = 1;
    } else {
        echo sucmes("Error marking loan");
    }
}


if ($update_loan_stage == 1 && $disburse_method == 2) {
    $proceed = 1;
    echo sucmes("Loan moved to next stage of disbursement");
    $event = "Loan moved to disbursement by [" . $userd['name'] . "(" . $userd['email'] . ")] on [$fulldate] with comment [<i>$comment</i>]";
    store_event('o_loans', decurl($loan_id), "$event");

    if ($configs_available == 1 && $disbursed != 1) {
        //////////---------All is set to send this money

        $provider = "DEFAULT";

        // for kenya disbursements default to mpesa
        if ($cc == 254) {
            queue_money($mobile_number, $customer_id, $disbursed_amount, decurl($loan_id),  $userd['uid']);
        } else {
            // handle case having disbursements with different phone providers e.g.UG
            switch ($MSISDN_provider) {
                case 'UG_MTN':
                    mtn_queue_loan($disbursed_amount, decurl($loan_id));
                    break;
                case 'UG_AIRTEL':
                    $dec_loan_id = decurl($loan_id);
                    updatedb('o_loans', "status=2", "uid=$dec_loan_id");
                    $fds = array('loan_id', 'amount', 'added_date', 'trials', 'status');
                    $vals = array($dec_loan_id, $disbursed_amount, "$fulldate", 0, 1);
                    $queue = addtodb('o_airtel_ug_queues', $fds, $vals);
                    break;
                default:
                    //store_event('o_loans', decurl($loan_id), "Phone number network provider could not be identified");
                    // queue_money($mobile_number, $customer_id, $disbursed_amount, decurl($loan_id),  $userd['uid']);
                    break;
            }
        }

        // store logs
        store_event('o_loans', decurl($loan_id), "Mobile Money Initiated via queue");
    }
}

///////------------End of validation



?>
<script>
    modal_hide();
    if ('<?php echo $proceed; ?>') {
        setTimeout(function() {
            reload();
        }, 2000);
    }
</script>