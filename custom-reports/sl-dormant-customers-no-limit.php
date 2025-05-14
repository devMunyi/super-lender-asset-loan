<?php

$branchs = table_to_obj('o_branches', "uid > 0", "1000", "uid", "name");

try {
    $sql = "SELECT customer_id FROM o_loans  WHERE final_due_date BETWEEN '$start_date' AND '$end_date' 
        AND disbursed = 1 
        AND `status` != 0
        $andbranch_loan 
        ORDER BY `uid` ASC";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($con));
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$customer_ids = [];
// $customer_ks = [];

// Fetch each row and add the customer_id to the array
while ($row = mysqli_fetch_assoc($result)) {
    $cust_id = $row['customer_id'];
    // $l_status = $row['status'];
    $customer_ids[] = $cust_id;

    // $customer_ks[$cust_id] = $l_status;
}


if ($has_archive == 1) {

    try {
        $sql = "SELECT customer_id FROM o_loans  WHERE final_due_date BETWEEN '$start_date' AND '$end_date'  
        AND disbursed = 1 
        AND `status` != 0
        $andbranch_loan 

        OR (final_due_date > '$end_date' AND disbursed = 1 AND `status` != 0 $andbranch_loan)

        ORDER BY `uid` ASC";

        $archive_result = mysqli_query($con1, $sql);

        if (!$archive_result) {
            throw new Exception("Query failed: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }

    // $archived_customer_ks = [];
    $archived_customer_ids = [];

    // Fetch each row and add the customer_id to the array
    while ($row = mysqli_fetch_assoc($archive_result)) {
        $cust = $row['customer_id'];
        // $l_status = $row['status'];
        if ($cust > 0) {
            $archived_customer_ids[] = $cust;
            // $archived_customer_ks[$cust] = $l_status;
        }
    }

    // merge the two arrays
    $customer_ids = array_merge($customer_ids, $archived_customer_ids);
    // $customer_ks = array_merge($customer_ks, $archived_customer_ks);

    // remove duplicates 
    $customer_ids = array_unique($customer_ids);

    // convert to comma separated string
    $customer_list = implode(',', $customer_ids);
} else {
    $customer_list = implode(',', $customer_ids);
}

/////--------------Customers with loans

$customers_with_loans = table_to_array('o_loans', "(disbursed=1 AND paid=0 AND status!=0 AND customer_id in ($customer_list)) OR (final_due_date > '$end_date' AND disbursed=1 AND paid=0 AND status!=0)", "10000000", "customer_id");
$customers_with_loans_list = implode(',', $customers_with_loans);
///----------------End of customers with loans


$hq_uid = $hq_uid ? $hq_uid : 1;
if($exempt_hq_from_report == 1 && $hq_uid > 0){
    $andExemptHQ = " AND branch != $hq_uid";
}else {
    $andExemptHQ = "";

}

$customers = fetchtable('o_customers', "uid IN ($customer_list) AND uid not in ($customers_with_loans_list) AND loan_limit < 1 $andExemptHQ", "uid", "ASC", 10000000, "uid, full_name, primary_mobile, national_id, gender, loan_limit, added_date, branch");


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
                <th>Added Date</th>
                <th>Branch</th>
            </tr>
        </thead>
        <tbody>
            <?php

            while ($c = mysqli_fetch_assoc($customers)) {
                $uid = $c['uid'] ?? 'N/A';
                // if ($customer_ks[$uid] == 5) {
                $full_name = $c['full_name'] ?? 'N/A';
                $primary_mobile = $c['primary_mobile'] ?? 'N/A';
                $national_id = $c['national_id'] ?? 'N/A';
                $gender = $c['gender'] ?? 'N/A';
                $loan_limit = $c['loan_limit'] ?? 'N/A';
                $added_date = $c['added_date'] ?? 'N/A';
                $branch_ = $c['branch'] ?? 'N/A';
                $branch = $branchs[$branch_] ?? 'N/A';

                echo
                "<tr>
                        <td>$uid</td>
                        <td>$full_name</td>
                        <td>$primary_mobile</td>
                        <td>$national_id</td>
                        <td>$gender</td>
                        <td>$loan_limit</td>
                        <td>$added_date</td>
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
                <th>Added Date</th>
                <th>Branch</th>
            </tr>
        </tfoot>
    </table>
</div>

<?php 
// include close connection
include_once("../configs/close_connection.inc");