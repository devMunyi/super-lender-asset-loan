<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$active_customers_array = array();

$userd = session_details();
$loan_branches = branch_permissions($userd, 'o_loans');
$branches_list = branch_permissions($userd, 'o_branches');
$pay_branches = branch_permissions($userd, 'o_incoming_payments');
$customer_branches = $user_branches = branch_permissions($userd, 'o_customers');
//echo $start_date.','.$end_date;


$loans = fetchtable('o_loans',"status!=0 AND given_date BETWEEN '$start_date' AND '$end_date' $loan_branches","uid","asc","1000000","uid, loan_amount, total_repayable_amount, total_repaid, loan_balance, total_addons, total_deductions, customer_id, disbursed, paid, status, other_info");
while($l = mysqli_fetch_array($loans)){
    $luid = $l['uid'];
    $loan_amount = $l['loan_amount'];
   // $total_repayable_amount = $l['total_repayable_amount'];   ///We are making p+i total repayable

    $sec = $l['other_info'];
    $sec_obj = (json_decode($sec, true));
    $interest = $sec_obj['INTEREST_AMOUNT'];

    // $total_repaid = $l['total_repaid']; /////We are deducting other charges first
    $loan_balance = $l['loan_balance'];
    $total_addons = $l['total_addons'];
    $total_deductions = $l['total_deductions'];
    $customer_id = $l['customer_id'];
    $disbursed = $l['disbursed'];
    $paid = $l['paid'];
    $status = $l['status'];

        $other_charges = $total_addons - $interest;

    $total_repayable_amount = $loan_amount + $interest;  ////---Making this the total repayable amount
    $total_repaid = false_zero($l['total_repaid'] - $other_charges); ////----Total repaid, we remove other charges paid


    if($status == 7)
       {
       $defaulters+= $loan_balance;
       }
    elseif ($status == 9){
        $written_off+= $written_off;
    }
    $active_customers_array[$customer_id] = 1;
    if($disbursed == 1) {
        $total_loans += $loan_amount;
    }
    $total_repayments+=$total_repaid;
    $total_repayable_total+=$total_repayable_amount;
}
$collection_rate = round(($total_repayments/$total_repayable_total)*100, 2);
$active_customers = count($active_customers_array);

/////////----
$total_leads = 0;
$total_customers = 0;
$customers = fetchtable('o_customers',"date(added_date) BETWEEN '$start_date' AND '$end_date' $customer_branches","uid","asc","1000000","uid, status");
while($c = mysqli_fetch_array($customers)){
    $cuid = $c['uid'];
    $cstatus = $c['status'];
    $total_leads+=1;
    if($cstatus == 1){
        $total_customers+=1;
    }

}

$bulk_sms = countotal('o_sms_outgoing',"date(queued_date) BETWEEN '$start_date' AND '$end_date' AND status=1","uid");
$interactions = countotal('o_customer_conversations',"date(conversation_date) BETWEEN '$start_date' AND '$end_date' AND status=1","uid");
$staff = countotal('o_users',"status=1 $user_branches","uid");
$branches = countotal('o_branches',"status=1 $branches_list","uid");
$loan_products = countotal('o_loan_products',"status=1","uid");
$payments = countotal('o_incoming_payments',"payment_date BETWEEN '$start_date' AND '$end_date' AND status=1 $pay_branches","uid");
$pending_disbursement = countotal('o_loans',"status=2 $loan_branches","uid");
$pending_approval = countotal('o_loans',"status=1 $loan_branches","uid");
?>

<table class="table">

    <tr class="text-center">
        <td class="bg-purple">
            <div class="font-18 font-bold font-18 font-bold"><?php echo number_format($total_loans); ?></div>
            Loans
        </td>
        <td class="bg-blue">
            <div class="font-18 font-bold"><?php echo number_format($total_repayments); ?></div>
            Re-payments
        </td>
        <td class="bg-green">
            <div class="font-18 font-bold"><?php echo number_format($collection_rate); ?>%</div>
            Collection Rate
        </td>
        <td class="bg-blue">
            <div class="font-18 font-bold"><?php echo number_format($active_customers); ?></div>
            Active Customers
        </td>
        <td class="bg-purple">
            <div class="font-18 font-bold">--</div>
            Dormant Customers
        </td>
        <td class="bg-green">
            <div class="font-18 font-bold"><?php echo number_format($defaulters); ?></div>
            Defaulters
        </td>
        <td class="bg-purple">
            <div class="font-18 font-bold"><?php echo number_format($total_leads); ?></div>
            Leads
        </td>
        <td class="bg-blue">
            <div class="font-18 font-bold"><button class="btn btn-default btn-sm btn-flat" onclick="modal_view('/jresources/loans/view-summaries.php','','Loan Summaries MTD')"><i class="fa fa-eye"></i> View</button></div>
            Loans Per Status
        </td>

    </tr>
    <tr class="text-center">

        <td class="bg-blue">
            <div class="font-18 font-bold"><?php echo number_format($bulk_sms); ?></div>
            Bulk SMS
        </td>
        <td class="bg-green">
            <div class="font-18 font-bold"><?php echo number_format($interactions); ?></div>
            Interactions
        </td>
        <td class="bg-blue">
            <div class="font-18 font-bold"><?php echo number_format($staff); ?></div>
            Active Staff
        </td>
        <td class="bg-purple">
            <div class="font-18 font-bold"><?php echo number_format($branches); ?></div>
            Branches
        </td>
        <td class="bg-green">
            <div class="font-18 font-bold"><?php echo number_format($loan_products); ?></div>
            Products
        </td>
        <td class="bg-purple">
            <div class="font-18 font-bold"><?php echo number_format($payments); ?></div>
            Incoming Payments
        </td>
        <td class="bg-blue">
            <div class="font-18 font-bold"><?php echo number_format($pending_disbursement); ?></div>
            Pending Disbursements
        </td>
        <td class="bg-purple">
            <div class="font-18 font-bold"><?php echo number_format($pending_approval); ?></div>
            Pending Approval
        </td>

    </tr>


</table>
