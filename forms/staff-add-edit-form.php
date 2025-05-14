<section class="content-header">
    <h1>
        <?php echo arrow_back('staff','Staff'); ?>
        <?php
        $sid = $_GET['add-edit'];
        if($sid > 0){
            $staff = fetchonerow('o_users',"uid='".decurl($sid)."'");
            $staff_name = $staff['name'];
            $tag = $staff['tag'];
            $pair_ = $staff['pair'];
            $pass = "";
            echo "Staff <small>Edit</small> <span class='text-green text-bold'>$staff_name</span> <a title='Back to staff list' class='font-16' href=\"staff?staff=$sid\"><i class='fa fa-arrow-circle-up'></i></a>";
            $act = "<span class='text-orange'><i class='fa fa-edit'></i>Edit</span>";
        }else{
            $staff = array();
            $pass = generateRandomString(6);
            echo "Staff <small>Add</small>";
            $act = "<span class='text-green'><i class='fa fa-edit'></i>Add</span>";
        }
        ?>

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Staff/Add</li>
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
                    <div class="col-sm-7 box-body">
                        <!-- /.box-header -->
                        <!-- form start -->
                        <h3><?php echo $act; ?> Staff Details</h3>
                        <form class="form-horizontal" onsubmit="return false;" method="post">
                            <div class="box-body">
                                <div class="form-group">
                                    <input type="hidden" id="sid" value="<?php echo $sid; ?>">
                                    <label for="full_name" class="col-sm-3 control-label">*Full Name</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?php echo $staff['name'] ?>" id="full_name" placeholder="First Middle Last">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="email_" class="col-sm-3 control-label">*Email</label>

                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" value="<?php echo $staff['email'] ?>" autocomplete="OFF" id="email_" placeholder="Preferably a work email">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="national_id" class="col-sm-3 control-label">*National ID</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?php echo $staff['national_id'] ?>" id="national_id" placeholder="8 Characters">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="phone_number" class="col-sm-3 control-label">*Phone Number</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="phone_number" value="<?php echo $staff['phone'] ?>" placeholder="07...">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="passwo" class="col-sm-3 control-label">*Password</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="passwo" value="<?php echo $pass; ?>" placeholder="Unchanged">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="group_" class="col-sm-3 control-label">*Group</label>

                                    <div class="col-sm-9">
                                        <select class="form-control" id="group_">
                                            <option value="0">--Select One</option>
                                            <?php

                                            $recs = fetchtable('o_user_groups',"uid>0 AND uid!=1", "uid", "asc", "100", "uid ,name");
                                            while($r = mysqli_fetch_array($recs))
                                            {
                                                $uid = $r['uid'];
                                                $name = $r['name'];
                                                if($uid ==  $staff['user_group']){
                                                    $g_selected = 'SELECTED';
                                                }
                                                else{
                                                    $g_selected = "";
                                                }
                                                echo "<option $g_selected value=\"$uid\">$name</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="group_" class="col-sm-3 control-label">*Tag</label>
                                      <?php
                                       $tags = array("A","B","C","BRANCH","LO","CO","CC","FA","EDC","BRANCH-MANAGER","CC-MANAGER","FA-MANAGER","EDC-MANAGER", "RM");
                                      $t_selected = "";
                                      ?>
                                    <div class="col-sm-9">
                                        <select class="form-control" id="tag_">
                                            <option value="">--NONE</option>
                                            <?php
                                               for($t=0; $t < sizeof($tags); ++$t){
                                                $tagx = $tags[$t];
                                                if($tag == $tagx){
                                                   $t_selected = "SELECTED";
                                                }
                                                else{
                                                    $t_selected = "";
                                                }
                                                echo "<option  $t_selected value=\"$tagx\">$tagx</option>";     
                                               }

                                        
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="group_" class="col-sm-3 control-label">Pair</label>

                                    <div class="col-sm-9">
                                        <input type="number" class="form-control" id="pair_" value="<?php echo $pair_; ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="branch_" class="col-sm-3 control-label">*Branch</label>

                                    <div class="col-sm-9">
                                        <select class="form-control" id="branch_">
                                            <option value="0">--Select One</option>
                                            <?php
                                            $o_branches_ = fetchtable('o_branches',"uid>0", "name", "asc", "0,1000", "uid ,name ");
                                            while($b = mysqli_fetch_array($o_branches_))
                                            {
                                                $uid = $b['uid'];
                                                $name = $b['name'];
                                                if($uid ==  $staff['branch']){
                                                    $b_selected = 'SELECTED';
                                                }
                                                else{
                                                    $b_selected = "";
                                                }

                                                echo "<option $b_selected value='$uid'>$name</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <?php
                                if($sid > 0){
                                ?>
                                <div class="form-group">
                                    <label for="branch_" class="col-sm-3 control-label">Branches() <a class="text-blue" title="For agents who access multiple branches e.g. regional managers or FAs"><i class="fa fa-question-circle"></i></a></label>

                                    <div class="col-sm-8">
                                        <select class="form-control" id="branches_">
                                            <option value="0">--Select One</option>
                                            <?php
                                            $o_branches_ = fetchtable('o_branches',"uid>0", "name", "asc", "0,1000", "uid ,name ");
                                            while($b = mysqli_fetch_array($o_branches_))
                                            {
                                                $uid = $b['uid'];
                                                $name = $b['name'];

                                                echo "<option value='$uid'>$name</option>";
                                            }
                                            ?>
                                        </select>

                                        <div id="staff_branches">

                                        </div>
                                    </div>
                                    <div class="col-sm-1">
                                        <button class="btn btn-success" onclick="addbranches('<?php echo $sid; ?>');"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                                <?php
                                }
                                ?>
                                <div class="form-group">
                                    <label for="status_" class="col-sm-3 control-label">*Status</label>

                                    <div class="col-sm-9">
                                        <select class="form-control" id="status_">
                                            <option value="0">--Select One</option>

                                            <?php
                                            $o_staff_statuses_ = fetchtable('o_staff_statuses',"uid>0", "uid", "asc", "0,20", "uid ,name ");
                                            while($r = mysqli_fetch_array($o_staff_statuses_))
                                            {
                                                $uid = $r['uid'];
                                                $name = $r['name'];
                                                if($uid == $staff['status']){
                                                    $selected = "SELECTED";
                                                }else{
                                                    $selected = "";
                                                }
                                                echo "<option $selected value=\"$uid\">$name</option>";
                                            }
                                            ?>
                                            <option value="99">Delete</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                <div class="box-footer">
                                    <br/>
                                    <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                    <button type="submit" class="btn btn-success btn-lg pull-right" onclick="staff_save();">Save</button>
                                </div>
                                </div>

                            </div>
                            <!-- /.box-body -->

                            <!-- /.box-footer -->
                        </form>

                    </div>
                    <div class="col-sm-3 box-body">
                        <?php
                        if($sid > 0) {
                            ?>
                            <button style="margin-top: 20px; margin-left: 90px;" onclick="block_member('<?php echo decurl($sid);?>', 'block this member')" class="btn btn-danger btn-md"><i class="fa fa-ban"></i> Block Member </button>
                            <?php
                        }
                        else{
                            ?>
                            
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

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        staff_branches(<?php echo $sid; ?>)
    });
</script>