<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$pid = $_POST['pid'];

if($pid < 1){
    die('<i>Payment not selected</i>');
}
?>
    <h4 class='text-orange'>ALLOCATED FROM PAYMENT: <?php echo $pid; ?></h4>
    <div class="well well-sm">

        <table class="table-bordered table-striped font-14 table table-hover table-condensed">
            <thead><tr><th>ID</th><th>Transaction Code</th><th>Amount</th><th>Loan ID</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php
            //-----------------------------Reused Query
            $o_pays_ = fetchtable('o_incoming_payments', "status = 1 AND split_from = $pid", "uid", "desc", "0,100", "*");
            ///----------Paging Option
            $alltotal = countotal_withlimit("o_incoming_payments", "status = 1 AND split_from = $pid","uid","1000");
            ///==========Paging Option
            if ($alltotal > 0) {
                while ($q = mysqli_fetch_array($o_pays_)) {
                    $uid = $q['uid'];
                    $payment_md = $q['payment_method']; //$payment_method = fetchrow('o_payment_methods',"uid='$payment_md'","name");
                    $mobile_number = $q['mobile_number'];
                    $amount = $q['amount'];
                    $transaction_code = $q['transaction_code'];
                    $loan_id = $q['loan_id'];
                    $payment_date = $q['payment_date'];
                    $split_from = $q['split_from'];
                    $record_method = $q['record_method'];

                    $total_amount = $total_amount + $amount;

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




                    echo "<tr>
            <td>$uid</td>
            <td><span>$transaction_code</span></td>
            <td><span>$amount</span>
            </td>
           
            <td>$loan_id</td>
            <td><span class=\"text-green\"><i class=\"fa fa-check\"></i> Added</span></td>
            <td><span><a href=\"incoming-payments?repayment=".encurl($uid)."\" title='Go to Payment'><span class=\"fa fa-external-link text-blue\"></span></a></span></td>
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