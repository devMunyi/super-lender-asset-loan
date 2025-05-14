<?php
session_start();

include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$from_ = $_GET['from'];
$to_ = $_GET['to'];
$product = $_GET['product_id'];

//////----------------------Adds
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Reports</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>



    <div class="container">

<?php
$p_array = array();
$payments = fetchtable('o_incoming_payments',"payment_date BETWEEN '$from_' AND '$to_' AND status =1","uid","asc","10000","uid, mobile_number, amount, loan_id, payment_date");
while($p = mysqli_fetch_array($payments)){
    $uid = $p['uid'];
    $mobile_number = $p['mobile_number'];
    $amount = $p['amount'];
    $loan_id = $p['loan_id'];
    $payment_date = $p['payment_date'];

    array_push($p_array, $loan_id);

}

$p_array_ring = implode(',', $p_array);


echo "<table class='table table-striped'>";
$loan_statuses = table_to_obj('o_loan_statuses',"uid>0","100","uid","name");

$loans_array = array();
$loan_obj = array();
$all_loans_obj = array();
$loans = fetchtable('o_loans',"uid in ($p_array_ring)","uid","asc","100000","uid, customer_id, given_date, final_due_date, account_number, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, total_addons, transaction_code, status");
while($l = mysqli_fetch_array($loans)){

    $uid = $l['uid'];
    $customer_id = $l['customer_id'];
    $account_number = $l['account_number'];
    $product_id = $l['product_id'];
    $loan_amount = $l['loan_amount'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $disbursed_amount = $l['disbursed_amount'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = $l['loan_balance'];
    $total_addons = $l['total_addons'];
    $transaction_code = $l['transaction_code'];
    $status = $l['status'];
     array_push($loans_array, $uid);


     $loan_obj['uid'] = $uid;
     $loan_obj['customer_id'] = $customer_id;
     $loan_obj['account_number'] = $account_number;
     $loan_obj['given_date'] = $given_date;
     $loan_obj['final_due_date'] = $final_due_date;
     $loan_obj['loan_amount'] = $loan_amount;
     $loan_obj['loan_balance'] = $loan_balance;
     $loan_obj['total_repaid'] = $total_repaid;
     $loan_obj['status'] = $status;

     $all_loans_obj[$uid] = $loan_obj;



    //echo "<tr><td>$uid</td><td>$customer_id</td></tr>";

}
$loan_string = implode(',', $loans_array);

$addon_b = array();
$addons = fetchtable('o_loan_addons',"loan_id in ($loan_string) AND status=1","uid","asc","10000000","loan_id, addon_id, addon_amount");
   while($a = mysqli_fetch_array($addons)){

       $loan_id = $a['loan_id'];
       $addon_id = $a['addon_id'];
       $addon_amount = $a['addon_amount'];
      $addon_b[$loan_id][$addon_id] = $addon_amount;
   }

echo "<table class='table table-condensed' id='example2'>";
echo "<thead><tr><th>Loan</th><th>Phone</th><th>Given Date</th><th>Due Date</th>  <th>Scheduled Penalty</th> <th>Paid Penalty </th><th>Penalty Balance</th><th>Scheduled Interest</th><th>Paid Interest</th><th>Interest Balance</th> <th>Principal</th> <th>Principal Paid</th> <th>Principal Balance</th><th>Total Repaid</th><th>Total Loan Balance</th><th>Status</th></tr></thead><tbody>";

  for($i=0; $i<=sizeof($loans_array); ++$i) {

      $loan_id = $loans_array[$i];
      $loan_amount  = $all_loans_obj[$loan_id]['loan_amount'];
      $loan_balance  = $all_loans_obj[$loan_id]['loan_balance'];
      $kitty = $total_repaid  = $all_loans_obj[$loan_id]['total_repaid'];
      //echo "[$kitty]";
      $loan_status  = $loan_statuses[$all_loans_obj[$loan_id]['status']];
      $late_fee_charged = $addon_b[$loan_id][3];
      $interest_charged = $addon_b[$loan_id][1]+$addon_b[$loan_id][4];

    //  echo [$late_fee_charged, $interest_charged, $loan_amount];

      $paid_penalty = $kitty - $late_fee_charged;
      $paid_interest = $kitty - $late_fee_charged - $interest_charged;
      $paid_principal = $kitty - $late_fee_charged -$interest_charged;
    // echo "[$kitty, $late_fee_charged, $interest_charged, $loan_amount]";

      if($paid_penalty > $late_fee_charged){
          $paid_penalty = $late_fee_charged;
      }
      if($paid_interest > $interest_charged){
          $paid_interest = $interest_charged;
      }
      if($paid_principal > $loan_amount){
          $paid_principal = $loan_amount;
      }

      $penalty_balance = false_zero($late_fee_charged - false_zero($paid_penalty));
      $interest_balance = false_zero($late_fee_charged - false_zero($paid_interest));
      $principal_balance = false_zero($loan_amount - false_zero($paid_principal));






      echo "<tr><td>".$loan_id."</td><td>".$all_loans_obj[$loan_id]['account_number']."</td><td>".$all_loans_obj[$loan_id]['given_date']."</td><td>".$all_loans_obj[$loan_id]['final_due_date']."</td>  <td>".false_zero($late_fee_charged)."</td> <td>".false_zero($penalty_balance)."</td><td>".false_zero($paid_penalty)."</td><td>".false_zero($interest_charged)."</td><td>".false_zero($paid_interest)."</td><td>".false_zero($interest_balance)."</td> <td>".false_zero($loan_amount)."</td> <td>".false_zero($paid_principal)."</td> <td>".false_zero($principal_balance)."</td><td>".false_zero($total_repaid)."</td><td>".false_zero($loan_balance)."</td><td>$loan_status</td></tr>";
      $kitty = 0;
  }



echo "</tbody><tfoot>";
echo "<tr><th>Loan</th><th>Phone</th><th>Given Date</th><th>Due Date</th>  <th>Scheduled Penalty</th> <th>Paid Penalty </th><th>Penalty Balance</th><th>Scheduled Interest</th><th>Paid Interest</th><th>Interest Balance</th> <th>Principal</th> <th>Principal Paid</th> <th>Principal Balance</th><th>Total Repaid</th><th>Total Loan Balance</th><th>Status</th></tr>";
echo "</tfoot></table>";




echo "</table>";

?>
    </div>

    </body>
</html>

