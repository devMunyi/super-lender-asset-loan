<?php

$loanCustomerQuery = "SELECT `uid` as loan_id, customer_id FROM o_loans where disbursed = 1 AND paid = 0 AND status NOT IN (0, 10) AND JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.CRB_LISTED')) = '1' AND JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.CRB_LISTED_DATE')) BETWEEN '$start_date' AND '$end_date' $andbranch_loan";

$loanCustomerResult = mysqli_query($con, $loanCustomerQuery);

$customer_ids = []; // expected output => [1, 2, 3, 4, 5]
$loan_ids = []; // expected output => [1, 2, 3, 4, 5]

while ($row = mysqli_fetch_array($loanCustomerResult)) {
    $customer_id = $row['customer_id'];
    $loan_id = $row['loan_id'];
    if (!in_array($customer_id, $customer_ids) && $customer_id > 0) {
        array_push($customer_ids, $customer_id);
    }
    if (!in_array($loan_id, $loan_ids) && $loan_id > 0) {
        array_push($loan_ids, $loan_id);
    }
}

$customer_list  =  implode(",", $customer_ids);
$loan_list  =  implode(",", $loan_ids);



$sql = "SELECT l.uid, c.uid as customer_id, c.full_name, c.national_id, b.name as Branch , l.account_number as Phone, l.loan_amount as Principal, l.total_repayable_amount as TotalAmount, l.total_repaid as Total_paid, l.loan_balance as Balance, l.transaction_code as Transcode, l.given_date as Disbursement_Date, l.final_due_date as Due_Date, JSON_UNQUOTE(JSON_EXTRACT(l.other_info, '$.CRB_LISTED_DATE')) as Listed_Date, l.disbursed_amount as Amount_Disbursed, l.total_addons as Interest, lo.name as LO, co.name as CO, ls.name as Status FROM o_loans l left join o_customers c on c.uid = l.customer_id left join o_loan_statuses ls on l.status = ls.uid left join o_branches b on l.current_branch = b.uid left join o_users lo on l.current_lo = lo.uid  left join o_users co on l.current_co = co.uid where l.uid IN ($loan_list) order by c.uid DESC LIMIT 1000000";


$sql_for_alternative_nos = "SELECT customer_id, `value` AS phoneNumber FROM o_customer_contacts WHERE contact_type = 1 AND customer_id IN ($customer_list) order by customer_id DESC LIMIT 1000000";


$result = mysqli_query($con, $sql);
$result_for_alternative_nos = mysqli_query($con, $sql_for_alternative_nos);

$alternative_nos = [];
while ($row = mysqli_fetch_array($result_for_alternative_nos)) {
    $key = $row['customer_id'];
    
    // Check if the key exists in the array
    if (array_key_exists($key, $alternative_nos)) {
        // Push the value to the array
        array_push($alternative_nos[$key], $row['phoneNumber']);
    } else {
        // Create a new array with the value
        $alternative_nos[$key] = [$row['phoneNumber']];
    }
}

?>
<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>UID</th>
                <th>Fullname</th>
                <th>National ID</th>
                <th>Branch</th>
                <th>Primary Phone</th>
                <th>Other Phone(s)</th>
                <th>Amount Disbursed</th>
                <th>Interest</th>
                <th>Total Amount</th>
                <th>Total Paid</th>
                <th>Balance</th>
                <th>Transcode</th>
                <th>Disbursement Date</th>
                <th>Due Date</th>
                <th>Listed Date</th>
                <th>LO</th>
                <th>CO</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $total_amount = $total_paid = $total_balance = $total_amount_disbursed = $total_interest = 0;
            while ($row = mysqli_fetch_array($result)) {
                $uid = $row['uid'];
                $customer_id = $row['customer_id'] ?? 0;
                $enc_customer_id = encurl($customer_id);
                $full_name = $row['full_name'] ?? '';
                $national_id = $row['national_id'] ?? '';
                $branch = $row['Branch'] ?? '';
                $phone = $row['Phone'] ?? '';
                $alternative_no = '';
                if($customer_id > 0 && $enc_customer_id > 0){
                    $alternative_no = $alternative_nos[$customer_id] ?? $alternative_nos[$enc_customer_id] ?? '';
                }
                
                if(!empty($alternative_no)){
                    $alternative_no =  implode(", ", $alternative_no);
                }
                $repayable_amount = $row['TotalAmount'] ?? 0;
                $total_paid = $row['Total_paid'] ?? 0;
                $balance = $row['Balance'] ?? 0;
                $transcode = $row['Transcode'] ?? '';
                $disbursement_date = $row['Disbursement_Date'];
                $due_date = $row['Due_Date'] ?? '';
                $listed_date = $row['Listed_Date'] ?? '';
                $amount_disbursed = $row['Amount_Disbursed'] ?? 0;
                $interest = $row['Interest'] ?? 0;
                $lo = $row['LO'] ?? '';
                $co = $row['CO'] ?? '';
                $status = $row['Status'] ?? '';

                
                echo "<tr><td>$uid</td><td>$full_name</td><td>$national_id</td><td>$branch</td><td>$phone</td><td>$alternative_no</td><td>$amount_disbursed</td><td>$interest</td><td>$repayable_amount</td><td>$total_paid</td><td>$balance</td><td>$transcode</td><td>$disbursement_date</td><td>$due_date</td><td>$listed_date</td><td>$lo</td><td>$co</td><td>$status</td></tr>";

                $total_amount += $repayable_amount;
                $total_paid += $total_paid;
                if($balance > 0){
                    $total_balance += $balance;
                }
                $total_amount_disbursed += $amount_disbursed;
                $total_interest += $interest;

            }



            ?>
        </tbody>

        <tfoot>
            <tr>
                <!-- totals --> 
                <th colspan="6">Total</th>
                <th><?php echo number_format($total_amount_disbursed, 2); ?></th>
                <th><?php echo number_format($total_interest, 2); ?></th>
                <th><?php echo number_format($total_amount, 2); ?></th>
                <th><?php echo number_format($total_paid, 2); ?></th>
                <th><?php echo number_format($total_balance, 2); ?></th>
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