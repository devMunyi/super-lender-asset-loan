<?php
$loansq = "disbursed=1 AND status!=0  $andbranch_loan AND   given_date >= '$start_date' AND given_date <= '$end_date'";

$customer_names_array = array();
$products_array = table_to_obj('o_loan_products',"uid > 0","100","uid","name");
$staff_array = table_to_obj('o_users',"uid > 0","10000","uid","name");
$branches_array = table_to_obj('o_branches',"uid > 0","10000","uid","name");
$statuses_array = table_to_obj('o_loan_statuses',"uid > 0","10000","uid","name");

$customers_available = table_to_array('o_loans',"$loansq","1000000","customer_id");
$loan_list = implode(',', table_to_array('o_loans',"$loansq","1000000","uid"));
$customer_list  = implode(",", $customers_available);
$customer_names_array = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","full_name");


$interests_array = array();
$penalties_array = array();
$initiation_array = array();

$ado = fetchtable('o_loan_addons',"loan_id in ($loan_list) AND status=1","uid","asc","10000000","loan_id, addon_id, addon_amount");
while($ad = mysqli_fetch_array($ado)){
    $loan_id = $ad['loan_id'];
    $addon_id = $ad['addon_id'];
    $addon_amount = $ad['addon_amount'];



    /////------------Interests
    if($addon_id == 1 || $addon_id == 7){
        $interests_array = obj_add($interests_array, $loan_id, $addon_amount);
    }

    ///-----------------Initiation fees
    /////------------Interests
    if($addon_id == 2){
        $initiation_array = obj_add($initiation_array, $loan_id, $addon_amount);
    }
    /////------------Penalties
    if($addon_id == 3 || $addon_id == 4 || $addon_id == 5 || $addon_id == 6){
        $penalties_array = obj_add($penalties_array, $loan_id, $addon_amount);
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
$loans = fetchtable('o_loans',"$loansq","uid","asc","10000000","uid, loan_code, loan_amount,account_number, customer_id, product_id, current_lo, current_co, current_branch, status, total_repayable_amount, total_repaid, loan_balance, transaction_code, application_mode, status, given_date, final_due_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $mobile = $l['account_number'];
    $customer_id = $l['customer_id'];
    $product_id = $l['product_id'];
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    $loan_amount = $l['loan_amount'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = $l['loan_balance'];
    $current_branch = $l['current_branch'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $transaction_code = $l['transaction_code'];
    $application_mode = $l['application_mode'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $status = $l['status'];

    $interest = $interests_array[$uid];
    $penalties = $penalties_array[$uid];
    $initiation = $initiation_array[$uid];

    echo " <tr><th>$uid</th>
        <td>".$customer_names_array[$customer_id]."</td>
        <td>".$branches_array[$current_branch]."</td>
        <td>$mobile</td>
        <td>$loan_amount</td>
        <td>$interest</td>
        <td>$penalties</td>
        <td>$initiation</td>
        <td>$total_repayable_amount</td>
        <td>$total_repaid</td>
        <td>$loan_balance</td>
        <td>$transaction_code</td>
        <td>$given_date</td>
        <td>$final_due_date</td>
        <td>".$staff_array[$current_lo]."</td>
        <td>".$staff_array[$current_co]."</td>
        <td>$application_mode</td>
        <td>".$statuses_array[$status]."</td>
    </tr>";

}
echo "</tbody>";
?>
</table>


