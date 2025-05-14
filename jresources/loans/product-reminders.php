<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$product_id = $_GET['product_id'];
$loan_statuses = table_to_obj('o_loan_statuses',"status=1","100","uid","name");
?>
<table class="table-bordered font-14 table table-hover">
    <thead><tr><th>ID</th><th>Loan Day</th><th>Status</th><th>Message</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $reminders = fetchtable('o_product_reminders',"(product_id='$product_id' OR product_id=0)","loan_day","asc","1000");
    while($rem = mysqli_fetch_array($reminders)){
        $uid = $rem['uid'];
        $loan_day = $rem['loan_day'];
        $custom_event = $rem['custom_event'];
        $loan_status = $rem['loan_status'];
        $message_body = $rem['message_body'];
        $status = $rem['status'];
        $state = status($status);
        if(input_available($custom_event) == 1){
            //$loan_day = "";
        }
        $act = "<a onclick=\"product_reminder_modal($uid,$product_id)\" class='text-orange font-16 pointer'><i class='fa fa-pencil'></i></a> |";
        //$act.="<a class='text-red font-16 pointer'><i class='fa fa-trash'></i></a>";
        echo "<tr><td>$uid</td><td>Day $loan_day, $custom_event</td><td>".$loan_statuses[$loan_status]."</td><td>$message_body</td><td>$state</td><td>$act</td></tr>";
    }
    ?>

    </tbody>

<?php

include_once ("../../configs/close_connection.inc");
?>
</table>