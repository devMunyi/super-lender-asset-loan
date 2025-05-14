<section class="content-header">
    <h1>
      Asset Finance

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Assets</li>
    </ol>
</section>
<section class="content">
    <div class="box">
        <div class="box-header bg-info">
            <div class="row">
                <div class="col-md-9">
                    <h3 class="box-title font-16">
                        <select class="btn font-16 btn-md btn-default text-bold top-select" id="assets_category" onchange="assets_filter()">
                            <option value="0">All Categories</option>
                            <?php 
                                $o_asset_categories = fetchtable('o_asset_categories', "status=1", "uid", "desc", "0,10", "uid ,name ");
                                while ($t = mysqli_fetch_array($o_asset_categories)) {
                                    $uid = $t['uid'];
                                    $name = $t['name'];
                                    echo "<option value='$uid'>$name</option>";
                                } 
                            ?>
                        </select>


                        <!-- <select class="btn font-16 btn-md btn-default text-bold top-select" id="sel_branch" onchange="customer_filters()">
                            <option value="0">All Branches</option>
                        </select> -->

                        <!-- <input type="search" class="btn font-16 btn-md btn-default text-bold top-select" placeholder="Search items" id="sel_status" onchange="customer_filters()"/> -->

                    </h3>
                </div>
                <div class="col-md-3">
                    <a class="btn btn-default bg-purple-gradient btn-md" href="assets?cat=cart"><i class="fa fa-shopping-cart"></i> Go to Cart</a>
                    <a class="btn btn-success float-right" href="?asset-add-edit"><i class="fa fa-plus"></i> ADD NEW</a>
                </div>
            </div>
        </div>
        <div class="box-body" id="asset-list">
            <i>Loading...</i>
            <!-- /.box -->



                <!-- /.box-header -->
                <!-- <div class="row">

                    <?php
                    for($i = 0; $i < 20; ++$i){
                        echo '<div class="col-md-2">
                        <a href="?cat=asset&asset=123">
                        <div class="box box-default box-solid">
                                <div class="box-header box-title font-bold font-16 text-black">Samsung Galaxy S12</div>
                                <div class="box-body">
                                    <img src="dist/img/photo1.png" width="100%">
                                </div>
                                <div class="box-footer font-16">
                        Ksh. 6,000
                        </div>
                            </div>
                        </a>
                    </div>';
                    }
                    ?>


                </div> -->
                <!-- /.box-body -->

            <!-- /.box -->
        </div>
        <!-- <div class="box-footer">
            <div class="pager" id="pager_foot">
                <nav aria-label="Pager">
                    <ul class="list-group">
                        <li class="page-item">
                            <a class="previous page-link btn bg-blue text-bold disabled" id="prev_" href="#" onclick="prev()" tabindex="-1"><i class="fa fa-arrow-left"></i> Previous</a>
                        </li>
                        <li class="page-item"><i>Page <span id="page_no">1</span></i></li>
                        <li class="page-item">
                            <a class="next page-link btn bg-blue text-bold" id="next_" onclick="next()" href="#">Next <i class="fa fa-arrow-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div> -->
        <!-- /.col -->
    </div>

    <?php
        echo "<div style='display: none;'>".paging_values_hidden('uid > 0',0,10,'uid','desc','', 'load_assets', 1)."</div>"
    ?>
</section>
