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

$current_bal = fetchrow('o_summaries', "uid=5", "value_");
/////---------End of session check

?>
            <form class="form-horizontal" id="doc-upload" method="POST" action="action/system/airtel-b2c-balance-change" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label">Airtel B2C Balance</label>

                        <div class="col-sm-9">
                            <input class="form-control" value="<?Php echo $current_bal; ?>" type="text" name="balance" id="balance">
                        </div>

                    </div>


                    <div class="col-sm-3"></div>
                    <div class="col-sm-9">
                        <div class="box-footer">
                            <br/>
                            <div class="prgress">
                                <div class="messagedoc-upload" id="message"></div>
                                <div class="progressdoc-upload" id="progress">
                                    <div class="bardo-upload" id="bar"></div>
                                    <br/>
                                    <div class="percentdoc-upload" id="percent"></div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                            <button type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('doc-upload');">Submit </button>
                        </div>
                    </div>
                    
                </div>
                <!-- /.box-body -->

                <!-- /.box-footer -->
            </form>
