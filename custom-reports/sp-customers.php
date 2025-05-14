<?php

$andbranch_client_ = str_replace('branch', 'c.branch', $andbranch_client);
$sql = "SELECT
  c.uid, 
  c.full_name AS `Name`, 
  c.gender,
  b.name AS Branch, 
  c.sec_data, 
  c.dob,
  cs.name AS `Status` 
FROM o_customers c 
LEFT JOIN o_branches b ON b.uid = c.branch 
LEFT JOIN o_customer_statuses cs ON cs.code = c.status WHERE c.status = 1 AND DATE(c.added_date) BETWEEN '$start_date' AND '$end_date' $andbranch_client_ order by c.uid DESC;
";

$result = mysqli_query($con, $sql);


?>
<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>UID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Branch</th>
                <th>Name of Business</th>
                <th>Type of Business</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php



            while ($row = mysqli_fetch_array($result)) {
                $uid = $row['uid'];
                $name = $row['Name'];
                $branch = $row['Branch'] ?? '';
                $sec_data = $row['sec_data'];
                $sec_data = json_decode($sec_data, true);
                $business_name = $sec_data[16] ?? '';
                $business_type = $sec_data[17] ?? '';
                $dob = $row['dob'];
                $age = date_diff(date_create($dob), date_create('today'))->y;
                $age = $age > 0 ? $age : '';

                if ($business_type == '--Select One') {
                    $business_type = '';
                }


                $gender = $row['gender'] ?? '';
                if ($gender == 'M') {
                    $gender = 'Male';
                } elseif ($gender == 'F') {
                    $gender = 'Female';
                } else {
                    $gender = '';
                }

                $status = $row['Status'] ?? '';


                echo "<tr><td>$uid</td><td>$name</td><td>$age</td><td>$gender</td><td>$branch</td><td>$business_name</td><td>$business_type</td><td>$status</td></tr>";

            }

            ?>
        </tbody>

        <tfoot>
            <tr>
            </tr>
        </tfoot>
    </table>
</div>