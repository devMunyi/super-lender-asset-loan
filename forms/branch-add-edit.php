<section class="content-header">
    <h1>
        <?php // echo arrow_back('branches','Branches'); 
        ?>
        <?php
        $bid = intval($_GET['add-edit']);
        if ($bid > 0) {
            $branch = fetchonerow('o_branches', "uid='" . decurl($bid) . "'");
            $branch_name = $branch['name'];
            echo "Branch <small>Edit</small> <span class='text-green text-bold'>$branch_name</span> <a title='Back to branch details' class='font-16' href=\"branches?branch=$bid\"><i class='fa fa-arrow-circle-up'></i></a>";
            $act = "<span class='text-orange'><i class='fa fa-edit'></i>Edit</span>";
        } else {
            $branch = array();
            echo "Branch <small>Add</small> <a title='Back to branches' class='font-16' href=\"branches\"><i class='fa fa-arrow-circle-up'></i></a>";
            $act = "<span class='text-green'><i class='fa fa-edit'></i>Add</span>";
        }

        // regions count
        $regions_count = totaltable('o_regions', "uid>0", "uid");
        ?>

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Branch/Add</li>
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
                        <h3><?php echo $act; ?> Branch Details</h3>
                        <form class="form-horizontal" onsubmit="return false;" method="post">
                            <div class="box-body">
                                <div class="form-group">
                                    <input type="hidden" id="bid" value="<?php echo $bid; ?>">
                                    <label for="branch_name" class="col-sm-3 control-label">*Name</label>

                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?php echo $branch['name'] ?>" id="branch_name" placeholder="Branch Name">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="address" class="col-sm-3 control-label">Address</label>

                                    <div class="col-sm-9">
                                        <textarea class="form-control" name="address" id="address"><?php echo $branch['address']; ?></textarea>
                                    </div>
                                </div>


                                <?php
                                if ($regions_count > 0) {
                                ?>

                                    <div class="form-group">
                                        <label for="region_id" class="col-sm-3 control-label">Region</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="region_id">
                                                <option value="0">--Select One</option>
                                                <?php

                                                $recs = fetchtable('o_regions', "uid>0", "uid", "asc", "100", "uid ,name");
                                                while ($r = mysqli_fetch_array($recs)) {
                                                    $uid = $r['uid'];
                                                    $name = $r['name'];
                                                    if ($uid ==  $branch['region_id']) {
                                                        $g_selected = 'SELECTED';
                                                    } else {
                                                        $g_selected = "";
                                                    }
                                                    echo "<option $g_selected value=\"$uid\">$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>


                                <?php
                                }
                                ?>
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <div class="box-footer">
                                        <br />
                                        <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                        <button type="submit" class="btn btn-success btn-lg pull-right" onclick="branch_save();">Save</button>
                                    </div>
                                </div>

                            </div>
                            <!-- /.box-body -->

                            <!-- /.box-footer -->
                        </form>

                    </div>
                    <div class="col-sm-3 box-body">


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
</script>