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
    <title><?php echo $company['name']; ?> | Branches</title>
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
        if(isset($_GET['branch'])){
        $view_branch = permission($userd['uid'],'o_branches',"0","read_");
        if($view_branch == 1){
            ?>
            <div class="_details">
             <?php
             include_once ('widgets/branch-details.php');
             ?>
            </div>
            <?php
        }
        }
        elseif (isset($_GET['add-edit'])){
            ?>
        <div class="_form">
            <?php
            $create_branch= permission($userd['uid'],'o_branches',"0","create_");
            if($create_branch== 1) {
                include_once('forms/branch-add-edit.php');
            }
            ?>
        </div>
            <?php
        }
        else {
            ?>
            <div class="_list">
            <?php
            $view_branch = permission($userd['uid'],'o_branches',"0","read_");
            if($view_branch == 1) {
                include_once('widgets/branches-list.php');
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
        if(document.getElementById('example1')){
            branch_list();
            pager('#example1');
        }

            $('#example2').DataTable({
                dom: 'Bfrtip',
                pageLength: 20,
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print',

                ]
            });



    })

    
</script>
</body>
</html>
