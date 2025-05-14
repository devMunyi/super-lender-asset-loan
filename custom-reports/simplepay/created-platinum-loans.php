<?php

$query = "SELECT l.uid, c.full_name,  b.name as branch, l.account_number as phone, lp.name as Product, l.disbursed_amount as loan_amount, l.total_addons as interest, l.total_repayable_amount as amount_repayable, l.total_repaid as amount_repaid, l.loan_balance as balance, lo.name as LO, co.name as CO, l.application_mode, l.added_date as created_at, ls.name as `status` FROM o_loans l left join o_customers c on c.uid = l.customer_id left join o_loan_statuses ls on l.status = ls.uid left join o_branches b on l.current_branch = b.uid left join o_users lo on l.current_lo = lo.uid  left join o_users co on l.current_co = co.uid left join o_loan_products lp ON lp.uid = l.product_id where l.status != 0  AND l.status = 1 AND l.given_date BETWEEN '$start_date' AND '$end_date' $andbranch_loan AND l.loan_amount >= 21000 order by l.uid DESC LIMIT 1000000";

$result = mysqli_query($con, $query);

?>

<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>UID</th>
                <th>Fullname</th>
                <th>Branch</th>
                <th>Phone</th>
                <th>Product</th>
                <th>Loan Amount</th>
                <th>Interest</th>
                <th>Amount Repayable</th>
                <th>Amount Repaid</th>
                <th>Balance</th>
                <th>LO</th>
                <th>CO</th>
                <th>Application Mode</th>
                <th>Created At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $total_loan_amount = $total_interest = $total_amount_repayable = $total_amount_repaid = $total_balance = 0;
            while ($row = mysqli_fetch_array($result)) {

                $uid = $row['uid'];
                $full_name = $row['full_name'] ?? '';
                $branch = $row['branch'] ?? '';
                $phone = $row['phone'] ?? '';
                $product = $row['Product'] ?? '';
                $loan_amount = $row['loan_amount'] ?? 0;
                if($loan_amount >= 21000) {
                    $product = "Platinum";
                }
                $interest = $row['interest'] ?? 0;
                $amount_repayable = $row['amount_repayable'] ?? 0;
                $amount_repaid = $row['amount_repaid'] ?? 0;
                $balance = $row['balance'] ?? 0;
                $LO = $row['LO'] ?? '';
                $CO = $row['CO'] ?? '';
                $application_mode = $row['application_mode'] ?? '';
                $created_at = $row['created_at'] ?? '';
                $status = $row['status'] ?? '';


                echo "<tr><td>$uid</td><td>$full_name</td><td>$branch</td><td>$phone</td><td>$product</td><td>$loan_amount</td><td>$interest</td><td>$amount_repayable</td><td>$amount_repaid</td><td>$balance</td><td>$LO</td><td>$CO</td><td>$application_mode</td><td>$created_at</td><td>$status</td></tr>";


                $total_loan_amount += $loan_amount;
                $total_interest += $interest;
                $total_amount_repayable += $amount_repayable;
                $total_amount_repaid += $amount_repaid;
                $total_balance += $balance;
            }
            ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="5">Total</th>
                <th><?php echo $total_loan_amount; ?></th>
                <th><?php echo $total_interest; ?></th>
                <th><?php echo $total_amount_repayable; ?></th>
                <th><?php echo $total_amount_repaid; ?></th>
                <th><?php echo $total_balance; ?></th>
                <th colspan="5"></th>
            </tr>
        </tfoot>
    </table>
</div>