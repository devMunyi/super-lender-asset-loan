<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");


$gid = $_POST['gid'];
if($gid > 0){
    $customer = decurl($gid);
    $staff_obj = table_to_obj('o_users',"uid>0","100000","uid","name");
    $status_name = table_to_obj('o_loan_statuses',"uid>0","100","uid","name");
    $status_color = table_to_obj('o_loan_statuses',"uid>0","100","uid","color_code");

    $customer_list = table_to_array('o_loans',"group_id=$gid AND status!=0","100","customer_id");
    $cust_list = implode(',', $customer_list);
    $cust_name_array = table_to_obj('o_customers',"uid in ($cust_list)","10000","uid","full_name");


    $loans = fetchtable('o_loans',"group_id=$gid AND status!=0","uid","desc","100","uid, account_number, product_id, loan_amount, customer_id, total_repayable_amount, total_repaid, loan_balance, given_date, next_due_date, final_due_date, current_lo, current_co, status");
    if(mysqli_num_rows($loans) > 0) {

        $t = "<table class='table font-13 table-bordered table-condensed table-striped'>";
        $t.="<tr><th>UID</th><th>Customer</th>  <th>Loan Amount</th> <th>Total Repayable</th><th>Paid</th> <th>Balance</th><th>Dates</th><th>BDO</th><th>Status</th><th>Action</th></tr>";

        while ($l = mysqli_fetch_array($loans)) {
            $uid = $l['uid'];                              $euid = encurl($uid);
            $account_number = $l['account_number'];
            $customer_id = $l['customer_id'];              $ecid = encurl($customer_id);
            $product_id = $l['product_id'];                 $cust_name = $cust_name_array[$customer_id];
            $loan_amount = $l['loan_amount'];
            $total_repayable_amount = $l['total_repayable_amount'];
            $total_repaid = $l['total_repaid'];
            $loan_balance = $l['loan_balance'];
            $given_date = $l['given_date'];
            $final_due_date = $l['final_due_date'];
            $current_lo = $l['current_lo'];  $lo_name = $staff_obj[$current_lo];
            $current_co = $l['current_co'];  $co_name = $staff_obj[$current_co];
            $status = $l['status'];

            $total_loans= $total_loans + 1;
            $amount_borrowed = $amount_borrowed + $loan_amount;
            $repayable_total = $repayable_total + $total_repayable_amount;
            $total_repaid_total = $total_repaid_total + $total_repaid;
            $total_balance = $total_balance + $loan_balance;
            $go = "<a title='Load external' href=\"loans?loan=".encurl($uid)."\"><span class=\"fa fa-external-link\"></span></a>";
            $load = "<a title='Load here' class='text-bold font-18' onclick=\"account_info('$ecid','$euid');\"><span class=\"fa fa-hand-o-right\"></span></a>";

            $state = "<span class='label custom-color' style='background-color: ".$status_color[$status].";'>".$status_name[$status]."</span>";

            $t.="<tr><td>$uid </td><td>$cust_name</td><td>".money($loan_amount)."</td> <td>".money($total_repayable_amount)."</td> <td>".money($total_repaid)."</td><td>".money($loan_balance)."</td><td>Given: <b>$given_date</b> <br/> Due: <b>$final_due_date</b>".fancydate($final_due_date)." </td><td>LO: <b>$lo_name</b> <br/> CO: <b>$co_name</b> </td><td>$state</td><td>$go $load</td></tr>";
        }
        echo "<h4 class='text-orange'>SUMMARY</h4>";
        $money_made = false_zero($total_repaid - $amount_borrowed);
        echo "<div class='well well-sm'>
                    <h4>Account Number</h4>
 <table class='table table-bordered bg-black-gradient font-16'>".
            "<tr><td>Total Loans</td><td class='font-bold'>".$total_loans."</td>".
            "<td>Amount Borrowed</td><td class='font-bold'>".money($amount_borrowed)."</td></tr>".
            "<tr><td>Repayable Total</td><td class='font-bold'>".money($repayable_total)."</td>".
            "<td>Total Repaid</td><td class='font-bold'>".money($total_repaid)."</td></tr>".
            "<tr><td>Total Balance</td><td class='font-bold'>".money($total_balance)."</td>".
            "<td>Money Made</td><td class='font-bold'>".money($money_made)."</td></tr>".
            "</table></div>";

        echo "<h4 class='text-orange'>ALL LOANS</h4>";
        echo $t;
    }
    else{
        echo ("<i>No records found</i>");
    }


    echo "</table>";
}
else{
    echo errormes("Unable to load account data");
}
include_once ("../../configs/close_connection.inc");