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

            <?php
            if(isset($_GET['customer'])){
                ?>
                <div class="_details">
                    <?php
                    include_once ('widgets/customer-details.php');
                    ?>
                </div>

                <?php
            }
            else if (isset($_GET['add-addon'])) {
                ?>
                <div class="_form">
                    <?php
                    include_once('forms/loan_addon_add_edit.php');
                    ?>
                </div>
                <?php
            }
            else if (isset($_GET['add-deduction'])){
                    ?>
                    <div class="_form">
                        <?php
                        include_once ('forms/loan_deduction_add_edit.php');
                        ?>
                    </div>
                    <?php
                }
            else if (isset($_GET['add-loan-stage'])){
                ?>
                <div class="_form">
                    <?php
                    include_once ('forms/loan_stage_add_edit.php');
                    ?>
                </div>
                <?php
            }

            else {
                ?>
                <div class="_list">
                    <?php
                  //  include_once ('widgets/customer-list.php');
                    ?>
                </div>
                <?php
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
        customer_list(0, 10, 'uid', 'asc');
        pager('#example1');
    })
</script>
</body>
</html>
