<section class="content-header">
    <h1>
        Incoming Payments
        <small>List</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Payments</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <!-- /.box -->

            <div class="box">
                <div class="box-header bg-info">
                    <div class="row">
                        <div class="col-md-9">
                            <h3 class="box-title">
                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="repayment_method" onchange="repayment_filters()">
                                    <option value="0"> All Payments</option>
                                    <?php
                                    $pay_methods = fetchtable("o_payment_methods", "status > 0", "uid", "asc", "0,100", "uid, name");
                                    while ($m = mysqli_fetch_array($pay_methods)) {
                                        $uid = $m['uid'];
                                        $name = $m['name'];
                                        echo "<option value=\"$uid\">$name</option>";
                                    }
                                    ?>
                                </select>

                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="repayment_order" onchange="repayment_filters()">
                                    <option value="desc">Newest First</option>
                                    <option value="asc">Oldest First</option>
                                </select>


                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="sel_branch" onchange="repayment_filters()">
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
                                <a href="?all" class="btn bg-navy"><i class="fa fa-check-circle"></i> All</a>
                                <a href="?not-allocated" class="btn bg-orange-active"><i class="fa fa-chain-broken"></i> Not Allocated</a>
                                <input type="text" name="daterange" class="btn btn-default" id="period_" title="Filter with Date Range" value="Filter with a date range" />
                                <input type="hidden" id="start_d"> <input type="hidden" id="end_d">

                            </h3>

                        </div>

                        <div class="col-md-3">

                            <a href="?add-edit" class="btn btn-success pull-right"><i class="fa fa-plus"></i> RECORD PAYMENT</a>
                        </div>

                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Amount Paid</th>
                                <th>Pay Method</th>
                                <th>Record Type</th>
                                <th>Transaction Code</th>
                                <th>Loan ID</th>
                                <th>Loan Balance</th>
                                <th>Pay Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="payment_list">
                            <tr>
                                <td colspan="11">Loading data...</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Amount Paid</th>
                                <th>Pay Method</th>
                                <th>Record Type</th>
                                <th>Transaction Code</th>
                                <th>Loan ID</th>
                                <th>Loan Balance</th>
                                <th>Pay Date</th>
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
if (isset($_GET['not-allocated'])) {
    $filt = " AND loan_id = 0 AND status=1";
} else {
    $filt = "";
}
if (isset($_GET['start_date']) or isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $filt = " AND status > -1 AND payment_date BETWEEN \"$start_date\" AND \"$end_date\"";
} else {
    //  $filt = " AND status > -1";
}

echo "<div style='display: none;'>" . paging_values_hidden("uid > 0 $filt", 0, 10, "uid", "desc", " ", "payment_list") . "</div>";
?>