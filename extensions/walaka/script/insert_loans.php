<?php 

// files includes
include_once ("../../../configs/conn.inc");
include_once ("../../../php_functions/functions.php");
include_once ("./reusable.php");

$customers = [];
$custs = fetchtable2('o_customers', 'uid > 0', 'uid', 'ASC');

while($c = mysqli_fetch_assoc($custs)){
    $cust_code = $c['customer_code'];
    $customers[$cust_code] = [
        $c['uid'], $cust_code, $c['primary_mobile'], $c['branch']
    ];
}

$file = "../data/dagoretti_loans.csv";
$open_l = fopen($file, "r");
$is_first_row = true; // Flag variable to track the first row

// set counters
$inserted = 0;
$skipped = 0;
$iteration = 0;

while(($ldata = fgetcsv($open_l, 1000000, ",")) !== FALSE){
    if ($is_first_row) {
        $is_first_row = false;
        continue; // Skip the first row and move to the next iteration
    }

    // Application ID / Customer Number(0), Loan No.(1), Branch(2),Loan Product(3), Principal Amount(4),
    // Loan Release Date(5), Repayment Date(6), Total Cost(7 ),Total Repaid(8), Loan Interest(9), Initiation Fee(10),Penalties(11)
    $cust_code  = trim($ldata[0]) ?? 0;
    $b = strtoupper($cust_code[0]);
    $cust_code = customerCode($cust_code, $b);
    $loan_num = trim($ldata[1]) ?? 0;
    $loan_code = $cust_code.'-'.$loan_num;
    $customer_id = $customers[$cust_code][0] ?? 0;
    $account_number = $customers[$cust_code][2] ?? '';
    $product_id = 1; // Supa Fast at id = 1
    $loan_type = 2; // business loan at id = 2
    $period = 30; 
    $period_units = 1; 
    $payment_frequency = 1; 
    $payment_breakdown = '';
    // $initiation_fee = doubleval(trim($ldata[10])) ?? 0;
    $interest = doubleval(trim($ldata[9]));
    $penalties = doubleval(trim($ldata[11]));
    $total_addons = $interest + $penalties;
    $loan_amount = $disbursed_amount = doubleval(trim($ldata[4])) ?? 0;
    $total_repayable_amount = $loan_amount + $total_addons;
    $total_repaid = doubleval(trim($ldata[8])) ?? 0;
    $loan_bal = $total_repayable_amount - $total_repaid;
    $total_deductions = 0.00;
    $total_instalments = 4;
    $amount_per_instalment = $total_repayable_amount / 4;
    $total_instalments_paid = floor($total_repaid / $amount_per_instalment);
    $current_instalment = ceil($total_repaid / $amount_per_instalment);
    $current_instalment_amount = $amount_per_instalment;
    $given_date = trim($ldata[5]) ?? '00/00/0000';
    $given_date = DateTime::createFromFormat("m/d/Y", $given_date);
    $given_date = $given_date->format("Y-m-d");

    
    if($customer_id >= 446){
        $repayment_date = addDaysToDate($given_date, 30);
    }else {
        $repayment_date = trim($ldata[6]) ?? '00/00/0000';
        $repayment_date = DateTime::createFromFormat("m/d/Y", $repayment_date);
        $repayment_date = $repayment_date->format("Y-m-d");
    }
    
    $current_date_obj = new DateTime($date);
    $repayment_date_obj = new DateTime($repayment_date);
    $given_date_obj = new DateTime($given_date);

    // Calculate the difference between current_date_obj and repayment_date_obj
    $interval = $current_date_obj->diff($repayment_date_obj);

    // Find the appropriate next due date based on the thresholds
    $next_due_date = $repayment_date;
    if ($current_date_obj > $repayment_date_obj) {
        $next_due_date = $repayment_date;
    }else{
        // Define the due date thresholds
        $due_date_thresholds = [
            21 => 21,
            14 => 14,
            7 => 7,
        ];

        foreach ($due_date_thresholds as $threshold => $days) {
            if ($days_difference >= $threshold) {
                $next_due_date = $current_date_obj->modify("+$days days")->format('Y-m-d');
                break;
            }
        }
    }

    $final_due_date = $repayment_date;
    //// Possible statuses => 7(overdue), 4(PP), 5(Cleared), 3(Disbursed)

    // 1) Partially Paid (PP) Status
    if($total_repaid > 0 && $current_date_obj < $repayment_date_obj && $loan_bal){
        $status = 4;
    }
    // 2) overdue status
    elseif($current_date_obj > $repayment_date_obj && $loan_bal > 0){
        $status = 7;

    // 3) cleared status
    }elseif($loan_bal == 0){
        $status = 5;
        $current_instalment_amount = 0.00;

    // 4) Disbursed Status
    }else if($disbursed_amount > 0){
        $status = 3;
    }else {

    // 6) Unknown Status
        $status = 0;
    }
    $added_by = 0;
    $current_agent = 0;
    $current_branch = $customers[$cust_code][3] ?? 0;
    $added_date = $transaction_date = $given_date ." 0000-00-00";
    $loan_stage = 2;
    $loan_flag = 0;
    $transaction_code = '';
    $application_mode = 'MANUAL';
    $disburse_state = 'DELIVERED';
    $disbursed = 1;



    $fds = array('loan_code','customer_id','account_number','product_id','loan_type','loan_amount','disbursed_amount','total_repayable_amount','total_repaid', 'loan_balance', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_addons', 'total_deductions', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'current_instalment_amount', 'given_date', 'next_due_date', 'final_due_date', 'added_by', 'current_agent', 'current_branch', 'added_date', 'loan_stage', 'loan_flag', 'transaction_code', 'transaction_date', 'application_mode', 'disburse_state', 'disbursed', 'status');
    $vals = array($loan_code, $customer_id, $account_number, $product_id, $loan_type, $loan_amount, $disbursed_amount, $total_repayable_amount, $total_repaid, $loan_bal, $period, $period_units, $payment_frequency, $payment_breakdown, $total_addons, $total_deductions, $total_instalments, $total_instalments_paid, $current_instalment, $current_instalment_amount, "$given_date", "$next_due_date", "$final_due_date", $added_by, $current_agent, $current_branch, "$added_date", $loan_stage, $loan_flag, "$transaction_code", "$transaction_date", "$application_mode", "$disburse_state", $disbursed, $status);
    
    $create = addtodb('o_loans' ,$fds, $vals);
    echo 'LOAN CODE: '.$loan_code .' TABLE INSERT RESPONSE: '.$create .'<br>'; 
    if($create == 1)
    {
        $inserted += 1;
    }
    else
    {
        $skipped += 1;
    }
}

echo "INSERTED LOANS: $inserted <br>";
echo "SKIPPED LOANS: $skipped <br>";