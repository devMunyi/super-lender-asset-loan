<?php

$loans_arr = table_to_array('o_loans', "status IN (3, 4, 5, 7, 8, 9, 10, 11) AND given_date BETWEEN '$start_date' AND '$end_date' $andbranch_loan $andLoanProductIdWithTableAlias", "10000000", "uid");

// remove duplicate items from loans array
$loans_arr = array_unique($loans_arr);

if (count($loans_arr) > 0) {
    $loans_string = implode(",", $loans_arr);

    $addons_obj = table_to_obj('o_loan_addons', "loan_id IN ($loans_string) AND addon_id IN (4, 7) AND status = 1", "10000000", "loan_id", "addon_amount");

    $sql = "SELECT l.uid, c.full_name, lp.name as Product, b.name as Branch, l.account_number as Phone, l.cleared_date, l.disbursed_amount as Amount_Disbursed, l.total_addons as Interest, l.total_repayable_amount as Amount_Repayable, l.total_repaid as Amount_Repaid, l.loan_balance as Balance, l.given_date as Disbursement_Date, 
    l.final_due_date as Repayment_Date, lo.name as LO, co.name as CO, l.application_mode, ls.name as Status FROM o_loans l left join o_customers c on c.uid = l.customer_id left join o_loan_statuses ls on l.status = ls.uid left join o_branches b on l.current_branch = b.uid left join o_users lo on l.current_lo = lo.uid left join o_users co on l.current_co = co.uid left join o_loan_products lp ON lp.uid = c.primary_product where l.uid IN($loans_string) LIMIT 1000000";

    $result = mysqli_query($con, $sql);
} 


?>
<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>UID</th>
                <th>Fullname</th>
                <th>Product</th>
                <th>Branch</th>
                <th>Phone</th>
                <th>Amount Disbursed</th>
                <th>Total Charges</th>
                <th>Amount Repayable</th>
                <th>Amount Repaid</th>
                <th>Penalty</th>
                <th>Balance</th>
                <th>Disbursement Date</th>
                <th>Due Date</th>
                <th>Cleared Date</th>
                <th>LO</th>
                <th>CO</th>
                <th>Application Mode</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $amount_disbursed_total = $interest_total = $amount_repayable_total = $amount_repaid_total = $penalty_total = $balance_total = 0;
                if($result && mysqli_num_rows($result) > 0){
                    while ($row = $result->fetch_assoc()) {
                        $uid = $row['uid'];
                        $full_name = $row['full_name'] ?? '';
                        $product = $row['Product'] ?? '';
                        $branch = $row['Branch'];
                        $phone = $row['Phone'];
                        $amount_disbursed = $row['Amount_Disbursed'];
                        $interest = $row['Interest'];
                        $amount_repayable = $row['Amount_Repayable'];
                        $amount_repaid = $row['Amount_Repaid'];
                        $cleared_date = $row['cleared_date'];
                        $penalty = $addons_obj[$uid] ?? 0;
                        $balance = $row['Balance'];
                        if($penalty > 0){
                            $balance = $balance - $penalty;
                        }
                        $disbursement_date = $row['Disbursement_Date'];
                        $repayment_date = $row['Repayment_Date'];
                        $lo = $row['LO'];
                        $co = $row['CO'];
                        $application_mode = $row['Application_Mode'];
                        $status = $row['Status'];
        
                        echo "<tr>
                            <td>$uid</td>
                            <td>$full_name</td>
                            <td>$product</td>
                            <td>$branch</td>
                            <td>$phone</td>
                            <td>$amount_disbursed</td>
                            <td>$interest</td>
                            <td>$amount_repayable</td>
                            <td>$amount_repaid</td>
                            <td>$penalty</td>
                            <td>$balance</td>
                            <td>$disbursement_date</td>
                            <td>$repayment_date</td>
                            <td>$cleared_date</td>
                            <td>$lo</td>
                            <td>$co</td>
                            <td>$application_mode</td>
                            <td>$status</td>";
        
                        $amount_disbursed_total += $amount_disbursed;
                        $interest_total += $interest;
                        $amount_repayable_total += $amount_repayable;
                        $amount_repaid_total += $amount_repaid;
                        $penalty_total += $penalty;
                        $balance_total += $balance;
        
                    }
                }    
            ?>
        </tbody>

        <tfoot>
            <tr>
                <th>Total</th>
                <th></th>
                <th></th>
                <th></th>
                <th><?php echo number_format($amount_disbursed_total, 2); ?></th>
                <th><?php echo number_format($interest_total, 2); ?></th>
                <th><?php echo number_format($amount_repayable_total, 2); ?></th>
                <th><?php echo number_format($amount_repaid_total, 2); ?></th>
                <th><?php echo number_format($penalty_total, 2); ?></th>
                <th><?php echo number_format($balance_total, 2); ?></th>
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
</div>