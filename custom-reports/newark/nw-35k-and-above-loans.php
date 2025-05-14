<?php

$query = "SELECT l.uid, c.full_name, b.name as Branch , l.account_number as Phone, l.disbursed_amount as Amount_Disbursed, l.total_addons as Interest, l.total_repayable_amount as Amount_Repayable, l.total_repaid as Amount_Repaid, l.loan_balance as Balance, l.given_date as Disbursement_Date, l.final_due_date as Repayment_Date, lo.name as LO, co.name as CO, l.application_mode, ls.name as Status FROM o_loans l left join o_customers c on c.uid = l.customer_id left join o_loan_statuses ls on l.status = ls.uid left join o_branches b on l.current_branch = b.uid left join o_users lo on l.current_lo = lo.uid  left join o_users co on l.current_co = co.uid  where l.disbursed = 1 AND l.status != 0 AND l.disbursed=1 AND l.loan_amount >= 35000 AND l.given_date  BETWEEN '$start_date' AND '$end_date' $andbranch_loan";

$loans = mysqli_query($con, $query);

?>

<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>uid</th>
                <th>full_name</th>
                <th>Branch</th>
                <th>Phone</th>
                <th>Amount_Disbursed</th>
                <th>Interest</th>
                <th>Amount_Repayable</th>
                <th>Amount_Repaid</th>
                <th>Balance</th>
                <th>Disbursement_Date</th>
                <th>Repayment_Date</th>
                <th>LO</th>
                <th>CO</th>
                <th>application_mode</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $sum_amount_disbursed = $sum_interest = $sum_amount_repayable = $sum_amount_repaid = $sum_balance = 0;
            while ($row = mysqli_fetch_array($loans)) {
                $uid = $row['uid'];
                $full_name = $row['full_name'] ?? '';
                $branch = $row['Branch'] ?? '';
                $phone = $row['Phone'];
                $amount_disbursed = $row['Amount_Disbursed'];
                $interest = $row['Interest'];
                $amount_repayable = $row['Amount_Repayable'];
                $amount_repaid = $row['Amount_Repaid'];
                $balance = $row['Balance'];
                $disbursement_date = $row['Disbursement_Date'];
                $repayment_date = $row['Repayment_Date'];
                $lo = $row['LO'] ?? '';
                $co = $row['CO'] ?? '';
                $application_mode = $row['application_mode'];
                $status = $row['Status'] ?? '';

                $sum_amount_disbursed += $amount_disbursed;
                $sum_interest += $interest;
                $sum_amount_repayable += $amount_repayable;
                $sum_amount_repaid += $amount_repaid;
                $sum_balance += $balance;

                echo "<tr>
                <td>$uid</td>
                <td>$full_name</td>
                <td>$branch</td>
                <td>$phone</td>
                <td>$amount_disbursed</td>
                <td>$interest</td>
                <td>$amount_repayable</td>
                <td>$amount_repaid</td>
                <td>$balance</td>
                <td>$disbursement_date</td>
                <td>$repayment_date</td>
                <td>$lo</td>
                <td>$co</td>
                <td>$application_mode</td>
                <td>$status</td>
            </tr>";
            }

            ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th><?php echo $sum_amount_disbursed; ?></th>
                <th><?php echo $sum_interest; ?></th>
                <th><?php echo $sum_amount_repayable; ?></th>
                <th><?php echo $sum_amount_repaid; ?></th>
                <th><?php echo $sum_balance; ?></th>
                <th colspan="6"></th>
            </tr>
        </tfoot>
    </table>


</div>