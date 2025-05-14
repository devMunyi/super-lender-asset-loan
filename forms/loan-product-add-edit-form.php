<section class="content-header">
    <h1>
        <?php echo arrow_back('loan-products','Loan Products'); ?>
        <?php
        $sid =  intval($_GET['add-edit']);
        
        if ($sid > 0) {
            $prod_id = decurl($sid);
            $prod = fetchonerow('o_loan_products', "uid=$prod_id");

            echo "Product <small>Edit</small>";
            $act = "<span class='text-orange'><i class='fa fa-edit'></i>Edit</span>";
            $breadcrumb_title = "Product/Edit";
        } else {
            $prod = array();
            $prod_id = "0";
            echo "Product <small>Add</small>";
            $act = "<span class='text-green'><i class='fa fa-edit'></i>Add</span>";
            $breadcrumb_title = "Product/Add";
        }
        ?>

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?php echo $breadcrumb_title; ?></li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <!-- /.box -->

            <div class="box">

                <!-- /.box-header -->
                <div class="row">

                    <div class="col-xs-1"></div>
                    <div class="col-sm-6">
                        <!-- /.box-header -->
                        <!-- form start -->

                        <h3><?php echo $act; ?> Loan Product</h3>
                        <form class="form-horizontal" onsubmit="return false;" method="post">
                            <div class="box-body">
                                <div class="form-group">
                                    <label for="product_name" class="col-sm-3 control-label">Product Name</label>

                                    <div class="col-sm-9">
                                        <input class="form-control" type="hidden" id="product_id" value="<?php echo $prod_id ?>">
                                        <input class="form-control" type="text" id="product_name" value="<?php echo $prod['name'] ?>">
                                    </div>

                                </div>

                                <div class="form-group">
                                    <label for="description" class="col-sm-3 control-label">Product Description</label>

                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="description"><?php echo $prod['description'] ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="period" class="col-sm-3 control-label">Period</label>

                                    <div class="col-sm-5">
                                        <input class="form-control" type="number" id="period" placeholder="1" value="<?php echo $prod['period']; ?>" />
                                    </div>
                                    <div class="col-sm-4">
                                        <select class="form-control" id="period_units">
                                            <option value="0">--Select One</option>
                                            <?php
                                            $punits = [
                                                ['uid' => 1, 'name' => 'Days(s)'],
                                                ['uid' => 7, 'name' => 'Week(s)'],
                                                ['uid' => 30, 'name' => 'Month(s)'],
                                                ['uid' => 365, 'name' => 'Year(s)']
                                            ];
                                            foreach($punits as $unit){
                                                $puid = $unit['uid'];
                                                $pname = $unit['name'];
                                                if($prod['period_units'] == $puid){
                                                    $selected = 'SELECTED';
                                                }else{
                                                    $selected = '';
                                                }

                                                echo "<option $selected value=\"$puid\">$pname</option>";
                                            }

                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="min_amount" class="col-sm-3 control-label">Min Amount</label>

                                    <div class="col-sm-9">
                                        <input class="form-control" type="number" id="min_amount" value="<?php echo $prod['min_amount']; ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="max_amount" class="col-sm-3 control-label">Max Amount</label>

                                    <div class="col-sm-9">
                                        <input class="form-control" type="number" id="max_amount" value="<?php echo $prod['max_amount']; ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="pay_frequency" class="col-sm-3 control-label">Pay Frequency</label>

                                    <div class="col-sm-9">
                                        <select class="form-control" id="pay_frequency">
                                            <?php
                                            $freqs = [
                                                ['uid' => 0, 'name' => '--Select One'],
                                                ['uid' => 1, 'name' => 'Daily'],
                                                ['uid' => 7, 'name' => 'Weekly'],
                                                ['uid' => 14, 'name' => 'BiWeekly'],
                                                ['uid' => 30, 'name' => 'Monthly'],
                                                ['uid' => 60, 'name' => 'Two Months'],
                                                ['uid' => 90, 'name' => 'Quarterly'],
                                                ['uid' => 180, 'name' => 'SemiAnnually'],
                                                ['uid' => 360, 'name' => 'Annually']
                                            ];

                                            foreach($freqs as $freq){
                                                $fuid = $freq['uid'];
                                                $fname = $freq['name'];
                                                if($prod['pay_frequency'] == $fuid){
                                                    $selected = 'SELECTED';
                                                }else{
                                                    $selected = '';
                                                }

                                                echo "<option $selected value='$fuid'>$fname</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="payment_breakdown" class="col-sm-3 control-label">Payment Breakdown</label>

                                    <div class="col-sm-9">
                                        <input class="form-control" type="text" placeholder="e.g. 20, 30, 50. Leave blank for equal breakdown" id="payment_breakdown" value="<?php echo $prod['payment_breakdown']; ?>" />
                                    </div>
                                </div>

                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <div class="box-footer">
                                        <br/>
                                        <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                        <button type="submit"
                                                class="btn btn-success bg-green-gradient btn-lg pull-right"
                                                onclick="save_loan_product();">
                                            Submit
                                        </button>
                                    </div>
                                </div>

                            </div>
                            <!-- /.box-body -->

                            <!-- /.box-footer -->
                        </form>

                    </div>
                    <div class="col-xs-1"></div>
                    <div class="col-sm-4 card">


                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>
