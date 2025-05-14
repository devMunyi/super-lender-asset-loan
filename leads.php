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
    <title><?php echo $company['name']; ?> | Leads</title>
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
        if(isset($_GET['type'])) {
            $view = $_GET['type'];
        }
        else{
            $view = 'Customer';
        }
        if(isset($_GET['customer'])){
            $view = 'customer-details'
            ?>
            <div class="_details">
                <?php
                include_once ('widgets/customer-details.php');
                ?>
            </div>

            <?php
        }
        elseif (isset($_GET['customer-add-edit'])){
            ?>
            <div class="_form">
                <?php
                include_once ('forms/customer-add-edit-form.php');
                ?>
            </div>
        <?php }else { ?>
            <div class="_list">
                <?php include_once ('widgets/lead-list.php');?>
            </div>
        <?php } ?>

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
    $(function() {
        let orderby = '<?php echo $_GET['orderby']; ?>';
        let dir = '<?php echo $_GET['dir']; ?>';
        if (!orderby) {
            orderby = 'uid';
        }
        if (!dir) {
            dir = 'desc';
        }


        if(document.getElementById('example1')){
            customer_list('LEAD');
            pager('#example1');
        }

        ////////-------------Doc Load Other Function

        if ('<?php echo $contact_list; ?>') {
            contact_list('<?php echo $contact_list; ?>', 'EDIT');
        }
        if ('<?php echo $guarantor_list; ?>') {
            guarantor_list('<?php echo $guarantor_list; ?>', 'EDIT');
        }
        if ('<?php echo $referee_list; ?>') {
            referee_list('<?php echo $referee_list; ?>', 'EDIT');
        }
        if ('<?php echo $collateral_list; ?>') {
            collateral_list('<?php echo $collateral_list; ?>', 'EDIT');
        }
        if ('<?php echo $upload_list; ?>') {
            upload_list('<?php echo $upload_list; ?>', 'EDIT');
        }

    })
</script>
</body>

</html>