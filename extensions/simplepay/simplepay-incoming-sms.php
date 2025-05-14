<?php
session_start();
include_once '../configs/20200902.php';
include_once("../php_functions/functions.php");
include_once("../php_functions/sms.php");
include_once("../configs/conn.inc");
if ($has_archive == 1) {
    include_once("../configs/archive_conn.php");
}
require_once('../php_functions/AfricasTalkingGateway.php');
require(__DIR__ . '/../vendor/autoload.php'); // must be imported for rmq.php to work 
require("../php_functions/rmqUtils.php"); // must be imported for rmq.php to work


$from = ltrim($_POST['from'], "+254");
$to = $_POST['to'];
$text = trim($_POST['text']);
$date_ = $_POST['date'];
$id = $_POST['id'];
$linkId = $_POST['linkId']; //This works for onDemand subscription products
$week_ago = datesub($date, 0, 0, $autopicked_payment_days ? $autopicked_payment_days : 90);

////----Log
/*
$data  = file_get_contents('php://input');
$logFile = 'log.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.''.date('Y-m-d H:i:s')."\n");
fclose($log); 
*/


$number = make_phone_valid(ltrim($from, '+'));
// $data = "FROM =====> $from, number =====> $number";
// $logFile = 'payments.log';
// $log = fopen($logFile,"a");
// fwrite($log, $data.' '.date('Y-m-d H:i:s')."\n");
// fclose($log); 
if ((strlen($number)) == 12) {
    // save_log("Incoming SMS: $from ($number), $to, $text, $date");
    ///////----------check if customer exists
    $cust = fetchonerow('o_customers', "primary_mobile='$number'", "uid, full_name, national_id, loan_limit, status, primary_product, added_by, branch, primary_mobile");
    $cid = intval($cust['uid']);
    $limit = $cust['loan_limit'];
    $added_by = $cust['added_by'];
    $branch = $cust['branch'];
    $primary_mobile = $cust['primary_mobile'];
    $primary_product = $cust['primary_product'];
    $customer_id = $cid;

    // check if branch is frozen
    $branch_det = fetchonerow('o_branches', "uid=$branch", "freeze, name");
    $branch_freeze = $branch_det['freeze'];
    $branch_name = trim($branch_det['name']);

    // write to file the value of $branch_freeze
    if (in_array($branch_freeze, ['API', 'BOTH'])) {
        $message = "$branch_name branch text loans disabled temporarily!";
        feedback($message);
        exit();
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

            ///// === Case of previous loan disbursement failure
            $prev_loan = fetchmaxid('o_loans', "disbursed=0  AND customer_id=$cid AND status != 0", "status");

            if (intval($prev_loan['status']) == 12) {
                $message = "Dear " . $first_name . ", your previous loan application disbursement failed. Please contact us for assistance!";
                feedback($message);
                die();
            }

            //// === End case of previous loan disbursement failure

            $update_repayment = 0;
            $skip_auto_allocate = 0;


            /////====  Check for upfronts payments
            /////==== Hook for insurance fee check before loan creation
            $total_upfront = 0;
            $check_insurance_fee_src = after_script($primary_product, "CHECK_INSURANCE_FEE");
            if ($check_insurance_fee_src != '0') {
                //== avail required variable(s) before including the script
                $customerID = $customer_id;
                include_once "../$check_insurance_fee_src";


                $paid = totaltable('o_incoming_payments', "(mobile_number='$primary_mobile' OR customer_id = '$customer_id') AND loan_id=0 AND payment_category in (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'", "amount");
                $total_repaid = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$customer_id'", "total_repaid");
                $total_repayable = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$customer_id'", "total_repayable_amount");
                $overpayments = intval($total_repaid - $total_repayable);

                if (($paid + $overpayments) < $total_upfront) {

                    // $data = "($paid + $overpayments) < $total_upfront";
                    // $logFile = 'payments.log';
                    // $log = fopen($logFile, "a");
                    // fwrite($log, $data . ' ' . date('Y-m-d H:i:s') . "\n");
                    // fclose($log);

                    $balance = money($total_upfront - $paid);
                    $message = "An upfront of Ksh$balance needs to be paid to cover insurance fee";
                    feedback($message);
                    exit();
                } else {
                    $update_repayment = 1;
                }
            }


            ////===== End check of upfronts payments

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
                //updatedb('o_key_values',"value=value+1","key_='SMS'");
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
                if ($limit >= 50) {
                    if ($text >= 3000 && $text <= $limit) {
                        ///---Amount entered, try to create a loan, 
                        /// ==== I'm parsing false to avoid double loan recalculation
                        $result = give_loan($cid, $cust['primary_product'], $text, 'TEXT', false);
                        if (intval($result) > 1) {

                            if ($update_repayment == 1 && $skip_auto_allocate != 1) {
                                ///-------Update the upfront code with latest loan ID
                                $balance = loan_balance($result);
                                $updatePayQuery = "UPDATE o_incoming_payments SET loan_id='$result', loan_balance='$balance' WHERE (mobile_number='$primary_mobile' OR customer_id = $customer_id) AND loan_id=0 AND payment_category in (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'";

                                // $logFile = 'payments.log';
                                // $log = fopen($logFile, "a");
                                // fwrite($log, $updatePayQuery . ' ' . date('Y-m-d H:i:s') . "\n");
                                // fclose($log);


                                mysqli_query($con, $updatePayQuery);


                                // updatedb('o_incoming_payments', "loan_id = $result, loan_balance='$balance'", "(mobile_number='$primary_mobile' OR customer_id = $customer_id) AND loan_id=0 AND payment_category in (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'");
                            }

                            ////=== $charge_insurance_fee and $insurance_addon_id are availed by check insurance hook
                            if (!empty($charge_insurance_fee)) {
                                // add insurance fee to the loan
                                apply_loan_addon_to_Loan($insurance_addon_id, $loan_id, false);
                            } else {
                                recalculate_loan($result);
                            }


                            //== Hook Check if Loan Should be marked as Platinum
                            $check_platinum_loan_src = after_script($primary_product, "CHECK_PLATINUM_LOAN");
                            if ($check_platinum_loan_src != '0') {
                                //== avail required variable(s) before including the script
                                $customerID = $cid;
                                $loanID = $result;
                                $currentLoanAmount = $limit;
                                include_once "../$check_platinum_loan_src";
                            }

                            //== Hook Check if Customer Should be Unmarked as Dormant
                            $check_unset_dormant_src = after_script($primary_product, "DORMANT_REACTIVATION");
                            if ($check_unset_dormant_src != '0') {
                                //== avail required variable(s) before including the script
                                $customerID = $cid;
                                include_once "../../$check_unset_dormant_src";
                            }

                            updatedb('o_loans', "current_lo='$current_lo', current_co='$current_co'", "uid='$result'");

                            // send_money($number, $text, $result);
                            $queued = intval(queue_money($number, $cid, $text, $result,  0));
                            if ($queued == 1) {

                                store_event('o_loans', $result, "Mobile Money Initiated via queue");
                                product_notify($cust['primary_product'], 0, 'DISBURSEMENT', 3, $result, $number);
                                $message = "Your request has been submitted successfully, please wait while we review it";

                                // publish queue id as a message to RMQ
                                if (b2CRmqIsSet() && $number != '254112553167') {
                                    // set variables required by RMQ
                                    $queueName = QueueName::B2CDEFQ;
                                    $msgID = $result;

                                    // include the RMQ file
                                    include_once("../extensions/rmq.php");
                                }
                            }
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
        $message = "Welcome to Simplepay Capital. Please visit our branches to get a business loan";
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
    send_sms_interactive($number, $message, $linkId);
}


function subtract_minutes_from_datetime($datetime, $minutes)
{
    $datetime_unix = strtotime($datetime);
    $new_datetime_unix = $datetime_unix - ($minutes * 60);
    $new_datetime = date('Y-m-d H:i:s', $new_datetime_unix);
    return $new_datetime;
}
