<div class="row">
    <div class="col-xs-12">

        <!-- /.box -->

        <div class="box">
            <div class="box-header bg-info">
                <div class="row">
                    <div class="col-md-10">
                        <?php
                        if (isset($_GET['approvals'])) {
                            $need_approval = "need approval";
                        ?>
                            <h3 class="box-title">

                                <a class="btn font-16 text-black font-bold" href=""><i class="fa fa-check-square-o"></i>REQUIRES YOUR APPROVAL <label id="approvals" class="label label-primary">0</label></a>
                            </h3>
                        <?php
                        } else {
                            $need_approval = "";
                        ?>
                            <h3 class="box-title">
                                <a class="btn bg-blue-gradient" href="loans"><i class="fa fa-refresh"> </i> Show All</a>
                                <select class="btn font-16 btn-default btn-md btn-default text-bold top-select" id="loan_order" onchange="loan_filters()">
                                    <option value="desc">Newest First</option>
                                    <option value="asc">Oldest First</option>
                                </select>
                                <select class="btn font-16 btn-default btn-md btn-default text-bold top-select" id="sel_product" onchange="loan_filters()">
                                    <option value="0">All Products</option>
                                    <?php
                                    $o_loan_products_ = fetchtable('o_loan_products', "status=1", "name", "asc", "0,100", "uid ,name ");
                                    while ($t = mysqli_fetch_array($o_loan_products_)) {
                                        $uid = $t['uid'];
                                        $name = $t['name'];
                                        echo "<option value='$uid'>$name</option>";
                                    }

                                    ?>

                                </select>
                                <select class="btn font-16 btn-default btn-md btn-default text-bold top-select" id="sel_branch" onchange="loan_filters()">
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
                                    $o_branches_ = fetchtable('o_branches', "status!=0 $andbranch", "name", "asc", "0,1000", "uid ,name ");
                                    while ($w = mysqli_fetch_array($o_branches_)) {
                                        $uid = $w['uid'];
                                        $name = $w['name'];
                                        echo "<option value='$uid'>$name</option>";
                                    }
                                    ?>
                                </select>
                                <select class="btn font-16 btn-default btn-md btn-default text-bold top-select" id="sel_stage" onchange="loan_filters()">
                                    <option value="0">All Stages</option>
                                    <?php
                                    $o_loan_stages_ = fetchtable('o_loan_stages', "status=1", "uid", "desc", "0,100", "uid ,name ");
                                    while ($p = mysqli_fetch_array($o_loan_stages_)) {
                                        $uid = $p['uid'];
                                        $name = $p['name'];
                                        echo "<option value='$uid'>$name</option>";
                                    }

                                    ?>
                                </select>
                                <select class="btn font-16 btn-default btn-md btn-default text-bold top-select" id="sel_status" onchange="loan_filters()">
                                    <option>All Statuses</option>
                                    <?php
                                    $o_loan_statuses_ = fetchtable('o_loan_statuses', "status=1", "name", "desc", "0,100", "uid ,name ");
                                    while ($l = mysqli_fetch_array($o_loan_statuses_)) {
                                        $uid = $l['uid'];
                                        $name = $l['name'];
                                        echo "<option value='$uid'>$name</option>";
                                    }
                                    ?>
                                </select>
                                <input type="text" name="daterange" class="btn btn-default" id="period_" title="Filter with Date Range" value="Filter with a date range" />
                                <input type="hidden" id="start_d"> <input type="hidden" id="end_d">

                            </h3>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="col-md-2">
                        <?php
                        $create_loan = permission($userd['uid'], 'o_loans', "0", "create_");
                        if ($create_loan == 1) {
                        ?>
                            <a href="assets" class="btn btn-success float-right"><i class="fa fa-plus"></i> CREATE LOAN</a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <table id="example1" class="table font-13 table-bordered table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>CODE</th>
                            <th>Customer</th>
                            <th>Principal</th>
                            <th>AddOns</th>
                            <th>Deductions</th>
                            <th>Repaid</th>
                            <th>Balance</th>
                            <th>Disbursed Date</th>
                            <th>Due Date</th>
                            <th>BDO</th>
                            <th>Status</th>
                            <th>Flag</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="loan_list">

                        <tr>
                            <td colspan="13">Loading data...</td>
                        </tr>

                    </tbody>
                    <tfoot>
                        <tr>
                            <th>CODE</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>AddOns</th>
                            <th>Deductions</th>
                            <th>Repaid</th>
                            <th>Balance</th>
                            <th>Disbursed Date</th>
                            <th>Due Date</th>
                            <th>BDO</th>
                            <th>Status</th>
                            <th>Flag</th>
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
<?php
if (isset($_GET['start_date']) or isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $filt = "status > -1 AND given_date BETWEEN \"$start_date\" AND \"$end_date\"";
} elseif (isset($_GET['loan-type'])) {
    $loan_type = $_GET['loan-type'];

    $filt = " loan_type = $loan_type";
} else {
    $filt = "status > -1";
}

echo "<input type='hidden' id = '_approvals_' value = \"$need_approval\">";
echo "<div style='display: none;'>" . paging_values_hidden("$filt", 0, 10, 'uid', 'desc', '', 'loan_list') . "</div>"
?>