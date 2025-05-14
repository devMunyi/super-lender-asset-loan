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

$inactive_customers_list = implode(',', $inactive_customers);

$customers_sql  = "SELECT c.uid, c.full_name, c.primary_mobile, c.national_id, c.gender, c.loan_limit, b.name as branch, cs.name as status FROM o_customers c LEFT JOIN o_branches b ON b.uid = c.branch LEFT JOIN o_customer_statuses cs ON cs.code = c.status WHERE c.uid IN ($inactive_customers_list) AND c.status = 1 $andbranch_client_ ORDER BY c.uid ASC";

// echo $customers_sql;

$customers = mysqli_query($con, $customers_sql);

?>

<div class="col-sm-12">
  <table id="example2" class="table table-condensed table-striped table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Customer Name</th>
        <th>Primary Mobile </th>
        <th>National ID</th>
        <th>Gender</th>
        <th>Loan Limit</th>
        <th>Branch</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php

            while ($c = mysqli_fetch_assoc($customers)) {
                $uid = $c['uid'] ?? '';
                // if ($customer_ks[$uid] == 5) {
                $full_name = $c['full_name'] ?? '';
                $primary_mobile = $c['primary_mobile'] ?? '';
                $national_id = $c['national_id'] ?? '';
                $gender = $c['gender'] ?? '';
                $loan_limit = $c['loan_limit'] ?? '';
                $branch = $c['branch'] ?? '';
                $status = $c['status'] ?? '';

                echo
                "<tr>
                        <td>$uid</td>
                        <td>$full_name</td>
                        <td>$primary_mobile</td>
                        <td>$national_id</td>
                        <td>$gender</td>
                        <td>$loan_limit</td>
                        <td>$branch</td>
                        <td>$status</td>
                    </tr>";
            }
            ?>
    </tbody>

    <tfoot>
      <tr>
        <th>ID</th>
        <th>Customer Name</th>
        <th>Primary Mobile </th>
        <th>National ID</th>
        <th>Gender</th>
        <th>Loan Limit</th>
        <th>Branch</th>
        <th>Status</th>
      </tr>
    </tfoot>
  </table>
</div>

<?php 
// include close connection
include_once("../configs/close_connection.inc");