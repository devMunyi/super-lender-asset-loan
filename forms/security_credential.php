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
$b2c_user= fetchrow('o_mpesa_configs',"property_name='b2c_InitiatorName'","property_value");
/////---------End of session check

?>
            <form class="form-horizontal" id="doc-upload" method="POST" onsubmit="return false;">
                <div class="box-body">
                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label">M-Pesa Password for <?php echo $b2c_user; ?></label>

                        <div class="col-sm-9">
                            <input class="form-control" type="password" name="password" id="password">
                        </div>

                    </div>


                    <div class="col-sm-3"></div>
                    <div class="col-sm-9">
                        <div class="box-footer">
                            <button type="submit" class="btn btn-success btn-lg pull-right" onclick="changeMpesaB2CPassword()">Submit </button>
                        </div>
                    </div>
                    
                </div>
                <!-- /.box-body -->

                <!-- /.box-footer -->
            </form>
