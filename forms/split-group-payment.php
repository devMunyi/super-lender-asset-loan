<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

/////----------Session Check
$userd = session_details();
$gid = $_POST['gid'];
$pid = $_POST['pid'];
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
if($gid > 0){}
else{
    die(errormes("Group not selected"));
}
if($pid > 0){}
else{
    die(errormes("Payment not selected"));
}

$pd = fetchonerow('o_incoming_payments',"uid = '$pid'","*");
$pay_amount = $pd['amount'];
$loan_id = $pd['loan_id'];

////------------Check if more than total amount is allocated
$total_allocated = totaltable('o_incoming_payments',"split_from='$pid' AND status=1","amount");

if($loan_id > 0){
    die(errormes("This payment can't be split because it is already allocated to another loan"));
}

echo "<h4>Payment Amount: <b>".money($pay_amount-$total_allocated)."</b></h4>";


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


            <label for="loan_id" class="col-sm-3 control-label">Loan ID</label>
            <div class="col-sm-9">

                <select class="form-control" id="loan_id">
                    <option value="0">--Select a Loan</option>
                    <?php
                    $all_members = table_to_array('o_group_members',"group_id='$gid' AND status=1","1000","customer_id");
                    $member_list = implode(',',$all_members);
                    $member_loan_balance = array();
                    $member_loan_id = array();
                    $member_loans_all = fetchtable('o_loans',"customer_id in ($member_list) AND group_id='$gid' AND disbursed=1 AND paid=0 AND status!=0","uid","asc","10000","customer_id, loan_balance, uid");
                    while($ml = mysqli_fetch_array($member_loans_all)){
                        $customer_id = $ml['customer_id'];
                        $loan_bal = $ml['loan_balance'];
                        $loan_id = $ml['uid'];
                        $member_loan_balance[$customer_id] = $loan_bal;
                        $member_loan_id[$customer_id] = $loan_id;
                    }
                    $with_loans = array(0);

                    $memb = fetchtable('o_customers',"uid in ($member_list)","full_name","asc","10000","full_name, uid");
                    while($m = mysqli_fetch_array($memb))
                    {
                        $cid = $m['uid'];
                        $name = $m['full_name'];
                        $loan_balance = money($member_loan_balance[$cid]);
                        $loan_id = $member_loan_id[$cid];
                        if($loan_balance > 0) {
                           array_push($with_loans, $cid);
                            echo "<option value='$loan_id'>$name (Loan Bal: $loan_balance)</option>";
                        }
                    }
                    ?>

                </select>

            </div>
        </div>
        <div class="form-group">
            <label for="loan_id" class="col-sm-3 control-label">Customer ID</label>
            <div class="col-sm-9">

                <select class="form-control" id="customer_id">
                    <option value="0">-All members</option>
                    <?php

                    $all_members_with_loans = implode(',', $with_loans);
                    echo $member_list;
                    $memb = fetchtable('o_customers',"uid in ($member_list)","full_name","asc","10000","full_name, uid");
                    while($m = mysqli_fetch_array($memb))
                    {
                        $cid = $m['uid'];
                        $name = $m['full_name'];

                            echo "<option value='$cid'>$name</option>";

                    }
                    ?>
                </select>

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
                <button type="submit" class="btn btn-success btn-lg pull-right" onclick="split_pay();">Split </button>
            </div>
        </div>

    </div>
    <!-- /.box-body -->

    <!-- /.box-footer -->
</form>
