<?php
session_start();
include_once '../../configs/20200902.php';
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");
require(__DIR__ . '/../../vendor/autoload.php'); // must be imported for rmq.php to work
require("../../php_functions/rmqUtils.php");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$resend_loan = permission($userd['uid'],'o_mpesa_queues',"0","update_");
if($resend_loan != 1){
    die(errormes("You don't have permission to resend a loan"));
    exit();
}

$loan_id = decurl($_POST['loan_id']);

///////----------------Validation
if($loan_id > 0) {}
else{

    die(errormes("Loan code needed"));
    exit();
}

$loan_d = fetchonerow('o_loans',"uid='$loan_id'");
$disbursed_amount = $loan_d['disbursed_amount'];
$msisdn = $loan_d['account_number'];
$proceed = 0;
$update_loan_stage = updatedb('o_loans',"disburse_state='NONE', status=2","uid=".$loan_id);
// $update_incoming_payments_status = updatedb("o_incoming_payments", "status = 0", "loan_id=".decurl($loan_id));
if($update_loan_stage == 1){


    ///-----Queue Message
    $queued = fetchonerow('o_mpesa_queues',"loan_id='$loan_id'","trials, status, uid");
    $q_status = $queued['status'];
    $q_trials = $queued['trials'];
    $q_uid = $queued['uid'];

    if($q_uid > 0){
        /////----Already there
        if($q_trials > 2){
            echo errormes("Error. Already resent");
            die();
        }
        else{
            if($q_status == 1){
                echo errormes("Error. Already queued");
                die();
            }
            else{
                $update_ = updatedb('o_mpesa_queues',"status=1, feedbackcode='Requeued'","loan_id='$loan_id'");
                if($update_ == 1){
                    echo sucmes("Success. Resent");
                    $event = "Loan resent by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
                    store_event('o_loans', $loan_id,"$event");
                    $proceed = 1;
                }
                else{
                    echo errormes("Error. Unable to resend");
                    die();
                }
            }
        }
    }
    else{
        ////----Not queued, queue
        $fds = array('loan_id','amount','added_date','trials','status');
        $vals = array("$loan_id","$disbursed_amount","$fulldate",'1','1');
        $queue = addtodb('o_mpesa_queues', $fds, $vals);
        if($queue == 1){
            echo sucmes("Success. Money resent");
            $event = "Loan resent by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
            store_event('o_loans', $loan_id,"$event");
            $proceed = 1;
        }
        else{
            echo errormes("Error. Not resent");
            die();
        }
    }


    if(b2CRmqIsSet() && $proceed == 1){
        // set variables required by RMQ
        $queueName = QueueName::B2CDEFQ;
        $msgID = $loan_id;

        // include the RMQ file
        include_once("../../extensions/rmq.php");
    }
}else{
    die(errormes("Oops!.An error occurred. Try again"));
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

