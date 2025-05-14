<?php
session_start();
include_once '../configs/20200902.php';
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");
if($has_archive == 1) {
    include_once("../configs/archive_conn.php");
}
require_once('../php_functions/AfricasTalkingGateway.php');

//// 21045 - Newark Code

$from = ltrim($_POST['from'], '+254');
$to = $_POST['to'];
$text = trim($_POST['text']);
$date_ = $_POST['date'];
$id = $_POST['id'];
$week_ago = datesub($date, 0, 0, 90);
$linkId = $_POST['linkId']; //This works for onDemand subscription products

////----Log

$data  = file_get_contents('php://input');

$logFile = 'sms-in.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.''.date('Y-m-d H:i:s')."\n");
fclose($log);

//die("Our SMS platform is being upgraded, please contact us directly for support");

//send_sms($from,"Reply to $text","23533","$linkId");
//

//$apply = new loan_application(254716330450, 2500, 123);
//$start = $apply->start_process();
//echo $start;
//die();
//$excluded_branches = array(22, 41, 44, 10, 31, 8,23, 6,11,9, 27, 29, 38, 16);

$number = make_phone_valid(ltrim($from, '+'));
if($number != '254716330450'){
    // die();
}
if((strlen($number)) == 12) {
    save_log("Incoming SMS: $from ($number), $to, $text, $date");
    ///////----------check if customer exists
    $cust = fetchonerow('o_customers',"primary_mobile='$number'","uid, full_name, national_id, loan_limit, status, primary_product, added_by, branch, national_id, badge_id");
    $cid = $cust['uid'];
    $limit = $cust['loan_limit'];
    $added_by = $cust['added_by'];
    $branch = $cust['branch'];
    $national_id = $cust['national_id'];
    $badge_id = $cust['badge_id'];


    // check if branch is frozen
    $branch_det = fetchonerow('o_branches', "uid='$branch'", "freeze, name");
    $branch_freeze = $branch_det['freeze'];
    $branch_name = trim($branch_det['name']);
    if (in_array($branch_freeze, ['API', 'BOTH'])) {
        exit(); // exist if branch is frozen without feedback
        // $message = "$branch_name branch text loans disabled temporarily!";
        // exit(feedback($message));
    }

  



    /* $current_lo = 0;
     $current_co = 0;


     if($added_by > 0){
         $current_lo = $added_by;
         $pair = fetchrow('o_pairing',"lo='$current_lo'","co");
         if($pair > 0){
             $current_co = $pair;
         }
         else{
             $current_co = 0;
         }
     }
      */

    $real_agents = real_loan_agent($cid);

    $current_lo = $real_agents['LO'];
    $current_co = $real_agents['CO'];



    $latest_loan = fetchmax('o_loans',"customer_id='$cid' AND disbursed=1 AND status!=0","uid","loan_amount");
    $limit = $latest_loan['loan_amount'];  ///////////////Overwrite limit with latest loan

    $full_name = $cust['full_name'];
    $name = explode(' ', $full_name);
    $first_name = $name[0];


    if($cust['uid'] > 0) {
        if ($cust['status'] != 1) {
            ////-------------Status is invalid
            $message = "Dear " . $first_name . ", your account is currently inactive.Please visit one of our branches";
            feedback($message);
            die();
        } else {
            //////---------Check if user has ever taken a Loan before
            $has_loan = checkrowexists('o_loans',"disbursed=1 AND paid=1 AND customer_id='" . $cust['uid'] . "' AND status=5");
            if($has_loan == 0){
                // $message = "Dear $first_name, You have not taken a Loan with us before, please visit one of our branches for assistance";
                //  feedback($message);
                // die();
            }
            /*
              if(in_array($branch, $excluded_branches) == 1){
                  $message = "Dear " . $first_name . ", we can not offer you credit at this time. Please visit our nearest branch for assistance";
                  feedback($message);
                  die();
              }
            */

            /////-----Check if customer is dormant
            $last_loan = fetchmax('o_loans',"disbursed=1  AND customer_id='" . $cust['uid'] . "' AND status != 0","uid","uid, final_due_date");
            $last_loan_due_date = $last_loan['final_due_date'];
            $ago = datediff($last_loan_due_date, $date);
            if($ago > 30){
                $message = "Dear $first_name, your account is dormant, please visit your nearest branch for assistance $last_loan_due_date";
                feedback($message);
                die();
            }
//            feedback("$ago ($last_loan_due_date) ");
//            die();
//
//            die();

            ////----Check is there is a loan taken last 20 minutes

            $ago_5_minutes = subtract_minutes_from_datetime($fulldate, 5);
            $duplicate = checkrowexists('o_loans',"added_date >= '$ago_5_minutes' AND customer_id='" . $cust['uid'] . "'");
            if($duplicate == 1){
                //updatedb('o_key_values',"value=value+1","key_='SMS'");
                die("Ignored duplicate");
            }


            ////////---------Check if they have an active loan
            $latest_loan = fetchonerow('o_loans', "disbursed='1' AND paid='0' AND customer_id='" . $cust['uid'] . "' AND status!=0", "uid, loan_amount, loan_balance, final_due_date");
            $pending_loan = fetchonerow('o_loans', "disbursed='0' AND paid='0' AND customer_id='" . $cust['uid'] . "' AND status in (1,2)", "uid, loan_amount, loan_balance, final_due_date");

            if($pending_loan['uid'] > 0){
                ///-----------------User has a Loan
                $message = "Dear $first_name, You have a pending loan of ".$pending_loan['loan_amount']." please wait while we review it";
                feedback($message);
                die();
            }


            ///----Has Limit
            // $limit = $cust['loan_limit'];
            if($latest_loan['uid'] > 0){
                ///-----------------User has a Loan
                $message = "Dear $first_name, You have a Loan balance of ".$latest_loan['loan_balance']." Please pay to TILL NO:640134. DO NOT PAY CASH";

                feedback($message);
                die();
            }
            else {

                if ($badge_id == 8 || $badge_id == 10 || $badge_id == 11) {
                    ///---This is a defaulter
                    $message = "Please visit one of our branches to get assistance.";
                    feedback($message);
                    // change_stage($session_id, 2);
                    die();
                }
              else{

                if ($limit >= 50) {
                    if ($text >= 500 && $text <= $limit) {
                        $product_id = $cust['primary_product'];
                        $loan_amount = $text;
                        $total_loans_taken = countotal_withlimit('o_loans', "customer_id = $cid AND disbursed = 1", "uid", "1000");
                        $upfronts = fetchtable('o_addons', "paid_upfront=1", "uid", "asc", "10", "uid, amount, amount_type, applicable_loan");
                        $total_upfront = 0;
                        while ($up = mysqli_fetch_array($upfronts)) {
                            $aid = $up['uid'];
                            $product_addon = fetchrow('o_product_addons', "addon_id='$aid' AND status=1 AND product_id='$product_id'", "uid");
                            if ($product_addon > 0) {
                                $upfront_addon = $up['uid'];
                                $applicable_loan = $up['applicable_loan'];
                                $amount = $up['amount'];
                                $amount_type = $up['amount_type'];

                                if ($amount_type == 'FIXED_VALUE') {
                                    $a_amount = $amount;
                                } else {
                                    $a_amount = $loan_amount * ($amount / 100);
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


                        $paid = totaltable('o_incoming_payments', "(mobile_number='$number' OR customer_id = $cid) AND loan_id=0 AND payment_category in (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'", "amount");
                        $total_repaid = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$cid'", "total_repaid");


                        if ($paid < $total_upfront) {
                            $balance = $total_upfront - $paid;
//                            die(errormes("An upfront fee of $balance needs to be paid"));
                            feedback("Please pay an upfront fee of $balance and enter the same amount again");
                            die();
                        } else {
                            $update_repayment = 1;
                        }


                        ///---Amount entered, try to create a loan
                        $result = give_loan($cust['uid'], $cust['primary_product'], $text, 'TEXT');
                        if (intval($result) > 1) {
                            updatedb('o_loans', "current_lo='$current_lo', current_co='$current_co'", "uid='$result'");

                            if ($update_repayment == 1) {
                                ///-------Update the upfront code with latest loan ID
                                $balance = loan_balance($result);
                                $updaterep = updatedb('o_incoming_payments', "loan_id = $result, loan_balance = $balance", "(mobile_number='$number' OR customer_id = $cid) AND loan_id=0 AND payment_category in (0, 1, 2, 4) AND status=1");
                                // echo $updaterep.'Update repayment';
                            }
                            recalculate_loan($result);

                            // send_money($number, $text, $result);
                            queue_money($number, $cust['uid'], $text, $result, 0);
                            store_event('o_loans', $result, "Mobile Money Initiated via queue");

                            product_notify($cust['primary_product'], 0, 'DISBURSEMENT', 3, $result, $number);
                            $message = "Your request has been submitted successfully, please wait while we review it";


                        } else {
                            $message = $result;
                        }


                    } else {
                        $message = "Dear $first_name, you have a limit of $limit.  Please enter an amount upto $limit. Minimum amount you can borrow is 3000";
                    }

                } else {
                    $message = "You dont have a limit with us, please visit one of our branches to get a limit review";
                }
                feedback($message);
              }
            }
        }
    }
    else{
        $message = "Welcome to Newark Frontiers. Please visit our branches to get a business loan";
        feedback($message);
    }

    // echo $message;
    //  send_sms($number, $message, 23303, $linkId);
    // send_sms_interactive($number, $message, $linkId);

////_____________Process SMS

//
//    $apply = new loan_application($number, $text, $linkId);
//    $start = $apply->start_process();

}
else{
    echo "Invalid number";
    save_log("Invalid Number $number:");
}

///_____________Process SMS

function feedback($message){
    global $number;
    global $linkId;
    $feed = send_sms_interactive2($number, $message, $linkId);
    echo "[$message]";

    /* $logFile = 'message.txt';
     $log = fopen($logFile,"a");
     fwrite($log, $message."Result($feed)".date('Y-m-d H:i:s')."\n");
     fclose($log); */
}


function subtract_minutes_from_datetime($datetime, $minutes) {
    $datetime_unix = strtotime($datetime);
    $new_datetime_unix = $datetime_unix - ($minutes * 60);
    $new_datetime = date('Y-m-d H:i:s', $new_datetime_unix);
    return $new_datetime;
}


?>