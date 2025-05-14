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

include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$phone_number = make_phone_valid(str_replace("+", "", $_POST['phoneNumber']));
$session_id = $_POST['sessionId'];
$text_ = $_POST['text'];
$t = explode('*', $text_);
$text = end($t);
$last_choice = fetchrow('o_ussd_sessions', "session_id='$session_id'","last_choice");


if((validate_phone($phone_number)) == 0){
    die("END Phone number invalid");
}
if((input_length($session_id, 5)) == 0){
    die("END Session invalid");
}

$dec = "";

$session_det = fetchonerow('o_ussd_sessions',"session_id='$session_id' AND mobile_number='$phone_number'","uid, stage_");
$customer_det = fetchonerow('o_customers',"primary_mobile='$phone_number'","uid, full_name, pin_, loan_limit, primary_product, total_loans, national_id, date(added_date) as join_date, badge_id");
$customer_uid = $customer_det['uid'];

$first_loan = fetchmin('o_loans',"account_number='$phone_number'","uid","given_date, uid");
$first_loan_date = $first_loan['given_date'];

$full_name = $customer_det['full_name'];
$national_id = $customer_det['national_id'];
$badge_id = $customer_det['badge_id'];
$name_array = explode(' ', $full_name);
$first_name = $name_array[0];
$loan_limit = $customer_det['loan_limit'];
$total_loans = $customer_det['total_loans'];
$savings_total = totaltable('o_incoming_payments',"customer_id='$customer_uid' AND status=1 AND payment_category='4'","amount");

/////////-----------Savings balance
$customer_join_date = $customer_det['join_date'];
if($first_loan['uid'] > 0) {
    $ago_ = datediff3($first_loan_date, $date);
}
else{
    $ago_ = 7;
}

$total_weeks = countXInY(7, $ago_);

$required_savings = $total_weeks * 50;

$total_savings = $savings_total;

$balance_savings = $required_savings - $total_savings;
if($total_loans < 1){
    $balance_savings = 0;
}
$balance_savings = 0;
///-----------------End of savings balance


$latest_loan_balance = fetchrow('o_loans', "disbursed='1' AND paid='0' AND customer_id='" . $customer_uid . "' AND status!=0", "loan_balance");

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
        die("END Welcome to Jaza, Please visit one of our branches to get assistance or call 0740934171");
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
            $feedback = "Your new PIN is $new_pin, please dial *483*5292# to login again";
        }
        else{
            $feedback = "An error occurred, please retry";
        }
        record_trans("SYS", "$feedback", $session_id, 0);
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
                    $feedback = "You have a Loan balance of ".$latest_loan['loan_balance']." due on ".$latest_loan['final_due_date']." . Please replay with \n 1. To repay the loan \n 3. Reset PIN";
                    record_trans("SYS", "$feedback", $session_id, 1);
                    change_stage($session_id, 5);
                    die("CON $feedback");
                }
                else{
                    if($limit > 50) {
                        ////----Check if client is a defaulter
                        if($badge_id == 8 ||  $badge_id == 10){
                            ///---This is a defaulter
                            $feedback = "Please visit one of our branches to get assistance or call 0740934171";
                            record_trans("SYS", "$feedback", $session_id);
                            change_stage($session_id, 2);
                            die("END $feedback");
                        }
                        else {

                            $feedback = "You have a loan Limit of $loan_limit. Reply with \n 1. To apply for loan \n 3. Change PIN";
                            record_trans("SYS", "$feedback", $session_id);
                            change_stage($session_id, 2);
                            die("CON $feedback");
                        }


                    }
                    else{
                        $feedback = "you dont have a limit with us, please visit one of our branches or call 0740934171";
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
    if($text == 1 && $balance_savings < 1){
        ////-----User wants to apply
        change_stage($session_id, 3);
        $feedback = "Please enter amount upto $loan_limit.";
        record_trans("SYS", "$feedback", $session_id, 1);
        die ("CON $feedback");
    }
    /* elseif($text == 2 || ($text == 1 && $balance_savings > 0)){
         //// ----User wants to save
         change_stage($session_id, 3);
         $feedback = "How much do you want to save?";
         record_trans("SYS", "$feedback", $session_id, 2);
         die ("CON $feedback");
     } */
    elseif ($text == 3){
        ///---User wants to change PIN
        change_stage($session_id, 8);
        $feedback = "Please enter your new PIN";
        record_trans("SYS", "$feedback", $session_id);
        die ("CON $feedback");
    }
    else{

        $feedback = "Invalid Choice, you have a loan Limit of $loan_limit. Reply with \n 1. To apply for loan \n 3. Change PIN";
        record_trans("SYS", "$feedback", $session_id);
        die("CON $feedback");

    }

}
if($stage == 3){
    ///------Please check previous choice
    if($last_choice == 1) {
        if ($text > 50 && $text <= $loan_limit) {
            ////-----Check processing fee
            $upfront_balance = upfront_fees($text);
            if ($upfront_balance > 1) {
                $feedback = "Welcome to Jaza, please pay a processing fee of $upfront_balance before loan booking. then dial *483*5292#";
                //  $amount = upfront_fees($last_choice);
                send_stk($phone_number, $upfront_balance, $national_id);

                record_trans("SYS", "$feedback", $session_id, $upfront_balance);
                change_stage($session_id, 3);
                die("END $feedback");

            }
            else{
                $res = give_loan($customer_uid, $primary_product, $text, 'USSD');
                if ($res >= 1) {

                    ///-----Mark processing fees as loan payments

                    $update_ = updatedb('o_incoming_payments', "loan_id=$res", "mobile_number='$phone_number' AND loan_id=0 AND payment_category in (1,2,4) AND status=1 AND payment_date >= '2024-10-01'");

                    /// ---

                    /////---Process loans
                    ///-------Send money if its qualified
                    $total_loans = countotal('o_loans', "disbursed='1' AND paid='1' AND customer_id='" . $customer_det['uid'] . "' AND status!=0 AND uid != '$res'");   /////---Has cleared at least one loan since the new system
                    $days_3 = datesub($date, 0, 0, 3);
                    $recent_loan = checkrowexists("o_loans", "customer_id='" . $customer_det['uid'] . "'  AND given_date >= '$days_3' AND uid != '$res'");  ///Customer has not taken a loan last 3 days. Fraud prevention
                    if ($total_loans != 99999999999) {
                        ///----Customer qualifies for auto disbursement
                        ////----Update loan status to pending disbursement

                        $prod = fetchonerow('o_loan_products', "uid='$primary_product'", "automatic_disburse");
                        $auto_disburse = $prod['automatic_disburse'];

                        $reason = "Product settings do not allow automatic disbursement";

                        ///-----For loans greater than 50k, disable auto disburse
                        if($text >= 50000){
                            $auto_disburse = 0;
                            $reason = "Amount is greater than or equal 50,000";
                        }

                        ////-----For someone who has not taken more than 1 loan
                        if($total_loans > 2){ }
                        else{
                            $auto_disburse = 0;
                            $reason = "Client has not taken at least 2 loans";
                        }

                        recalculate_loan($res);


                        if ($auto_disburse == 1) {
                            $vals = array("$res", "$text", "$fulldate", '0', '1');
                            $update_ = updatedb('o_loans', "status=2, loan_stage=4", "uid='$res'");
                            $fds = array('loan_id', 'amount', 'added_date', 'trials', 'status');
                            $queue = addtodb('o_mpesa_queues', $fds, $vals);
                            store_event('o_loans', $res, "Queued for automatic processing");
                        } else {
                            ///----Push to final stage
                            $update_ = updatedb('o_loans', "loan_stage=4", "uid='$res'");
                            store_event('o_loans', $res, "Loan skipped to final stage automatically");
                            store_event('o_loans', $res, "Not sent automatically because ($reason)");
                        }
                    }

                    /////----End of loan process


                    change_stage($session_id, 4);
                    $feedback = "Your loan is being processed. Please wait a few minutes";
                    record_trans("SYS", "$feedback", $session_id);
                    // mpesa_addon(4, $res);


                    die ("END $feedback");
                } else {
                    $feedback = "$res";
                    record_trans("SYS", "$feedback", $session_id);
                    die ("CON $feedback");
                }
            }
        } else {
            $feedback = "Please enter a valid amount between 50 and $loan_limit.";
            record_trans("SYS", "$feedback", $session_id);
            change_stage($session_id, 4);
            die ("END $feedback");
        }
    }
    elseif ($last_choice == 2){
        ///-----Send STK Push to save
        // send_stk($phone_number, $text, "S$national_id");
        send_stk($phone_number, $text, 's'.$national_id);
        die ("END You will receive a prompt on your phone, please enter your M-Pesa PIN");
    }
    else{
        die ("END Last choice $last_choice");
    }

}
if($stage == 5){
    if($text == 1){
        ////----Repay Loan
        $latest_loan = fetchonerow('o_loans', "disbursed='1' AND paid='0' AND customer_id='" . $customer_det['uid'] . "' AND status!=0", "uid, loan_amount, loan_balance, final_due_date");

        // send_stk($phone_number, $latest_loan_balance, $phone_number);
        $feedback = "Your loan balance is ".$latest_loan['loan_balance'].". Please select an option below. Reply with \n  1. Repay full amount \n 2. Make partial payment";
        record_trans("SYS", "$feedback", $session_id);
        change_stage($session_id, 6);
        die ("CON $feedback");


    }
    else if($text == 2){
        /////-----Save
        change_stage($session_id, 3);
        $feedback = "How much do you want to save?";
        record_trans("SYS", "$feedback", $session_id, 2);
        die ("CON $feedback");
    }
    else if($text == 3){
        ////-----Reset PIN
        change_stage($session_id, 8);
        $feedback = "Please enter your new PIN";
        record_trans("SYS", "$feedback", $session_id);
        die ("CON $feedback");
    }
    else {
        $feedback = "Session ended, start a new session";
        record_trans("SYS", "$feedback", $session_id);
        die ("END $feedback");
    }
}

if($stage == 6){
    /////----Partial payment or full payment
    if($text == 1){
        ////----Full payment, prompt
        $latest_loan = fetchonerow('o_loans', "disbursed='1' AND paid='0' AND customer_id='" . $customer_det['uid'] . "' AND status!=0", "uid, loan_amount, loan_balance, final_due_date");

        send_stk($phone_number, $latest_loan['loan_balance'], $national_id);
        die ("END we have sent a prompt to your phone, please enter your M-Pesa PIN");
    }
    elseif ($text == 2){
        ///----Partial payment, choose amount
        $feedback = "How much do you want to pay?";
        change_stage($session_id, 7);
        record_trans("SYS", "$feedback", $session_id);
        die ("CON $feedback");
    }
    else{
        ///-----Client did not choose partial or full payment
        $latest_loan = fetchonerow('o_loans', "disbursed='1' AND paid='0' AND customer_id='" . $customer_det['uid'] . "' AND status!=0", "uid, loan_amount, loan_balance, final_due_date");

        // send_stk($phone_number, $latest_loan_balance, $phone_number);
        $feedback = "Invalid Choice! Your loan balance is ".$latest_loan['loan_balance'].". Please select an option below. Reply with \n  1. Repay full amount \n 2. Make partial payment";
        record_trans("SYS", "$feedback", $session_id);
        die ("CON $feedback");

    }



}
if($stage == 7){//////Partial payment amount
    if($text >= 5 ){
        ////----Client has entered a valid partial amount
        send_stk($phone_number, $text, $national_id);
        die ("END we have sent a prompt to your phone, please enter your M-Pesa PIN");
    }
    else{
        $feedback = "Invalid amount! How much do you want to pay?";
        record_trans("SYS", "$feedback", $session_id);
        die ("CON $feedback");
    }
}

if($stage == 8){
    ////----User is changing PIN
    if($text >= 1000 AND $text <= 9999) {
        change_stage($session_id, 9);
        $feedback = "Please confirm your PIN";
        //$new_pin = rand(1000, 9999);
        record_trans("SYS", "$feedback", $session_id, $text);
        die ("CON $feedback");
    }
    else{
        $feedback = "Invalid PIN! please enter a 4 digit PIN ";
        record_trans("SYS", "$feedback", $session_id);
        die ("CON $feedback");
    }


}
if($stage == 9){
    if($text >= 1000 AND $text <= 9999) {
        if($text == $last_choice){
            ///----PIN does match
            $upd = updatedb('o_customers',"pin_='".md5($text)."'","primary_mobile='$phone_number'");
            if($upd == 1) {
                $feedback = "Your new PIN has been set, please dial *789*600# to login again";
            }
            else{
                $feedback = "An error occurred, please retry";
            }
            record_trans("SYS", "$feedback", $session_id, 0);
            die ("END $feedback");
        }
        else{
            $feedback = "PIN does not match your previous choice, please re-enter new PIN";
            record_trans("SYS", "$feedback", $session_id);
            die ("CON $feedback");
        }
    }
    else{
        $feedback = "Invalid PIN! please enter a 4 digit PIN";
        record_trans("SYS", "$feedback", $session_id);
        die ("CON $feedback");
    }

}
if($stage == 10){
    $amount = upfront_fees($last_choice);
    send_stk($phone_number, $amount, $national_id);
    die ("END we have sent a prompt to your phone, please enter your M-Pesa PIN");
}



function change_stage($session_id, $stage){
    $upd = updatedb('o_ussd_sessions',"stage_='$stage'","session_id='$session_id'");
    return $upd;
}
function record_trans($user, $message, $session_id, $last_choice=1){
    global $fulldate;
    $dec ="[$user] at <i>$fulldate</i>: $message <br/>";
    $upd = updatedb('o_ussd_sessions',"info_supplied = CONCAT(info_supplied, '".$dec."'), last_choice='$last_choice'","session_id='$session_id'");
    //echo $upd;
}

function upfront_fees($amount_applied){
    global $customer_uid;
    global $primary_product;
    global $text;
    global $phone_number;
    ////-------Processing fees hardcoded
    // $loan_limit = fetchrow('o_customers',"primary_mobile='$phone_number'","loan_limit");

    $paid = totaltable('o_incoming_payments', "mobile_number='$phone_number' AND loan_id=0 AND payment_category in (1, 2,4) AND status=1 AND payment_date >= '2024-10-01'", "amount");



    $upfronts = fetchtable('o_addons', "paid_upfront=1", "uid", "asc", "10", "uid, amount, amount_type, applicable_loan");
    $total_upfront = 0;
    while ($up = mysqli_fetch_array($upfronts)) {
        $aid = $up['uid'];
        $product_addon = fetchrow('o_product_addons', "addon_id='$aid' AND status=1 AND product_id='$primary_product'", "uid");
        if ($product_addon > 0) {
            $upfront_addon = $up['uid'];
            $applicable_loan = $up['applicable_loan'];
            $amount = $up['amount'];
            $amount_type = $up['amount_type'];

            if ($amount_type == 'FIXED_VALUE') {
                $a_amount = $amount;
            } else {
                $a_amount = $amount_applied * ($amount / 100);
            }


            if ($applicable_loan == 0) {
                $total_upfront += $a_amount;
            }
        }
    }

    $upfront_balance = false_zero($total_upfront-$paid);
    return $upfront_balance;

}