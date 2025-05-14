<?php
session_start();
include_once ("php_functions/functions.php");
include_once ("configs/conn.inc");
include_once("php_functions/authenticator.php");

$expense_cat = array(0=>"One Time",1=>"Daily",7=>"Weekly",30=>"Monthly", 365=>"Yearly");

$end_date = $date;
$start_date = first_date_of_month($date);

$company = company_settings();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }
    </style>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Expenses</title>
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
                        Expenses
                        <small>List</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Expenses</li>
                    </ol>
                </section>
        <?php
        $view_expenses = permission($userd['uid'],'o_expenses',"0","read_");

        if($view_expenses == 1){
        ?>
        <section class="content">
                <div class="row">
                    <div class="col-xs-3 font-14">
                        <ol class="list-group">
                            <li class="list-group-item text-black text-bold">
                            <a class="text-primary" href="expenses?all"><i class="fa fa-angle-double-right"></i> List</a>
                            </li>
                            <li class="list-group-item text-black text-bold">
                                <a class="text-primary" href="expenses?add"><i class="fa fa-plus"></i> Add</a>
                            </li>

                        </ol>
                    </div>
                    <div class="col-xs-9">

                        <!-- /.box -->

                        <div class="box">

                            <!-- /.box-header -->
                            <div class="box-body">
                                <?php

                                if (isset($_GET['add']) || isset($_GET['edit']))
                                      {
                                    include_once ("forms/expense_add_edit.php");
                                      }
                                elseif ($_GET['view']){
                                    include_once ("widgets/accounting/expense_view.php");
                                }
                                else{

                                        include_once ("widgets/accounting/expense_list.php");
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
        $('#example3').DataTable({
            dom: 'Bfrtip', // "Bfrtip" positions the buttons at the top of the table
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf'
                // you can also add 'print' if you want print button
            ]
        });
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
    });

    function expense_filter(){
        let start_date = $('#start_date').val();
        let end_date = $('#end_date').val();
        gotourl('expenses?all&start_date='+start_date+"&end_date="+end_date);
    }
</script>
</body>
</html>
