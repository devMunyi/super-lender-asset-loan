<?php
session_start();
include_once ("../configs/20200902.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


    include_once("../php_functions/functions.php");

$limit= $_GET['limit'];
if($limit < 1){
    die("Add limit parameter");
}
        $db = $db_;
        $_SESSION['db_name'] = $db;
        include_once("../configs/conn.inc");

     $unsent = fetchtable('o_loans',"disburse_state='FAILED' AND application_mode='TEXT' AND status=2 AND given_date ='$date'","uid","asc","$limit","uid, loan_amount");
     while($l = mysqli_fetch_array($unsent)){
         $lid = $l['uid'];
         $loan_amount = $l['loan_amount'];

         echo "$lid, $loan_amount<br/>";
         $update_loan_stage = updatedb('o_loans',"disburse_state='NONE'","uid=".$lid);
         if($update_loan_stage == 1){
             ///----Mark disburse state to prevent multiple queues
             $update_ = updatedb('o_mpesa_queues',"status=1, feedbackcode='Requeued'","loan_id='$lid' AND status!=1 AND trials < 2 AND amount='$loan_amount'");
             if($update_ == 1){
                 echo sucmes("Success. Resent");
                 $event = "Loan resent by Bulk service.";
                 store_event('o_loans', $lid,"$event");
                 $proceed = 1;
             }

         }
     }


include_once("../configs/close_connection.inc");