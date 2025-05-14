<?php
// files includes
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$customers = [];
$custs = fetchtable2('o_customers', 'uid > 0', 'uid', 'ASC');

$customer_uids = array();
$customer_branches = array();
while($c = mysqli_fetch_assoc($custs)){
      $mobile = $c['primary_mobile'];
      $customer_uids[$mobile] = $c['uid'];
      $customer_branches[$mobile] = $c['branch'];
}

$file = "statement.csv";
$open_l = fopen($file, "r");
$is_first_row = true; // Flag variable to track the first row

// set counters
$inserted = 0;
$skipped = 0;
$iteration = 0;

while(($ldata = fgetcsv($open_l, 1000000, ",")) !== FALSE){

    $transcode = $ldata[0];
    $given_date = $ldata[1];
    $amount = $ldata[6]*-1;
    $customer = $ldata[10];
    $cust = explode('-', $customer);
    $customer_phone = trim(make_phone_valid($cust[0]));


    if($amount > 0) {
        $d = explode('/', $given_date);
        $year = $d[2];
        if ($year > 0) {

        } else {
            $d = explode('-', $given_date);
        }
        $given_date = $d[2] . '-' . $d[1] . '-' . $d[0];
        $due_date = dateadd($given_date, 0, 0, 30);
        $interest = $amount*0.12;
        $customer_id = $customer_uids[$customer_phone];
        $customer_branch = $customer_branches[$customer_phone];
        //echo "$transcode, $given_date, $due_date, $amount, $customer_id, $customer_branch, $interest <br/>";

        $fds = array('customer_id','account_number', 'product_id', 'loan_amount', 'disbursed_amount','total_repayable_amount','total_repaid','loan_balance', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'given_date', 'next_due_date', 'final_due_date', 'added_by','current_lo','current_co', 'current_branch', 'added_date', 'loan_stage', 'transaction_code' ,'application_mode','disbursed','paid', 'status');
        $vals = array("$customer_id","$customer_phone", "1", "$amount", "$amount","$amount","0","$amount", "30", "1", "7", "", "4", "0", "0", "$given_date", "$due_date", "$due_date", "1", "0","0","$customer_branch", "$fulldate", "1", "$transcode","MANUAL","1","0", "3");
        $create = addtodb('o_loans', $fds, $vals);
        if($create == 1) {
            echo $create;
            $loan_d = fetchrow('o_loans', "transaction_code='$transcode'", "uid");
           echo addon_with_amount(1, $loan_d, $interest, 0);

        }
    }


}

echo "INSERTED LOANS: $inserted <br>";
echo "SKIPPED LOANS: $skipped <br>";