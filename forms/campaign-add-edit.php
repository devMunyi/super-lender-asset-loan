<section class="content-header">
    <h1>
        <?php echo arrow_back('broadcasts', 'Broadcasts'); ?>
        <?php
        if ($max_sms_char > 10) {
        } else {
            $max_sms_char = 900;
        }


        $cid = $_GET['campaign-add-edit'];
        if ($cid > 0) {
            $edit_campaign = permission($userd['uid'], 'o_campaigns', "0", "update_");
            if ($edit_campaign != 1) {
                die(errormes("You don't have permission to edit broadcasts"));
                exit();
            }

            $campaign = fetchonerow('o_campaigns', "uid='" . decurl($cid) . "'");
            $target_cust = $campaign['target_customers'];
            $target_cust_ = fetchrow("o_campaign_target_customers", "uid = $target_cust", "name");
            $campaign_id = $_GET['campaign-add-edit'];

            echo "Campaign <small>Edit</small> <span class='text-green text-bold'>$target_cust_</span> <a title='Back to campaign' class='font-16' href=\"broadcasts?campaign=$cid\"><i class='fa fa-arrow-circle-up'></i></a>";
            $act = "<span class='text-orange'><i class='fa fa-edit'></i>Edit</span>";
        } else {
            $add_campaign = permission($userd['uid'], 'o_campaigns', "0", "create_");
            if ($add_campaign != 1) {
                die(errormes("You don't have permission to add broadcasts"));
                exit();
            }

            $campaign = array();
            $campaign_id = "";
            echo "Campaign <small>Add</small>";
            $act = "<span class='text-green'><i class='fa fa-edit'></i>Add</span>";
        }
        ?>

    </h1>

    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Campaign</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <!-- /.box -->

            <div class="box">

                <!-- /.box-header -->
                <div class="row">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-6">
                        <?php
                        //message form start
                        $message_types = [
                            'GENERAL' => 'General',
                            'PERSONALIZED' => 'Personalized'
                        ];
                        if (isset($_GET['message'])) {
                            $message_id = $_GET['message'];
                            if ($message_id > 0) {
                                echo "<h3>Edit Message";
                                $message_det = fetchonerow("o_campaign_messages", "uid=" . decurl($message_id), "message, type");
                                $message = $message_det['message'];
                                $type = $message_det['type'];
                            } else {
                                echo "<h3>Add Message";
                                $message = "";
                            }
                        ?>
                            <a class="btn-outline-black pull-right" href="broadcasts?campaign=<?php echo $cid; ?>">Finish <i class="fa fa-angle-double-right"></i></a><a href="broadcasts?campaign-add-edit=<?php echo $cid; ?>&message" class="btn-outline-black pull-right">New <i class="fa fa-plus"></i></a>

                            </h3>

                            <form class="form-horizontal" onsubmit="return false;" id="" method="post">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="description" class="col-sm-3 control-label">Description</label>

                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="description"><?php echo $message; ?></textarea>

                                            <div class="well well-sm font-italic text-black font-13" style="margin-top: 15px;"><b>Variables:</b>
                                                {loans.account_number}, {loans.loan_amount}, {loans.disbursed_amount}, {loans.total_repayable_amount}, {loans.total_repaid}, {loans.loan_balance}, {loans.current_instalment},
                                                {loans.current_instalment_amount}, {loans.given_date}, {loans.next_due_date},{loans.final_due_date} <br>
                                                {customers.full_name},{customers.primary_mobile}, {customers.national_id}, {customers.loan_limit}

                                            </div>


                                        </div>
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9 text-purple font-16 font-bold" id="char_count"></div>
                                    </div>

                                    <!-- select input for message type options GENERAL AND PERSONALIZED -->
                                    <div class="form-group">
                                        <label for="message_type" class="col-sm-3 control-label">Message Type</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" name="message_type" id="message_type">
                                                <option value="">Select One</option>
                                                <?php foreach ($message_types as $key => $value) {
                                                    $selected = $type == $key ? 'selected' : '';
                                                    echo "<option value='$key' $selected>$value</option>";
                                                } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="box-footer">
                                            <br />
                                            <button type="submit" class="btn btn-lg btn-flat btn-default">Cancel</button>
                                            <button type="submit" id="btn_campaign_msg"
                                                class="btn btn-success btn-flat bg-green-gradient btn-lg pull-right"
                                                onclick="campaign_save_message('<?php echo $cid; ?>','<?php echo $message_id; ?>');">
                                                Save
                                            </button>
                                        </div>


                                    </div>
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <hr />
                                        <h5>Samples</h5>
                                        <?php
                                        $recent_templates = fetchtable('o_campaign_messages', "status=1", "uid", "desc", "10", "uid, message");
                                        while ($rm = mysqli_fetch_array($recent_templates)) {
                                            $muid = $rm['uid'];
                                            $mmessage = $rm['message'];
                                            echo "<div class=\"well well-sm font-italic text-black font-13\">
                                                 $mmessage
                                            </div>";
                                        }

                                        ?>
                                    </div>



                                </div>
                                <!-- /.box-body -->

                                <!-- /.box-footer -->
                            </form>


                        <?php
                        } else {
                        ?>
                            <h3><?php echo $act; ?> Campaign Details</h3>
                            <form class="form-horizontal" id="campaign-upload" method="POST" action="action/campaign/campaign_save" enctype="multipart/form-data">
                                <div class="box-body">
                                    <div class="form-group">
                                        <input type="hidden" id="cid" name="cid" value="<?php echo $cid; ?>">
                                        <label for="title" class="col-sm-3 control-label">Title</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $campaign['name']; ?>" id="title" name="title" placeholder="Campaign title">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-3 control-label">Description</label>

                                        <div class="col-sm-9">
                                            <textarea class="form-control" name="description" id="description"><?php echo $campaign['description']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="date" class="col-sm-3 control-label">Running Date</label>

                                        <div class="col-sm-9">
                                            <input type="datetime-local" class="form-control" name="date" id="date"
                                                value="<?php echo $campaign['running_date']; ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="end_date" class="col-sm-3 control-label">End Date</label>

                                        <div class="col-sm-9">
                                            <input type="datetime-local" class="form-control" name="end_date" id="end_date"
                                                value="<?php echo $campaign['running_date']; ?>">
                                            <p class="help-block">If you have waivers, they will revert/end on this date</p>
                                        </div>
                                    </div>





                                    <div class="form-group">
                                        <label for="target_customers" class="col-sm-3 control-label">Target Customers</label>

                                        <div class="col-sm-4">
                                            Upload
                                            <input type="file" id="target_customers" name="target_customers" class="form-control">
                                        </div>
                                        <div class="col-sm-1">
                                            Or
                                        </div>
                                        <div class="col-sm-4">
                                            Select
                                            <select class="form-control" name="general_audience">
                                                <option value="0">Select Audience</option>
                                                <option value="1">All Defaulters</option>
                                                <option value="4">All With Active Loans</option>
                                                <option value="5">All Leads</option>
                                                <option value="6">All Customers (Defaulters or not)</option>
                                            </select>
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
                                                <div class="messagecampaign-upload" id="message"></div>
                                                <div class="progresscampaign-upload" id="progress">
                                                    <div class="barcampaign-upload" id="bar"></div>
                                                    <br>
                                                    <div class="percentcampaign-upload" id="percent"></div>
                                                </div>
                                            </div>
                                            <br />
                                            <!-- <button class="btn btn-lg btn-default">Cancel</button> -->
                                            <!-- <input type="button" class="btn btn-lg btn-default">Cancel</input> -->
                                            <button type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('campaign-upload');">Upload
                                            </button>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.box-body -->

                                <!-- /.box-footer -->
                            </form>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="col-sm-4 box-body">
                        <?php
                        if (isset($_GET['message'])) {
                            $message_list = $_GET['campaign-add-edit'];
                        ?>
                            <div class="small_list" id="message_">
                                Loading ...
                            </div>

                        <?php
                        }
                        ?>
                    </div>

                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>