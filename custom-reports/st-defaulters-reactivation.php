<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

?>


<table class="table table-condensed table-striped" id="example2">
    <thead>
    <tr>
        <th>UID</th>
        <th>Full Name</th>
        <th>Phone</th>
        <th>email</th>
        <th>NationalID</th>
        <th>Total Loans</th>
        <th>Last Loan Date</th>
        <th>Last Loan Due</th>
        <th>Due Ago</th>
        <th>Last Payment Date</th>
        <th>Last Payment Ago</th>
        <th>Days Late</th>
        <th>Last Loan Amount</th>
    </tr>
    </thead>
<?php
$loans_per_customer = array();
$all_customers = table_to_array('o_loans',"disbursed = 1 AND status!=0 AND given_date BETWEEN '$start_date' AND '$end_date'","1000000","customer_id");

$customers_unique = array_unique($all_customers);

$all_custs = implode(',', $customers_unique);
$last_loan_date = array();
$last_due_date = array();
$last_amount = array();

//$last_payment_dates = table_to_obj('o_incoming_payments',"customer_id in ($all_custs)","1000000","customer_id","payment_date");
$last_payment_date = fetchtable('o_incoming_payments',"customer_id in ($all_custs) AND status=1","uid","asc","10000000","customer_id, payment_date");
while($lp = mysqli_fetch_array($last_payment_date)){
    $cust_id = $lp['customer_id'];
    $p_date = $lp['payment_date'];
    $last_payment_dates[$cust_id] = $p_date;
}

$loans = fetchtable('o_loans',"disbursed = 1 AND status != 0 AND paid = 1 AND customer_id in ($all_custs)","uid","asc","10000000","uid, customer_id, given_date, final_due_date, loan_amount");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $customer_id = $l['customer_id'];
    $given_date = $l['given_date'];
    $due_date = $l['final_due_date'];
    $loan_amount = $l['loan_amount'];
    $last_loan_date[$customer_id] = $given_date;
    $last_due_date[$customer_id] = $due_date;
    $last_amount[$customer_id] = $loan_amount;


    $loans_per_customer = obj_add($loans_per_customer, $customer_id, 1);

}

$uncleared = table_to_array('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND customer_id in ($all_custs)","1000000000","customer_id");

$uncleared_list = implode(',', $uncleared);




echo "<tbody>";
        $customers = fetchtable('o_customers',"uid in ($all_custs) AND uid not in ($uncleared_list) AND loan_limit < 100","uid","asc","1000000","uid, full_name, primary_mobile, national_id, loan_limit, DATE(added_date) as added_date");
        while($c = mysqli_fetch_array($customers)) {
            $cuid = $c['uid'];
            $full_name = $c['full_name'];
            $primary_mobile = $c['primary_mobile'];
            $national_id = $c['national_id'];
            $loan_limit = $c['loan_limit'];
            $email_address = $c['email_address'];
            $added_date = $c['added_date'];
            $loan_given_date = $last_loan_date[$cuid];
            $loan_due_date = $last_due_date[$cuid];
            $last_paym = $last_payment_dates[$cuid];
            $last_paym_ago = datediff3($last_paym, $date);

            $last_ago = datediff3($loan_due_date, $date);

            $days_late = $last_ago - $last_paym_ago;

            $total_loans = $loans_per_customer[$cuid];
            $last_amount_value = $last_amount[$cuid];



            echo "<tr>
         <td>$cuid</td>
        <td>$full_name</td>
        <td>$primary_mobile</td>
        <td>$email_address</td>
        <td>$national_id</td>
        <td>$total_loans</td>
        <td>$loan_given_date</td>
        <td>$loan_due_date</td>
        <td>$last_ago</td>
        <td>$last_paym</td>
        <td>$last_paym_ago</td>
        <td>$days_late</td>
        <td>$last_amount_value</td>
       
    </tr>";

        }
echo "</tbody>";
?>
</table>


