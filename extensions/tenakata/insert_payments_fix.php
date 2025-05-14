<?php

// files includes
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

// Read the JSON file content
$paymentsJson = file_get_contents('repayments5.json');


// Convert JSON data to a PHP array
$paymentsData = json_decode($paymentsJson, true);

$members = [];
$branches = [];


$customer_phones = array();
$customer_branches = array();
$customer_ids = array();

$customers = fetchtable('o_customers',"uid > 0","uid","asc","1000000","uid, primary_mobile, branch, national_id");
while($c = mysqli_fetch_array($customers)){
    $cid = $c['uid'];
    $primary_mob = $c['primary_mobile'];
    $branch = $c['branch'];
    $national_id = $c['national_id'];
    $customer_phones[$cid] = $primary_mob;
    $customer_branches[$cid] = $branch;
    $customer_ids[$national_id] = $cid;
}
$loan_customers = table_to_obj('o_loans',"uid > 0","10000000","uid","customer_id");

// set counters
$inserted = 0;
$skipped = 0;


// Check if "data" key exists and it is an array
if (isset($paymentsData['data']) && is_array($paymentsData['data'])) {
    // Iterate through the "data" array and print "member_no" and "branch"
    foreach ($paymentsData['data'] as $payData) {
        // Accessing the "member_no" and "branch" values
      $id  = $payData['id'];
      $full_name = $payData['full_name'];
      $amount = $payData['amount'];
      $transaction_reference = $payData['transaction_reference'];
      $transaction_timestamp = $payData['transaction_timestamp'];
        $trans = explode('T', $transaction_timestamp);
        $date = $trans[0];
        $trans_time = explode('+', $trans[1]);
        $time = $trans_time[0];
        $payment_date = "$date";
        $repayment_time = "$date $time";
      $bill_ref_number = $payData['bill_ref_number'];
      $loan_id = $payData['loan'];
      $direction = $payData['transaction_direction'];

      if($direction != 'in'){
          echo updatedb('o_incoming_payments',"status=0","transaction_code='$transaction_reference'");
         // echo $transaction_reference.',';
      }

      if($loan_id > 0){
          $customer_id = $loan_customers[$loan_id];
          $branch_id = $customer_branches[$customer_id];
          $mobile_number = $customer_phones[$customer_id];
      }
      else{
          $customer_id = $customer_ids[$bill_ref_number];
          $branch_id = $customer_branches[$customer_id];
          $mobile_number = $customer_phones[$customer_id];
      }

      //  $fds = array('customer_id','branch_id','payment_method','payment_category','mobile_number','amount','transaction_code','loan_id', 'payment_date','record_method','added_by','comments','status');
        $vals.=',("'.$customer_id.'","'.$branch_id.'","3","1","'.$mobile_number.'","'.$amount.'","'.$transaction_reference.'","'.$loan_id.'","'.$payment_date.'","'.$repayment_time.'","MANUAL","1","'.$full_name.'","1")<br/>';
      //  $create = addtodb('o_incoming_payments',$fds,$vals);


        
    }

   // echo $vals;
} else {
    echo "Error: Unable to read loan data." . PHP_EOL;
}



?>