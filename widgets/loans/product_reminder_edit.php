<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
/////----------Session Check
$reminder_id = $_POST['reminder_id'];
$product_id = $_POST['product_id'];
if($reminder_id > 0){
    $rem = fetchonerow('o_product_reminders',"uid='$reminder_id'","*");
}


$custom_events = array('DISBURSEMENT','LOAN_REJECTED','LOAN_FAILED','PARTIAL_PAYMENT','FULL_PAYMENT','INSTALMENT_DATE','DUE_TOMORROW','DUE_TODAY','DUE_YESTERDAY');

?>
<form class="form-horizontal" onsubmit="return false;" method="post">
    <div class="box-body">
        <div class="form-group">
            <label for="product_id" class="col-sm-3 control-label">Product Name</label>

            <div class="col-sm-9">
                <select class="form-control" id="product_id">
                    <option value="0">--All Products</option>
                    <?php
                    $products = fetchtable('o_loan_products',"status=1","name","asc","1000","uid, name");
                    while($p = mysqli_fetch_array($products)){
                        $pid = $p['uid'];
                        $pname = $p['name'];

                        if($pid == $product_id){
                            $p_selected = "SELECTED";
                        }
                        else{
                            $p_selected = "";
                        }

                        echo "<option $p_selected value='$pid'>$pname</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <input type="hidden" id="rid" value="<?php echo $reminder_id; ?>">

            <label for="loan_day" class="col-sm-3 control-label">Day after disbursement</label>

            <div class="col-sm-9">
                <input type="number" class="form-control"  value="<?php echo $rem['loan_day']; ?>" id="loan_day">
            </div>
        </div>
        <div class="form-group">
            <label for="custom_event" class="col-sm-3 control-label">Custom Event Name</label>

            <div class="col-sm-9">
                <select class="form-control" id="custom_event">
                    <option value="">--Unspecified</option>
                    <?php
                     for($i=0; $i<sizeof($custom_events); ++$i)
                     {
                         if($custom_events[$i] == $rem['custom_event']){
                             $cust_selected = "SELECTED";
                         }
                         else{
                             $cust_selected = "";
                         }
                         echo "<option $cust_selected>".$custom_events[$i]."</option>";
                     }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="loan_status" class="col-sm-3 control-label">Loan Status</label>

            <div class="col-sm-9">
                <select class="form-control" id="loan_status">
                    <option value="0">--Any Status</option>
                    <?php
                    $statuses = fetchtable('o_loan_statuses',"status=1","name","asc","1000","uid, name");
                    while($s = mysqli_fetch_array($statuses)){
                        $uid = $s['uid'];
                        $name = $s['name'];

                        if($uid == $rem['loan_status']){
                            $status_selected = "SELECTED";
                        }
                        else{
                            $status_selected = "";
                        }

                        echo "<option $status_selected value='$uid'>$name</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="message_" class="col-sm-3 control-label">Message</label>

            <div class="col-sm-9">
                <textarea  class="form-control" placeholder="Dear {customers.full_name}, "  id="message"><?php echo $rem['message_body']; ?></textarea>

            </div>

            <div class="col-sm-12 well well-sm font-italic text-black font-13" style="margin-top: 15px;"><b>Variables:</b>
                {loans.account_number}, {loans.loan_amount}, {loans.disbursed_amount}, {loans.total_repayable_amount}, {loans.total_repaid}, {loans.loan_balance}, {loans.current_instalment},
                {loans.current_instalment_amount}, {loans.given_date}, {loans.next_due_date},{loans.final_due_date} <br/>
                {customers.full_name},{customers.primary_mobile}, {customers.national_id}, {customers.loan_limit}

            </div>
        </div>

        <div class="form-group">
            <label for="status_" class="col-sm-3 control-label">Status</label>

            <div class="col-sm-9">
                <select class="form-control" id="status_">
                    <?php
                    if($rem['status'] == 1){
                        $active = "SELECTED";
                    }
                    else{
                        $inactive = "SELECTED";
                    }
                    ?>
                    <option <?php echo $active; ?> value="1">Active</option>
                    <option <?php echo $inactive; ?> value="0">InActive</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <div  class="col-sm-3 control-label"></div>
             <div class="col-sm-9">
                <button class="btn btn-success" onclick="save_reminder();"> Save </button>
            </div>
        </div>

    </div>
</form>


