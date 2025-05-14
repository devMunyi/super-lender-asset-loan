<?php

$andbranch_loan_with_alias = str_replace('current_branch', 'l.current_branch', $andbranch_loan);

$sql = "SELECT l.uid, o_customers.full_name, o_branches.name as Branch , l.account_number as Phone, lp.name as Product, l.total_repayable_amount as Repayable, l.total_repaid as Paid, l.loan_balance as Balance, l.transaction_code as Transcode, l.given_date as Disbursement_Date, l.final_due_date as Due_Date, l.disbursed_amount as Amount_Disbursed, l.total_addons as Addons, lo.name as LO, co.name as CO, o_loan_statuses.name as Status FROM o_loans l left join o_customers on o_customers.uid = l.customer_id left join o_loan_statuses on l.status = o_loan_statuses.uid left join o_branches on l.current_branch = o_branches.uid left join o_users lo on l.current_lo = lo.uid  left join o_users co on l.current_co = co.uid left join o_loan_products lp ON lp.uid = l.product_id  where l.disbursed = 1 AND l.status != 0 AND l.paid = 0 AND l.final_due_date BETWEEN '$start_date' AND '$end_date' $andbranch_loan_with_alias $andLoanProductIdWithTableAlias";

$result = mysqli_query($con, $sql);
if (!$result) {
    die('Query failed: ' . mysqli_error($con));
}

?>

<table class="table table-condensed table-striped" id="example2">
    <thead>
        <tr>
            <th>UID</th>
            <th>full_name</th>
            <th>Branch</th>
            <th>Phone</th>
            <th>Product</th>
            <th>Amount_Disbursed</th>
            <th>Addons</th>
            <th>Repaybale</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Transcode</th>
            <th>Disbursement Date</th>
            <th>Due Date</th>
            <th>LO</th>
            <th>CO</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>

        <?php
        
        $total_amount_disbursed = $total_addons = $total_repayable = $total_paid = $total_balance = 0;
        while ($row = mysqli_fetch_array($result)) {
            $uid = $row['uid'];
            $full_name = $row['full_name'];
            $Branch = $row['Branch'];
            $Phone = $row['Phone'];
            $Product = $row['Product'];
            $Amount_Disbursed = $row['Amount_Disbursed'];
            $Addons = $row['Addons'];
            $Repayable = $row['Repayable'];
            $Paid = $row['Paid'];
            $Balance = doubleval($row['Balance']);
            $Transcode = $row['Transcode'];
            $Disbursement_Date = $row['Disbursement_Date'];
            $Due_Date = $row['Due_Date'];
            $LO = $row['LO'];
            $CO = $row['CO'];
            $Status = $row['Status'];

            // Calculate totals
            $total_amount_disbursed += doubleval($Amount_Disbursed);
            $total_addons += doubleval($Addons);
            $total_repayable += doubleval($Repayable);
            $total_paid += doubleval($Paid);
            $total_balance += doubleval($Balance);

            echo "<tr>
                <td>$uid</td>
                <td>$full_name</td>
                <td>$Branch</td>
                <td>$Phone</td>
                <td>$Product</td>
                <td>$Amount_Disbursed</td>
                <td>$Addons</td>
                <td>$Repayable</td>
                <td>$Paid</td>
                <td>$Balance</td>
                <td>$Transcode</td>
                <td>$Disbursement_Date</td>
                <td>$Due_Date</td>
                <td>$LO</td>
                <td>$CO</td>
                <td>$Status</td>
            </tr>";
        }

        ?>
    </tbody>

    <tfoot>
        <tr>
            <th colspan="5">Total</th>
            <th><?php echo $total_amount_disbursed; ?></th>
            <th><?php echo $total_addons; ?></th>
            <th><?php echo $total_repayable; ?></th>
            <th><?php echo $total_paid; ?></th>
            <th><?php echo $total_balance; ?></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </tfoot>
</table>