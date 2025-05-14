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
        c.full_name, 
        c.primary_mobile, 
        c.national_id, 
        c.gender, 
        c.loan_limit, 
        cd.dormant_date as dormant_at, 
        b.name as branch, 
        cs.name as status
    FROM 
        o_customers c
    LEFT JOIN 
        o_branches b ON b.uid = c.branch
    LEFT JOIN 
        o_customer_statuses cs ON cs.code = c.status
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
        <th>Dormant At</th>
        <th>Branch</th>
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
                $dormant_at = $c['dormant_at'] ?? '';
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
                        <td>$dormant_at</td>
                        <td>$branch</td>
                    </tr>";
                // }
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
        <th>Dormant At</th>
        <th>Branch</th>
      </tr>
    </tfoot>
  </table>
</div>

<?php 
// include close connection
include_once("../configs/close_connection.inc");