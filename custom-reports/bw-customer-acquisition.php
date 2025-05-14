<?php

$custDetailsQuery = "SELECT c.uid as customer_id FROM o_customers c where DATE(c.added_date) between '$start_date' AND '$end_date' AND c.status = 1 $anduserbranch";

$custDetailsResult = mysqli_query($con, $custDetailsQuery);

$customer_uids  = [];
while ($row = mysqli_fetch_array($custDetailsResult)) {
    $customer_uids[] = $row['customer_id'];
}

// implode custoemr as string
$customer_list = implode(',', $customer_uids);


// get customer referees
$customerRefereesQuery = "SELECT customer_id, referee_name, mobile_no as referee_phone FROM o_customer_referees WHERE customer_id IN ($customer_list) AND status = 1";


$customerRefereesResult = mysqli_query($con, $customerRefereesQuery);

// expected output 
// $cutoemrReferees = [1 => ['referee_name' => 'John Doe', 'referee_phone' => '0712345678'], 2 => ['referee_name' => 'Jane Doe', 'referee_phone' => '0712345678']];

$customerReferees = [];
while ($row = mysqli_fetch_array($customerRefereesResult)) {
    $key = $row['customer_id'];

    // check if key exists so as not to overwrite the value as customer can have many refrees 
    if (array_key_exists($key, $customerReferees)) {
        array_push($customerReferees[$key], ['referee_name' => $row['referee_name'], 'referee_phone' => $row['referee_phone']]);
    } else {
        $customerReferees[$key] = [['referee_name' => $row['referee_name'], 'referee_phone' => $row['referee_phone']]];
    }
}

$customerAcquisationQuery = "SELECT c.uid, c.full_name, c.primary_mobile, o_branches.name as branch_name, o_users.name as Added_by, c.physical_address, c.national_id, c.dob, c.added_date, c.loan_limit, l.loan_amount, l.	total_repayable_amount, l.total_repaid, l.loan_balance, ls.name as status, c.sec_data FROM o_customers c left join o_branches on o_branches.uid = c.branch left join o_users on o_users.uid = c.added_by LEFT JOIN o_loans l ON l.customer_id = c.uid LEFT JOIN o_loan_statuses ls ON ls.uid = l.status where c.uid IN ($customer_list)";

$customerAcquisationResult = mysqli_query($con, $customerAcquisationQuery);

/*
Business Name => comes from sec_data, key 16
Business Direction => comes from sec_data, key 19
Referees can be gotten from $customerReferees
*/

?>

<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>uid</th>
                <th>Full Name</th>
                <th>Primary Mobile</th>
                <th>Referees</th>
                <th>Business Name</th>
                <th>Business Direction</th>
                <th>Branch</th>
                <th>Added By</th>
                <th>Physical Address</th>
                <th>National ID</th>
                <th>DOB</th>
                <th>Added Date</th>
                <th>Loan Limit</th>
                <th>Loan Amount</th>
                <th>Amount Repayable </th>
                <th>Total Repaid</th>
                <th>Loan Balance</th>
                <th>Status</th>

            </tr>
        </thead>
        <tbody>
            <?php

            $sum_loan_amount = $sum_repayable_amount = $sum_total_repaid = $sum_loan_balance = 0;
            while ($row = mysqli_fetch_array($customerAcquisationResult)) {
                $uid = $row['uid'];
                $full_name = $row['full_name'];
                $primary_mobile = $row['primary_mobile'];
                $branch = $row['branch_name'] ?? '';
                $added_by = $row['Added_by'] ?? '';
                $physical_address = $row['physical_address'];
                $national_id = $row['national_id'];
                $dob = $row['dob'];
                $added_date = $row['added_date'];
                $loan_limit = $row['loan_limit'] ?? 0;
                $loan_amount = $row['loan_amount'] ?? 0;
                $amount_repayable = $row['total_repayable_amount'] ?? 0;
                $total_repaid = $row['total_repaid'] ?? 0;
                $loan_balance = $row['loan_balance'] ?? 0;
                $status = $row['status'] ?? "";

                $referees = $customerReferees[$uid];
                $business_name = '';
                $business_direction = '';
                $sec_data = json_decode($row['sec_data'], true);
                if (isset($sec_data["16"])) {
                    $business_name = $sec_data["16"];
                }
                if (isset($sec_data["19"])) {
                    $business_direction = $sec_data["19"];
                }

                $referees_html = '';
                foreach ($referees as $referee) {
                    $referees_html .= $referee['referee_name'] . ' - ' . $referee['referee_phone'] . ',<br><br>';
                }

                // strip last comma
                $referees_html = rtrim($referees_html, ',<br><br>');



                echo "<tr>
                    <td>$uid</td>
                    <td>$full_name</td>
                    <td>$primary_mobile</td>
                    <td>$referees_html</td>
                    <td>$business_name</td>
                    <td>$business_direction</td>
                    <td>$branch</td>
                    <td>$added_by</td>
                    <td>$physical_address</td>
                    <td>$national_id</td>
                    <td>$dob</td>
                    <td>$added_date</td>
                    <td>$loan_limit</td>
                    <td>$loan_amount</td>
                    <td>$amount_repayable</td>
                    <td>$total_repaid</td>
                    <td>$loan_balance</td>
                    <td>$status</td></tr>";

                $sum_loan_amount += $loan_amount;
                $sum_repayable_amount += $amount_repayable;
                $sum_total_repaid += $total_repaid;
                $sum_loan_balance += $loan_balance;

            }


            ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="13">Total</th>

                <th><?php echo money($sum_loan_amount); ?></th>
                <th><?php echo money($sum_repayable_amount); ?></th>
                <th><?php echo money($sum_total_repaid); ?></th>
                <th><?php echo money($sum_loan_balance); ?></th>
                <th></th>
            </tr>
        </tfoot>
    </table>


</div>