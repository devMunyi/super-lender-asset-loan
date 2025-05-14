<section class="content-header">
    <?php
        $asset_id_ = $_GET['asset'];
        $asset_id = decurl($asset_id_);
        $asset = fetchonerow("o_assets", "uid = $asset_id", "*");
        
        $uid = $asset_id;
        $title = $asset["name"]; 
        $description = $asset["description"];
        $category_ = $asset["category_"]; $category = fetchrow("o_asset_categories", "uid = $category_", "name");
        $buying_price = $asset["buying_price"];
        $selling_price = $asset["selling_price"];
        $added_date = $asset["added_date"];
        $photo_name = $asset["photo"];
        $photoSrc = $photo_name ? "assets-upload/thumb_".$photo_name : "dist/img/avatar.png";
        $stock = $asset["stock"];
        $loans = 0;

    ?>
    <h1>
        Asset Details &raquo;<span class="text-green text-bold"> <?php echo $title; ?> </span>
        <small>
        <a href="?cat=assets"><i class="fa fa-arrow-up"></i> Back to Assets</a>
        </small>

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Asset</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified font-16">
                    <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-info"></i> Asset Info</a></li>
                    <li class="nav-item nav-100"><a href="#tab_3" data-toggle="tab" aria-expanded="false"><i class="fa fa-clock-o"></i> Events</a></li>

                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">

                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-info"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <tr><td class="text-bold">UID</td><td><?php echo $uid; ?></td></tr>
                                    <tr><td class="text-bold">Title</td><td> <?php echo $title; ?> </td></tr>
                                    <tr><td class="text-bold">Description</td><td> <?php echo $description; ?> </td></tr>
                                    <tr><td class="text-bold">Category</td><td> <?php echo $category; ?> </td></tr>
                                    <tr><td class="text-bold">Buying Price</td><td> <?php echo money($buying_price); ?> </td></tr>
                                    <tr><td class="text-bold">Selling Price</td><td> <?php echo money($selling_price); ?> </td></tr>
                                    <tr><td class="text-bold">Added Date</td><td> <?php echo "$added_date". "(". fancydate($added_date).")"; ?> </td></tr>
                                    <tr><td class="text-bold">Stock</td><td> <?php echo "$stock Units"; ?> </td></tr>
                                    <tr><td class="text-bold">Loans</td><td> <?php echo $loans; ?> <a href="#"><i class="fa fa-external-link-square"></i></a></td></tr>
                                </table>
                                <hr/>
                                <button onclick="cart_add(<?php echo $asset_id; ?>)" class="btn btn-success btn-md"><i class="fa fa-cart-plus"></i> Add to Cart</button>
                                <a class="btn btn-primary btn-md" href="?asset-add-edit=<?php echo $asset_id_; ?>"><i class="fa fa-pencil"></i> Update Asset</a>
                                <button onclick="modal_view('/forms/asset_stock.php','asset_id=<?php echo $asset_id; ?>','Update Stock')" class="btn btn-warning btn-md"><i class="fa fa-plus"></i> Update Stock</button>
                                <!-- <button class="btn btn-danger btn-md"><i class="fa fa-trash"></i> Delete</button> -->
                                <a class="btn btn-default btn-md pull-right" href="loans.php?loan-type=4"><i class="fa fa-external-link"></i> All Asset Loans</a>
                                <a class="btn btn-default btn-md pull-right" href="assets?cat=cart"><i class="fa fa-shopping-cart"></i> Go to Cart</a>
                            </div>
                            <div class="col-md-3">
                               <img src="<?php echo $photoSrc; ?>" width="100%"/>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-money" aria-hidden="true"></i></span>
                            </div>
                            <div class="col-md-8">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Amount</th>
                                        <th>Pay Method</th>
                                        <th>Record Type</th>
                                        <th>Transaction Code</th>
                                        <th>Loan Balance</th>
                                        <th>Pay Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>


                                    </tbody>


                                </table>
                            </div>

                        </div>
                    </div>

                    <!-- /.tab-pane -->
                     <!-- /.tab-pane -->
                     <div class="tab-pane" id="tab_3">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            </div>
                            <div class="col-md-10">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead><tr><th>Event</th><th>Date</th></tr></thead>
                                    <tbody>

                                    <?php
                                    $o_events_ = fetchtable('o_events',"tbl='o_assets' AND fld=$asset_id", "uid", "DESC", "0,100", "uid ,event_details ,event_date ,event_by ,status ");
                                    while($d = mysqli_fetch_array($o_events_))
                                    {
                                        $uid = $d['uid'];
                                        $event_details = $d['event_details'];
                                        $event_date = $d['event_date'];
                                        $event_by = $d['event_by'];
                                        $status = $d['status'];

                                        echo " <tr><td>$event_details</td><td>$event_date</td> </tr>";
                                    }
                                    ?>
                                    </tbody>


                                </table>
                            </div>

                        </div>
                    </div>
                    <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
            </div>
        </div>
    </div>
</section>
