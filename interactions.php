<?php
session_start();
include_once("php_functions/functions.php");
include_once("configs/conn.inc");
include_once("php_functions/authenticator.php");

$company = company_settings();
// by [username(email)(uid)]

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Interactions</title>
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
        $agent_id = $userd['uid'];
        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->


            <!-- Main content -->

            <?php
            // if (isset($_GET['customer'])) {
            //     $width1 = 4;
            //     $width2 = 8;
            // } else {
            //     $width1 = 12;
            //     $width2 = 4;
            // }

            $width1 = 12;
            $width2 = 12;

            ?>
            <section class="content-header">
                <h1>
                    Interactions
                    <small>List</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Interactions</li>
                </ol>
            </section>
            <section class="content">
                <div class="row">

                    <?php
                    $for_customer = 0;
                    if (isset($_GET['customer']) && $_GET['customer'] > 0) {
                        // Show Specific Customer Interactions
                        $customer_id = $_GET['customer'];
                        $for_customer = 1;
                        $customer_name = fetchrow('o_customers', "uid='" . decurl($customer_id) . "'", "full_name");
                        if ($customer_id > 0) {

                    ?>
                            <div class="col-xs-<?php echo $width2; ?> scroll-hor well shadow p-3">

                                <div class="box">
                                    <div class="box-header bg-info" style="padding: 0 !important;">
                                        <h4><a href="interactions.php" class="btn btn-outline-black"><i class="fa fa-reply"></i> Go to all</a>Interactions for <b><?php echo $customer_name; ?></b></h4>
                                        <input type='hidden' id='cust_id_' value='<?php echo $_GET["customer"]; ?>'>
                                    </div>

                                    <div class="box-body">
                                        <table id="hd" style="background: white; padding: 5px; font-size: 12px;" class="table table-condensed table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer Name</th>
                                                    <th>Interaction Mode</th>
                                                    <th>Date</th>
                                                    <th>Agent</th>
                                                    <th>Outcome Details</th>
                                                    <th>Next Interaction</th>
                                                    <th>Account</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="customer_interactions">
                                                <tr>
                                                    <td colspan="9" class="text-center">Loading data...</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer Name</th>
                                                    <th>Interaction Mode</th>
                                                    <th>Date</th>
                                                    <th>Agent</th>
                                                    <th>Outcome Details</th>
                                                    <th>Next Interaction</th>
                                                    <th>Account</th>
                                                    <th>Action</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            <?php
                        }
                    } else { ?>

                            <!-- Show All Interactions -->
                            <div class="col-xs-<?php echo $width1; ?>">

                                <!-- /.box -->

                                <div class="box">
                                    <div class="box-header bg-info">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h3 class="box-title font-16">
                                                    <a class="btn font-16 btn-md bg-navy text-bold" href="javascript:void(0)" onclick="reload()"><i class="fa fa-refresh"></i> All</a>
                                                    <a class="btn font-16 btn-md bg-blue-gradient text-bold" href="javascript:void(0)" title="My PTPs" onclick="select_item('#search_',''); select_item('#agent_','<?php echo $agent_id; ?>');interactions_filter();"><i class="fa fa-user-circle-o"></i> My</a>
                                                    <input type="hidden" id="agent_" value="0">

                                                    <a class="btn font-16 btn-md bg-maroon-gradient text-bold" href="javascript:void(0)" onclick="select_item('#interaction_outcome','6'); interactions_filter();"><i class="fa fa-thumbs-up"></i> PTPs</a>

                                                    <select class="btn font-16 btn-default btn-md btn-default text-bold top-select" id="interaction_outcome" onchange="interactions_filter()">
                                                        <option value="0"> All Outcomes</option>
                                                        <?php
                                                        $out = fetchtable('o_flags', "status=1", "name", "asc", "100", "uid, name");
                                                        while ($o = mysqli_fetch_array($out)) {
                                                            $oid = $o['uid'];
                                                            $name = $o['name'];

                                                            echo "<option value='$oid'>$name</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                    <select class="btn font-16 btn-default btn-md btn-default text-bold top-select" id="tags" onchange="interactions_filter()">
                                                        <option value="0">Customer Type</option>
                                                       <option value="CLIENT.Lead">Leads</option>
                                                       <option value="CLIENT.Active">Active</option>
                                                       <option value="LOAN.Overdue">Defaulters</option>
                                                    </select>

                                                    <select class="btn font-14 btn-default btn-md btn-default text-bold top-select" id="interaction_method" onchange="interactions_filter()">
                                                        <option value="0">All Methods</option>
                                                        <?php
                                                        $o_conversation_methods = fetchtable('o_conversation_methods', "status=1", "uid", "asc", "100", "*");
                                                        while ($cov = mysqli_fetch_array($o_conversation_methods)) {
                                                            $cid = $cov['uid'];
                                                            $cname = $cov['name'];
                                                            $cdetails = $cov['details'];
                                                            //  echo " <a class=\"btn font-16 btn-md btn-default text-black text-bold\" href=\"#\" onclick=\"face_to_face_interactions('sort_1')\"><i class=\"$cdetails\"></i> $cname</a>";
                                                            echo "<option value='$cid'>$cname</option>";
                                                        }
                                                        ?>
                                                    </select>

                                                    <select class="btn font-16 btn-md btn-default text-bold top-select" id="sel_branch" onchange="interactions_filter()">
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

                                                    <button onclick="interactions_filter('duetoday', '')" class="btn bg-purple-gradient"><i class="fa fa-bell"></i> Due Today</button>
                                                    <button onclick="interactions_filter('', 'overdue')" class="btn bg-blue-gradient"><i class="fa fa-binoculars"></i> Overdue</button>

                                                    <input style="width: 135px" type="text" name="daterange" class="btn btn-default" id="period_" title="Filter with interaction date (Day interaction was added)" value="Interaction date" />
                                                    <input type="hidden" id="c_start_d"> <input type="hidden" id="c_end_d">

                                                    <input style="width: 135px;" type="text" name="daterange" class="btn btn-default" id="ni_period_" title="Filter with interaction due date (Next interaction)" value="Next date" />
                                                    <input type="hidden" id="ni_start_d"> <input type="hidden" id="ni_end_d">
                                                </h3>


                                                <button title="New Interaction" class="btn btn-success pull-right btn-float" onclick="modal_view('/forms/interaction_add_form.php','','New Interaction')"><i class="fa fa-plus"></i> NEW</button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.box-header -->
                                    <div class="box-body">
                                        <table id="example1" class="table table-condensed table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer Name</th>
                                                    <th>Interaction Mode</th>
                                                    <th>Date</th>
                                                    <th>Agent</th>
                                                    <th>Outcome Details</th>
                                                    <th>Next Interaction</th>
                                                    <th>Account</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="interactions_">
                                                <tr>
                                                    <td colspan="9">Loading data...</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer Name</th>
                                                    <th>Interaction Mode</th>
                                                    <th>Date</th>
                                                    <th>Agent</th>
                                                    <th>Outcome Details</th>
                                                    <th>Next Interaction</th>
                                                    <th>Account</th>
                                                    <th>Action</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                <!-- /.box -->
                            </div>

                        <?php }

                    // if ($for_customer == 0) {
                    //     echo "<h4 class='font-italic'>Click <i class='fa fa-reorder text-blue'></i> to view a customer's full interactions</h4>";
                    // }
                        ?>

                            </div>



                            <!-- /.col -->
                </div>
            </section>
            <?php
            echo "<div style='display: none;'>" . paging_values_hidden2('uid > 0', 0, 10, 'uid', 'desc', '', 'load_interactions', 'default_sort') . "</div>"
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
            if (document.getElementById('interactions_')) {
                load_interactions()
            }

            if (document.getElementById('customer_interactions')) {
                specific_customer_interactions()
            }

            $('#period_')?.daterangepicker({
                autoUpdateInput: false,
                opens: 'left'
            }, function(start, end, label) {
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                $('#c_start_d').val(start.format('YYYY-MM-DD'));
                $('#c_end_d').val(end.format('YYYY-MM-DD'));
                interactions_filter();
            });

            $('#ni_period_')?.daterangepicker({
                autoUpdateInput: false,
                opens: 'left'
            }, function(start, end, label) {
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                $('#ni_start_d').val(start.format('YYYY-MM-DD'));
                $('#ni_end_d').val(end.format('YYYY-MM-DD'));
                interactions_filter();
            });

            // check the element exists first 
            if (document.getElementById('example1')) {
                pager('#example1');
            }

        });
        
    </script>
</body>

</html>