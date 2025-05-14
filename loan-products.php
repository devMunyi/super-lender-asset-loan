<?php
session_start();
require_once ("php_functions/functions.php");
require_once ("configs/conn.inc");
require_once("php_functions/authenticator.php");

$company = company_settings();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Loan Products</title>
    <!-- Tell the browser to be responsive to screen width -->
    <?php
    require_once('header_includes.php');
    ?>
</head>
<body class="hold-transition skin-purple sidebar-mini">
<div class="wrapper">

    <?php
    require_once('header.php');
    ?>
    <!-- Left side column. contains the logo and sidebar -->
    <?php
    require_once('menu.php');
    ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->


        <!-- Main content -->

        <?php

        $view_products= permission($userd['uid'],'o_loan_products',"0","read_");
        if($view_products == 1){
            if(isset($_GET['product'])){
                require_once("./widgets/loan_product.php");
                }
                elseif (isset($_GET['add-edit'])){
                    require_once('./forms/loan-product-add-edit-form.php');
                }
                else {
                    require_once("./widgets/loan_product_list.php");
                }
        }
        else{      
           echo "<div class=\"alert alert-danger\">You do not have permission to view this page</div>";
        
        }
        ?>


        <!-- /.content -->
    </div>

    <!-- /.content-wrapper -->
    <?php
    require_once("./footer.php");
    ?>


    <!-- Control Sidebar -->

    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<?php
require_once("./footer_includes.php");
?>


<script>
    $(function () {
        // time picker
        $('#meeting_time').datetimepicker({
            local: 'en',
            format: 'hh:mm:ssA'
        });
    });
</script>
</body>
</html>
