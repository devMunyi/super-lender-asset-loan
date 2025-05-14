<?php
$pid = decurl($_GET['product']);
$y = fetchonerow("o_loan_products","uid='$pid'","*");
$name = $y['name'];
$description = $y['description'];
$period = $y['period'];
$period_units = $y['period_units'];
$min_amount = $y['min_amount'];
$max_amount = $y['max_amount'];
$pay_frequency = $y['pay_frequency'];
$percent_breakdown = $y['percent_breakdown'];
$added_date = $y['added_date'];
$status = $y['status'];   $status_name = status($status);

        $total_addons = countotal("o_product_addons","product_id='$pid' AND status=1");
        $total_deductions = countotal("o_product_deductions","product_id='$pid' AND status=1");
?>
<section class="content-header">
    <h1>
        Product Details
        <small><?php echo $name; ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Products</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified font-16">
                    <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-info"></i> Details</a></li>
                    <li class="nav-item nav-100"><a href="#tab_2" data-toggle="tab" aria-expanded="false"><i class="fa fa-plus-circle"></i> AddOns</a></li>
                    <li class="nav-item nav-100"><a href="#tab_3" data-toggle="tab" aria-expanded="false"><i class="fa fa-minus-circle"></i> Deductions</a></li>
                    <li class="nav-item nav-100"><a href="#tab_4" data-toggle="tab" aria-expanded="false"><i class="fa fa-check-circle"></i> Loan Stages</a></li>
                    <li class="nav-item nav-100"><a href="#tab_5" onclick="product_reminders('<?php echo $pid; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-gear"></i> Reminder Settings</a></li>
                    <li class="nav-item nav-100"><a href="#tab_6" data-toggle="tab" aria-expanded="false"><i class="fa fa-bar-chart"></i> Stats</a></li>



                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">

                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-info"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <tr><td class="text-bold">CODE</td><td><?php echo $pid; ?></td></tr>
                                    <tr><td class="text-bold">Product Name</td><td><h3><?php echo $name; ?></h3></td></tr>
                                    <tr><td class="text-bold">Description</td><td><?php echo $description; ?></td></tr>
                                    <tr><td class="text-bold">Period</td><td><?php echo $period; ?> (<?php echo $period_units; ?>)</td></tr>
                                    <tr><td class="text-bold">Payment Frequency</td><td><?php echo $pay_frequency; ?> (Days)</td></tr>
                                    <tr><td class="text-bold">Min Value</td><td><?php echo $min_amount; ?></td></tr>
                                    <tr><td class="text-bold">Maximum Value</td><td><?php echo $max_amount; ?> </td></tr>
                                    <tr><td class="text-bold">AddOns</td><td><a href="" class="label label-default font-14"><?php echo $total_addons; ?> Addons</a></td></tr>
                                    <tr><td class="text-bold">Deductions</td><td><a href="" class="label label-default font-14"><?php echo $total_deductions; ?> Deductions</a></td></tr>
                                    <tr><td class="text-bold">Status</td><td><span class="text-success"><?php echo $status_name; ?></span></td></tr>

                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr><td><a href="?add-edit" class="btn btn-success btn-block  btn-md grid-width-10"><i class="fa fa-plus-circle"></i> Add New Product</a></td></tr>
                                    <tr><td><a href="?add-edit=<?php echo encurl($pid); ?>" class="btn btn-primary btn-block btn-md"><i class="fa fa-pencil"></i> Edit this Product</a></td></tr>
                                    <tr><td><button onclick="loan_product_delete(<?php echo $pid; ?>)" class="btn btn-danger btn-block btn-md"><i class="fa fa-times"></i> Delete this Product</button></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
                            </div>
                            <div class="col-md-8">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead><tr><th>ID</th><th>Name</th><th>Amount</th><th>Stage</th><th>Applied Automatically</th><th>Paid Upfront</th><th>Deducted Upfront</th></th><th>Status</th></tr></thead>
                                    <tbody>
                                    <?php
                                    $o_addons_ = fetchtable('o_addons',"status=1", "uid", "desc", "0,50", "uid ,name ,description ,amount ,amount_type ,loan_stage, automatic, paid_upfront, deducted_upfront");
                                    while($c = mysqli_fetch_array($o_addons_))
                                    {
                                        $uid = $c['uid'];
                                        $name = $c['name'];
                                        $description = $c['description'];
                                        $amount = $c['amount'];
                                        $amount_type = $c['amount_type'];
                                        $loan_stage = $c['loan_stage'];
                                        $automatic = yesno($c['automatic']);
                                        $paid_upfront = yesno($c['paid_upfront']);
                                        $deducted_upfront = yesno($c['deducted_upfront']);


                                              $addon = addon_exists($uid, $pid);

                                              $act = "<a class='text-warning' href=\"details?add-addon&return-to=loan-products&product=".encurl($pid)."&addOnId=".encurl($uid)."\"><i class='fa fa-pencil'></i></a>";


                                        echo "<tr><td>$uid</td><td>$name</td><td>$amount ($amount_type)</td><td>$loan_stage</td><td>$automatic</td><td>$paid_upfront</td><td>$deducted_upfront</td><td> <span id='a$uid$pid'>$addon</span> | $act</td> </tr>";
                                    }
                                    ?>


                                    </tbody>


                                </table>
                            </div>
                            <div class="col-md-2">
                                <table class="table">
                                    <tr><td><a href="details?add-addon&return-to=loan_product=loan-products?product=<?php echo encurl($pid); ?>" class="btn btn-success"><i class="fa  fa-plus"></i> New Addon</a></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_3">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead><th>Name</th><th>Amount</th><th>Stage</th><th>Applied Automatically</th><th>Status</th></tr></thead>
                                    <tbody>
                                    <?php
                                    $o_addons_ = fetchtable('o_deductions',"status=1", "uid", "desc", "0,50", "uid ,name ,description ,amount ,amount_type ,loan_stage, automatic ");
                                    while($c = mysqli_fetch_array($o_addons_))
                                    {
                                        $uid = $c['uid'];
                                        $name = $c['name'];
                                        $description = $c['description'];
                                        $amount = $c['amount'];
                                        $amount_type = $c['amount_type'];
                                        $loan_stage = $c['loan_stage'];
                                        $automatic = $c['automatic'];
                                        if($automatic == 1){
                                            $auto = "YES";
                                        }else{
                                            $auto = 'NO';
                                        }

                                        $deduction = deduction_exists($uid, $pid);

                                        echo "<tr><td>$name</td><td>$amount ($amount_type)</td><td>$loan_stage</td><td style='text-align: center;'>$auto</td><td> <span id='d$uid$pid'>$deduction</span> </td> </tr>";
                                    }
                                    ?>


                                    </tbody>


                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr><td><a href="details?add-deduction&return-to=loan_product=loan-products?product=<?php echo encurl($pid); ?>" class="btn btn-success"><i class="fa  fa-plus"></i> New Deduction</a></td></tr>
                                </table>

                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_4">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table table-bordered font-14 table table-hover">
                                    <tr><th>#</th><th>Name</th><th>Description</th><th style="width: 20%;" colspan="2"> Status </th></tr>
                                    <tbody>
                                    <?php
                                    $stage = 1;
                                    $o_loan_stages_ = fetchtable('o_loan_stages',"status=1", "stage_order", "asc", "0,100", "uid ,name ,description ,stage_order ,permissions");
                                    while($i = mysqli_fetch_array($o_loan_stages_))
                                    {
                                        $uid = $i['uid'];
                                        $name = $i['name'];
                                        $description = $i['description'];
                                        $stage_order = $i['stage_order'];
                                        $permissions = $i['permissions'];


                                        $stage_val = stage_exists($uid, $pid);

                                        echo "<tr><td>$stage</td><td>$name</td><td>$description</td><td colspan='2'><span id='s$uid$pid'>$stage_val</span></td></tr>";
                                        $stage = $stage + 1;
                                    }
                                    ?>
                                    </tbody>
                                    <tr><th>#</th><th>Name</th><th>Description</th><th colspan="2">Status</th></tr>

                                </table>
                                <br/>
                                <div class="well">
                                <h4 class="text-orange"><i class="fa fa-gear"></i> Stage Settings</h4>
                                    <div class="row">
                                <div class="form-group">
                                    <label for="next_int" class="col-sm-3 control-label"> Loan's Final Stage</label>

                                    <div class="col-sm-7">
                                        <?php
                                        $is_final_stage = fetchrow('o_product_stages',"product_id='$pid' AND is_final_stage=1 AND status=1","stage_id");
                                        ?>
                                        <select class="form-control" id="final_stage">
                                            <option>--Select One</option>
                                            <?php

                                            $o_loan_stages_ = fetchtable('o_loan_stages',"status=1", "stage_order", "asc", "0,10", "uid ,name");
                                            while($ii = mysqli_fetch_array($o_loan_stages_))
                                            {
                                                $uid = $ii['uid'];
                                                $name = $ii['name'];
                                                if($is_final_stage == $uid){
                                                    $selected = "SELECTED";
                                                }
                                                else{
                                                    $selected = "";
                                                }
                                                echo "<option $selected value=\"$uid\">$name</option>";

                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button class="btn btn-sm btn-primary" onclick="final_stage_save('<?php echo $pid; ?>')"><i class="fa fa-check"></i> Save</button>
                                    </div>
                                </div>
                                    </div>
                                </div>


                                <div class="well" style="display: none;">
                                <h4 class="text-orange"><i class="fa fa-check-circle"></i> Who can approve?</h4>
                                <div class="form-group">
                                   <div class="row">
                                    <div class="col-sm-4">

                                        <select class="form-control" id="final_stage">
                                            <option>--Select User</option>
                                            <?php

                                            $o_loan_stages_ = fetchtable('o_loan_stages',"status=1", "stage_order", "asc", "0,10", "uid ,name");
                                            while($ii = mysqli_fetch_array($o_loan_stages_))
                                            {
                                                $uid = $ii['uid'];
                                                $name = $ii['name'];
                                                if($is_final_stage == $uid){
                                                    $selected = "SELECTED";
                                                }
                                                else{
                                                    $selected = "";
                                                }
                                                echo "<option $selected value=\"$uid\">$name</option>";

                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">

                                        <select class="form-control" id="final_stage">
                                            <option>--Select Stage</option>
                                            <?php

                                            $o_loan_stages_ = fetchtable('o_loan_stages',"status=1", "stage_order", "asc", "0,10", "uid ,name");
                                            while($ii = mysqli_fetch_array($o_loan_stages_))
                                            {
                                                $uid = $ii['uid'];
                                                $name = $ii['name'];
                                                if($is_final_stage == $uid){
                                                    $selected = "SELECTED";
                                                }
                                                else{
                                                    $selected = "";
                                                }
                                                echo "<option $selected value=\"$uid\">$name</option>";

                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <button class="btn btn-sm btn-primary" onclick="final_stage_save('<?php echo $pid; ?>')"><i class="fa fa-plus"></i> Add</button>
                                        <button class="btn btn-sm btn-danger" onclick="final_stage_save('<?php echo $pid; ?>')"><i class="fa fa-times"></i> Remove</button>
                                    </div>
                                   </div>
                                </div>
                                </div>

                                <div class="well" style="display: none;">
                                <h4 class="text-orange"><i class="fa fa-times-circle"></i> Who can reject?</h4>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-sm-4">

                                                <select class="form-control" id="final_stage">
                                                    <option>--Select User</option>
                                                    <?php

                                                    $o_loan_stages_ = fetchtable('o_loan_stages',"status=1", "stage_order", "asc", "0,10", "uid ,name");
                                                    while($ii = mysqli_fetch_array($o_loan_stages_))
                                                    {
                                                        $uid = $ii['uid'];
                                                        $name = $ii['name'];
                                                        if($is_final_stage == $uid){
                                                            $selected = "SELECTED";
                                                        }
                                                        else{
                                                            $selected = "";
                                                        }
                                                        echo "<option $selected value=\"$uid\">$name</option>";

                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-4">

                                                <select class="form-control" id="final_stage">
                                                    <option>--Select Stage</option>
                                                    <?php

                                                    $o_loan_stages_ = fetchtable('o_loan_stages',"status=1", "stage_order", "asc", "0,10", "uid ,name");
                                                    while($ii = mysqli_fetch_array($o_loan_stages_))
                                                    {
                                                        $uid = $ii['uid'];
                                                        $name = $ii['name'];
                                                        if($is_final_stage == $uid){
                                                            $selected = "SELECTED";
                                                        }
                                                        else{
                                                            $selected = "";
                                                        }
                                                        echo "<option $selected value=\"$uid\">$name</option>";

                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <button class="btn btn-sm btn-primary" onclick="final_stage_save('<?php echo $pid; ?>')"><i class="fa fa-plus"></i> Add</button>
                                                <button class="btn btn-sm btn-danger" onclick="final_stage_save('<?php echo $pid; ?>')"><i class="fa fa-times"></i> Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div class="col-md-3">
                                <div class="row">
                                <table class="table">
                                    <tr><td><a href="details?add-loan-stage&return-to=loan_product=loan-products?product=<?php echo encurl($pid); ?>" class="btn btn-success"><i class="fa  fa-plus"></i> New Stage</a></td></tr>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_5">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-minus"></i></span>
                            </div>
                            <div class="col-md-7">
                                <div id="preminders_">
                                    Loading...
                                </div>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr><td><button onclick="product_reminder_modal(0,<?php echo $pid; ?>)" class="btn btn-success"><i class="fa  fa-calendar"></i> Add Reminder</button></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_6">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead><th>Name</th><th>Amount</th><th>Stage</th><th>Status</th></tr></thead>
                                    <tbody>
                                    <?php
                                    $o_addons_ = fetchtable('o_deductions',"status=1", "uid", "desc", "0,50", "uid ,name ,description ,amount ,amount_type ,loan_stage ");
                                    while($c = mysqli_fetch_array($o_addons_))
                                    {
                                        $uid = $c['uid'];
                                        $name = $c['name'];
                                        $description = $c['description'];
                                        $amount = $c['amount'];
                                        $amount_type = $c['amount_type'];
                                        $loan_stage = $c['loan_stage'];
                                        $status = "<a onclick=\"\" class=\"text-success\"><i class=\"fa fa-check\"></i> Added </a>";
                                        echo "<tr><td>$name</td><td>$amount ($amount_type)</td><td>$loan_stage</td><td> $status </td> </tr>";
                                    }
                                    ?>


                                    </tbody>


                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr><td><a href="details?add-deduction&return-to=loan_product=loan-products?product=<?php echo encurl($pid); ?>" class="btn btn-success"><i class="fa  fa-plus"></i> New Deduction</a></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.tab-content -->
            </div>
        </div>
    </div>
</section>