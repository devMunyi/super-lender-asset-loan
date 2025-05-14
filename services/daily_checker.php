<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$company = $_GET['c'];

include_once("../configs/20200902.php");
$db = $db_;
//include_once("../configs/auth.inc");
{

   include_once("../php_functions/functions.php"); {

      $_SESSION['db_name'] = $db;
      include_once("../configs/conn.inc");
      $month_7_ago = datesub($date, 0, 7, 0);

      $last_ = fetchrow('o_last_service', "uid=1", "last_date");
      $dt = new DateTime($last_);

      $last_date = $dt->format('Y-m-d');


      if ($last_date == $date) {
         //  die("Scheduled messages already sent for $fulldate $db");
         //  exit();
      } else {
         echo "Ready to send messages<br/>";
      }


      // echo "Date: $date, Last Date: $last_date, Month 3 Ago: $month_7_ago<br/>";


      ///////-------------------------End of get company details
      ///-------Lets start here
      $loans = fetchtable('o_loans', "disbursed=1 AND paid=0 AND status !=0 AND given_date >= '$month_7_ago'", "uid", "asc", "100000", "uid, customer_id, account_number, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, period, period_units, given_date, next_due_date, final_due_date, status");
      while ($l = mysqli_fetch_array($loans)) {
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

         //   echo "loan $uid, Given $given_date, Balance=$loan_balance, Final Due $final_due_date, status=$status";

         /////--------If due date has passed, mark loan as overdue
         if (datediff($date, $final_due_date) < 0 && $loan_balance > 1) {
            if ($status != 7 && $status != 9) {
               ////--------Mark as overdue
               $over = updatedb('o_loans', "status=7", "uid='$uid' AND disbursed= 1 AND status=3");
               $event = "Loan marked as overdue by system";
               store_event('o_loans', $uid, "$event");
            }
            /////-> echo ", Date Diff ". datediff($date, $final_due_date);
         }
         ////--------Apply late interest

         /////-------Check daily Interest and Update next due date
         $daily =  mid_addons($uid);  // FUNCTIONALITY MOVED TO A DIFFERENT SERVICE TO REDUCE LOAD
         //  echo "Daily $daily, ";

         //////------Send reminders
         ///
         // echo "REM ProductId:$product_id, Loan Day: $days_ago, Loan Status: $status; ";
         $message = fetchonerow('o_product_reminders', "(product_id='$product_id' OR product_id='0') AND loan_day='" . abs($days_ago) . "' AND (loan_status='$status' OR loan_status=-1) AND status=1 AND (custom_event is null OR custom_event = '' OR custom_event = ' ')", "uid, message_body");
         $message_uid = $message['uid'];
         if ($message_uid > 0) {
            $message_body = $message['message_body'];
            $message_body_conv = convert_message($message_body, $uid);
            $q = queue_message($message_body_conv, $account_number);
            //   echo "Q $q,";
            /////-> echo "Queue Message for loan ($uid) $q, $message_body_conv <br/>";

         }
         //  echo "<br/>";
      }
   }
   $update = updatedb('o_last_service', "last_date='$fulldate'", "uid=1");
}

include_once("../configs/close_connection.inc");
