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

        //$date = '2023-10-13';
        $last_ = fetchrow('o_last_service',"uid=1","last_date");
        $dt = new DateTime($last_);

        $last_date = $dt->format('Y-m-d');


        if($last_date == $date)
        {
         //  die("Scheduled messages already sent for $fulldate $db");
        //   exit();
        }
        else
        {
            echo "Ready to send messages<br/>";
        }

        $reminder_obj = array();
        $statuses = array();
        $loan_days = array();
        $products = array();

        $all_reminders = fetchtable('o_product_reminders',"status=1 AND loan_day > 0","uid","asc","1000","uid, message_body, product_id, loan_day, loan_status");
        while($ar = mysqli_fetch_array($all_reminders)){
            $uid = $ar['uid'];
            $message_body = $ar['message_body'];
            $product_id = $ar['product_id'];
            $loan_day = $ar['loan_day'];
             $loan_date = datesub($date, 0, 0, $loan_day);
            $loan_status = $ar['loan_status'];

            $reminder_obj = array();
            $reminder_obj['message_body'] = $message_body;

            array_push($statuses, $loan_status);
            array_push($loan_days, $loan_date);
            array_push($products, $product_id);

            $reminder_details[$product_id][$loan_day][$loan_status] = $message_body;
        }
      
      //  $vals = array('message_body','product_id','loan_day','loan_status');
      //  $all_reminders = table_to_obj2('o_product_reminders',"status=1",1000,"uid",$vals);

      // echo json_encode($reminder_details);


     //   die();
    $status_string = implode(',', $statuses);
    $days_string = "'".implode("','", $loan_days)."'";
    $products_string = implode(',', $products);

   // echo $days_string;

///////-------------------------End of get company details
        ///-------Lets start here
      $customers_list = table_to_array('o_loans',"disbursed=1 AND paid=0 AND status !=0 AND status in ($status_string) AND given_date in ($days_string) AND product_id in ($products_string)","10000","customer_id","uid","asc");

     // echo var_dump($customers_list).;
      $all_customers = array();
      $customer_details = fetchtable('o_customers',"uid in (".implode(',', $customers_list).")","uid","asc","10000","uid, full_name, primary_mobile, email_address, national_id, loan_limit, total_loans");
      while($cd = mysqli_fetch_array($customer_details)){
          $cid = $cd['uid'];
          $full_name = $cd['full_name'];
          $names = explode(' ', $full_name);
          $customer_array = array();
          foreach ($cd as $fieldName => $fieldValue) {
              $customer_array[$fieldName] = $fieldValue; // Assign field name as key and field value as value
              $customer_array['first_name'] = $names[0]; // Assign field name as key and field value as value
            //   echo "$fieldName:$fieldValue <br/>";

          }
          $all_customers[$cid] = $customer_array;
      }

      echo json_encode($all_customers);

     // die();

       $loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status !=0 AND status in ($status_string) AND given_date in ($days_string) AND product_id in ($products_string)","uid","desc","10000","uid, customer_id, account_number, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, period, period_units, given_date, next_due_date, final_due_date, status, current_instalment_amount");
       while($l = mysqli_fetch_array($loans, MYSQLI_ASSOC)){
           $uid = $l['uid'];
           $customer_id = $l['customer_id'];
           $account_number = $l['account_number'];
           $product_id = $l['product_id'];
//           $loan_amount = $l['loan_amount'];
//           $disbursed_amount = $l['disbursed_amount'];
//           $total_repayable_amount = $l['total_repayable_amount'];
//           $total_repaid = $l['total_repaid'];
//           $loan_balance = $l['loan_balance'];
//           $period = $l['period'];
           $given_date = $l['given_date'];
//           $next_due_date = $l['next_due_date'];
//           $final_due_date = $l['final_due_date'];
           $status = $l['status'];
           $days_ago = datediff($given_date, $date);
           $current_instalment_amount = $l['current_instalment_amount'];

           $message = $reminder_details[$product_id][$days_ago][$status];

    
           if(input_length($message, 10) == 1){
            $loan_array = array(); // Create an empty array for each row
          //  $customer_array = array();
            foreach ($l as $fieldName => $fieldValue) {
                $loan_array[$fieldName] = $fieldValue; // Assign field name as key and field value as value
               // echo "$fieldName:$fieldValue <br/>";
                }

           // var_dump($loan_array).'<br/>';
            $customer_obj = $all_customers[$customer_id];

               if(strpos($message, "current_instalment_amount") !== false){
                  if($current_instalment_amount < 4){
                      continue;
                  }
               }

//            echo '@@@'.$customer_obj.'@@@<br/>';
               if($loan_array['loan_balance'] > 0) {
                   $messaged_dec = convert_message_offline($message, $loan_array, $customer_obj);
               }
               else{
                   $messaged_dec = "";
               }
               $loan_array = array();
               $customer_obj = array();
          // echo json_encode($loan_array, true).'<br/>';
          // echo "UID: $uid, Date: $given_date, Ago: $days_ago, Message: $messaged_dec <br/>";
               if(input_length($messaged_dec,5) == 1) {
                   $q = queue_message($messaged_dec, $account_number);
                   echo $messaged_dec.$account_number.','.$customer_obj['primary_mobile']."<hr/>";
                   $total_q += $q;
                   $total += 1;
               }
           }

         //  echo "loan $uid, Given $given_date, Balance=$loan_balance, Final Due $final_due_date, status=$status";


      //     echo "REM ProductId:$product_id, Loan Day: $days_ago, Loan Status: $status; ";
        //   $message = fetchonerow('o_product_reminders',"(product_id='$product_id' OR product_id='0') AND loan_day='".abs($days_ago)."' AND (loan_status='$status' OR loan_status=-1) AND status=1 AND (custom_event is null OR custom_event = '' OR custom_event = ' ')","uid, message_body");
          // $message_uid = $message['uid'];
        //   if($message_uid > 0) {
             //  $message_body = $message['message_body'];
             //  $message_body_conv = convert_message($message_body, $uid);

              // echo "Queue Message for loan ($uid) $q, $messaged_dec <br/>";

       //    }
      // echo "<br/>";
       }


echo "Total $total_q/$total Sent";



