<?php
$loansq = "disbursed=1 AND status!=0  $andbranch_loan $andLoanProductId AND  given_date >= '$start_date' AND given_date <= '$end_date'";

$products_array = table_to_obj('o_loan_products', "uid > 0", "100", "uid", "name");
$staff_array = table_to_obj('o_users', "uid > 0", "10000", "uid", "name");
$branches_array = table_to_obj('o_branches', "uid > 0", "10000", "uid", "name");
$statuses_array = table_to_obj('o_loan_statuses', "uid > 0", "10000", "uid", "name");

$customers_available = table_to_array('o_loans', "$loansq", "1000000", "customer_id");
$loan_list = implode(',', table_to_array('o_loans', "$loansq", "1000000", "uid"));
$customer_list  = implode(",", $customers_available);


$cust_names = [];
$cust_ages = [];
$cust_genders = [];
$cust_biz_categories = [];
$sql = "SELECT uid, full_name, dob, gender, JSON_UNQUOTE(JSON_EXTRACT(sec_data, '$.\"43\"')) AS business_category FROM o_customers where uid in ($customer_list) limit 100000";
$customerInfoFetch = mysqli_query($con, $sql);
while ($row = mysqli_fetch_array($customerInfoFetch)) {
    $key = $row['uid'];
    $dob = $row['dob'] ?? "";
    $age = date_diff(date_create($dob), date_create('today'))->y;
    $age = $age > 0 ? $age : '';

    $gender = $row['gender'] ?? "";
    if ($gender == 'M') {
        $gender = 'Male';
    } elseif ($gender == 'F') {
        $gender = 'Female';
    } else {
        $gender = '';
    }

    $business_category = $row['business_category'] ?? "";
    if ($business_category == '--Select One') {
        $business_category = '';
    }

    $cust_names[$key] = $row['full_name'];
    $cust_ages[$key] = $age;
    $cust_genders[$key] = $gender;
    $cust_biz_categories[$key] = $business_category;

}





$interests_array = array();
$penalties_array = array();
$initiation_array = array();

$ado = fetchtable('o_loan_addons', "loan_id in ($loan_list) AND status=1", "uid", "asc", "10000000", "loan_id, addon_id, addon_amount");
while ($ad = mysqli_fetch_array($ado)) {
    $loan_id = $ad['loan_id'];
    $addon_id = $ad['addon_id'];
    $addon_amount = $ad['addon_amount'];



    /////------------Interests[1, 5, 15, 16]
    $interest_addonIds = [1, 5, 8, 14, 15, 16];
    if (in_array($addon_id, $interest_addonIds)) {
        $interests_array = obj_add($interests_array, $loan_id, $addon_amount);
    }

    ///-----------------Initiation fees [2, 3]
    $initiation_addonIds = [2, 3];
    if (in_array($addon_id, $initiation_addonIds)) {
        $initiation_array = obj_add($initiation_array, $loan_id, $addon_amount);
    }
    /////------------Penalties [4, 6, 7]
    $penalties_addonIds = [4, 6, 7];
    if (in_array($addon_id, $penalties_addonIds)) {
        $penalties_array = obj_add($penalties_array, $loan_id, $addon_amount);
    }
}
?>

<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped">
        <thead>
            <tr>
                <th>UID</th>
                <th>Full Name</th>
                <th>Age</th>
                <th>Gender</th> 
                <th>Branch</th>
                <th>Business Category</th>
                <th>Phone</th>
                <th>Product</th>
                <th>Principal</th>
                <th>Interest</th>
                <th>Penalties</th>
                <th>Initiation</th>
                <th>Total Amount</th>
                <th>Total Paid</th>
                <th>Balance</th>
                <th>Transcode</th>
                <th>Disb. Date</th>
                <th>Due Date</th>
                <th>LO</th>
                <th>CO</th>
                <th>Application Mode</th>
                <th>Status</th>
            </tr>
        </thead>
        <?php

        echo "<tbody>";
        $loans = fetchtable('o_loans', "$loansq", "uid", "asc", "10000000", "uid, loan_code, loan_amount,account_number, customer_id, product_id, current_lo, current_co, current_branch, status, total_repayable_amount, total_repaid, loan_balance, transaction_code, application_mode, status, given_date, final_due_date");
        while ($l = mysqli_fetch_array($loans)) {
            $uid = $l['uid'];
            $mobile = $l['account_number'];
            $customer_id = $l['customer_id'];
            $product_id = $l['product_id'];
            $product_name = $products_array[$product_id] ?? '';
            $current_lo = $l['current_lo'];
            $current_co = $l['current_co'];
            $loan_amount = $l['loan_amount'];
            $total_repaid = $l['total_repaid'];
            $loan_balance = $l['loan_balance'];
            $current_branch = $l['current_branch'];
            $total_repayable_amount = $l['total_repayable_amount'];
            $transaction_code = $l['transaction_code'];
            $application_mode = $l['application_mode'];
            $given_date = $l['given_date'];
            $final_due_date = $l['final_due_date'];
            $status = $l['status'];

            $interest = $interests_array[$uid] ?? 0;
            $penalties = $penalties_array[$uid] ?? 0;
            $initiation = $initiation_array[$uid] ?? 0;
            $customer_name = $cust_names[$customer_id] ?? "";
            $gender = $cust_genders[$customer_id] ?? "";
            $age = $cust_ages[$customer_id] ?? "";
            $business_category = $cust_biz_categories[$customer_id] ?? "";



            echo "<tr><td>$uid</td>
        <td>$customer_name</td>
        <td>$age</td>
        <td>$gender</td>
        <td>" . $branches_array[$current_branch] . "</td>
        <td>$business_category</td>
        <td>$mobile</td>
        <td>$product_name</td>
        <td>$loan_amount</td>
        <td>$interest</td>
        <td>$penalties</td>
        <td>$initiation</td>
        <td>$total_repayable_amount</td>
        <td>$total_repaid</td>
        <td>$loan_balance</td>
        <td>$transaction_code</td>
        <td>$given_date</td>
        <td>$final_due_date</td>
        <td>" . $staff_array[$current_lo] . "</td>
        <td>" . $staff_array[$current_co] . "</td>
        <td>$application_mode</td>
        <td>" . $statuses_array[$status] . "</td>
    </tr>";
        } ?>

        </tbody>
    </table>
</div>