<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$phone_number = make_phone_valid(str_replace("+", "", $_POST['phoneNumber']));
$session_id = $_POST['sessionId'];
$text_ = $_POST['text'];
$t = explode('*', $text_);
$text = end($t);


if((validate_phone($phone_number)) == 0){
    die("END Phone number invalid");
}
if((input_length($session_id, 5)) == 0){
    die("END Session invalid");
}

$dec = "";

$session_det = fetchonerow('o_ussd_sessions',"session_id='$session_id' AND mobile_number='$phone_number'","uid, stage_");
$customer_det = fetchonerow('o_customers',"primary_mobile='$phone_number'","uid, full_name, pin_, loan_limit, primary_product");
$full_name = $customer_det['full_name'];
$name_array = explode(' ', $full_name);
$first_name = $name_array[0];
$loan_limit = $customer_det['loan_limit'];
$customer_uid = $customer_det['uid'];
$primary_product = $customer_det['primary_product'];
if($session_det['uid'] > 0){
    ///---Session exists
    $stage = $session_det['stage_'];
    record_trans($phone_number, $text, $session_id);
}
else{
    ///---No session, create one
    if($customer_det['uid'] > 0) {
        $dec ="[$phone_number] at <i>$fulldate</i>: $text  <br/>";

        $fds = array('session_id', 'started_date', 'stage_', 'mobile_number', 'info_supplied', 'status');
        $vals = array("$session_id", "$fulldate", "1", "$phone_number", "$dec", "1");
        $create = addtodb('o_ussd_sessions', $fds, $vals);
        if ($create == 1) {
            /// die("END Phone number invalid");
            $stage = 1;
        } else {
            die("END An internal error occurred");
        }
    }
    else{
        die("END Welcome to Stawika, Please come back later to be able to signup.");
    }
}

if ($stage == 1){
   /////////////------PIN is provided or 0
    if($text == '0'){
        ///------------------Reset PIN and send
        change_stage($session_id, 4);
        $new_pin = rand(1000, 9999);
        $upd = updatedb('o_customers',"pin_='".md5($new_pin)."'","primary_mobile='$phone_number'");
        if($upd == 1) {
            $feedback = "Your new PIN is $new_pin, please dial *872# again";
        }
        else{
            $feedback = "An error occurred, please retry";
        }
        record_trans("SYS", "$feedback", $session_id);
        change_stage($session_id, 4);
        die ("END $feedback");
    }
    elseif ((strlen($text)) == 4){
        ///----PIN Supplied
        if ((md5($text)) == $customer_det['pin_']){
            $stage_2 = change_stage($session_id, 2);
            if($stage_2 == 1) {
                ////----Proceed, to stage 3
                $latest_loan = fetchonerow('o_loans', "disbursed='1' AND paid='0' AND customer_id='" . $customer_det['uid'] . "' AND status!=0", "uid, loan_amount, loan_balance, final_due_date");

                $pending_loan = fetchonerow('o_loans', "disbursed='0' AND paid='0' AND customer_id='" . $customer_det['uid'] . "' AND status in (1,2)", "uid, loan_amount, loan_balance, final_due_date");

                if($pending_loan['uid'] > 0){
                    ///-----------------User has a Loan
                    $feedback = "You have a pending loan of ".$pending_loan['loan_amount']." please wait while we review it";
                    record_trans("SYS", "$feedback", $session_id);
                    change_stage($session_id, 4);
                    die("END $feedback");
                }

                ///----Has Limit
                $limit = $customer_det['loan_limit'];
                if($latest_loan['uid'] > 0){
                    ///-----------------User has a Loan
                    $feedback = "You have a Loan balance of ".$latest_loan['loan_balance']." due on ".$latest_loan['final_due_date']." Please pay now to paybill: 830685, account number [Your Phone]";
                    record_trans("SYS", "$feedback", $session_id);
                    change_stage($session_id, 4);
                    die("END $feedback");
                }
                else{
                    if($limit > 50) {
                        $feedback = "you have a loan Limit of $loan_limit. Reply with \n 1. To apply for loan \n 2. Contact Support";
                        record_trans("SYS", "$feedback", $session_id);
                        die("CON $feedback");
                    }
                    else{
                        $feedback = "you dont have a limit with us, please send your 6 months M-Pesa statement to statements@stawika.co.ke or contact 0207606564, 0207606556 or support@stawika.co.ke";
                        record_trans("SYS", "$feedback", $session_id);
                        change_stage($session_id, 4);
                        die("END $feedback");
                    }
                }
            }
            else{
                $feedback = "Internal error changing stage";
                record_trans("SYS", "$feedback", $session_id);
                change_stage($session_id, 4);
                die ("END An internal error occurred. Please retry");
            }

        }
        else{
            $feedback = "PIN incorrect, please enter your correct PIN  or reply with 0 to reset";
            record_trans("SYS", "$feedback", $session_id);
          //  change_stage($session_id, 4);
            die ("CON $feedback");
        }
    }
    else{
        $feedback = "Hi $first_name, Please enter your PIN to proceed. Reply with 0 to reset";
        record_trans("SYS", "$feedback", $session_id);
        die ("CON $feedback");
    }
}

if($stage == 2){
   if($text == 1){
       ////-----User wants to apply
       change_stage($session_id, 3);
       $feedback = "Please enter amount upto $loan_limit.";
       record_trans("SYS", "$feedback", $session_id);
       die ("CON $feedback");
   }
   elseif($text == 2){
       //// ----User wants to get help
       change_stage($session_id, 4);
       $feedback = "Call 020 760 6561 or email support@stawika.co.ke for assistance";
       record_trans("SYS", "$feedback", $session_id);
       die ("END $feedback");
   }
   else{

       $feedback = "Invalid Choice, you have a loan Limit of $loan_limit. Reply with \n 1. To apply for loan \n 2. Contact Support";
       record_trans("SYS", "$feedback", $session_id);
           die("CON $feedback");

   }

}
if($stage == 3){
    if($text > 50 && $text <= $loan_limit){
        ////-----Try to create loan
        $res = give_loan($customer_uid, $primary_product, $text, 'USSD');
        if($res >= 1) {
            change_stage($session_id, 4);
            $feedback = "Your loan is being processed. Please wait a few minutes";
            record_trans("SYS", "$feedback", $session_id);
            mpesa_addon(4, $res);

            ///-------Send money if its qualified
            $total_loans = countotal('o_loans',"disbursed='1' AND paid='1' AND customer_id='" . $customer_det['uid'] . "' AND status!=0 AND given_date >= '2022-04-01' AND uid != '$res'");   /////---Has cleared at least one loan since the new system
            $days_3 = datesub($date, 0,0, 3);
            $recent_loan = checkrowexists("o_loans", "customer_id='" . $customer_det['uid'] . "'  AND given_date >= '$days_3' AND uid != '$res'");  ///Customer has not taken a loan last 3 days. Fraud prevention
            if($total_loans >= 1 AND $recent_loan == 0){
                ///----Customer qualifies for auto disbursement
                ////----Update loan status to pending disbursement

                $prod = fetchonerow('o_loan_products',"uid='$primary_product'","automatic_disburse");
                $auto_disburse = $prod['automatic_disburse'];

                if(($primary_product == 9 || $primary_product == 1) && $text >= 50000){
                    $auto_disburse = 0;
                }


                if($auto_disburse == 1){
                    $vals = array("$res","$text","$fulldate",'0','1');
                    $update_ = updatedb('o_loans',"status=2","uid='$res'");
                    $fds = array('loan_id','amount','added_date','trials','status');
                    $queue = addtodb('o_mpesa_queues', $fds, $vals);
                    store_event('o_loans', $res, "Queued for automatic processing");
                }
                else{
                    store_event('o_loans', $res, "Not sent automatically because of product settings");
                }

            }
            else{
                store_event('o_loans', $res, "Not sent automatiacally because (total loans: $total_loans and recent loan: $recent_loan)");
            }


            ///------End of send money if its qualified


            die ("END $feedback");
        }
        else{
            $feedback = "$res";
            record_trans("SYS", "$feedback", $session_id);
            die ("CON $feedback");
        }
    }
    else{
        $feedback = "Please enter a valid amount between 50 and $loan_limit.";
        record_trans("SYS", "$feedback", $session_id);
        change_stage($session_id, 4);
        die ("END $feedback");
    }

}
if($stage == 4){
    $feedback = "Session ended, start a new session";
    record_trans("SYS", "$feedback", $session_id);
    die ("END $feedback");
}


function change_stage($session_id, $stage){
    $upd = updatedb('o_ussd_sessions',"stage_='$stage'","session_id='$session_id'");
    return $upd;
}
function record_trans($user, $message, $session_id){
    global $fulldate;
    $dec ="[$user] at <i>$fulldate</i>: $message <br/>";
    $upd = updatedb('o_ussd_sessions',"info_supplied = CONCAT(info_supplied, '".$dec."')","session_id='$session_id'");
    //echo $upd;
}