<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$group_id = $_POST['gid'];

$userd = session_details();

$payment_categories = table_to_obj('o_payment_categories',"uid > 0","100","uid","name");
$permi = permission($userd['uid'],'o_incoming_payments',"0","delete_");
$approve_permi = permission($userd['uid'],'o_incoming_payments',"0","APPROVE");



if($group_id < 1){
    die('<i>Group not selected</i>');
}
?>
    <h4 class='text-orange'>PAYMENTS</h4>
    <div class="well well-sm">


        <table class="table-bordered table-striped font-14 table table-hover table-condensed">
            <thead><tr><th>ID</th><th>Transaction Code</th><th>Payment Category</th><th>Amount</th><th>Date Repaid</th><th>--</th><th>Loan ID</th><th>Status</th><th>______Action______</th></tr></thead>
            <tbody>
            <?php
            //-----------------------------Reused Query
            $o_pays_ = fetchtable('o_incoming_payments', "status > 0 AND group_id = '$group_id'", "uid", "desc", "0,10000", "*");
            ///----------Paging Option
            $alltotal = countotal_withlimit("o_incoming_payments", "status > 0 AND group_id > 0 AND split_from=0","uid","10000");
            ///==========Paging Option
            if ($alltotal > 0) {
                while ($q = mysqli_fetch_array($o_pays_)) {
                    $uid = $q['uid'];   $euid = encurl($uid);
                    $payment_md = $q['payment_method']; //$payment_method = fetchrow('o_payment_methods',"uid='$payment_md'","name");
                    $mobile_number = $q['mobile_number'];
                    $amount = $q['amount'];
                    $transaction_code = $q['transaction_code'];
                    $loan_id = $q['loan_id'];
                    $payment_date = $q['payment_date'];
                    $split_from = $q['split_from'];
                    $record_method = $q['record_method'];
                    $payment_category = $q['payment_category'];
                    $status = $q['status'];
                    $payment_category_name = $payment_categories[$payment_category];
                    $approve_button = "";
                    if($status == 1) {
                        $total_amount = $total_amount + $amount;
                        $state = "<span class=\"text-green\"><i class=\"fa fa-check\"></i> Added</span>";
                    }
                    else if($status == 2){
                        $state = "<span class=\"\"><i class=\"fa fa-circle-o-notch\"></i> Bulk</span>";
                    }
                    else if($status == 5){
                        $state = "<span class=\"text-orange text-bold\"><i class=\"fa fa-spinner\"></i> Pending</span>";
                        if($approve_permi == 1){
                            $approve_button = "<a href='#' title='Approve allocation' class='btn btn-success btn-sm' onclick='payment_approve($euid);'><i class='fa fa-check'></i></a>";
                        }
                        else{
                            $approve_button = "";
                        }

                    }

                    $loan_id = $q['loan_id'];
                    if($loan_id > 0){
                        $loan_balance_ = $q['loan_balance'];
                        $loan_balance = money($loan_balance_);
                        $l = loan_obj($loan_id);
                        $next_due = $l['next_due_date'];
                    } else{
                        $loan_balance = "<i>Unspecified</i>";
                        $next_due ="<i>Unspecified</i>";
                    }
                    if($permi == 1){
                        $delete_button = "<a href='#' title='Delete payment' class='btn btn-sm btn-danger' onclick='payment_delete($euid);'><i class='fa fa-times'></i></a>";
                    }
                    else{
                        $delete_button = "";
                    }


                    if($split_from > 0){
                        $split = "<i title='This is a split payment' class='fa fa-hand-scissors-o text-red'></i> ";
                        $split_button = "";
                        $view_button = "";
                    }
                    else{
                        $split = "";
                        $split_button = "<a onclick=\"split_group_payment($group_id, $uid);\" href=\"#\" class='btn btn-primary btn-sm' title='Allocate to loans'><span class=\"fa fa-hand-scissors-o\"></span></a>";
                        $view_button = "<span><a href=\"#\" onclick=\"split_payment_list($uid)\" title=\"View payment\"><span class=\"fa fa-eye text-green\"></span></a></span>";
                    }

                    $go = "<a title='Go to payment' href=\"incoming-payments?repayment=".encurl($uid)."\"><span class=\"fa fa-external-link\"></span></a>";


                    echo "<tr>
            <td>$split$uid $go</td>
            <td><span>$transaction_code</span></td>
            <td><span class='text-green'>$payment_category_name</span></td>
            <td><span>$amount</span>
            </td>
            <td><span>$payment_date</span><br/> <span>".fancydate($payment_date)."</span></td>
            <td>--</td>
            <td>$loan_id</td>
            <td>$state</td>
            <td class='pull-right'>$view_button <span>$split_button</span> $delete_button <span>$approve_button</span></td>
            </tr>";

                    //////------Paging Variable ---
                    //$page_total = $page_total + 1;
                    /////=======Paging Variable ---


                }
            }else{
                echo "<tr><td colspan='13'><i>No Records Found</i></td></tr>";
            }
            ?>
            </tbody>


        </table>
    </div>
<?php
echo "<h4>Total: <b>".money($total_amount)."</b></h4>";
include_once ("../../configs/close_connection.inc");
?>