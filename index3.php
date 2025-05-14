<?php
session_start();
//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once ("php_functions/functions.php");
include_once ("configs/conn.inc");
include_once ("php_functions/authenticator.php");
//ini_set('memory_limit','512M');

$company = company_settings();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Analysis</title>
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
                Home
                <small>Borrowers Analysis</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Analysis</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <?php
           // include_once ('widgets/company-graph.php');
            /*
            $view_summary = permission($userd['uid'],'o_summaries',"0","read_");
          
            $view_summary = permission($userd['uid'],'o_summaries',"0","read_");
            if($view_summary == 1) {
               include_once('widgets/company-graph2.php');
            }
            else{
               // echo "<h3>Welcome to your Dashboard</h3>";
               include_once('widgets/company-graph2.php');
            }
            */
            include_once ('widgets/loan-distribution.php');
            
            ?>
            <!-- /.row -->
            <!-- Main row -->

            <!-- /.row (main row) -->

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
    graph_load();
   // performance_breakdown_load();
   // collection_rate();
  //  defaulters_breakdown();

    localStorage.setItem("company_logo", "<?php echo $company['logo']; ?>");

})
</script>

</body>
</html>
