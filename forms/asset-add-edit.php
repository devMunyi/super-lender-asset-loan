<section class="content-header">
    <h1>
        Asset Management
        <small>
            <a href="?cat=assets"><i class="fa fa-arrow-up"></i> Back to Assets</a>
        </small>
    
        
        <?php 

            $aid = intval($_GET['asset-add-edit']) ?? 0;
            $resource = "action/asset_loans/save-asset";
            if($aid > 0){
                $resource = "action/asset_loans/update-asset";
                $edit_asset = permission($userd['uid'],'o_assets',"0","update_");
                if($edit_asset != 1){
                    die(errormes("You don't have permission to edit assets"));
                    exit();
                }

                $asset = fetchonerow('o_assets',"uid='".decurl($aid)."'"); 
                // $target_cust = $asset['target_customers']; $target_cust_ = fetchrow("o_campaign_target_customers", "uid = $target_cust", "name");
                $asset_id = $_GET['asset-add-edit'];

                // echo "<small>Edit</small> <span class='text-green text-bold'>$target_cust_</span> <a title='Back to campaign' class='font-16' href=\"broadcasts?campaign=$cid\"><i class='fa fa-arrow-circle-up'></i></a>";
                $act = "<span class='text-orange'><i class='fa fa-edit'></i>Edit Asset</span>";
            }
            else{
                $add_asset = permission($userd['uid'],'o_assets',"0","create_");
                if($add_asset != 1){
                    die(errormes("You don't have permission to add broadcasts"));
                    exit();
                }

                $asset = array();
                $asset_id = "";
                // echo "Asset <small>Add</small>";
                $act = "<span class='text-green'><i class='fa fa-edit'></i>Add Asset</span>";
            }
?>
        
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Assets</li>
    </ol>
</section>
<section class="content">
    <div class="box">
        <div class="box-header">
            <div class="row">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <h3><?php echo $act; ?></h3>

                    <form class="form-horizontal" id="asset"  method="POST" action="<?php echo $resource; ?>" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="form-group">
                                <input type="hidden" id="asset_id_" name="asset_id_" value="<?php echo $aid; ?>">
                                <label for="name" class="col-sm-3 control-label">Name</label>

                                <div class="col-sm-9">
                                    <input type="text" class="form-control" value="<?php echo $asset['name']; ?>"  id="name" name="name" placeholder="Full name e.g. Samsung Galaxy S12">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-3 control-label">Description</label>

                                <div class="col-sm-9">
                                    <textarea class="form-control" name="description" id="description" placeholder="Full description e.g. specs, features e.t.c."><?php echo $asset['description']; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="category" class="col-sm-3 control-label">Category</label>

                                <div class="col-sm-9">
                                <select class="form-control" name="category" id="category">
                                    <option value="0">Select One</option>
                                    <?php 
                                       $o_asset_categories = fetchtable('o_asset_categories', "status=1", "uid", "desc", "0,100", "uid ,name ");
                                       
                                       while ($a = mysqli_fetch_array($o_asset_categories)) {
                                           $uid = $a['uid'];
                                           $name = $a['name'];
                                           if($asset["category_"] == $uid){
                                               $selected_t = "SELECTED";
                                           }
                                           else{
                                               $selected_t = "";
                                           }
                                           echo "<option $selected_t value=\"$uid\">$name</option>";
                                       } 
                                    ?>
                                </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="buying_price" class="col-sm-3 control-label">Buying Price</label>

                                <div class="col-sm-9">
                                    <input type="number" class="form-control" value="<?php echo $asset['buying_price']; ?>"  id="buying_price" name="buying_price" placeholder="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="selling_price" class="col-sm-3 control-label">Selling Price</label>

                                <div class="col-sm-9">
                                    <input type="number" class="form-control" value="<?php echo $asset['selling_price']; ?>"  id="selling_price" name="selling_price" placeholder="">
                                </div>
                            </div>

                            <?php 
                            if($aid == 0){ ?>
                                <div class="form-group">
                                    <label for="stock" class="col-sm-3 control-label">Stock</label>
                                    <div class="col-sm-9">
                                        <input type="number" class="form-control" value="<?php echo $asset['stock']; ?>"  id="stock" name="stock" placeholder="">
                                    </div>
                                </div>
                            <?php }?>
                            <div class="form-group">
                                <label for="image_" class="col-sm-3 control-label">Image</label>
                                <div class="col-sm-9">
                                    <?php $existingImg = $asset['photo'] ? $asset['photo'] : ""; ?>
                                    <input type="file" id="image_" name="image_" class="form-control">
                                    <input type="hidden" id="existing_image_" name="existing_image_" value="<?php echo $existingImg; ?>" class="form-control">
                                    <!-- <img src = <?php // echo $imgSrc; ?> alt="Existing Image" width="100"> -->
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-3 control-label">Status</label>

                                <div class="col-sm-9">
                                    <select class="form-control" name="status" id="status">
                                        <option value="1">Active</option>
                                        <option value="2">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <div class="box-footer">
                                    <div class="prgress">
                                        <div class="messageasset" id="message"></div>
                                        <div class="progressasset" id="progress">
                                            <div class="barasset" id="bar"></div>
                                            <br>
                                            <div class="percentasset" id="percent"></div>
                                        </div>
                                    </div>
                                    <br/>
                                    <button type="reset" class="btn btn-lg btn-default">Cancel</button>
                                    <button type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('asset');">Submit
                                    </button>
                                </div>
                            </div>

                        </div>
                        <!-- /.box-body -->

                        <!-- /.box-footer -->
                    </form>
                </div>
                <div class="col-sm-3"></div>
            </div>
        </div>
    </div>
</section>

