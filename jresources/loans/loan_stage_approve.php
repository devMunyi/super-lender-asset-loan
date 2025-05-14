<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$loan_id = $_POST['loan_id'];
$approve_version = $B2C_RMQ_IS_SET == 1 ? "2" : "";
$stage_id = $_POST['stage_id'];

if ($loan_id > 0) {
    $l = fetchonerow("o_loans", "uid='" . decurl($loan_id) . "'", "uid, product_id, loan_stage, status");
    $product_id = $l['product_id'];
    $current_stage = $l['loan_stage'];

    $lstatus = intval($l['status']);
    if ($lstatus != 1) {
        $status_name = fetchrow("o_loan_statuses", "uid=$lstatus", "name");
        exit(errormes("Loan is not approved. Current status is $status_name"));
    }


    $next_stage = loan_next_stage(decurl($loan_id));


    // $user_group = $userd["user_group"] ?? 0;


    //echo $next_stage['stage_details']['uid']."$loan_id ddk";
    if ($next_stage['stage_details']['uid'] > 0) {
?>
        <h4 class="text-black alert bg-gray-light font-italic"><i class="fa fa-info-circle"></i> When you approve the loan
            it will be moved to (<span class="font-bold text-blue"><?php echo $next_stage['stage_details']['name']; ?></span>)</h4>


        <form class="" onsubmit="return false;" method="post">
            <div class="box-body">


                <div class="form-group">
                    <label for="deduction_description" class="col-sm-3 control-label">Comments</label>

                    <div class="col-sm-9">
                        <textarea class="form-control" id="comments_"></textarea>
                    </div>
                </div>


                <div class="col-sm-3"></div>
                <div class="col-sm-9">
                    <div class="box-footer">
                        <br />
                        <button type="submit" onclick="modal_hide();" class="btn btn-lg btn-default">Exit</button>
                        <button type="submit" class="btn btn-success bg-green-gradient btn-lg pull-right nodbclick"
                            onclick="move_stage('<?php echo $loan_id; ?>', '<?php echo $stage_id; ?>');">
                            <i class="fa fa-check"></i> Approve to Next Stage
                        </button>
                    </div>
                </div>

            </div>
            <!-- /.box-body -->

            <!-- /.box-footer -->
        </form>
    <?php
    } else {
        $settings = fetchonerow("o_loan_products", "uid='" . $product_id . "'", "disburse_method");
        $method = fetchonerow("o_disburse_methods", "uid='" . $settings['disburse_method'] . "'", "name, via_api");

        $method_name = $method['name'];
        $via_api = $method['via_api'];
        if ($via_api == 1) {
            $mode = "Automatically";
        } else {
            $mode = "Manually";
        }
    ?>
        <h4 class="text-black alert bg-yellow-gradient font-italic"><i class="fa fa-info-circle"></i> The Loan is in final stage of approval</span></h4>
        <h4 class="font-italic">Money will be sent via <b><?php echo $method_name . ' ' . $mode; ?></b></h4>

        <form class="" onsubmit="return false;" method="post">
            <div class="box-body">


                <div class="form-group">
                    <label for="deduction_description" class="col-sm-3 control-label">Comments</label>

                    <div class="col-sm-9">
                        <textarea class="form-control" id="comments_"></textarea>
                    </div>
                </div>


                <div class="col-sm-3"></div>
                <div class="col-sm-9">
                    <div class="box-footer">
                        <br />
                        <button type="submit" onclick="modal_hide();" class="btn btn-lg btn-default">Exit</button>
                        <button type="submit" class="btn btn-success bg-green-gradient btn-lg pull-right"
                            onclick="approve_disburse('<?php echo $loan_id; ?>', '<?php echo $approve_version; ?>');"> <i class="fa fa-check"></i> Approve to Disburse
                        </button>
                    </div>
                </div>

            </div>
            <!-- /.box-body -->

            <!-- /.box-footer -->
        </form>




    <?php
    }

    ?>



<?php
}
include_once("../../configs/close_connection.inc");
?>

<script>
    localStorage.removeItem("sl_clicked");
</script>