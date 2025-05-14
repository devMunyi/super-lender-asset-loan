<?php
$bid = decurl($_GET['branch']);
$branch_det = fetchonerow("o_branches", "uid='$bid'", "*");
$freeze = $branch_det['freeze'] ?? '';
$freeze_options = array('NONE', 'API', 'MANUAL', 'BOTH');
$branchFreezeTitle = $branch_det['name'] ?? '';
$branchFreezeID = $_GET['branch'] ?? 0;

?>
<section class="content-header">
    <h1>
        Branch Details
        <small><?php echo $branch_det['name']; ?> </small>
        <a title='Back to branches' class='font-16' href="branches"><i class='fa fa-arrow-circle-up'></i></a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Branch</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified font-16">
                    <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-info"></i> Info</a></li>
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
                                    <?php
                                    $uid = $branch_det['uid'];
                                    $name = $branch_det['name'];
                                    $address = $branch_det['address'] && trim($branch_det['address']) != "" ? $branch_det['address'] : "<i>Unspecified</i>";
                                    $region_id = intval($branch_det['region_id']);

                                    $manager_id = $branch_det['manager_id'];
                                    $added_date = $branch_det['added_date'];
                                    $status = $branch_det['status'];

                                    if ($region_id > 0) {
                                        $region_name = fetchrow('o_regions', "uid = $region_id", "name");
                                    } else {
                                        $region_name = "<i>Unspecified</i>";
                                    }

                                    if ($branch > 0) {
                                        $br = fetchonerow("o_branches", "uid='$branch'", "uid, name");
                                        $branch_name = $br['name'];
                                    } else {
                                        $branch_name = "<i>No Branch</i>";
                                    }

                                    // get regional manager
                                    $regional_manager = fetchrow('o_users', "uid = $manager_id AND status = 1", "name");

                                    if (!$regional_manager) {
                                        $regional_manager = "<i>Unspecified</i>";
                                    }


                                    // get branch manager
                                    $branch_manager = fetchrow('o_users', "branch = $branch AND user_group = 5 AND status = 1", "name");


                                    if (!$branch_manager) {
                                        $branch_manager = "<i>Unspecified</i>";
                                    }

                                    $f = fetchonerow("o_branch_statuses", "uid='$status'", "name, color");
                                    $status_name = $f['name'];
                                    $state_col = $f['color'];
                                    ?>
                                    <tr>
                                        <td class="text-bold">UID</td>
                                        <td><?php echo encurl($uid); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Name</td>
                                        <td><?php echo $name; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Address</td>
                                        <td><?php echo $address; ?></td>
                                    </tr>


                                    <!-- <tr>
                                        <td class="text-bold">Branch Manager</td>
                                        <td><? // echo $branch_manager ; 
                                            ?> </td>
                                    </tr> -->
                                    <tr>
                                        <td class="text-bold">Region</td>
                                        <td><?php echo $region_name; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Regional Manager</td>
                                        <td><?php echo $regional_manager; ?></td>
                                    </tr>

                                    <tr>
                                        <td class="text-bold">Added Date</td>
                                        <td><?php echo $added_date; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Status</td>
                                        <td><span class='label $ <?php echo $state_col; ?>'><?php echo $status_name; ?></span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="branches?add-edit=<?php echo encurl($bid); ?>" class="btn btn-warning btn-block btn-md"><i class="fa fa-edit"></i> Edit Branch</a></td>
                                    </tr>
                                    <tr>
                                        <td><a href="branches?add-edit" class="btn btn-success btn-block btn-md"><i class="fa fa-plus"></i> New Branch</a></td>
                                    </tr>

                                    <tr>
                                        <?php

                                        $branch_status = intval($branch_det['status']);
                                        if ($bid > 0 && in_array($branch_status, array(1, 2))) {
                                            if ($branch_status == 1) {
                                                $btn_class = "btn-danger";
                                                $btn_text = "Block Branch";
                                                $title = "block this branch";
                                                $action = "block";
                                            } else {
                                                $btn_class = "btn-success";
                                                $btn_text = "Unblock Branch";
                                                $title = "unblock this branch";
                                                $action = "unblock";
                                            }

                                        ?>
                                        <td>
                                            <!-- Freeze/Unfreeze Branch select element -->
                                            <select onchange="freezeBranch('<?php echo $branchFreezeTitle; ?>', '<?php echo $branchFreezeID ; ?>')" class="form-control text-center btn-gradient" id="freeze_option">
                                                <option value="">--Select Freeze Option--</option>
                                                <?php
                                                foreach($freeze_options as $opt){
                                                    $selected = $opt == $freeze ? "selected" : "";
                                                    echo "<option value='$opt' $selected>$opt</option>";
                                                }
                                                ?>
                                            </select>

                                        </td>
                                        <?php }

                                        ?>
                                    </tr>
                                    <tr>
                                    <tr>
                                        <td><button onclick="resetBranchLimit('<?php echo encurl($bid); ?>', '<?php echo $branchFreezeTitle; ?>')" class='btn btn-danger btn-block btn-md'><i class='fa fa-flash'></i> Reset Limit</button></td>
                                    </tr>
                                    </tr>
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
                                <div class="scroll-hor body-box">
                                    <table class="table table-condensed table-striped table-hover" id="">
                                        <thead>
                                            <tr>
                                                <th>Event</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                                            $o_events_ = fetchtable('o_events', "tbl='o_branches' AND fld = $bid AND status = 1", "uid", "desc", "0,1000", "uid ,event_details ,event_date ,event_by ,status");
                                            while ($d = mysqli_fetch_array($o_events_)) {
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
                    </div>
                    <!-- /.tab-pane -->

                </div>
                <!-- /.tab-content -->
            </div>
        </div>
    </div>
</section>