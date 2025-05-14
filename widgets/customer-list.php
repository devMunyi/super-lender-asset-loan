<section class="content-header">
    <h1>
        <?php

        if (isset($_GET['type'])) {
            $view = $_GET['type'];
        } else {
            $view = 'Customer';
        }
        echo $view; ?>
        <small>List</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?php echo $view; ?></li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <!-- /.box -->

            <div class="box">
                <div class="box-header bg-info">
                    <div class="row">
                        <div class="col-md-10">
                            <h3 class="box-title font-16">
                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="customer_order" onchange="customer_filters()">
                                    <option value="desc">Newest First</option>
                                    <option value="asc">Oldest First</option>
                                </select>


                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="sel_branch" onchange="customer_filters()">
                                    <option value="0">All Branches</option>
                                    <?php
                                    ////-----------List Branches To See
                                    $read_all = permission($userd['uid'], 'o_customers', "0", "read_");
                                    if ($read_all == 1) {
                                        $andbranch = "";
                                    } else {
                                        $user_branch = $userd['branch'];
                                        $andbranch = " AND uid='$user_branch'";
                                        //////-----Check users who view multiple branches
                                        $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
                                        if (sizeof($staff_branches) > 0) {
                                            ///------Staff has been set to view multiple branches
                                            array_push($staff_branches, $userd['branch']);
                                            $staff_branches_list = implode(",", $staff_branches);
                                            $anduserbranch = " AND branch in ($staff_branches_list)";
                                            $andbranch = " AND uid in ($staff_branches_list)";
                                        }
                                    }


                                    $o_branches_ = fetchtable('o_branches', "status > 0 $andbranch", "name", "asc", "1000", "uid ,name ");
                                    while ($w = mysqli_fetch_array($o_branches_)) {
                                        $uid = $w['uid'];
                                        $name = $w['name'];
                                        echo "<option value='$uid'>$name</option>";
                                    }
                                    ?>
                                </select>

                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="sel_status" onchange="customer_filters()">
                                    <option value="0">All Types</option>
                                    <?php
                                    $o_statuses_ = fetchtable('o_customer_statuses', "status > 0 AND code IN(1, 2)", "name", "asc", "0,10", "uid, code ,name ");
                                    while ($s = mysqli_fetch_array($o_statuses_)) {
                                        $code = $s['code'];
                                        $name = $s['name'];
                                        echo "<option value='$code'>$name</option>";
                                    }
                                    ?>
                                </select>


                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="sel_agent" onchange="customer_filters()">
                                    <option value="0">All Agents</option>
                                    <?php
                                    $o_agents_ = fetchtable('o_users', "status > 0", "name", "asc", "0,100000", "uid, name");
                                    while ($a = mysqli_fetch_array($o_agents_)) {
                                        $uid = $a['uid'];
                                        $name = $a['name'];
                                        echo "<option value='$uid'>$name</option>";
                                    }
                                    ?>
                                </select>
                            </h3>
                        </div>
                        <div class="col-md-2">

                            <a class="btn btn-success float-right" href="?customer-add-edit"><i class="fa fa-plus"></i> ADD NEW</a>
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Passport</th>
                                <th>Customer</th>
                                <th>Agent</th>
                                <th>Phone</th>
                                <th>Branch</th>
                                <th>Latest Loan</th>
                                <th> <?php echo $switch_home_with_business_address ? "Business Address" :  "Home Direction"; ?></th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="customer_list">
                            <tr>
                                <td colspan="10">Loading data...</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Passport</th>
                                <th>Customer</th>
                                <th>Agent</th>
                                <th>Phone</th>
                                <th>Branch</th>
                                <th>Latest Loan</th>
                                <th><?php echo $switch_home_with_business_address ? "Business Address" :  "Home Direction"; ?></th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>

<?php
echo "<div style='display: none;'>" . paging_values_hidden('status = 1', 0, 10, 'uid', 'desc', '', 'customer_list', 1) . "</div>"
?>