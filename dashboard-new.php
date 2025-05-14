<?php
session_start();
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once ("php_functions/functions.php");
include_once ("php_functions/secondary-functions.php");
include_once ("configs/conn.inc");
include_once ("php_functions/authenticator.php");

$company = company_settings();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Dashboard</title>
    <!-- Tell the browser to be responsive to screen width -->
    <?php
    include_once ('header_includes.php');
    ?>
    <style>
        hr.hrule {
            margin-bottom: 10px;
            margin-top: 10px;
            border-top: 5px solid #dbdbdb;
        }
        .bar_sep {
            margin: 0;
            background: transparent;
            border: transparent;
            border-left: 2px solid #d2d2d2;
        }
    </style>
</head>
<body class="hold-transition skin-purple sidebar-mini">
<div class="wrapper">

    <?php
    include_once ('header.php');
    ?>
    <!-- Left side column. contains the logo and sidebar -->
    <?php
    include_once ('menu.php');
    ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <span class="font-18">
                <a class="font-14 font-italic" href="index"><i class="fa fa-arrow-left"></i>Old Dashboards</a>    Dashboard
                <small>Summaries</small>
            </span>
                <input type="search" class="input form-control" style="width: 200px;
    display: inline-block;
    margin-bottom: 5px;" placeholder="Search Dashboard">

            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content box" style="background: whitesmoke;">

            <?php
            include_once "dashboards/sl-top-summaries.php";
           include_once "dashboards/sl-disb-coll-graphs.php";
            include_once "dashboards/sl-progress-summary.php";
            include_once "dashboards/sl-totals-blocks.php";
            include_once "dashboards/sl-disbursements-block.php";
            include_once "dashboards/sl-collections-block.php";
           /* include_once "dashboards/sl-npl-block.php";
            include_once "dashboards/sl-customer-acquisition.php";
            include_once "dashboards/sl-customer-retention.php";
            include_once "dashboards/sl-defaulters-ageing.php";
            include_once "dashboards/sl-demographic-analysis.php";
            include_once "dashboards/sl-performance-comparison.php";
            include_once "dashboards/sl-progress-summary.php";
            include_once "dashboards/sl-projections.php";
            include_once "dashboards/sl-top-clients.php"; */

            ?>




        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <?php
    include_once ("footer.php");
    ?>

    <!-- Control Sidebar -->

    <!-- /.Control sidebar is in jresources/aside.php -->
    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<?php
include_once ("footer_includes.php");


?>
<script>
    window.onload = function () {

        //nd_loan_list();
        // let params = '';
        // nd_col_today();
        // nd_col_this_week();
        // nd_col_this_month()
       // nd_pay_list();
       // nd_numbers();
        //nd_loan_progress();
        //nd_payments_progress();
        // Add more functions as needed
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>
