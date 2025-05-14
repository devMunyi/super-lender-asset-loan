<section class="content-header">
    <?php
    $campaign = $_GET["campaign"];
    $campaign_id = decurl($campaign);
    $campaign_ = fetchonerow("o_campaigns", "uid = $campaign_id");
    $camp_id_ = $campaign_["uid"];
    $camp_id = encurl($camp_id_);
    $camp_name = $campaign_["name"];
    $camp_message = $campaign_["description"];
    $camp_added_date = $campaign_["added_date"];
    $camp_running_date = $campaign_["running_date"];
    $waiver_addon = $campaign_['waiver_addon'];
    $waiver_addon_name = fetchrow('o_addons', "uid='$waiver_addon'", "name") ?? "";
    $waiver_amount = $campaign_['waiver_amount'];
    $waiver_deduction = $campaign_['waiver_deduction'];
    $waiver_deduction_name = fetchrow('o_deductions', "uid='$waiver_deduction'", "name");
    $camp_running_status_ = $campaign_['running_status'];
    $camp_running_status = fetchrow("o_campaign_running_statuses", "uid = $camp_running_status_", "name");

    if ($waiver_amount == 0) {
        $waiver_amount = 100;
    }



    $target_customers = $campaign_["target_customers"];
    $total_customers = $campaign_['total_customers'];

    $audience_list = array("1" => "All Defaulters", "4" => "All with active Loans", "5" => "All Leads", "6" => "All Customers (Defaulters or not)");

    if ($target_customers > 0) {
        $audience = $audience_list[$target_customers];
    } else {
        $audience = "$target_customers";
    }

    //$camp_target_audience = fetchrow("o_campaign_target_customers", "uid = '$camp_target_'", "name");
    $camp_status = $campaign_["status"];
    $state = fetchonerow("o_campaign_statuses", "code='$camp_status'", "color, name");
    $status = "<span class='label " . $state['color'] . "'>" . $state['name'] . "</span>";
    $run_version = $SMS_RMQ_IS_SET == 1 ? "2" : "";
    ?>
    <h1>
        <?php echo arrow_back('broadcasts', 'Broadcasts'); ?>
        Campaign Details
        <small><?php echo $camp_name; ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?php echo $camp_name; ?></li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified font-16">
                    <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-info"></i> Info</a></li>
                    <li class="nav-item nav-100"><a href="#tab_2" onclick="audience_list();" data-toggle="tab" aria-expanded="false"><i class="fa fa-link"></i> Audience</a></li>
                    <li class="nav-item nav-100"><a href="#tab_3" data-toggle="tab" aria-expanded="false"><i class="fa fa-envelope"></i> Campaign Message</a></li>
                    <li class="nav-item nav-100"><a href="#tab_4" data-toggle="tab" aria-expanded="false"><i class="fa fa-smile-o"></i> Waivers</a></li>
                    <li class="nav-item nav-100"><a href="#tab_5" onclick="campaignEvents('<?php echo $campaign; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-clock-o"></i> Events</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon-x"><i class="fa fa-info"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <tr>
                                        <td class="text-bold">UID</td>
                                        <td><?php echo decurl($camp_id); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Campaign Name</td>
                                        <td><?php echo $camp_name; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Message</td>
                                        <td><?php echo $camp_message; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Added Date</td>
                                        <td><?php echo $camp_added_date; ?><br><span class="text-orange font-13 font-bold"><?php echo fancydate($camp_added_date); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Running Date</td>
                                        <td><?php echo $camp_running_date; ?><br><span class="text-orange font-13 font-bold"><?php echo fancydate($camp_running_date); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Running Status
                                        <td><?php echo $camp_running_status; ?></td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Waiver Addon
                                        <td><?php echo $waiver_addon_name; ?></td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Waiver Deduction
                                        <td><?php echo $waiver_deduction_name; ?></td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Waiver %
                                        <td><?php echo $waiver_amount; ?></td>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-bold">Target Audience</td>
                                        <td><?php echo $audience; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Campaign Status</td>
                                        <td><span class="text-success"><?php echo $status; ?></span></td>
                                    </tr>

                                </table>
                                <br />
                                <?php
                                ///-----Campaign has audience
                                $doc = "campaign_uploads/$target_customers";
                                if (file_exists($doc) || $target_customers > 0) {
                                    // echo "<tr><td>The file $doc exists</td></tr>";
                                    $file_exists = 1;
                                } else {
                                    $file_exists = 0;
                                    echo ("<span class='text-red font-18'><i class='fa fa-warning'></i>Please upload audience file</span>");
                                }
                                $message_found = checkrowexists('o_campaign_messages', "status=1 AND campaign_id=$campaign_id");
                                if ($message_found == 0) {
                                    echo ("<br/><span class='text-red font-18'><i class='fa fa-warning'></i>Please add a message</span>");
                                }

                                if ($message_found == 1 && $file_exists) {

                                    echo "<h4 class='text-purple font-bold'><i class='fa fa-info-circle'></i> Campaign is scheduled to run on <span class='font-italic text-black'>$camp_running_date</span> </h4>";

                                ?>
                                    <button onclick="run_campaign('<?php echo $camp_id_; ?>', 'Run Campaign Now', '<?php echo $run_version; ?>')" class="btn bg-black-gradient btn-lg btn-md"><i class="fa fa-bomb"></i> Run Campaign Now</button>
                                <?php
                                }

                                ///-----Campaign has message
                                ?>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="?campaign-add-edit" class="btn btn-success btn-block btn-md"><i class="fa fa-plus"></i> New Campaign</a></td>
                                    </tr>
                                    <?php
                                    if ($camp_status == 1) {
                                    ?>
                                        <tr>
                                            <td><a href="?campaign-add-edit=<?php echo $camp_id; ?>" class="btn btn-primary btn-block btn-md"><i class="fa fa-edit"></i> Update Campaign </a></td>
                                        </tr>

                                        <tr>
                                            <td><button onclick="disable_campaign('<?php echo $camp_id_; ?>', 'disable this campaign')" class="btn btn-warning btn-block btn-md"><i class="fa fa-ban"></i> Stop Campaign</button></td>
                                        </tr>

                                        <tr>
                                            <td><button onclick="delete_campaign('<?php echo $camp_id_; ?>', 'delete this campaign')" class="btn btn-danger btn-block btn-md"><i class="fa fa-times"></i> Delete Campaign</button></td>
                                        </tr>
                                    <?php }
                                    ?>

                                    <?php
                                    if ($camp_status == 2) {
                                    ?>
                                        <tr>
                                            <td><button onclick="enable_campaign('<?php echo $camp_id_; ?>', 'enable this campaign')" class="btn btn-primary btn-block btn-md"><i class="fa fa-check-square-o"></i>Enable Campaign</button></td>
                                        </tr>

                                        <tr>
                                            <td><button onclick="delete_campaign('<?php echo $camp_id_; ?>', 'delete this campaign')" class="btn btn-danger btn-block btn-md"><i class="fa fa-times"></i> Delete Campaign</button></td>
                                        </tr>

                                    <?php }
                                    ?>

                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="row">
                            <div class="col-md-2">
                                <?php
                                if ($camp_status == 2) {
                                ?>
                                    <span class="info-box-icon-x"><i class="fa fa-exclamation-triangle"></i></span>
                                <?php } else { ?>
                                    <span class="info-box-icon-x"><i class="fa fa-link"></i></span>
                                <?php } ?>
                            </div>
                            <div class="col-md-7">
                                <?php
                                $o_users_ = fetchtable('o_customers', "uid > 0 AND status > 1", "uid", "desc", "0,10");
                                while ($l = mysqli_fetch_array($o_users_)) {
                                    $uid = $l['uid'];
                                    $uid_enc = encurl($uid);
                                    $full_name = $l['full_name'];
                                    $branch = $l['branch'];
                                    $branch_name = fetchrow('o_branches', "uid='$branch'", "name");
                                    $status = $l['status'];
                                    $state = fetchonerow("o_customer_statuses", "code='$status'", " color, name");
                                } ?>
                                <div id="inactive_campaign"></div>
                                <table id="example2" class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Branch</th>
                                            <th>Status</th>
                                            <th>Action</th>

                                        </tr>
                                    </thead>
                                    <tbody id="audience_list">
                                        <p style="display:none;"><input id="_camp_id_" type="text" name="" value="<?php echo $camp_id_; ?>"></p>
                                        <tr>
                                            <td colspan="5" class="text-center">Loading data...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Branch</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <?php
                                    if ($camp_status == 2) { ?>

                                        <tr>
                                            <td><a href="?campaign-add-edit" class="btn btn-success btn-block btn-md"><i class="fa fa-plus"></i> New Campaign</a></td>
                                        </tr>
                                        <tr>
                                            <td><button onclick="enable_campaign('<?php echo $camp_id_; ?>', 'enable this campaign')" class="btn btn-primary btn-block btn-md"><i class="fa fa-check-square-o"></i>Enable Campaign</button></td>
                                        </tr>

                                        <tr>
                                            <td><button onclick="delete_campaign('<?php echo $camp_id_; ?>', 'delete this campaign')" class="btn btn-danger btn-block btn-md"><i class="fa fa-times"></i> Delete Campaign</button></td>
                                        </tr>

                                    <?php }
                                    ?>
                                </table>

                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->

                    <div class="tab-pane" id="tab_3">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-sm-2">
                                    <span style="align-content: center;" class="info-box-icon-x"><i class="fa fa-envelope"></i></span>
                                </div>
                                <div class="col-md-7">
                                    <table class="table-bordered font-14 table table-hover">
                                        <?php
                                        $messages = fetchtable('o_campaign_messages', "status=1 AND campaign_id=$campaign_id", "uid", "desc", "0,10", "*");
                                        $messages_total = countotal('o_campaign_messages', "status=1 AND campaign_id=$campaign_id");
                                        if ($messages_total > 0) {
                                            while ($m = mysqli_fetch_array($messages)) {
                                                $uid = $m['uid'];
                                                $message = $m['message'];
                                                echo "<tr><td class=\"text-bold\">Message</td><td>$message</td></tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='8'><i>No Message Found</i></td></tr>";
                                        }

                                        ?>
                                    </table>
                                </div>

                                <div class="col-md-3">
                                    <table class="table">
                                        <?php
                                        if ($messages_total > 0) {
                                        ?>
                                            <tr>
                                                <td><a href="broadcasts?campaign-add-edit=<?php echo encurl($campaign_id); ?>&message" class="btn btn-warning btn-block  btn-md"><i class="fa  fa-pencil"></i>Edit/Delete Message</a></td>
                                            </tr>
                                        <?php
                                        } else {
                                        ?>
                                            <tr>
                                                <td><a href="broadcasts?campaign-add-edit=<?php echo encurl($campaign_id); ?>&message" class="btn btn-success btn-block  btn-md"><i class="fa  fa-plus"></i>Add/Edit Message</a></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>


                                        <tr style="display: none;">
                                            <td><button class="btn btn-primary btn-block btn-md"><i class="fa  fa-pencil"></i> Edit Contact</button></td>
                                        </tr>
                                        <tr style="display: none;">
                                            <td><button class="btn btn-danger btn-block btn-md"><i class="fa  fa-times"></i> Remove Contact</button></td>
                                        </tr>
                                    </table>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="tab-pane" id="tab_4">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-sm-2">
                                    <span style="align-content: center;" class="info-box-icon-x"><i class="fa fa-smile-o"></i></span>
                                </div>
                                <div class="col-md-7">
                                    <div class="font-16 font-bold text-purple">Waivers will reduce the amount payable by offsetting the penalties/Interests</div>

                                    <form class="form-horizontal" id="waiver-save" method="POST" action="action/campaign/waiver-save" enctype="multipart/form-data">
                                        <div class="box-body">
                                            <div class="form-group">
                                                <input type="hidden" id="cid" name="cid" value="<?php echo $campaign; ?>">
                                                <label for="addon_to_offset" class="col-sm-3 control-label">Addon to offset</label>

                                                <div class="col-sm-9">
                                                    <select class="form-control" name="addon">
                                                        <option value="0">Select an Addon</option>
                                                        <?php
                                                        $all_addons = fetchtable('o_addons', "status=1", "name", "asc", "100", "uid,name");
                                                        while ($ad = mysqli_fetch_array($all_addons)) {
                                                            $aid = $ad['uid'];
                                                            $aname = $ad['name'];
                                                            if ($aid == $waiver_addon) {
                                                                $selected = "SELECTED";
                                                            } else {
                                                                $selected = "";
                                                            }
                                                            echo "<option $selected value='$aid'>$aname</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                    <p class="help-block">Please select the addon corresponding to penalty/interest</p>
                                                </div>


                                            </div>
                                            <div class="form-group">
                                                <label for="deduction_to_apply" class="col-sm-3 control-label">Deduction Value %</label>

                                                <div class="col-sm-9">
                                                    <input type="number" class="form-control" name="addon_amount" value="<?php echo $waiver_amount; ?>">
                                                    <p class="help-block">If you want to waive whole penalty/interest, leave it as 100%</p>
                                                </div>


                                            </div>
                                            <div class="form-group">
                                                <label for="addon_to_offset" class="col-sm-3 control-label">Deduction</label>

                                                <div class="col-sm-9">
                                                    <select class="form-control" name="deduction">
                                                        <option value="0">Select a Deduction</option>
                                                        <?php
                                                        $all_addons = fetchtable('o_deductions', "status=1", "name", "asc", "100", "uid, name");
                                                        while ($ad = mysqli_fetch_array($all_addons)) {
                                                            $did = $ad['uid'];
                                                            $dname = $ad['name'];
                                                            if ($did == $waiver_deduction) {
                                                                $selected = "SELECTED";
                                                            } else {
                                                                $selected = "";
                                                            }
                                                            echo "<option $selected value='$did'>$dname</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                    <p class="help-block">If there is no deduction in the list, add a deduction called 'Waiver'</p>
                                                </div>
                                            </div>
                                            <div class="form-group">

                                                <label for="addon_to_offset" class="col-sm-3 control-label"> </label>
                                                <div class="col-sm-4">
                                                    <input type="submit" onclick="formready('waiver-save')" class="btn btn-primary" value="Save" />
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="reset" class="btn btn-default" value="Save" />
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="box-footer">
                                                    <div class="prgress">
                                                        <div class="messagewaiver-save" id="message"></div>
                                                        <div class="progresswaiver-save" id="progress">
                                                            <div class="barwaiver-save" id="bar"></div>
                                                            <br>
                                                            <div class="percentwaiver-save" id="percent"></div>
                                                        </div>
                                                    </div>
                                                    <br />

                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->

                     <!-- /.tab-pane -->
                     <div class="tab-pane" id="tab_5">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon-x"><i class="fa fa-clock-o"></i></span>
                            </div>
                            <div class="col-md-10">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Event</th>
                                            <th>Event ID</th>
                                        </tr>
                                    </thead>
                                    <tbody id="campaign_events_placeholder">
                                        <tr>
                                            <td colspan='3'>
                                                <i>Loading events...</i>
                                            </td>
                                        </tr>
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


<?php
echo "<div style= 'display:none'>" . paging_values_hidden('uid > 0', 0, 10, 'uid', 'desc', '', 'audience_list') . "</div>";
?>