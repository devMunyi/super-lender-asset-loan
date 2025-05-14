<?php
session_start();
include_once ("../configs/20200902.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


    include_once("../php_functions/functions.php");


        $db = $db_;
        $_SESSION['db_name'] = $db;
        include_once("../configs/conn.inc");

        $last_ = fetchrow('o_last_service',"uid=1","last_date");
        $dt = new DateTime($last_);

        $last_date = $dt->format('Y-m-d');


        if($last_date == $date)
        {
           die("Scheduled messages already sent for $fulldate $db");
           exit();
        }
        else
        {
            echo "Ready to send messages<br/>";
        }



///////-------------------------End of get company details
        ///-------Lets start here
       $loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status !=0 AND product_id in (10,11)","uid","asc","100000","uid, customer_id, account_number, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, period, period_units, given_date, next_due_date, final_due_date, status");
       while($l = mysqli_fetch_array($loans)){
           $uid = $l['uid'];
           $customer_id = $l['customer_id'];
           $account_number = $l['account_number'];
           $product_id = $l['product_id'];
           $loan_amount = $l['loan_amount'];
           $disbursed_amount = $l['disbursed_amount'];
           $total_repayable_amount = $l['total_repayable_amount'];
           $total_repaid = $l['total_repaid'];
           $loan_balance = $l['loan_balance'];
           $period = $l['period'];
           $given_date = $l['given_date'];
           $next_due_date = $l['next_due_date'];
           $final_due_date = $l['final_due_date'];
           $status = $l['status'];
           $days_ago = datediff($given_date, $date);



         //  echo "loan $uid, Given $given_date, Balance=$loan_balance, Final Due $final_due_date, status=$status";
           if ($days_ago % 7 == 0) {
          echo "Day $status, $product_id <br/>";

      //     echo "REM ProductId:$product_id, Loan Day: $days_ago, Loan Status: $status; ";
           $message = fetchonerow('o_product_reminders',"product_id='$product_id' AND status=1 AND loan_status='$status'","uid, message_body");
           $message_uid = $message['uid'];
           if($message_uid > 0) {
               $message_body = $message['message_body'];
               $message_body_conv = convert_message($message_body, $uid);
               $q = queue_message($message_body_conv, $account_number);
               echo "Queue Message for loan ($uid) $q, $message_body_conv <br/>";

           }
           else{
               echo "No message <br/>";
           }


           } else {
               echo "Not the day <br/>";
           }
       }






