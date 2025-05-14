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
$asset_id = $_POST['asset_id'];
$row = fetchonerow("o_assets", "uid = $asset_id", "uid, stock");
$uid = $row['uid'];
$stock = $row["stock"];
?>

<form class="form-horizontal" autocomplete="off" onsubmit="return false;" method="post">
    <div class="box-body">
        <div class="form-group">
            <input type="hidden" class="form-control" id="asset_id_val" name="asset_id_" value="<?php echo $asset_id; ?>" placeholder="" >
            <label for="details" class="col-sm-3 control-label">Stock</label>

            <div class="col-sm-9">
                <input type="number" class="form-control" id="stock_amount" name ="stock_amount" value="<?php echo $stock; ?>" placeholder="">
            </div>
        </div>
        <div class="col-sm-3"></div>
        <div class="col-sm-9">
            <div class="box-footer">
                <br/>
                <!-- <button type="submit" class="btn btn-lg btn-default">Cancel</button> -->
                <button type="submit"   class="btn btn-success btn-lg pull-right"   onclick="update_asset_stock()"> Update</button>
            </div>
        </div>

    </div>
    <!-- /.box-body -->

    <!-- /.box-footer -->
</form>
<?php include_once '../configs/close_connection.inc'; ?>
