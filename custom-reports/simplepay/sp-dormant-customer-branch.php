<?php


$query1 = "SELECT uid, customer_id, MAX(dormant_date) AS dormant_date FROM o_customer_dormancy WHERE dormant_date BETWEEN '$start_date' AND '$end_date' GROUP BY customer_id;";

$result1 = mysqli_query($con, $query1);
$target_dormancy_ids = [];
while ($row = mysqli_fetch_assoc($result1)) {
    $target_dormancy_ids[] = $row['uid'];
}

$dormant_id_list = implode(',', $target_dormancy_ids);

// Replace 'branch' with 'c.branch' for aliasing in the query
$andbranch_client_ = str_replace('branch', 'c.branch', $andbranch_client);
$query2 = "
    SELECT 
        c.uid, 
        c.branch
    FROM 
        o_customers c
    INNER JOIN 
        o_customer_dormancy cd ON cd.customer_id = c.uid
    WHERE 
        cd.uid IN ($dormant_id_list)
        AND c.status = 1
        AND JSON_UNQUOTE(JSON_EXTRACT(c.other_info, '$.DORMANT_ID')) > 0
        $andbranch_client_
    ORDER BY 
        cd.dormant_date ASC;
";

// Step 4: Execute the second query
$customers = mysqli_query($con, $query2);

$dormant_customer_branchwise = [];
$dormant_customer_uids = [];
while ($row = mysqli_fetch_assoc($customers)) {
    if (isset($dormant_customer_branchwise[$row['branch']])) {
        $dormant_customer_branchwise[$row['branch']] += 1;
    } else {
        $dormant_customer_branchwise[$row['branch']] = 1;
    }

    $dormant_customer_uids[] = $row['uid'];
}

// inactive customer is active_cleared customer and not in $dormant_id_list
$andbranch_loan_ = str_replace('current_branch', 'l.current_branch', $andbranch_loan);
$loans_query = "SELECT l.customer_id, l.current_branch, l.status FROM o_loans l INNER JOIN o_customers c ON c.uid = l.customer_id WHERE l.given_date BETWEEN '$start_date' AND '$end_date' $andbranch_loan_ AND l.disbursed=1 AND l.status!=0 AND c.status = 1 order by l.uid ASC";

// echo "loans_query: $loans_query <br>";

$loans = mysqli_query($con, $loans_query); 

$customer_loan_status_kv  = [];
$active_customer_branch_kv = [];
while($loan = mysqli_fetch_assoc($loans)) {

    $status = $loan['status'];
    $customer_id = $loan['customer_id'];

    $active_customer_branch_kv[$customer_id] = $loan['current_branch'];
    $customer_loan_status_kv[$customer_id] = $status;
    
}


$inactive_customers = [];

// loop through $customer_loan_status  and if status = 5 then add to $inactive_customers
foreach ($customer_loan_status_kv as $customer_id => $status) {
    if($status == 5 && !in_array($customer_id, $dormant_customer_uids)) {
        $inactive_customers[] = $customer_id;
    }
}


$inactive_customers_branchwise = [];
foreach ($inactive_customers as $customer_id) {
    $branch = $active_customer_branch_kv[$customer_id] ?? null; // Handle missing branch

    if ($branch === null) {
        continue; // Skip if branch data is missing
    }

    if (isset($inactive_customers_branchwise[$branch])) {
        $inactive_customers_branchwise[$branch] += 1;
    } else {
        $inactive_customers_branchwise[$branch] = 1;
    }
}



?>

<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>Branch</th>
                <th>Dormant Customers</th>
                <th>Inactive Customers</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $branches = fetchtable('o_branches',"uid > 1 $andbranch1","uid","asc","1000","uid, name");

            $sum_dormant_customers = $sum_inactive_customers = 0;
            while($branch = mysqli_fetch_assoc($branches)) {
                $branch_id = $branch['uid'];
                $branch_name = $branch['name'];

                if (in_array(strtolower($branch_name), $hq_branch_names) && $omit_hq_from_report == 1) {
                    continue;
                }

                $dormant_customers = $dormant_customer_branchwise[$branch_id] ?? 0;
                $inactive_customers = $inactive_customers_branchwise[$branch_id] ?? 0;

                $branch_id_enc = encurl($branch_id);

                $dormant_customers_url = "<a href=\"reports?hreport=sp-dormant-customers.php&from=$start_date&to=$end_date&branch=$branch_id_enc\" target='_blank'><i class='fa fa-external-link-square'></i></a>";

                $inactive_customers_url = "<a href=\"reports?hreport=sp-inactive-customers.php&from=$start_date&to=$end_date&branch=$branch_id_enc\" target='_blank'><i class='fa fa-external-link-square'></i></a>";

                echo "<tr><td>$branch_name</td><td>$dormant_customers $dormant_customers_url</td><td>$inactive_customers $inactive_customers_url</td></tr>";

                $sum_dormant_customers += $dormant_customers;
                $sum_inactive_customers += $inactive_customers;

            }
            ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="1">Total</th>
                <th><?php echo $sum_dormant_customers; ?></th>
                <th><?php echo $sum_inactive_customers; ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<?php
// include close connection
include_once("../configs/close_connection.inc");
