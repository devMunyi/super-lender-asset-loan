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
    <title><?php echo $company['name']; ?> | Customers</title>
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


            <div class="_form">
                <?php
                if(isset($_GET['cat'])){
                 $cat = $_GET['cat'];
                 if($cat == 'assets'){
                     include_once('widgets/asset-finance/assets.php');
                 }
                 elseif ($cat == 'asset'){
                     include_once('widgets/asset-finance/asset-details.php');
                 }
                 elseif ($cat == 'stock'){
                     include_once('widgets/asset-finance/stock.php');
                 }
                 elseif ($cat == 'cart'){
                     include_once('widgets/asset-finance/cart.php');
                 }
                 elseif ($cat == 'loans'){
                     include_once('widgets/asset-finance/loans.php');
                 }
                 elseif ($cat == 'other'){
                     include_once('widgets/asset-finance/other.php');
                 }
                 else{
                    //  include_once('widgets/asset-finance/summary.php');
                    include_once('widgets/asset-finance/assets.php');
                 }
                }
                elseif (isset($_GET['asset-add-edit'])){
                    include_once('forms/asset-add-edit.php');
                }
                else {
                    // include_once('widgets/asset-finance/summary.php');
                    include_once('widgets/asset-finance/assets.php');
                }
                ?>
            </div>


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
    $(document).ready(function () {
    load_assets();
    pager('#asset-list');
    });
</script>
</body>

</html>