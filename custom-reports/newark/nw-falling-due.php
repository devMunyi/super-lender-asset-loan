<?php 

$sql = "SELECT l.uid, c.full_name, b.name as Branch , l.account_number as Phone, l.loan_amount as Principal, l.total_repayable_amount as TotalAmount, l.total_repaid as Total_paid, l.loan_balance as Balance, l.transaction_code as Transcode, l.given_date as Disbursement_Date, l.final_due_date as Due_Date, 
 l.disbursed_amount as Amount_Disbursed, l.total_addons as Interest, lo.name as LO, co.name as CO, ls.name as Status FROM o_loans l right join o_customers c on c.uid = l.customer_id left join o_loan_statuses ls on l.status = ls.uid left join o_branches b on l.current_branch = b.uid left join o_users lo on l.current_lo = lo.uid  left join o_users co on l.current_co = co.uid  where l.disbursed = 1 AND l.status != 0 AND l.final_due_date  BETWEEN '$start_date' AND '$end_date' $andbranch_loan LIMIT 1000000";


$result = mysqli_query($con, $sql);

?>
<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>uid</th>
                <th>full_name</th>
                <th>Branch</th>
                <th>Phone</th>
                <th>Principal</th>
                <th>TotalAmount</th>
                <th>Total_paid</th>
                <th>Balance</th>
                <th>Transcode</th>
                <th>Disbursement_Date</th>
                <th>Due_Date</th>
                <th>Amount_Disbursed</th>
                <th>Interest</th>
                <th>LO</th>
                <th>CO</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php


            $sum_pricipal = $sum_total_amount = $sum_total_paid = $sum_balance = $sum_amount_disbursed = $sum_interest = 0;
            while ($row = mysqli_fetch_array($result)) {

                $uid = $row['uid'];
                $full_name = $row['full_name'] ?? '';
                $branch = $row['Branch'] ?? '';
                $phone = $row['Phone'] ?? '';
                $principal = $row['Principal'];
                $total_amount = $row['TotalAmount'];
                $total_paid = $row['Total_paid'];
                $balance = $row['Balance'];
                $transcode = $row['Transcode'];
                $disbursement_date = $row['Disbursement_Date'];
                $due_date = $row['Due_Date'];
                $amount_disbursed = $row['Amount_Disbursed'];
                $interest = $row['Interest'];
                $lo = $row['LO'] ?? '';
                $co = $row['CO'] ?? '';
                $status = $row['Status'] ?? '';

                $sum_pricipal += $principal;
                $sum_total_amount += $total_amount;
                $sum_total_paid += $total_paid;
                $sum_balance += $balance;
                $sum_amount_disbursed += $amount_disbursed;
                $sum_interest += $interest;

                echo "<tr><td>$uid</td> <td>$full_name</td> <td>$branch</td> <td>$phone</td> <td>$principal</td> <td>$total_amount</td> <td>$total_paid</td> <td>$balance</td> <td>$transcode</td> <td>$disbursement_date</td> <td>$due_date</td> <td>$amount_disbursed</td> <td>$interest</td> <td>$lo</td> <td>$co</td> <td>$status</td> </tr>";

                
            }

            ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th><?php echo money($sum_pricipal); ?></th>
                <th><?php echo money($sum_total_amount); ?></th>
                <th><?php echo money($sum_total_paid); ?></th>
                <th><?php echo money($sum_balance); ?></th>
                <th colspan="3"></th>
                <th><?php echo money($sum_amount_disbursed); ?></th>
                <th><?php echo money($sum_interest); ?></th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>
</div>