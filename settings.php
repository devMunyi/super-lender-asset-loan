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
    <title><?php echo $company['name']; ?> | Settings</title>
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


                <section class="content-header">
                    <h1>
                        Settings
                        <small>List</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Settings</li>
                    </ol>
                </section>
        <?php
        $view_platform_settings = permission($userd['uid'],'platform_settings',"0","general_");
        if($view_platform_settings == 1){
        ?>
        <section class="content">
                <div class="row">
                    <div class="col-xs-2 font-14">
                        <ol class="list-group">
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?group-permissions"><i class="fa fa-lock"></i> Group Permissions</a>
                            </li>
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?user-permissions"><i class="fa text-red fa-lock"></i> User Permissions</a>
                            </li>
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?general"><i class="fa fa-wrench"></i> General Settings</a>
                            </li>
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?system"><i class="fa fa-gears"></i> System Settings</a>
                            </li>
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?branches"><i class="fa fa-map-marker"></i> Branches</a>
                            </li>
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?pairing"><i class="fa fa-chain"></i> Pairing</a>
                            </li>
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?leaders"><i class="fa fa-chain"></i> Team Leaders</a>
                            </li>
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?targets"><i class="fa fa-bullseye"></i> Targets</a>
                            </li>
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?configs"><i class="fa fa-cogs"></i> Configs</a>
                            </li>
                            <li class="list-group-item">
                                <a class="text-navy" href="settings?controls"><i class="fa fa-dashboard"></i> Controls</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col-xs-10">

                        <!-- /.box -->

                        <div class="box">
                            <div class="box-header bg-info">
                                <span class="font-16 text-bold">Settings</span>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <?php
                                if (isset($_GET['group-permissions'])) {
                                    include_once ("widgets/settings/permissions.php");
                                }
                                elseif(isset($_GET['user-permissions'])) {
                                    include_once ("widgets/settings/permissions-user.php");
                                }
                                elseif(isset($_GET['general'])){
                                    include_once ("widgets/settings/permissions.php");
                                }elseif(isset($_GET['system'])){
                                    include_once ("widgets/settings/system_settings.php");
                                }elseif (isset($_GET['pairing'])){
                                    include_once ("widgets/settings/pairing.php");
                                }
                                 elseif (isset($_GET['leaders'])){
                                    include_once ("widgets/settings/team-leaders.php");
                                }
                                elseif (isset($_GET['targets'])){
                                    include_once ("widgets/settings/targets.php");
                                }
                                elseif (isset($_GET['branches'])){
                                    include_once ("widgets/settings/branches.php");
                                }
                                else{
                                    include_once ("widgets/settings/permissions.php");
                                }
                                ?>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- /.col -->
                </div>
        </section>
        <?php
        }
        else{
            echo errormes("You don't have permission to view this page");
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

if((isset($_GET['g'])) && (isset($_GET['t']))) {
    $load_perms = 1;
}
$g = $_GET['g'];
$t = $_GET['t'];
$u = $_GET['u'];
$r = $_GET['r'];
?>
<script>
    $(function () {
        $('#example1').DataTable()
        $('#example2').DataTable({
            'paging'      : true,
            'lengthChange': false,
            'searching'   : false,
            'ordering'    : true,
            'info'        : true,
            'autoWidth'   : false
        })
       if('<?php echo $load_perms; ?>'){
        permissions('<?php echo $g; ?>','<?php echo $u; ?>','<?php echo $t; ?>','<?php echo $r; ?>','','');
           }

           if(document.getElementById('team_members')){
            load_std('/jresources/staff/team-members.php','#team_members','leader=0');
           }
    })
</script>
</body>
</html>
