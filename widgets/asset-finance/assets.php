<section class="content-header">
    <h1>
        Shop Items

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Shop Items</li>
    </ol>
</section>
<section class="content">
    <div class="box">
        <div class="box-header bg-info">
            <div class="row">
                <div class="col-md-9">
                    <h3 class="box-title font-16">
                        <select class="btn font-16 btn-md btn-default text-bold top-select" id="assets_category"
                            onchange="assets_filter()">
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

                    </h3>
                </div>
                <div class="col-md-3">
                    <a class="btn btn-default bg-purple-gradient btn-md" href="assets?cat=cart"><i
                            class="fa fa-shopping-cart"></i> Go to Cart <span class="badge"
                            id="cart-counter">0</span></a>
                    <a class="btn btn-success float-right" href="?asset-add-edit"><i class="fa fa-plus"></i> ADD NEW</a>
                </div>
            </div>
        </div>
        <div class="box-body">
            <div class="container-fluid">
                <div id="asset-list">
                    <i>Loading...</i>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
echo "<div style='display: none;'>" . paging_values_hidden('uid > 0', 0, 10, 'uid', 'desc', '', 'load_assets', 1) . "</div>"
    ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const savedCartValue = localStorage.getItem("cartCounterValue");
        if (savedCartValue !== null) {
            document.getElementById("cart-counter").innerHTML = savedCartValue;
        }
    });
</script>