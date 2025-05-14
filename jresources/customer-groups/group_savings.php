<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$group_id = $_POST['gid'];

$userd = session_details();

$payment_categories = table_to_obj('o_payment_categories',"uid > 0","100","uid","name");
$permi = permission($userd['uid'],'o_incoming_payments',"0","delete_");
$approve_permi = permission($userd['uid'],'o_incoming_payments',"0","edit_");



if($group_id < 1){
    die('<i>Group not selected</i>');
}
?>
    <h4 class='text-orange'>Savings</h4>
    <div class="well well-sm">


        <table class="table-bordered table-striped font-14 table table-hover table-condensed">
            <thead><tr><th>ID</th><th>Client</th><th>Transaction Code</th><th>Source</th><th>Amount</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php
            //-----------------------------Reused Query
            $o_pays_ = fetchtable('o_incoming_payments', "status = 1 AND payment_category = 4 AND group_id = '$group_id'", "uid", "desc", "0,10000", "*");
            ///----------Paging Option
            $alltotal = countotal_withlimit("o_incoming_payments", "status = 1 AND payment_category = 4 AND group_id > 0 AND split_from=0","uid","10000");
            ///==========Paging Option
            if ($alltotal > 0) {
                $member_ids = table_to_array('o_incoming_payments',"status = 1 AND payment_category = 4 AND group_id = '$group_id'","10000","customer_id");
                $customer_ids = implode(',', $member_ids);
                $member_names = table_to_obj('o_customers',"uid in ($customer_ids)","10000","uid","full_name");
                while ($q = mysqli_fetch_array($o_pays_)) {
                    $uid = $q['uid'];   $euid = encurl($uid);
                    $payment_md = $q['payment_method']; //$payment_method = fetchrow('o_payment_methods',"uid='$payment_md'","name");
                    $customer_id = $q['customer_id'];
                    $amount = $q['amount'];
                    $transaction_code = $q['transaction_code'];
                    $loan_id = $q['loan_id'];
                    $payment_date = $q['payment_date'];


                    $total_savings+=$amount;

                    $loan_id = $q['loan_id'];
                    if($loan_id > 0){
                      $source = "Loan Deduction";
                    } else{
                      $source = "Payment";
                    }
                    $client_name = $member_names[$customer_id];





                    $go = "<a title='Go to payment' href=\"incoming-payments?repayment=".encurl($uid)."\"><span class=\"fa fa-external-link\"></span></a>";


                    echo "<tr>
            <td>$uid</td><td>$client_name</td>
            <td><span>$transaction_code</span></td>
            <td><span class='text-green'>$source</span></td>
            <td><span>$amount</span>
            </td>
            <td><span>$payment_date</span><br/> <span>".fancydate($payment_date)."</span></td>
           
            
            <td>$go</td>
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
echo "<h4>Total Savings: <b>".money($total_savings)."</b></h4>";
include_once ("../../configs/close_connection.inc");
?>