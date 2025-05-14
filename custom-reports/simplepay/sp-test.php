<?php

$branch = $_GET['branch'] ?? 0;

if ($branch > 0) {
    $branch = decurl($branch);
    $andloanbranch = " AND l.current_branch = $branch";
}

$customers_array = table_to_array('o_loans', "disbursed = 1 AND paid = 0 AND status NOT IN (0, 10) AND final_due_date  BETWEEN '$start_date' AND '$end_date' $andloanbranch", "customer_id", "customer_id");

$andLoanCustomers = "";
$andCustomerAlternativeNos = "";
if(count($customers_array) > 0){
    // implode the array to a string
    $customer_list  =  implode(",", $customers_array);
    $andLoanCustomers =  " AND l.customer_id IN ($customer_list)";
    $andCustomerAlternativeNos = " AND customer_id IN ($customer_list)";
}


$sql = "SELECT l.uid, c.uid as customer_id, c.full_name, c.national_id, b.name as Branch , l.account_number as Phone, l.loan_amount as Principal, l.total_repayable_amount as TotalAmount, l.total_repaid as Total_paid, l.loan_balance as Balance, l.transaction_code as Transcode, l.given_date as Disbursement_Date, l.final_due_date as Due_Date, l.disbursed_amount as Amount_Disbursed, l.total_addons as Interest, lo.name as LO, co.name as CO, ls.name as Status FROM o_loans l left join o_customers c on c.uid = l.customer_id left join o_loan_statuses ls on l.status = ls.uid left join o_branches b on l.current_branch = b.uid left join o_users lo on l.current_lo = lo.uid  left join o_users co on l.current_co = co.uid  where l.disbursed = 1 AND l.paid = 0 AND l.status NOT IN (0, 10) AND l.final_due_date  BETWEEN '$start_date' AND '$end_date' $andLoanCustomers $andloanbranch order by c.uid DESC LIMIT 1000000";

$sql_for_alternative_nos = "SELECT customer_id, `value` AS phoneNumber FROM o_customer_contacts WHERE contact_type = 1 $andCustomerAlternativeNos order by customer_id DESC LIMIT 1000000";


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
                <th>Principal</th>
                <th>Total Amount</th>
                <th>Total Paid</th>
                <th>Balance</th>
                <th>Transcode</th>
                <th>Disbursement Date</th>
                <th>Due Date</th>
                <th>Amount Disbursed</th>
                <th>Interest</th>
                <th>LO</th>
                <th>CO</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $total_principal = $total_amount = $total_paid = $total_balance = $total_amount_disbursed = $total_interest = 0;
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
                $principal = $row['Principal'] ?? 0;
                $total_amount = $row['TotalAmount'] ?? 0;
                $total_paid = $row['Total_paid'] ?? 0;
                $balance = $row['Balance'] ?? 0;
                $transcode = $row['Transcode'] ?? '';
                $disbursement_date = $row['Disbursement_Date'];
                $due_date = $row['Due_Date'] ?? '';
                $amount_disbursed = $row['Amount_Disbursed'] ?? 0;
                $interest = $row['Interest'] ?? 0;
                $lo = $row['LO'] ?? '';
                $co = $row['CO'] ?? '';
                $status = $row['Status'] ?? '';

                $total_principal += $principal;
                $total_amount += $total_amount;
                $total_paid += $total_paid;
                if($balance > 0){
                    $total_balance += $balance;
                }
                $total_amount_disbursed += $amount_disbursed;
                $total_interest += $interest;

                
                echo "<tr><td>$uid</td><td>$full_name</td><td>$national_id</td><td>$branch</td><td>$phone</td><td>$alternative_no</td><td>$principal</td><td>$total_amount</td><td>$total_paid</td><td>$balance</td><td>$transcode</td><td>$disbursement_date</td><td>$due_date</td><td>$amount_disbursed</td><td>$interest</td><td>$lo</td><td>$co</td><td>$status</td></tr>";

            }



            ?>
        </tbody>

        <tfoot>
            <tr>
                <!-- totals --> 
                <th colspan="6">Total</th>
                <th><?php echo number_format($total_principal, 2); ?></th>
                <th><?php echo number_format($total_amount, 2); ?></th>
                <th><?php echo number_format($total_paid, 2); ?></th>
                <th><?php echo number_format($total_balance, 2); ?></th>
                <th></th>
                <th></th>
                <th></th>
                <th><?php echo number_format($total_amount_disbursed, 2); ?></th>
                <th><?php echo number_format($total_interest, 2); ?></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>