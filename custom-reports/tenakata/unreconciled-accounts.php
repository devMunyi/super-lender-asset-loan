<?php

// session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");


// filter loans to check using date range 
$targeted_loans = table_to_array('o_loans', "final_due_date BETWEEN '$start_date' AND '$end_date' $andbranch_loan AND disbursed = 1 AND status != 0", "10000000", "uid");
$targeted_loans_list  = implode(',', $targeted_loans);

// prepare loan addons associative array
$loan_addons = fetchtable2('o_loan_addons', "status = 1 AND loan_id IN ($targeted_loans_list)", 'uid', 'asc', 'loan_id, addon_amount');
$l_addon = [];
while ($lad = mysqli_fetch_assoc($loan_addons)) {
    $lid = $lad['loan_id'];
    $laddon_amt = $lad['addon_amount'];

    $l_addon = obj_add($l_addon, $lid, $laddon_amt);
}


// prepare loan repaid totals associative array
$all_payments = fetchtable2("o_incoming_payments", "status = 1 AND loan_id IN ($targeted_loans_list)", "uid", "DESC", "amount, loan_id");
$loan_payment_totals = [];
while ($p = mysqli_fetch_assoc($all_payments)) {
    $paid_amount = $p['amount'] ?? 0;
    $loan_uid = $p['loan_id'] ?? 0;

    if (!in_array($loan_uid, array(0, 1, 2))) {
        $loan_payment_totals = obj_add($loan_payment_totals, $loan_uid, $paid_amount);
    }
}

// filter loans to check using date range 
$due_accounts = table_to_array('o_loans', "final_due_date BETWEEN '$start_date' AND '$end_date' $andbranch_loan AND disbursed = 1 AND status != 0", "10000000", "customer_id");
$due_accounts_list  = implode(',', $due_accounts);

// prepare loans to check associative array
$loans_to_check = fetchtable('o_loans', "customer_id IN ($due_accounts_list)", "uid", "ASC", "1000000", "uid, total_repaid, loan_amount, customer_id, final_due_date, status, loan_balance");


$unreconciled_loans = [];
$unreconciled_accounts = [];


while ($ltc = mysqli_fetch_assoc($loans_to_check)) {
    $ltc_uid = $ltc['uid'];
    $ltc_cust_id = $ltc['customer_id'];
    $ltc_given_amount = doubleval($ltc['loan_amount']);
    $ltc_addons_total = doubleval($l_addon[$ltc_uid]);
    $ltc_status = intval($ltc['status']);
    // $ltc_recorded_loan_bal = abs(doubleval($ltc['loan_balance']));
    

    $ltc_repayable_amount = $ltc_given_amount + $ltc_addons_total;
    $ltc_repaid_amount = doubleval($loan_payment_totals[$ltc_uid]);
    // $ltc_expected_loan_bal = $ltc_repaid_amount - $ltc_repayable_amount;

    if ($ltc_repaid_amount = $ltc_repayable_amount) {
    } else {
        if($ltc_status == 5){
            if (isset($unreconciled_loans[$ltc_cust_id])) {
                array_push($unreconciled_loans[$ltc_cust_id], $ltc_uid);
            } else {
                $unreconciled_loans[$ltc_cust_id] = [$ltc_uid];
                $unreconciled_accounts[] = $ltc_cust_id;
            }
        }
    }

}


?>
<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>Customer UID</th>
                <th>Customer Name</th>
                <th>Branch</th>
                <th>Unreconciled Loan(s)</th>
                <th>Customer Status</th>
            </tr>
        </thead>
        <tbody>
            <?php

                $branch_names = table_to_obj('o_branches', "uid > 0", "1000", "uid", "name");
                $customer_status_names = table_to_obj('o_customer_statuses', "uid > 0", "1000", "code", "name");
                $unreconciled_accounts_list = implode(",", $unreconciled_accounts);
                

                $accounts = fetchtable2("o_customers", "uid IN ($unreconciled_accounts_list)", "uid", "desc", "uid, full_name, branch, status");

                while($ac = mysqli_fetch_assoc($accounts)){
                    $ac_uid = $ac["uid"];
                    $ac_name = $ac["full_name"];
                    $ac_branch = $ac['branch']; 
                    $ac_branch = $branch_names[$ac_branch];
                    $unreconciled_list = implode(",", $unreconciled_loans[$ac_uid] ?? []);
                    $ac_status = $ac['status'];
                    $ac_status = $customer_status_names[$ac_status];

                    $go_to_account = "<a href=\"customers?customer=".encurl($ac_uid)."\" target='_blank'><i class='fa fa-external-link-square'></i><a>";

                    echo "<tr><td>$ac_uid</td><td>$ac_name $go_to_account</td><td>$ac_branch</td><td>$unreconciled_list</td><td>$ac_status</td></tr>";
                }
            ?>
        </tbody>

        <tfoot>
            <!-- <tr>
                <th>#</th>
                <th>--</th>
            </tr> -->
        </tfoot>
    </table>
</div>