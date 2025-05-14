<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once '../configs/20200902.php';
include_once("../php_functions/functions.php");
include_once("../php_functions/digivas.php");
include_once("../php_functions/sms.php");
include_once("../configs/conn.inc");

require_once('../php_functions/AfricasTalkingGateway.php');


$sms_settings = array();

$sms_settings['property_value'] = '20880';
$sms_settings['short_code_username']  = 'Samawati';
$sms_settings['aft_2way_key'] = 'atsk_40bfa07e661b52572c6c2f97a5a663bfad64d2ec65b3995e5bed9faba896a15d8a8b545c';
$sms_settings['aft_2way_keyword'] = 'uwezo';



$from = $_POST['from'];
$to = $_POST['to'];
$text = trim($_POST['text']);
$date_ = $_POST['date'];
$id = $_POST['id'];
$linkId = $_POST['linkId']; //This works for onDemand subscription products

////----Log

$data  = file_get_contents('php://input');

$logFile = 'log-sms.txt';
$log = fopen($logFile, "a");
fwrite($log, $data . '' . date('Y-m-d H:i:s') . "\n");
fclose($log);


$original_string = strtolower($text);
$substring_to_remove = "uwezo";
$pos = strpos($original_string, $substring_to_remove);
if ($pos !== false) {
    $original_string = substr_replace($original_string, '', $pos, strlen($substring_to_remove));
}

$text = trim($original_string);
$number = make_phone_valid(ltrim($from, '+'));
if ((strlen($number)) == 12) {
    // save_log("Incoming SMS: $from ($number), $to, $text, $date");
    ///////----------check if customer exists
    $cust = fetchonerow('o_customers', "primary_mobile='$number'", "uid, full_name, national_id, loan_limit, status, primary_product, added_by, branch");
    $cid = intval($cust['uid']);
    $limit = $cust['loan_limit'];
    $added_by = $cust['added_by'];
    $branch = $cust['branch'];

    // check if branch is frozen
    $branch_det = fetchonerow('o_branches', "uid='$branch'", "freeze, name");
    $branch_freeze = $branch_det['freeze'];
    $branch_name = trim($branch_det['name']);
    if (in_array($branch_freeze, ['API', 'BOTH'])) {
        $message = "$branch_name branch text loans disabled temporarily!";
        feedback($message);
        die();
    }

    $real_agents = real_loan_agent($cid);

    $current_lo = $real_agents['LO'];
    $current_co = $real_agents['CO'];



    $latest_loan = fetchmax('o_loans', "customer_id=$cid AND disbursed=1 AND status!=0", "uid", "loan_amount");
    $limit = $latest_loan['loan_amount'];  ///////////////Overwrite limit with latest loan

    $full_name = $cust['full_name'];
    $name = explode(' ', $full_name);
    $first_name = $name[0];



    if ($cid > 0) {
        if ($cust['status'] != 1) {
            ////-------------Status is invalid
            $message = "Dear " . $first_name . ", your account is currently inactive.Please visit one of our branches";
            feedback($message);
            die();
        } else {
            //////---------Check if user has ever taken a Loan before
            $total_loans = totaltable('o_loans', "disbursed=1 AND paid=1 AND customer_id='$cid' AND status!=0", "uid");
            if ($total_loans <= 3) {
                $message = "Dear $first_name, You have not taken at least 3 loans to access this service, please visit one of our branches for assistance";
                feedback($message);
                die();
            }

            /////-----Check if customer is dormant
            $last_loan = fetchmax('o_loans', "disbursed=1  AND customer_id=$cid AND status != 0", "uid", "uid, final_due_date");
            $last_loan_due_date = $last_loan['final_due_date'];
            $ago = datediff($last_loan_due_date, $date);
            if ($ago > 30) {
                $message = "Dear $first_name, your account is dormant, please visit your nearest branch for assistance";
                feedback($message);
                die();
            }

            ////----Check is there is a loan taken last 20 minutes
            $ago_5_minutes = subtract_minutes_from_datetime($fulldate, 5);
            $duplicate = checkrowexists('o_loans', "added_date >= '$ago_5_minutes' AND customer_id=$cid");
            if ($duplicate == 1) {
                die("Ignored duplicate");
            }


            ////////---------Check if they have an active loan

            $pending_loan = fetchonerow('o_loans', "customer_id=$cid AND status in (1,2,3,4,7,8,9,10)", "uid, loan_amount");

            if ($pending_loan['uid'] > 0) {
                ///-----------------User has a Loan
                $message = "Dear $first_name, You have a pending loan of " . $pending_loan['loan_amount'] . " please wait while we review it";
                feedback($message);
                die();
            }


            $latest_loan = fetchonerow('o_loans', "disbursed='1' AND paid='0' AND customer_id=$cid AND status!=0", "uid, loan_balance");
            if (intval($latest_loan['uid']) > 0) {
                ///-----------------User has a Loan
                $message = "Dear $first_name, You have a Loan balance of " . $latest_loan['loan_balance'] . " Please pay now";

                feedback($message);
                die();
            } else {
                /////-----Total loans
                $total_loans = totaltable('o_loans', "disbursed=1 AND paid=1 AND customer_id='$cid' AND status!=0", "uid");



                if ($limit >= 50) {
                    if ($text >= 500 && $text <= $limit) {
                        ///---Amount entered, try to create a loan
                        $result = give_loan($cid, $cust['primary_product'], $text, 'TEXT');
                        if (intval($result) > 1) {
                            updatedb('o_loans', "current_lo='$current_lo', current_co='$current_co'", "uid='$result'");

                            // send_money($number, $text, $result);
                            queue_money($number, $cid, $text, $result,  0);
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
    } else {
        $message = "Welcome to Samawati Capital. Please visit our branches to get a business loan";
        feedback($message);
    }
} else {
    echo "Invalid number";
    save_log("Invalid Number $number:");
}

///_____________Process SMS

function feedback($message)
{
    global $number;
    global $linkId;
    global $sms_settings;
    send_sms_interactive_v2($number, $message, $linkId, $sms_settings);
    // queue_message($message,$number,0, 0,0, 2);
    // send_via_digivas($number,$message,$linkId);
}


function subtract_minutes_from_datetime($datetime, $minutes)
{
    $datetime_unix = strtotime($datetime);
    $new_datetime_unix = $datetime_unix - ($minutes * 60);
    $new_datetime = date('Y-m-d H:i:s', $new_datetime_unix);
    return $new_datetime;
}
