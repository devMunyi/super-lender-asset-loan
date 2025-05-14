<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

/////----------Session Check
$userd = session_details();
$split_for = $_POST['split_for'];
$pid = decurl($_POST['pid']);
$overpayment = $_POST['overpayment'];
$customer_id = $_POST['customer_id'] > 0 ? decurl($_POST['customer_id']) : 0;
$split_for = $_POST['split_for'];
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
if($customer_id > 0){}
else{
    die(errormes("Please allocate the payment to a customer first"));
}
if($pid > 0){}
else{
    die(errormes("Payment not selected"));
}

$pd = fetchonerow('o_incoming_payments',"uid = '$pid'","*");
$pay_amount = $pd['amount'];
$loan_id = $pd['loan_id'];

// check if user has uncleared loans
$uncleared_loans = totaltable('o_loans', "customer_id='$customer_id' AND paid = 0 AND disbursed = 1", "uid");

////------------Check if more than total amount is allocated
$total_allocated = totaltable('o_incoming_payments',"split_from='$pid' AND status=1","amount");

if($loan_id > 0){
    die(errormes("This payment can't be split because it is already allocated to another loan"));
}

echo "<h4>Maximum Allowed Amount: <b>".money($pay_amount-$total_allocated)."</b></h4>";

?>
<form class="form-horizontal"  method="POST" onsubmit="return false;">
    <div class="box-body">
        <div class="form-group">
            <label for="amount_" class="col-sm-3 control-label">Amount</label>

            <div class="col-sm-9">
                <input class="form-control" type="number" name="amount" id="amount_">
                <input class="form-control" type="hidden" value="<?php echo $pid; ?>" name="payment_id" id="payment_id">
            </div>

        </div>

        <div class="form-group">

            <label for="new_loan_id" class="col-sm-3 control-label">Loan ID</label>
            <div class="col-sm-9">

            <?php
                if($uncleared_loans > 0){
                 ?>
                    <select class="form-control" id="new_loan_id">
                        <option value="0">--Select a Loan</option>
                        <?php
                        $all_loans = fetchtable('o_loans', "customer_id = $customer_id AND disbursed=1 AND paid=0 AND status!=0", "uid", "asc", "10000", "customer_id, loan_balance, uid");
                        while ($ml = mysqli_fetch_array($all_loans)) {
                            $loan_bal = $ml['loan_balance'];
                            $loan_id = $ml['uid'];

                            echo "<option value='$loan_id'>Loan ID:$loan_id (Loan Bal: $loan_bal)</option>";
                        }
                        ?>
                    </select>
                <?php
                }else {
                    ?>
                    <input class="form-control" value="0" name="new_loan_id" id="new_loan_id">
                <?php
                }?>

            </div>
        </div>
        <div class="form-group hide">
            <label for="customer_id" class="col-sm-3 control-label">Customer ID</label>
            <div class="col-sm-9">
            <input class="form-control" type="number" name="customer_id" id="customer_id" value="<?php echo $customer_id; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="payment_for" class="col-sm-3 control-label">Payment for</label>
            <div class="col-sm-9">
                <select class="form-control" name="payment_for" id="payment_for">
                    <option value="0">--Select One</option>
                    <?php
                    $cats = fetchtable('o_payment_categories',"status=1", "uid", "asc", "0,10", "uid ,name");
                    while($c = mysqli_fetch_array($cats))
                    {
                        $uid = $c['uid'];
                        $name = $c['name'];

                        echo "<option value=\"$uid\">$name</option>";
                    }

                    ?>
                </select>
            </div>
        </div>



        <div class="col-sm-3"></div>
        <div class="col-sm-9">
            <div class="box-footer">
                <br/>
                <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                <button type="submit" class="btn btn-success btn-lg pull-right" onclick="split_individual_pay('unallocated');">Split </button>
            </div>
        </div>

    </div>
    <!-- /.box-body -->

    <!-- /.box-footer -->
</form>
