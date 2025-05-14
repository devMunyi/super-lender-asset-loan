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
$row = fetchonerow("o_assets", "uid = $asset_id", "uid, name, selling_price, photo");
$uid = $row["uid"];
$amount = $row["selling_price"];
$name = $row["name"];
$photo_name = $row["photo"];
$photoSrc = $photo_name ? "assets-upload/thumb_".$photo_name : "dist/img/avatar2.png";
// $asset_id = decurl($asset_id_);

?>

            <form class="form-horizontal" autocomplete="off" onsubmit="return false;" method="post">
                <div class="box-body">
                    <div class="form-group">
                        <input type="hidden" class="form-control" id = "asset_id_" name = "asset_id_" value="<?php echo $uid; ?>" placeholder="E.g. Serial number" >
                        <label for="customer_search" class="col-sm-3 control-label">Customer</label>

                        <div class="col-sm-9">
                            <input class="form-control" type="text" autocomplete="off" onkeyup="search_cust();" id="customer_search" value="<?php echo $loan['customer_id'];?>" placeholder="Start typing customer name ...">
                            <input type="hidden" id="customer_id_">
                            <div id="customer_results">

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="details" class="col-sm-3 control-label">Asset Items</label>

                        <div class="col-sm-9">
                            <?php
                            $total = totaltable('o_asset_cart',"loan_id=0 AND status=1","quantity");
                            $total_price = totaltable('o_asset_cart',"loan_id=0 AND status=1","total_price");

                            echo "<i>$total Items</i>";
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="asset_product" class="col-sm-3 control-label">Asset Product</label>

                        <div class="col-sm-9">
                            <select class="form-control" id="asset_product">
                                <option value="0">--Select One</option>
                                <?php
                                $o_next_steps_ = fetchtable('o_loan_products',"status=1 AND uid in ($asset_product_list)", "uid", "desc", "0,100", "uid ,name");
                                while($a = mysqli_fetch_array($o_next_steps_))
                                {
                                    $uid = $a['uid'];
                                    $name = $a['name'];
                                    echo "<option value='$uid'>$name</option>";
                                }

                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="details" class="col-sm-3 control-label">Amount</label>

                        <div class="col-sm-9">
                            <input type="number" class="form-control disabled" id = "loan_amount" name = "loan_amount" value="<?php echo $total_price; ?>" placeholder="Add the default selling price">
                        </div>
                    </div>
                    <div class="form-group" style="display: none">
                        <label for="details" class="col-sm-3 control-label">Period in days</label>

                        <div class="col-sm-9">
                            <input type="number" class="form-control" id = "period" name = "period" placeholder="e.g. 30, 60, 90">
                        </div>
                    </div>
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9">
                        <div class="box-footer">
                            <br/>
                            <!-- <button type="submit" class="btn btn-lg btn-default">Cancel</button> -->
                            <button type="submit"
                                    class="btn btn-success btn-lg pull-right"
                                    onclick="create_asset_loan()">
                                Create Loan
                            </button>
                        </div>
                    </div>

                </div>
                <!-- /.box-body -->

                <!-- /.box-footer -->
            </form>
<?php include_once '../configs/close_connection.inc'; ?>
