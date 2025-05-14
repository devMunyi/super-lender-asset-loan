<?php
session_start();
include_once ("php_functions/functions.php");
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
            <h1>
                Dashboard
                <small>Summaries</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">

           <div id="perform_">

           </div>
            <div id="collection_rate">

            </div>


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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script>
$('document').ready(function (){
    //graph_load();
    performance_breakdown_load();
    collection_rate();
  //  defaulters_breakdown();

    localStorage.setItem("company_logo", "<?php echo $company['logo']; ?>");


})
</script>
</body>
</html>
