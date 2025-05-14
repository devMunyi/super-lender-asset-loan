<?php
session_start();
$_SESSION['db_name'] = 'finabora_new_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");
require_once('../php_functions/AfricasTalkingGateway.php');



$from = $_POST['from'];
$to = $_POST['to'];
$text = trim($_POST['text']);
$date_ = $_POST['date'];
$id = $_POST['id'];
$linkId = $_POST['linkId']; //This works for onDemand subscription products

//die("Our SMS platform is being upgraded, please contact us directly for support");

//send_sms($from,"Reply to $text","23533","$linkId");
//

//$apply = new loan_application(254716330450, 2500, 123);
//$start = $apply->start_process();
//echo $start;
//die();


$number = make_phone_valid(ltrim($from, '+'));
if((strlen($number)) == 12) {
    save_log("Incoming SMS: $from ($number), $to, $text, $date");
    ///////----------check if customer exists
    $cust = fetchonerow('o_customers',"primary_mobile='$number'","uid, full_name, national_id, added_by, current_agent,loan_limit, status, primary_product");
    $loan_limit = $cust['loan_limit'];
    $full_name = $cust['full_name'];
    $added_by = $cust['added_by'];
    $current_agent = $cust['current_agent'];
    $name = explode(' ', $full_name);
    $first_name = $name[0];
    if($cust['uid'] > 0) {
        if ($cust['status'] != 1) {
            ////-------------Status is invalid
            $message = "Dear " . $first_name . ", your account is currently inactive.Please visit one of our branches";
            feedback($message);
            die();
        } else {
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
            $limit = $cust['loan_limit'];
            if($latest_loan['uid'] > 0){
                ///-----------------User has a Loan
                $message = "Dear $first_name, You have a Loan balance of ".$latest_loan['loan_balance']." due on ".$latest_loan['final_due_date']." Paybill 876472";

                feedback($message);
                die();
            }
            else{
                if($limit >= 50) {
                    if($text >= 50) {
                        ///---Amount entered, try to create a loan
//                        $deno = denomination_okey($cust['primary_product'], $text);
//                        if($deno[0] == 0){
//                            feedback("Please enter amount in multiples of ".$deno[1]);
//                            die();
//                        }

                        if ($text % 1000 === 0) {
                           // echo "$number is divisible by $divisor";
                        } else {
                            feedback("Please enter amount in multiples of 1000");
                            die();
                        }


                        $result = give_loan($cust['uid'], $cust['primary_product'], $text, 'TEXT' );
                        if($result > 1){
                            ///-----Add LO and CO
                            if($current_agent > 0){
                            $update_lo = updatedb('o_loans',"added_by='$current_agent', current_lo='$current_agent', current_co='$current_agent'","uid = '$result'");
                            }
                            
                            $message = "Your request has been submitted successfully, please wait while we review it";
                        }
                        else{
                            $message = $result;                        }



                    }
                    else{
                        $message = "Dear $first_name, you have a limit of $loan_limit.  Please enter an amount upto $loan_limit";
                    }

                }
                else{
                    $message = "You dont have a limit with us, please visit one of our branches to get a limit review";
                }
                feedback($message);
            }
        }
    }
    else{
        $message = "Welcome to Finabora Credit LTD. Please visit our branches to get a business loan";
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
    $feed = send_sms_interactive($number, $message, $linkId);
    echo "[$message]";

    $logFile = 'message.txt';
    $log = fopen($logFile,"a");
    fwrite($log, $message."Result($feed)".date('Y-m-d H:i:s')."\n");
    fclose($log);
}



?>