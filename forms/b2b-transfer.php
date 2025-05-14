<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$b2c_user = fetchrow('o_mpesa_configs', "property_name='b2c_InitiatorName'", "property_value");
/////---------End of session check

?>
<form class="form-horizontal" method="POST" onsubmit="return false;">
    <div class="box-body">
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">FROM: <?php echo ''; ?></label>

            <div class="col-sm-9">
                <input class="form-control" type="number" name="from" id="from">
            </div>

        </div>

        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">To: <?php echo ''; ?></label>

            <div class="col-sm-9">
                <input class="form-control" type="number" name="to" id="to">
            </div>

        </div>

        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">Amount: <?php echo ''; ?></label>

            <div class="col-sm-9">
                <input class="form-control" step="2" type="number" name="amount" id="amount">
            </div>
        </div>


        <div class="col-sm-3"></div>
        <div class="col-sm-9">
            <div class="box-footer">
                <!-- <br />
                <button type="submit" class="btn btn-lg btn-default">Cancel</button> -->
                <button id="mpesa-b2b-transfer" type="submit" class="btn btn-success btn-lg pull-right" onclick="makeB2BTransfer()">Submit </button>
            </div>
        </div>

    </div>
    <!-- /.box-body -->

    <!-- /.box-footer -->
</form>