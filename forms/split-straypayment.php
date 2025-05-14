<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

/////----------Session Check
$userd = session_details();
$pid = intval($_POST['pid']) > 0 ? decurl(intval($_POST['pid'])) : 0;
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
if ($pid > 0) {
} else {
    die(errormes("Payment not selected"));
}

$pd = fetchonerow('o_incoming_payments', "uid = '$pid'", "amount, loan_id");
$pay_amount = $pd['amount'];
$loan_id = $pd['loan_id'];

////------------Check if more than total amount is allocated
$total_allocated = totaltable('o_incoming_payments', "split_from='$pid' AND status=1", "amount");

if ($loan_id > 0) {
    die(errormes("This payment can't be split because it is already allocated to another loan"));
}

echo "<h4>Payment Amount: <b>" . money($pay_amount - $total_allocated) . "</b></h4>";


?>
<form class="form-horizontal" method="POST" onsubmit="return false;">
    <div class="box-body">
        <div class="form-group">
            <label for="amount_" class="col-sm-3 control-label">Amount</label>

            <div class="col-sm-9">
                <input class="form-control" type="number" name="amount" id="amount_">
                <input class="form-control" type="hidden" value="<?php echo $pid; ?>" name="payment_id" id="payment_id">
            </div>
        </div>

        <div class="form-group">
            <label for="loan_id" class="col-sm-3 control-label">Loan ID</label>
            <div class="col-sm-9">
                <input class="form-control" value="0" name="loan_id" id="loan_id">
            </div>
        </div>
        <div class="form-group">
            <label for="payment_for" class="col-sm-3 control-label">Payment for</label>
            <div class="col-sm-9">
                <select class="form-control" name="payment_for" id="payment_for">
                    <option value="0">--Select One</option>
                    <?php
                    $cats = fetchtable('o_payment_categories', "status=1", "uid", "asc", "0,10", "uid ,name");
                    while ($c = mysqli_fetch_array($cats)) {
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
                <br />
                <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                <button type="submit" class="btn btn-success btn-lg pull-right" onclick="split_individual_stray_pay('<?php echo $name; ?>');">Split </button>
            </div>
        </div>

    </div>
    <!-- /.box-body -->

    <!-- /.box-footer -->
</form>