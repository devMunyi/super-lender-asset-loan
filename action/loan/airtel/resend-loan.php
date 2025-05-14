<?php
session_start();
include_once '../../../configs/20200902.php';
include_once ("../../../php_functions/functions.php");
include_once ("../../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}
$resend_loan = permission($userd['uid'],'o_airtel_ug_queues',"0","RESEND");
if($resend_loan != 1){
    exit(errormes("You don't have permission to resend a loan"));
}

$loan_id = intval(decurl($_POST['loan_id']));

///////----------------Validation
if($loan_id > 0) {}
else{

    exit(errormes("Loan code needed"));
}

$loan_d = fetchonerow('o_loans',"uid=$loan_id");
$disbursed_amount = $loan_d['disbursed_amount'];
$msisdn = $loan_d['account_number'];
$proceed = 0;
$update_loan_stage = updatedb('o_loans',"disburse_state='NONE', status = 2","uid=".$loan_id);

if($update_loan_stage == 1){
    ///-----Queue Message
    $queued = fetchonerow('o_airtel_ug_queues',"loan_id=$loan_id","trials, status, uid");
    $q_status = $queued['status'];
    $q_trials = $queued['trials'];
    $q_uid = $queued['uid'];

    if($q_uid > 0){
        /////----Already there
        if($q_trials > 2){
            echo errormes("Error. Exhausted resend trials. Please review the loan details or consider recreating it.");
            exit();
        }
        else{
            if($q_status == 1){
                echo errormes("Error. Already queued");
                exit();
            }
            else{
                $update_ = updatedb('o_airtel_ug_queues',"status=1, feedbackcode='Requeued', requeued_date='$fulldate'","loan_id=$loan_id");
                if($update_ == 1){
                    echo sucmes("Success. Loan Requeued for Automatic Resend.");
                    $event = "Loan requeued by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
                    store_event('o_loans', $loan_id,"$event");
                    $proceed = 1;
                }
                else{
                    echo errormes("Error. Unable to resend");
                    exit();
                }
            }
        }
    }
    else{
        ////----Not queued, queue
        $fds = array('loan_id','amount','added_date','trials','status');
        $vals = array("$loan_id","$disbursed_amount","$fulldate",'1','1');
        $queue = addtodb('o_airtel_ug_queues', $fds, $vals);
        if($queue == 1){
            echo sucmes("Success. Loan Queued for Automatic Sending.");
            $event = "Loan requeued by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
            store_event('o_loans', $loan_id,"$event");
        }
        else{
            echo errormes("Error. Not resent");
            exit();
        }
    }

  //  echo sucmes(send_money($msisdn, $disbursed_amount, $loan_id));
}else{
    exit(errormes("Oops! An error occurred. Try again"));
}



///////------------End of validation
?>
<script>
    modal_hide();
    if('<?php echo $proceed; ?>'){
        setTimeout(function () {

                reload();

        },2000);
    }
</script>

