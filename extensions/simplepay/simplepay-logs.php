<!DOCTYPE html>
<html>
<head>
    <title>Logs</title>
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="../bower_components/datatables.net-bs/css/buttons.dataTables.min.css">
</head>
<body>


<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$staff_names = array();
$staff_emails = array();

$all_staff = fetchtable('o_users',"uid > 0","uid","asc","100000","uid, name, email");
while($st = mysqli_fetch_array($all_staff)){
    $staff_uid = $st['uid'];
    $staff_name = $st['name'];
    $staff_email = $st['email'];
    $staff_names[$staff_uid] = $staff_name;
    $staff_emails[$staff_uid] = $staff_email;

}

////-----Log Loans
$logs_loans = table_to_array('o_events',"event_details LIKE '%Loan moved to disbursement by %' AND tbl='o_loans' AND DATE(event_date) as ed BETWEEN '2023-01-01' AND '2023-01-31' ","10000000","fld");

$all_loans_string = implode(',', $logs_loans);

$loan_amounts = array();
$loan_balances = array();
$loan_statuses = array();
$all_customers = array();
$phone_numbers = array();
$loan_customer = array();
$statuses = table_to_obj('o_loan_statuses',"uid >0","100","uid","name");

$loans = fetchtable('o_loans',"uid in ($all_loans_string)","uid","asc","100000","uid, customer_id, loan_amount, loan_balance, status, account_number");
while($loan = mysqli_fetch_array($loans)){
    $lid = $loan['uid'];
    $customer_id = $loan['customer_id'];
    $loan_amount = $loan['loan_amount'];
    $loan_balance = $loan['loan_balance'];
    $loan_status = $loan['status'];
    $phone_number = $loan['account_number'];
    array_push($all_customers, $customer_id);
    $loan_amounts[$lid] = $loan_amount;
    $loan_balances[$lid] = $loan_balance;
    $loan_statuses[$lid] = $statuses[$loan_status];
    $phone_numbers[$lid] = $phone_number;
    $loan_customer[$lid] = $customer_id;

}

$customers_string = implode(',', $all_customers);
$customer_names = table_to_obj('o_customers',"uid in ($customers_string)","1000000","uid","full_name");

/////-----Logs

$all_customers = table_to_obj('o_customers',"uid > 0","1000000","uid","full_name");
$logs = fetchtable('o_events',"event_details LIKE '%Loan moved to disbursement by %' AND tbl='o_loans' AND  event_by not in (422, 67, 276, 66, 1, 58,59,55, 279) ","uid","desc","1000000","uid, event_details, event_date, event_by, fld");
?>
<table id="tbl">
    <thead>
    <tr><th>Event ID</th><th>Customer</th><th>Phone</th><th>Loan ID</th><th>Loan Amount</th><th>Loan Balance</th><th>Loan Status</th><th>Staff Name</th><th>Staff Email</th><th>Staff ID</th><th>Event Details</th><th>Event Date</th></tr>
    </thead>
    <tbody>
    <?php
while($l = mysqli_fetch_array($logs)){
    $uid = $l['uid'];
    $event_det = $l['event_details'];
    $event_date = $l['event_date'];
    $event_by = $l['event_by'];
    $loan_id = $l['fld'];
    $staff_n = $staff_names[$event_by];
    $staff_e = $staff_emails[$event_by];
    $loan_am = $loan_amounts[$loan_id];
    $loan_bal = $loan_balances[$loan_id];
    $loan_state = $loan_statuses[$loan_id];

    $customer = $customer_names[$loan_customer[$loan_id]];
    $phone = $phone_numbers[$loan_id];


    echo " <tr><td>$uid</td><td>$customer</td><td>$phone</td><td>$loan_id</td><td>$loan_am</td><td>$loan_bal</td><td>$loan_state</td><td>$staff_n</td><td>$staff_e</td><td>$event_by</td><td>$event_det</td><td>$event_date</td></tr>";
}



?>
    </tbody>
</table>


<script type="application/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="application/javascript" src="//cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

<script src="../bower_components/datatables.net/js/dataTables.buttons.min.js"></script>
<script src="../bower_components/datatables.net/js/pdfmake.min.js"></script>
<script src="../bower_components/datatables.net/js/jszip.min.js"></script>
<script src="../bower_components/datatables.net/js/vfs_fonts.js"></script>
<script src="../bower_components/datatables.net/js/buttons.html5.min.js"></script>
<script src="../bower_components/datatables.net/js/buttons.print.min.js"></script>
<script>
    $(document).ready( function () {
        $('#tbl').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    } );
</script>

</body>
</html>