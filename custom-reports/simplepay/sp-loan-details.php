<?php
$loansq = "disbursed = 1 AND status != 0  $andbranch_loan AND given_date BETWEEN '$start_date' AND '$end_date'";

$customer_names_array = array();
$products_array = table_to_obj('o_loan_products',"uid > 0","100","uid","name");
$staff_array = table_to_obj('o_users',"uid > 0","10000","uid","name");
$branches_array = table_to_obj('o_branches',"uid > 0","10000","uid","name");
$statuses_array = table_to_obj('o_loan_statuses',"uid > 0","10000","uid","name");

$loan_det = fetchtable('o_loans', "$loansq", "uid", "DESC", "10000000", "uid, customer_id");
$customers_available = array();
$loans_available = array();
while($ld = mysqli_fetch_array($loan_det)){
    $customers_available[] = $ld['customer_id'];
    $loans_available[] = $ld['uid'];
}

$loan_list = implode(",", $loans_available);
$customer_list  = implode(",", $customers_available);
$customer_names_array = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","full_name");


$interests_array = array();
$penalties_array = array();
$initiation_array = array();
$insurance_array = array();

$ado = fetchtable('o_loan_addons',"loan_id in ($loan_list) AND status=1","uid","asc","10000000","loan_id, addon_id, addon_amount");
while($ad = mysqli_fetch_array($ado)){
    $loan_id = $ad['loan_id'];
    $addon_id = $ad['addon_id'];
    $addon_amount = $ad['addon_amount'];


    ///------------Interests 
    if(in_array($addon_id, [1, 7, 8, 10])){
        $interests_array = obj_add($interests_array, $loan_id, $addon_amount);
    }

    ///-----------------Initiation fees
    if($addon_id == 2){
        $initiation_array = obj_add($initiation_array, $loan_id, $addon_amount);
    }

    /// ------------Penalties
    if(in_array($addon_id, [3, 4, 5, 6])){
        $penalties_array = obj_add($penalties_array, $loan_id, $addon_amount);
    }

    /// ------------Insurance
    if($addon_id == 9){
        $insurance_array = obj_add($insurance_array, $loan_id, $addon_amount);
    }


}
?>


<table class="table table-condensed table-striped" id="example2">
    <thead>
    <tr><th>UID</th>
        <th>Full Name</th>
        <th>Branch</th>
        <th>Phone</th>
        <th>Principal</th>
        <th>Interest</th>
        <th>Penalties</th>
        <th>Initiation</th>
        <th>Insurance</th>
        <th>Total Amount</th>
        <th>Total Paid</th>
        <th>Balance</th>
        <th>Transcode</th>
        <th>Disb. Date</th>
        <th>Due Date</th>
        <th>LO</th>
        <th>CO</th>
        <th>Application Mode</th>
        <th>Status</th>
    </tr>
    </thead>
<?php

echo "<tbody>";
$loans = fetchtable('o_loans',"uid IN ($loan_list)","uid","DESC","10000","uid, loan_code, loan_amount,account_number, customer_id, product_id, current_lo, current_co, current_branch, status, total_repayable_amount, total_repaid, loan_balance, transaction_code, application_mode, status, given_date, final_due_date");

$total_principal = $total_interest = $total_penalties = $total_initiation = $total_insurance = $total_total_repayable_amount = $total_total_repaid = $total_loan_balance = 0;
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $mobile = $l['account_number'];
    $customer_id = $l['customer_id'];
    $product_id = $l['product_id'];
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    $loan_amount = $l['loan_amount'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = doubleval($l['loan_balance']);
    $current_branch = $l['current_branch'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $transaction_code = $l['transaction_code'];
    $application_mode = $l['application_mode'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $status = $l['status'];

    $interest = $interests_array[$uid] ?? 0;
    $penalties = $penalties_array[$uid] ?? 0;
    $initiation = $initiation_array[$uid] ?? 0;
    $insurance = $insurance_array[$uid] ?? 0;

    $customer_name = $customer_names_array[$customer_id] ?? '';
    $branch_name = $branches_array[$current_branch] ?? '';
    $current_lo_name = $staff_array[$current_lo] ?? '';
    $current_co_name = $staff_array[$current_co] ?? '';
    $status_name = $statuses_array[$status] ?? '';

    $total_principal += $loan_amount;
    $total_interest += $interest;
    $total_penalties += $penalties;
    $total_initiation += $initiation;
    $total_insurance += $insurance;
    $total_total_repayable_amount += $total_repayable_amount;
    $total_total_repaid += $total_repaid;
    $total_loan_balance += $loan_balance;
    if($loan_balance > 0){
        $total_loan_balance += $loan_balance;
    }


    echo "<tr><th>$uid</th>
        <td>$customer_name</td>
        <td>$branch_name</td>
        <td>$mobile</td>
        <td>$loan_amount</td>
        <td>$interest</td>
        <td>$penalties</td>
        <td>$initiation</td>
        <td>$insurance</td>
        <td>$total_repayable_amount</td>
        <td>$total_repaid</td>
        <td>$loan_balance</td>
        <td>$transaction_code</td>
        <td>$given_date</td>
        <td>$final_due_date</td>
        <td>$current_lo_name</td>
        <td>$current_co_name</td>
        <td>$application_mode</td>
        <td>$status_name</td>
    </tr>";

}
echo "</tbody>";
?>
<tfoot>
    <tr>
        <th colspan="4">Total</th>
        <th><?php echo number_format($total_principal, 2); ?></th>
        <th><?php echo number_format($total_interest, 2); ?></th>
        <th><?php echo number_format($total_penalties, 2); ?></th>
        <th><?php echo number_format($total_initiation, 2); ?></th>
        <th><?php echo number_format($total_insurance, 2); ?></th>
        <th><?php echo number_format($total_total_repayable_amount, 2); ?></th>
        <th><?php echo number_format($total_total_repaid, 2); ?></th>
        <th><?php echo number_format($total_loan_balance, 2); ?></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
</tfoot>
</table>


