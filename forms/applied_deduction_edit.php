<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");
/////----------Session Check
$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$loan_deduction_id = $_POST['uid'];

$update_deduction = permission($userd['uid'],'o_loan_deductions',"0","update_");
if($update_deduction != 1){
    die(errormes("You don't have permission to edit deduction"));
    exit();
}

/////-------------Check Permission


if($loan_deduction_id > 0){
$loan_deduct_d = fetchonerow('o_loan_deductions',"uid='$loan_deduction_id'","deduction_id, deduction_amount");
$deduction_amount = $loan_deduct_d['deduction_amount'];
$deduction_id = $loan_deduct_d['deduction_id'];

$deduction_details = fetchonerow('o_deductions',"uid='$deduction_id'","name");

}
else{
    die(errormes("An error occurred, please reload page and try again"));
    exit();
}
/////---------End of session check
?>



            <form class="form-horizontal" autocomplete="off" onsubmit="return false;" method="post">
                <div class="box-body">

                    <div class="form-group">
                        <label for="conversation_method" class="col-sm-3 control-label">Deduction Name</label>

                        <div class="col-sm-9">
                            <?php
                                   echo "<h4>".$deduction_details['name']."</h4>";
                            ?>

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="conversation_method" class="col-sm-3 control-label">Amount</label>

                        <div class="col-sm-9">

                            <input type="number" value="<?php echo $deduction_amount; ?>" class="form-control" id="deduct_amount">

                        </div>
                    </div>

                    <div class="col-sm-3"></div>
                    <div class="col-sm-9">
                        <div class="box-footer">
                            <br/>
                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                            <button id='edit-deduction-btn' type="submit" class="btn btn-success btn-lg pull-right"  onclick="save_edited_deduction('<?php echo $loan_deduction_id; ?>');">
                                Save
                            </button>
                        </div>
                    </div>

                </div>
                <!-- /.box-body -->

                <!-- /.box-footer -->
            </form>
