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

$_SESSION['db_name'] = 'bodafund_db';
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
$customer_det = fetchonerow('o_customers',"primary_mobile='$phone_number'","uid, full_name, pin_, loan_limit, primary_product, national_id, date(added_date) as join_date");
$customer_uid = $customer_det['uid'];

$full_name = $customer_det['full_name'];
$national_id = $customer_det['national_id'];
$name_array = explode(' ', $full_name);
$first_name = $name_array[0];
$loan_limit = $customer_det['loan_limit'];
$savings_total = totaltable('o_incoming_payments',"customer_id='$customer_uid' AND status=1 AND payment_category='4'","amount");

/////////-----------Savings balance
$customer_join_date = $customer_det['join_date'];
$ago_ = datediff3($customer_join_date, $date);

$total_weeks = countXInY(7, $ago_);

$required_savings = $total_weeks * 50;

$total_savings = $savings_total;

$balance_savings = $required_savings - $total_savings;

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
        die("END Welcome to Bodafund, Please consult you group agent to signup.");
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
            $feedback = "Your new PIN is $new_pin, please dial *789*600# to login again";
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
                    $feedback = "You have a Loan balance of ".$latest_loan['loan_balance']." due on ".$latest_loan['final_due_date']." . Please replay with \n 1. To repay the loan \n 2. Save \n 3. Reset PIN";
                    record_trans("SYS", "$feedback", $session_id, 1);
                    change_stage($session_id, 5);
                    die("CON $feedback");
                }
                else{
                    if($limit > 50) {
                        ////----Check if registration fees are paid
                        $upfront_balance = upfront_fees();
                        if($upfront_balance > 1){
                            $feedback = "Welcome to Bodafund, please pay one time registration fee of Ksh.200 to access our services. Press \n 1 to continue";
                            record_trans("SYS", "$feedback", $session_id);
                            change_stage($session_id, 10);
                            die("CON $feedback");
                        }
                        else {

                            if($balance_savings > 0){
                                $feedback = "You have not made your weekly savings. Please save $balance_savings to continue. Reply with \n  2. Save  \n 3. Change PIN";
                                record_trans("SYS", "$feedback", $session_id);
                                change_stage($session_id, 2);
                                die("CON $feedback");
                            }
                            else {
                                $feedback = "You have a loan Limit of $loan_limit and total savings of $savings_total. Reply with \n 1. To apply for loan \n 2. Save more \n 3. Change PIN";
                                record_trans("SYS", "$feedback", $session_id);
                                change_stage($session_id, 2);
                                die("CON $feedback");
                            }
                        }
                    }
                    else{
                        $feedback = "you dont have a limit with us, please talk to your group agent";
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
   elseif($text == 2 || ($text == 1 && $balance_savings > 0)){
       //// ----User wants to save
       change_stage($session_id, 3);
       $feedback = "How much do you want to save?";
       record_trans("SYS", "$feedback", $session_id, 2);
       die ("CON $feedback");
   }
   elseif ($text == 3){
       ///---User wants to change PIN
       change_stage($session_id, 8);
       $feedback = "Please enter your new PIN";
       record_trans("SYS", "$feedback", $session_id);
       die ("CON $feedback");
   }
   else{

       $feedback = "Invalid Choice, you have a loan Limit of $loan_limit and savings amount $savings_total. Reply with \n 1. To apply for loan \n 2. Save more \n 3. Change PIN";
       record_trans("SYS", "$feedback", $session_id);
           die("CON $feedback");

   }

}
if($stage == 3){
    ///------Please check previous choice
   if($last_choice == 1) {
       if ($text > 50 && $text <= $loan_limit) {
           ////-----Try to create loan

           $res = give_loan($customer_uid, $primary_product, $text, 'USSD');
           if ($res >= 1) {
               $gr = fetchrow('o_group_members',"customer_id='$customer_uid' AND status=1","group_id");
               if($gr > 0){
                   $upd = updatedb('o_loans',"group_id='$gr'","uid='$res'");
               }
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
    $amount = upfront_fees();
    send_stk($phone_number, $amount, 'r'.$national_id);
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

function upfront_fees(){
    global $customer_uid;
    global $primary_product;
    global $text;
    global $phone_number;
    global $national_id;
    $total_loans_taken = countotal_withlimit('o_loans',"customer_id = $customer_uid AND disbursed = 1","uid","1000");
   //echo "[".$total_loans_taken."]";
    if($total_loans_taken == 0) {
        /// --------------Check all upfront fees
        $upfronts = fetchtable('o_addons', "paid_upfront=1", "uid", "asc", "10", "uid, amount, amount_type, applicable_loan");
        $total_upfront = 0;
        while ($up = mysqli_fetch_array($upfronts)) {
            $aid = $up['uid'];
            $product_addon = fetchrow('o_product_addons', "addon_id='$aid' AND status=1 AND product_id='$primary_product'", "uid");
            if ($product_addon > 0) {
                $upfront_addon = $up['uid'];
                $applicable_loan = $up['applicable_loan'];
                $amount_ = $up['amount'];
                $amount_type = $up['amount_type'];

                if ($amount_type == 'FIXED_VALUE') {
                    $a_amount = $amount_;
                } else {
                    $a_amount = $text * ($text / 100);
                }


                if ($applicable_loan == 0) {
                    $total_upfront += $a_amount;
                } else {
                    if ($total_loans_taken < $applicable_loan) {
                        $total_upfront += $a_amount;
                    }
                }
            }
        }
//die (errormes($total_loans_taken));
        $paid = totaltable('o_incoming_payments', "mobile_number='$phone_number' AND loan_id=0 AND payment_category in (2) AND status=1", "amount");
        return $total_upfront-$paid;

    }
    else{
        return 0;
    }
}