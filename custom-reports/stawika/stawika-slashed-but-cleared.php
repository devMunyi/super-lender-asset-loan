<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

?>


<table class="table table-condensed table-striped" id="example2">
    <thead>
    <tr><th>UID</th>
        <th>Full Name</th>
        <th>Phone</th>
        <th>email</th>
        <th>NationalID</th>
        <th>Credit Limit</th>
        <th>Cleared Loans</th>
        <th>Join Date</th>

    </tr>
    </thead>
<?php
$loans_per_customer = array();
$all_customers = table_to_array('o_customer_limits',"status = 1 AND (comments LIKE '%Halved automatically by system%' OR  comments LIKE '%Zeroed automatically by system%') AND date(given_date) BETWEEN '$start_date' AND '$end_date'","1000000","customer_uid");
$all_custs = implode(',', $all_customers);
$loans = fetchtable('o_loans',"disbursed = 1 AND status != 0 AND paid = 1 AND customer_id in ($all_custs)","uid","asc","10000000","uid, customer_id");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $customer_id = $l['customer_id'];
    $loans_per_customer = obj_add($loans_per_customer, $customer_id, 1);


}



$uncleared = table_to_array('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND customer_id in ($all_custs)","1000000000","customer_id");




echo "<tbody>";
        $customers = fetchtable('o_customers',"uid in ($all_custs)","uid","asc","1000000","uid, full_name, primary_mobile, national_id, loan_limit, DATE(added_date) as added_date");
        while($c = mysqli_fetch_array($customers)) {
            $cuid = $c['uid'];
            $full_name = $c['full_name'];
            $primary_mobile = $c['primary_mobile'];
            $national_id = $c['national_id'];
            $loan_limit = $c['loan_limit'];
            $email_address = $c['email_address'];
            $added_date = $c['added_date'];

            $cleared_loans = $loans_per_customer[$cuid];

        if((in_array($cuid, $uncleared)) != 1) {

            echo "<tr><td>$cuid</td>
        <td>$full_name</td>
        <td>$primary_mobile</td>
        <td>$email_address</td>
        <td>$national_id</td>
        <td>$loan_limit</td>
        <td>$cleared_loans</td>
        <td>$added_date</td>
       
    </tr>";
        }
        }
echo "</tbody>";
?>
</table>


