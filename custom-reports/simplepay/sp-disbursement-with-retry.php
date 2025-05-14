<?php

//  Track loans that have been retried more than once.
$loans_with_retry = table_to_array('o_mpesa_queues', "status = 2 AND trials > 1 AND sent_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'", "1000000", "loan_id");

$loans_with_retry_list = implode(',', $loans_with_retry);
$sql = "SELECT l.uid, c.full_name, lp.name AS product, b.name AS branch, c.gender, c.dob, c.sec_data, l.disbursed_amount as principal, l.total_repaid as Amount_Repaid, l.loan_balance as Balance, l.given_date, l.final_due_date, ls.name as Status FROM o_loans l left join o_customers c on c.uid = l.customer_id left join o_loan_statuses ls on l.status = ls.uid left join o_branches b ON b.uid = l.current_branch left join o_loan_products lp ON lp.uid = c.primary_product where l.uid IN ($loans_with_retry_list) AND l.status IN (3, 4, 5, 7, 8, 9, 10) AND l.given_date BETWEEN '$start_date' AND '$end_date' $andbranch_loan LIMIT 1000000";

$result = mysqli_query($con, $sql);



?>
<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>LoanID</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Branch</th>
                <th>Business Category</th>
                <th>Principal</th>
                <th>Amount Repaid</th>
                <th>Balance</th>
                <th>Disbursement Date</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $amount_disbursed_total =  $amount_repaid_total = $amount_balance_total = 0;

            while ($row = mysqli_fetch_array($result)) {
                $uid = $row['uid'];
                $full_name = $row['full_name'];
                $product = $row['product'] ?? '';
                $branch = $row['branch'];
                $gender = $row['gender'];
                $dob = $row['dob'];
                $age = date_diff(date_create($dob), date_create('today'))->y;
                $age = $age > 0 ? $age : '';
                $sec_data = $row['sec_data'];
                $sec_data = json_decode($sec_data, true);
                $business_category = $sec_data[43] ?? '';
                if ($business_category == '--Select One') {
                    $business_category = '';
                }
                $principal = $row['principal'];
                $amount_repaid = $row['Amount_Repaid'];
                $balance = $row['Balance'];
                $given_date = $row['given_date'] ?? '';
                $due_date = $row['final_due_date'] ?? '';
                $status = $row['Status'] ?? '';

                $amount_disbursed_total += $principal;
                $amount_repaid_total += $amount_repaid;
                $amount_balance_total += $balance;


                if ($gender == 'M') {
                    $gender = 'Male';
                } elseif ($gender == 'F') {
                    $gender = 'Female';
                } else {
                    $gender = '';
                }


                echo "<tr><td>$uid</td><td>$full_name</td><td>$product</td><td>$age</td><td>$gender</td><td>$branch</td><td>$business_category</td><td>$principal</td><td>$amount_repaid</td><td>$balance</td><td>$given_date</td><td>$due_date</td><td>$status</td></tr>";
            }

            ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="6">Total</th>
                <th><?php echo number_format($amount_disbursed_total, 2); ?></th>
                <th><?php echo number_format($amount_repaid_total, 2); ?></th>
                <th><?php echo number_format($amount_balance_total, 2); ?></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>