<?php


////-------------Check if a user has permission to update a loan to tie it with viewing unallocated
$whereCondition = "";
$permi = permission($userd['uid'], 'o_incoming_payments', "0", "general_");
if ($permi == 1) {
    $whereCondition .= " AND ip.loan_id > -1";
} else {
    $whereCondition .= " AND ip.loan_id > 0";
}

$query = "SELECT ip.uid, ip.amount, ip.comments, ip.mobile_number, ip.transaction_code, ip.loan_id, l.loan_balance, ip.payment_date, ip.record_method, b.name as branch, ps.name as `status` FROM o_incoming_payments ip LEFT JOIN o_branches b on ip.branch_id = b.uid LEFT JOIN o_loans l ON l.uid = ip.loan_id LEFT JOIN o_payment_statuses ps ON ps.uid = ip.status where ip.status = 1 AND ip.loan_id = 0 AND ip.payment_date BETWEEN '$start_date' AND '$end_date' $andbranch_pay $whereCondition ORDER BY ip.payment_date DESC";

$payments = mysqli_query($con, $query);

?>


<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>uid</th>
                <th>amount</th>
                <th>comments</th>
                <th>mobile_number</th>
                <th>transaction_code</th>
                <th>loan_id</th>
                <th>loan_balance</th>
                <th>payment_date</th>
                <th>record_method</th>
                <th>branch</th>
                <th>status</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $sum_amount = $sum_loan_balance = 0;
            while ($p = mysqli_fetch_assoc($payments)) {

                $uid = $p['uid'];
                $amount = $p['amount'];
                $comments = $p['comments'] ?? '';
                $mobile_number = $p['mobile_number'];
                $transaction_code = $p['transaction_code'];
                $loan_id = $p['loan_id'] ?? 0;
                $loan_balance = $p['loan_balance'] ??  0;
                $payment_date = $p['payment_date'];
                $record_method = $p['record_method'];
                $branch = $p['branch'] ?? '';
                $status = $p['status'] ?? '';

                echo "<tr><td>$uid</td><td>$amount</td><td>$comments</td><td>$mobile_number</td><td>$transaction_code</td><td>$loan_id</td><td>$loan_balance</td><td>$payment_date</td><td>$record_method</td><td>$branch</td><td>$status</td></tr>";

                $sum_amount += $amount;
                $sum_loan_balance += $loan_balance;

            }

            ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="1">Total</th>
                <th><?php echo money($sum_amount); ?></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th><?php echo money($sum_loan_balance); ?></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    </table>


</div>