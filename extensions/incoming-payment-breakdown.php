<?php
session_start();

include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$from_ = $_GET['from'];
$to_ = $_GET['to'];
$product = $_GET['product_id'];
///----------------Get all the payments for specific period
///----------------Get all the loan details of the payments above
///----------------Add in a month-year object
///

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reports</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>



<div class="container">

    <?php
    $total_payment = 0;
    $loan_ids = array();
    $total_pay_perloan = array();
    $payments = fetchtable('o_incoming_payments',"payment_date BETWEEN '$from_' AND '$to_' AND status=1","uid","asc","100000","uid, amount, loan_id");
    while($p = mysqli_fetch_array($payments)){
        $pid = $p['uid'];
        $pamount = $p['amount'];
        $loan_id = $p['loan_id'];
        $total_payment = $total_payment + $pamount;
        array_push($loan_ids, $loan_id);
        $total_pay_perloan = obj_add($total_pay_perloan, $loan_id, $pamount);

    }


    echo "<h3>Total Payments: ".money($total_payment)."</h3>";
     ////-----------Loans
    $total_per_ym = array();
    $loan_string = implode(',', $loan_ids);
    $loans = fetchtable('o_loans',"uid in ($loan_string)","uid","asc","100000","uid, total_repayable_amount, given_date");
    while($l = mysqli_fetch_array($loans)){
        $lid = $l['uid'];
        $total_repayable_amount = $l['total_repayable_amount'];
        $given_date = $l['given_date'];
        $g_d = explode('-', $given_date);
        $y = $g_d[0];
        $m = $g_d[1];

        $ym = "$m-$y";
        $total_per_ym = obj_add($total_per_ym, $ym, $total_pay_perloan[$lid]);
     //  echo $lid;
    }

    echo "<h4>Paying for Loans taken in:</h4>";
    echo "<table class='table table-bordered'>";
    echo "<tr><th>Period</th><th>Amount</th></tr>";
    $obj = '';
    foreach ($total_per_ym as $ym => $amount) {
        $obj = "<tr><td>".fancy_d($ym)."</td><td>".money($amount)."</td></tr>". $obj;

    }
    echo $obj;
    echo "</table>";



    function fancy_d($d){
        $darr = explode('-', $d);
        $month = $darr[0];
        $month_name = month_name($month);
        return "$month_name, ".$darr[1];
    }

    ?>
</div>

</body>
</html>