<?php
session_start();
include_once ("php_functions/functions.php");
include_once ("configs/conn.inc");
include_once("php_functions/authenticator.php");

$company = company_settings();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Falling Due</title>
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
                    Falling Due
                    <small>List</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Falling Due</li>
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
                                                <a  class="btn font-16 btn-md bg-navy text-bold" href="falling-due"><i class="fa fa-clone"></i>All</a>
                                                <a onclick="dues_filter('<?php echo $date; ?>','<?php echo $date; ?>')" class="btn font-16 btn-md btn-danger text-bold" href="#"><i class="fa fa-info-circle"></i> Today</a>
                                                <a onclick="dues_filter('<?php echo $date; ?>','<?php echo dateadd($date, 0, 0, 1); ?>')" class="btn font-16 btn-md btn-warning text-bold" href="#"><i class="fa fa-info-circle"></i> Tomorrow</a>
                                                <a onclick="dues_filter('<?php echo $date; ?>','<?php echo dateadd($date, 0, 0, 2); ?>')" class="btn font-16 btn-md bg-orange text-bold" href="#"><i class="fa fa-chevron-circle-left"></i> 2 Days</a>
                                                <a onclick="dues_filter('<?php echo $date; ?>','<?php echo dateadd($date, 0, 0, 3); ?>')" class="btn font-16 btn-md btn-primary text-bold" href="#"><i class="fa fa-chevron-circle-left"></i> 3 Days</a>
                                                <a onclick="dues_filter('<?php echo $date; ?>','<?php echo dateadd($date, 0, 0, 7); ?>')" class="btn font-16 btn-md bg-purple text-bold" href="#"><i class="fa fa-chevron-circle-left"></i> 7 Days</a>
                                                <a onclick="dues_filter('<?php echo $date; ?>','<?php echo dateadd($date, 0, 0, 14); ?>')" class="btn font-16 btn-md btn-success text-bold" href="#"><i class="fa fa-chevron-circle-left"></i> 14 Days</a>
                                              
                                                <select class="btn font-16 btn-default btn-md btn-default text-bold top-select" id="sel_branch" onchange="falling_filters()">
                                                    <option value="0">All Branches</option>
                                                    <?php
                                                    ////-----------List Branches To See
                                                    $read_all= permission($userd['uid'],'o_loans',"0","read_");
                                                    if($read_all == 1){
                                                        $andbranch = "";
                                                    }
                                                    else{
                                                        $user_branch = $userd['branch'];
                                                        $andbranch = " AND uid='$user_branch'";
                                                        //////-----Check users who view multiple branches
                                                        $staff_branches = table_to_array('o_staff_branches',"agent=".$userd['uid']." AND status=1","1000","branch","uid","asc");
                                                        if(sizeof($staff_branches) > 0){
                                                            ///------Staff has been set to view multiple branches
                                                            array_push($staff_branches, $userd['branch']);
                                                            $staff_branches_list = implode(",", $staff_branches);
                                                            $anduserbranch = " AND branch in ($staff_branches_list)";
                                                            $andbranch = " AND uid in ($staff_branches_list)";
                                                        }

                                                    }
                                                    $o_branches_ = fetchtable('o_branches',"status!=0 $andbranch", "name", "asc", "1000", "uid ,name ");
                                                    while($w = mysqli_fetch_array($o_branches_))
                                                    {
                                                        $uid = $w['uid'];
                                                        $name = $w['name'];
                                                        echo "<option value='$uid'>$name</option>";
                                                    }
                                                    ?>
                                                </select>
                                                <input type="text" name="daterange" class="btn btn-default" id="period_" title="Filter with Date Range" value="Filter with a date range" />
                                <input type="hidden" id="start_d"> <input type="hidden" id="end_d">
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
                                        <th>Amount</th>
                                        <th>AddOns</th>
                                        <th>Deductions</th>
                                        <th>Repaid</th>
                                        <th>Balance</th>
                                        <th>Disbursed Date</th>
                                        <th>Due Date</th>
                                        <th>BDO</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody id="falling_due_list">
                                        <tr>
                                            <td colspan="10">Loading data...</td>
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
if(isset($_GET['start_date']) OR isset($_GET['end_date'])){
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $filt = "status > -1 AND final_due_date BETWEEN \"$start_date\" AND \"$end_date\"";
}
else{
    $filt = "uid > 0";
}

echo "<input type='hidden' id = '_approvals_' value = \"$need_approval\">";
echo "<div style='display: none;'><input type=\"text\" id=\"xty\">".paging_values_hidden("$filt",0,10,'uid','desc','','falling_due_list')."</div>";

include_once("footer_includes.php");
?>
<script>
    $(document).ready(function () {
        if(document.getElementById('example1')){
            falling_due_list();
            pager('#example1');
        }

        $('input[name="daterange"]').daterangepicker({
            autoUpdateInput: false,
            opens: 'left'
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            $('#start_d').val(start.format('YYYY-MM-DD'));
            $('#end_d').val(end.format('YYYY-MM-DD'));
            falling_filters();
        });
        
    });

</script>
</body>
</html>
