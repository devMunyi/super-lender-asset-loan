<?php
session_start();
$company = $_GET['c'];
$data  = file_get_contents('php://input');

/*
$logFile = 'sms-log.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."-> $data ->".date('Y-m-d H:i:s')."\n");
fclose($log);
*/
$result = json_decode(trim($data), true);



////////-------------------------Get the company Details
if($company > 0) {

    include_once("../php_functions/functions.php");
    include_once("../configs/auth.inc");
    $company_d = company_details($company);
    if ($company_d['uid'] > 0) {



        $db = $company_d['db_name'];
        $_SESSION['db_name'] = $db;
        include_once("../configs/conn.inc");
///////-------------------------End of get company details
       $current_company = company_settings();
       $company_name = $current_company['name'];
        ////----------SMS is here
        $from = make_phone_valid(ltrim($_POST['from'], '+'));

        $to = $_POST['to'];
        $text = trim(ltrim(strtolower($_POST['text'])), 'tenova');

        $date_ = $_POST['date'];

        $linkId = $_POST['linkId'];

        $sfds = array('shortcode','keyword','sender_phone','message','transdate','direction','link_id','status');
        $svals = array("$to",'',"$from","$text","$fulldate","1","$linkId","1");
        addtodb('o_sms_interaction',$sfds, $svals);
        if(validate_phone($from)  == 1){
            ////////----------------Phone is valid
            $cust = fetchonerow('o_customers',"primary_mobile='$from'","uid, full_name, loan_limit, primary_product ,status, branch");
            $cust_id = $cust['uid'];
            $cust_branch = $cust['branch'];
            if($cust_id > 0) {
                ///----------Customer is found
                $full_name = $cust['full_name'];
                $loan_limit = $cust['loan_limit'];
                $status = $cust['status'];
                $primary_product = $cust['primary_product'];
                if($status != 1){
                    $feedback = "Dear $full_name, your account is inactive. Please visit any of our branches for assistance";
                }
                else{
                    //////-----Acoount is active, check if user has a Loan
                    $current_loan = fetchmaxid('o_loans',"customer_id='$cust_id' AND disbursed=1 AND paid=0","uid, loan_balance, final_due_date");
                    //var_dump($current_loan);
                    if($current_loan['uid'] > 0){
                        $feedback = "Dear $full_name, you have an outstanding loan balance of ".$current_loan['loan_balance']." due on ".$current_loan['final_due_date']."";
                    }
                    else {
                        ///----Checking existing pending loan
                        $pending_exist = checkrowexists('o_loans', "customer_id='$cust_id' AND disbursed=0 AND status in (1,2)");
                        if ($pending_exist == 1) {
                            $feedback = "You have a pending Loan request. Please wait while we process it";
                        }
                        else{
                            /////-----No Loan
                            if ($loan_limit >= 100) {
                                /////-------Check product details
                                $product = fetchonerow('o_loan_products', "uid='$primary_product'", "period, period_units, min_amount, max_amount, pay_frequency, percent_breakdown, status");
                                if ($product['status'] == 1) {
                                    $prod_period = $product['period'];
                                    $prod_period_units = $product['period_units'];
                                    $min_amount = $product['min_amount'];
                                    $max_amount = $product['max_amount'];
                                    $prod_pay_frequency = $product['pay_frequency'];
                                    $prod_percent_breakdown = $product['percent_breakdown'];
                                    ////--------Product available and valid
                                    $amount = (int)$text;

                                    if ($amount >= 100) {
                                        if ($amount <= $loan_limit && $amount <= $max_amount && $amount >= $min_amount) {

                                            $given_date = $date;         ////Initialization
                                            ////Calculated from product
                                            $final_due_date = final_due_date($given_date, $prod_period, $prod_period_units);         ////Calculated from product
                                            $transaction_date = $fulldate;         ////Initialization
                                            $added_date = $fulldate;
                                            $loan_stage_d = fetchminid('o_product_stages', "product_id='$primary_product' AND status=1 AND is_final_stage=1", "stage_order, uid");
                                            $loan_stage = $loan_stage_d['stage_id'];

                                            $total_instalments = total_instalments($prod_period, $prod_period_units, $prod_pay_frequency);         //////Calculated from product
                                            $total_instalments_paid = 0.00;  /////Initialization
                                            $current_instalment = 1;         ////Initialization

                                            $next_due_date = next_due_date($given_date, $prod_period, $prod_period_units, $prod_pay_frequency);
                                            ////------Create a Loan
                                            $fds = array('customer_id', 'account_number', 'product_id', 'loan_amount', 'disbursed_amount', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'given_date', 'next_due_date', 'final_due_date', 'added_by', 'current_branch', 'added_date', 'loan_stage', 'application_mode', 'status');
                                            $vals = array("$cust_id", "$from", "$primary_product", "$text", "$text", "$prod_period", "$prod_period_units", "$prod_pay_frequency", "$prod_pay_frequency", "$total_instalments", "$total_instalments_paid", "$current_instalment", "$given_date", "$next_due_date", "$final_due_date", "1", "$cust_branch", "$added_date", "$loan_stage", "SMS", "1");
                                            $create = addtodb('o_loans', $fds, $vals);
                                            if ($create == 1) {

                                                $feedback = "Your request for Kes. $text has been received. Please wait while we process it. ";
                                                ////----Send money
                                                $latest_loan = fetchmaxid('o_loans', "customer_id='$cust_id'", "uid");
                                                ////////-----------Add Automatic AddOns
                                                $addons = fetchtable('o_product_addons',"product_id='$primary_product'","addon_id","asc","20","addon_id");
                                                while($addon = mysqli_fetch_array($addons)){
                                                    $addon_id = $addon['addon_id'];
                                                    $automatic = fetchrow('o_addons',"uid='$addon_id' AND from_day = 0","automatic");
                                                    if($automatic == 1){
                                                        apply_loan_addon_to_Loan($addon_id, $latest_loan['uid'], false);
                                                    }

                                                }
                                               echo "Send Money Response".$latest_loan['uid'].send_money($from, $amount, $latest_loan['uid']);
                                            } else {
                                                $feedback = "An error occurred while applying for the loan. Please re-enter amount and try again. If the problem persists, contact us";
                                            }
                                        }
                                        else{
                                            $feedback = "Enter an amount between $min_amount and $max_amount and not greater than your limit $loan_limit";
                                        }


                                    } else {
                                        $feedback = "Dear $full_name, you have a Limit of Kes. $loan_limit with us. Please reply with KEYWORD and Amount. Product allows amounts between $min_amount - $max_amount.";
                                    }
                                } else {
                                    $feedback = "You dont have an active product set in your profile";
                                }
                            } else {
                                $feedback = "Dear $full_name, you dont have a limit with us. Please visit our offices for assistance";
                            }
                    }

                    }

                }
            }
            else{
                $feedback = "Welcome to Super Lender";
            }

        }
        else{
            echo "Invalid phone $from";
        }


    }
    if((input_length($feedback, 3)) == 1){
        ////----SEnd Feedback
        echo $feedback;
      echo send_sms_interactive($from, $feedback, $linkId);
        $sfds = array('shortcode','keyword','sender_phone','message','transdate','direction','link_id','status');
        $svals = array("$to",'',"$from","$feedback","$fulldate","2","$linkId","1");
        addtodb('o_sms_interaction',$sfds, $svals);
    }
}

include_once("../configs/close_connection.inc");