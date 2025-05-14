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
$loan_addon_id = $_POST['uid'];

$update_addon = permission($userd['uid'],'o_loan_addons',"0","update_");
if($update_addon != 1){
    die(errormes("You don't have permission to edit addon"));
    exit();
}

/////-------------Check Permission


if($loan_addon_id > 0){
$loan_add_d = fetchonerow('o_loan_addons',"uid='$loan_addon_id'","addon_id, addon_amount");
$addon_amount = $loan_add_d['addon_amount'];
$addon_id = $loan_add_d['addon_id'];

$addon_details = fetchonerow('o_addons',"uid='$addon_id'","name");

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
                        <label for="conversation_method" class="col-sm-3 control-label">Addon Name</label>

                        <div class="col-sm-9">
                            <?php
                                   echo "<h4>".$addon_details['name']."</h4>";
                            ?>

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="conversation_method" class="col-sm-3 control-label">Amount</label>

                        <div class="col-sm-9">

                            <input type="number" value="<?php echo $addon_amount; ?>" class="form-control" id="add_amount">

                        </div>
                    </div>

                    <div class="col-sm-3"></div>
                    <div class="col-sm-9">
                        <div class="box-footer">
                            <br/>
                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                            <button type="submit"    class="btn btn-success btn-lg pull-right"  onclick="save_edited_addon('<?php echo $loan_addon_id; ?>');">
                                Save
                            </button>
                        </div>
                    </div>

                </div>
                <!-- /.box-body -->

                <!-- /.box-footer -->
            </form>
