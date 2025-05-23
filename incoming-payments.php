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
    <title><?php echo $company['name']; ?> | Payments</title>
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

        <?php
        if(isset($_GET['repayment'])){
            include_once ("widgets/payments/payment-one.php");
        }
        elseif (isset($_GET['add-edit'])){
            include_once ("widgets/payments/add-edit.php");
        }
        elseif (isset($_GET['blind-allocation'])){
            include_once ("widgets/payments/blind-allocation.php");
        }
        else {
            include_once ("widgets/payments-list.php");
        }
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
    $(function () {
        if(document.getElementById('example1')){
            payment_list();
            pager('#example1');
        }

        $('input[name="daterange"]').daterangepicker({
            autoUpdateInput: false,
            opens: 'left'
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            $('#start_d').val(start.format('YYYY-MM-DD'));
            $('#end_d').val(end.format('YYYY-MM-DD'));
            repayment_filters();
        });



    })
</script>
</body>
</html>
