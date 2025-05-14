<section class="content-header">
    <h1>

        <?php
        $return_page = $_GET['return-to'];
        $product_idd = $_GET['product'];
        echo arrow_back("$return_page?product=".$product_idd,'Product'); ?>
        <?php
        $aid = $_GET['addOnId'];
        if ($aid > 0) {
            $addon = fetchonerow('o_addons', "uid='" . decurl($aid) . "'");
            $name = $addon['name'];
            $description = $addon['description'];
            $amount = $addon['amount'];
            $amount_type = $addon['amount_type'];
            $loan_stage = $addon['loan_stage'];
            $automatic = $addon['automatic'];
            $addon_on = $addon['addon_on'];
            $from_day = $addon['from_day'];
            $to_day = $addon['to_day'];
            $apply_frequency = $addon['apply_frequency'];
            $notify_user = $addon['notify_user'];
            $applicable_loan = $addon['applicable_loan'];
            $paid_upfront = $addon['paid_upfront'];
            $deducted_upfront = $addon['deducted_upfront'];
            if($paid_upfront == 1){ $yes = 'SELECTED';
            }
            else{
                $no = 'SELECTED';
            }
            if($deducted_upfront == 1){
                $dyes = 'SELECTED';
            }
            else{
                $dno = 'SELECTED';
            }

            if($amount_type == 'PERCENTAGE'){
                $perc_selected = 'SELECTED';
            }
            else{
                $fixed_selected = 'SELECTED';
            }

            echo "AddOn <small>Edit</small>";
        } else {
            $cust = array();
            $customer_id = "";
            echo "AddOn <small>Add</small>";
        }

        if($loan_stage == 'CREATION'){
            $creation = 'SELECTED';
        }
        else if ($loan_stage == 'APPROVAL'){
            $approval = 'SELECTED';
        }
        else if($loan_stage == 'PARTIAL_DEFAULT'){
            $partial_default = 'SELECTED';
        }
        else if($loan_stage == 'FINAL_DEFAULT'){
            $final_default = 'SELECTED';
        }
        else if($loan_stage == 'LOAN_EXTENSION'){
            $loan_extension = 'SELECTED';
        }
        else{
            $approval = 'SELECTED';
        }




        ?>

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">AddOn/Add</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <!-- /.box -->

            <div class="box">

                <!-- /.box-header -->
                <div class="row">
                    <div class="col-sm-2">


                    </div>
                    <div class="col-xs-1"></div>
                    <div class="col-sm-6">
                        <!-- /.box-header -->

                        <h3>Loan AddOn <?php
                            if($aid > 0){
                                ?>
                                <a class="text-blue font-14 pull-right"
                                   href="details?add-addon&return-to=loan-products&product=<?php echo $product_idd; ?>">New AddOn
                                    <i class="fa fa-angle-double-right"></i></a>
                            <?php
                            }
                            ?></h3>
                            <form class="form-horizontal" onsubmit="return false;" method="post">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="addon_name" class="col-sm-3 control-label">Name</label>
                                        <input type="hidden" id="addon_id" value="<?php echo $aid; ?>">

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $name; ?>" id="addon_name">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="addon_description" class="col-sm-3 control-label">Description</label>

                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="addon_description"><?php echo $description; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="addon_amount" class="col-sm-3 control-label">Amount</label>

                                        <div class="col-sm-9">
                                            <input type="number" value="<?php echo $amount;?>" class="form-control" placeholder="In % or a Fixed Value" id="addon_amount">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount_type" class="col-sm-3 control-label">Type</label>

                                        <div class="col-sm-9">
                                            <select id="amount_type" class="form-control">
                                                <option value="PERCENTAGE" <?php echo $perc_selected; ?> >Percentage</option>
                                                <option value="FIXED_VALUE" <?php echo $fixed_selected; ?>>Fixed Value</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="addon_name" class="col-sm-3 control-label">Loan Stage</label>

                                        <div class="col-sm-9">
                                            <select id="loan_stage" class="form-control">
                                                <option value="0">--Select One</option>
                                                <option value="CREATION" <?php echo $creation; ?>>During Creation</option>
                                                <option value="APPROVAL" <?php echo $approval; ?>>During Approval</option>
                                                <option value="PARTIAL_DEFAULT" <?php echo $partial_default; ?>>Partial Default</option>
                                                <option value="FINAL_DEFAULT" <?php echo $final_default; ?>>Final Default</option>
                                                <option value="LOAN_EXTENSION" <?php echo $loan_extension; ?>>During Loan Extension</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="automatic" class="col-sm-3 control-label">Apply Automatically</label>

                                        <div class="col-sm-9">
                                            <select id="automatic" class="form-control">
                                                <option value="0" <?php echo $automatically_no; ?>>No</option>
                                                <option value="1" <?php echo $automatically_yes; ?>>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="addon_on"  class="col-sm-3 control-label">AddOn On</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $addon_on; ?>" placeholder="Qualified Loan Field Name" id="addon_on">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="starting_day" class="col-sm-3 control-label">Starting Day</label>

                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" value="<?php echo $from_day; ?>" id="starting_day">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="ending_day" class="col-sm-3 control-label">Ending Day</label>

                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" value="<?php echo $to_day; ?>" id="ending_day">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="apply_frequency" class="col-sm-3 control-label">Apply Frequency</label>

                                        <div class="col-sm-9">
                                            <input type="text" value="<?php echo $apply_frequency; ?>" class="form-control" placeholder="DAILY, WEEKLY, MONTHLY" id="apply_frequency">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="notify_user" class="col-sm-3 control-label">Notify User</label>

                                        <div class="col-sm-9">
                                            <select id="notify_user" class="form-control">
                                                <option <?php echo $automatically_no; ?> value="0">No</option>
                                                <option <?php echo $automatically_yes; ?> value="1">Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="applicable_loan" class="col-sm-3 control-label">Applicable Loan</label>

                                        <div class="col-sm-9">
                                            <input type="number" value="<?php echo $applicable_loan; ?>" class="form-control" placeholder="0 for all loans" id="applicable_loan">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="paid_upfront" class="col-sm-3 control-label">Paid Upfront</label>

                                        <div class="col-sm-9">
                                            <select id="paid_upfront" class="form-control">
                                                <option <?php echo $no; ?> value="0">No</option>
                                                <option <?php echo $yes; ?> value="1">Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="deducted_upfront" class="col-sm-3 control-label">Deducted Upfront</label>

                                        <div class="col-sm-9">
                                            <select id="deducted_upfront" class="form-control">
                                                <option <?php echo $dno; ?> value="0">No</option>
                                                <option <?php echo $dyes; ?> value="1">Yes</option>
                                            </select>
                                        </div>
                                    </div>



                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="box-footer">
                                            <br/>
                                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                            <button type="submit"
                                                    class="btn btn-success bg-green-gradient btn-lg pull-right"
                                                    onclick="addon_save('<?php echo $aid; ?>');">
                                                Save
                                            </button>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.box-body -->

                                <!-- /.box-footer -->
                            </form>



                    </div>
                    <div class="col-sm-2 box-body">


                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>
