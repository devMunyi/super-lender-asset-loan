<?php
session_start();
include_once '../php_functions/functions.php';
include_once '../configs/conn.inc';

/////----------Session Check
$userd = session_details();
if ($userd == null) {
    die(errormes('Your session is invalid. Please re-login'));
    exit();
}

/////---------End of session check
$customer_id = $_POST['customer_id'];
?>

            <form class="form-horizontal" autocomplete="off" onsubmit="return false;" method="post">
                <div class="box-body">
                    <div class="form-group">
                        <label for="details" class="col-sm-3 control-label">Message</label>

                        <div class="col-sm-9">
                            <textarea class="form-control" id="message-details" placeholder="Type your message here..."></textarea>
                        </div>
                    </div>
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9">
                        <div class="box-footer">
                            <br/>
                            <!-- <button type="submit" class="btn btn-lg btn-default">Cancel</button> -->
                            <button type="submit"
                                    class="btn btn-success btn-lg pull-right"
                                    onclick="saveBroadcastMessage('<?php echo $customer_id; ?>');">
                                Send Message
                            </button>
                        </div>
                    </div>

                </div>
                <!-- /.box-body -->

                <!-- /.box-footer -->
            </form>
<?php include_once '../configs/close_connection.inc'; ?>
