<?php
session_start();
include_once '../../configs/20200902.php';
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}
$resend_loan = permission($userd['uid'],'o_mpesa_queues',"0","update_");
if($resend_loan != 1){
    exit(errormes("You don't have permission to resend a loan"));
}

$loan_id = decurl($_POST['loan_id']);

///////----------------Validation
if($loan_id > 0) {}
else{

    exit(errormes("Loan code needed"));
}

$loan_d = fetchonerow('o_loans',"uid='$loan_id'");
$disbursed_amount = $loan_d['disbursed_amount'];
$msisdn = $loan_d['account_number'];
$customer_id = $loan_d['customer_id'];
$proceed = 0;

// check if customer has another loan and not this one
$another_loan = fetchmaxid('o_loans',"customer_id='$customer_id' and uid!='$loan_id' and status IN (1,2,3,4,7,8,9,10)", "uid, status");
if($another_loan["uid"] > 0){
    $loan_status_id = $another_loan["status"];
    $loan_status = fetchonerow('o_loan_status', "uid='$loan_status_id'", "name");
    exit(errormes("Customer has another loan with status: ".$loan_status['name'].". Please review it first."));
}

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
    
        }
        else{
            if($q_status == 1){
                echo errormes("Error. Already queued");
        
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
        }
        else{
            echo errormes("Error. Not resent");
    
        }
    }

  //  echo sucmes(send_money($msisdn, $disbursed_amount, $loan_id));
}else{
    exit(errormes("Oops!.An error occurred. Try again"));
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

