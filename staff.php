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
    <title><?php echo $company['name']; ?> | Staff</title>
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
            if (isset($_GET['staff'])) {
                $view_staff = permission($userd['uid'], 'o_users', "0", "read_");
                if ($view_staff == 1) {
                    ?>
                    <div class="_details">
                        <?php
                        include_once('widgets/staff-details.php');
                        ?>
                    </div>
                    <?php
                }
            } elseif (isset($_GET['add-edit'])) {
                ?>
                <div class="_form">
                    <?php
                    $create_staff = permission($userd['uid'], 'o_users', "0", "create_");
                    if ($create_staff == 1) {
                        include_once('forms/staff-add-edit-form.php');
                    }
                    ?>
                </div>
                <?php
            } else {
                ?>
                <div class="_list">
                    <?php
                    $view_staff = permission($userd['uid'], 'o_users', "0", "read_");
                    if ($view_staff == 1) {
                        include_once('widgets/staff-list.php');
                    }
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
            if (document.getElementById('example1')) {
                staff_list();
                pager('#example1');
            }
        });
    </script>

    <script>
        if ('<?php echo $environment; ?>' != "dev") {
            // diable right-click context menu
            document.addEventListener('contextmenu', (e) => {
                e.preventDefault();
            });
        }
    </script>
</body>
</html>