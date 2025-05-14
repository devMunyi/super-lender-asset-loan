<?php

$branch = $_GET['branch'] ?? 0;

if ($branch > 0) {
    $branch = decurl($branch);
    $andloanbranch = " AND l.current_branch = $branch";
} else {
    $andloanbranch = "";
}


$sql = "SELECT l.uid, c.full_name, b.name AS branch, c.gender, c.dob, c.sec_data, l.disbursed_amount as principal, l.total_repaid as Amount_Repaid, l.loan_balance as Balance, l.given_date, l.final_due_date, ls.name as Status FROM o_loans l left join o_customers c on c.uid = l.customer_id left join o_loan_statuses ls on l.status = ls.uid left join o_branches b ON b.uid = l.current_branch where l.status IN (3, 4, 5, 7, 8, 9, 10) AND l.given_date BETWEEN '$start_date' AND '$end_date' $andloanbranch LIMIT 1000000";

$result = mysqli_query($con, $sql);



?>
<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>LoanID</th>
                <th>Customer Name</th>
                <th>Branch</th>
                <th>Business Category</th>
                <th>Age</th>
                <th>Gender</th>
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
                $branch = $row['branch'];
                $gender = $row['gender'];
                $dob = $row['dob'];
                $age = date_diff(date_create($dob), date_create('today'))->y;
                $sec_data = $row['sec_data'];
                $sec_data = json_decode($sec_data, true);
                $business_category = $sec_data[43] ?? '';
                if ($business_category == '--Select One') {
                    $business_category = '<i>Unspecified</i>';
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


                echo "<tr><td>$uid</td><td>$full_name</td><td>$branch</td><td>$business_category</td><td>$age</td><td>$gender</td><td>$principal</td><td>$amount_repaid</td><td>$balance</td><td>$given_date</td><td>$due_date</td><td>$status</td></tr>";
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