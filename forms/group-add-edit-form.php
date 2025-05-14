<section class="content-header">
    <h1>
        <?php echo arrow_back('groups', 'Groups'); ?>
        <?php
        $gid = $_GET['group-add-edit'];

        if ($gid > 0) {
            $gr = decurl($gid);
            $g = fetchonerow('o_customer_groups', "uid='$gr'", "*");
            echo "Group <small>Edit</small> <span class='text-green text-bold'>" . $g['group_name'] . "</span> <a title='Back to group' class='font-16' href=\"groups?group=$gid\"><i class='fa fa-arrow-circle-up'></i></a>";
            $act = "<span class='text-orange'><i class='fa fa-edit'></i>Edit</span>";
        } else {
            $gr = 0;
            echo "Group <small>Add</small>";
            $act = "<span class='text-green'><i class='fa fa-plus'></i>Add</span>";
        }
        ?>

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Groups</li>
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
                        <?php
                        $edit = $_GET['customer-add-edit'];
                        if ($edit > 0) {

                            ////---------Check if user has edit permission
                            $update_addon = permission($userd['uid'], 'o_customer_groups', "0", "update_");
                            if ($update_addon != 1) {
                                die(errormes("You don't have permission to edit customer groups"));
                                exit();
                            }

                        ?>


                        <?php
                        } else {
                            $update_addon = permission($userd['uid'], 'o_customer_groups', "0", "create_");
                            if ($update_addon != 1) {
                                die(errormes("You don't have permission to add group"));
                                exit();
                            }
                        }
                        ?>
                    </div>
                    <div class="col-sm-6">
                        <!-- /.box-header -->
                        <!-- form start -->








                        <h3><?php echo $act; ?> Customer Group</h3>
                        <form class="form-horizontal" onsubmit="return false;" method="post">
                            <div class="box-body">
                                <div class="form-group">

                                    <label for="group_name" class="col-sm-3 control-label">Group Name</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?php echo $g['group_name'] ?>" id="group_name">
                                    </div>
                                </div>



                                <div class="form-group">
                                    <label for="branch_" class="col-sm-3 control-label">Branch</label>

                                    <div class="col-sm-9">
                                        <select class="form-control" id="group_branch">
                                            <option value="0">--Select One</option>
                                            <?php
                                            $o_branches_ = fetchtable('o_branches', "status=1", "uid", "desc", "0,100", "uid ,name ");
                                            while ($u = mysqli_fetch_array($o_branches_)) {
                                                $uid = $u['uid'];
                                                $name = $u['name'];
                                                if (($g['branch']) == $uid) {
                                                    $selected_br = 'SELECTED';
                                                } else {
                                                    $selected_br = '';
                                                }
                                                echo "<option $selected_br value='$uid'>$name</option>";
                                            }

                                            ?>
                                        </select>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label for="group_description" class="col-sm-3 control-label">Group Description</label>

                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="group_description" placeholder="About this group"><?php echo $g['group_description']; ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="Meeting Schedule" class="col-sm-3 control-label">Meeting Schedule</label>

                                    <div class="col-sm-4">
                                        <select class="form-control" id="meeting_day">
                                            <option value="0">--Select Day</option>
                                            <?php
                                            
                                            $days = fetchtable('o_days', "status=1", "uid", "asc", "0,100", "uid ,name ");
                                            while ($day = mysqli_fetch_array($days)) {
                                                $duid = $day['uid'];
                                                $dname = $day['name'];
                                                if ($g['meeting_day'] == $duid) {
                                                    $selected = 'SELECTED';
                                                } else {
                                                    $selected = '';
                                                }

                                                echo "<option $selected value=\"$duid\">$dname</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-5">
                                        <input class="form-control" type="time" id="meeting_time" value="<?php echo $g['meeting_time']; ?>" />
                                    </div>
                                </div>

                                <div class="form-group">

                                    <label for="Meeting Venue" class="col-sm-3 control-label">Meeting venue</label>

                                    <div class="col-sm-9">
                                        <input class="form-control" type="text" id="meeting_venue" value="<?php echo $g['meeting_venue']; ?>" />
                                    </div>
                                </div>

                                <div class="form-group">

                                    <label for="leader_name" class="col-sm-3 control-label">Group Leader Name</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?php echo $g['chair_name'] ?>" id="leader_name">
                                    </div>
                                </div>

                                <div class="form-group">

                                    <label for="group_phone" class="col-sm-3 control-label">Group Phone</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?php echo $g['group_phone'] ?>" id="group_phone">
                                    </div>
                                </div>
                                <div class="form-group">

                                    <label for="group_till" class="col-sm-3 control-label">Group Till/Paybill</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?php echo $g['till'] ?>" id="group_till">
                                    </div>
                                </div>
                                <div class="form-group">

                                    <label for="group_acc" class="col-sm-3 control-label">Group Account</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?php echo $g['account_number'] ?>" id="group_acc">
                                    </div>
                                </div>




                                <div class="col-sm-3">

                                </div>
                                <div class="col-sm-9">
                                    <div class="box-footer">
                                        <br />
                                        <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                        <button type="submit" class="btn btn-success btn-lg pull-right" onclick="customer_group_save();">Save
                                        </button>
                                    </div>
                                </div>

                            </div>
                            <!-- /.box-body -->

                            <!-- /.box-footer -->
                        </form>

                        <input type="hidden" id="gid" value="<?php echo $gid; ?>">
                    </div>

                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>