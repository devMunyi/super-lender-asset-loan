<?php
session_start();
include_once("php_functions/functions.php");
include_once("configs/conn.inc");
include_once("php_functions/authenticator.php");

$company = company_settings();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Defaulters</title>
    <!-- Tell the browser to be responsive to screen width -->
    <?php
    include_once('header_includes.php');
    ?>
</head>

<body class="hold-transition skin-purple sidebar-mini">
    <div class="wrapper">

        <?php
        include_once('header.php');
        ?>
        <!-- Left side column. contains the logo and sidebar -->
        <?php
        include_once('menu.php');
        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->


            <!-- Main content -->


            <section class="content-header">
                <h1>
                    Defaulters
                    <small>List</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Defaulters</li>
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
                                        <h3 class="box-title">
                                            <a onclick="defaulters_filter('all')" class="btn font-16 btn-md btn-default text-bold" href="#"><i class="fa fa-info-circle"></i> ALL Defaulters</a>
                                            <a onclick="defaulters_filter('newest')" class="btn font-16 btn-md btn-default text-bold" href="#"><i class="fa fa-chevron-circle-right"></i> Newest</a>
                                            <a onclick="defaulters_filter('oldest')" class="btn font-16 btn-md btn-default text-bold" href="#"><i class="fa fa-chevron-circle-right"></i> Oldest</a>
                                            <a onclick="defaulters_filter('max')" class="btn font-16 btn-md btn-default text-bold" href="#"><i class="fa fa-chevron-circle-right"></i> Maximum Amount</a>
                                            <a onclick="defaulters_filter('min')" class="btn font-16 btn-md btn-default text-bold" href="#"><i class="fa fa-chevron-circle-right"></i> Minimum Amount</a>
                                            <a onclick="defaulters_filter('uncommitted')" class="btn font-16 btn-md btn-default text-bold" href="#"><i class="fa fa-chevron-circle-right"></i> Uncommitted</a>
                                            <select class="btn btn-md btn-default text-bold" id="loan_age" onchange="defaulters_filter2();">
                                                <option value="0"> Age</option>
                                                <option value="1"> 1 Day</option>
                                                <option value="2"> 2 Days</option>
                                                <option value="3"> 3 Days</option>
                                                <option value="7"> 7 Days</option>
                                                <option value="14"> 14 Days</option>
                                                <option value="21"> 21 Days</option>
                                                <option value="30"> 30 Days</option>
                                                <option value="45"> 45 Days</option>
                                                <option value="60"> 60 Days</option>
                                                <option value="90"> 90 Days</option>
                                                <option value="120"> 120 Days</option>
                                            </select>

                                            <select class="select btn font-16 btn-md btn-default text-bold top-select" id="sel_agent" onchange="defaulters_filter2()">
                                                <option value="0">All Agents</option>
                                                <?php
                                                $o_agents_ = fetchtable('o_users', "status IN (1, 3) AND tag IS NOT NULL AND tag != ''", "name", "asc", "0,10000", "uid, name");
                                                while ($a = mysqli_fetch_array($o_agents_)) {
                                                    $uid = $a['uid'];
                                                    $name = $a['name'];
                                                    echo "<option value='$uid'>$name</option>";
                                                }
                                                ?>
                                            </select>

                                            <select class="select btn font-16 btn-default btn-md btn-default text-bold top-select" id="sel_branch" onchange="defaulters_filter2()" style="font-weight: 700 !important;">
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

                                        </h3>
                                    </div>

                                </div>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>CODE</th>
                                            <th>Customer</th>
                                            <th>Agent</th>
                                            <th>Amount</th>
                                            <th>AddOns</th>
                                            <th>Deductions</th>
                                            <th>Repaid</th>
                                            <th>Balance</th>
                                            <th>Disbursed Date</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="defaulters_list">
                                        <tr>
                                            <td colspan="12">Loading data...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>CODE</th>
                                            <th>Customer</th>
                                            <th>Agent</th>
                                            <th>Amount</th>
                                            <th>AddOns</th>
                                            <th>Deductions</th>
                                            <th>Repaid</th>
                                            <th>Balance</th>
                                            <th>Disbursed Date</th>
                                            <th>Due Date</th>
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
            echo "<div style='display: none ;'>" . paging_values_hidden2('uid > 0', 0, 10, 'uid', 'desc', '', 'defaulters_list', 'all') . "</div>";
            ?>
            <!-- /.content -->
        </div>


        <!-- /.content-wrapper -->


        <?php
        include_once("footer.php");
        ?>


        <!-- Control Sidebar -->

        <!-- /.control-sidebar -->
        <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->

    <?php
    include_once("footer_includes.php");
    ?>
    <script>
        $(document).ready(function() {

            if(document.getElementById('example1')){
                defaulters_list();
                pager('#example1');
            }
        });
    </script>
</body>

</html>