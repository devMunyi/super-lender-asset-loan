<?php
session_start();
include_once("php_functions/authenticator.php");
include_once("php_functions/functions.php");
include_once("configs/conn.inc");

$company = company_settings();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Installments</title>
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
                    Installments Due
                    <small>List</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Installments</li>
                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">

                        <!-- /.box -->

                        <div class="box">
                            <div class="box-header bg-info">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h3 class="box-title">
                                            <a onclick="installments_filter('past')" class="btn font-16 btn-md bg-black text-bold" href="#"><i class="fa fa-chevron-circle-left"></i>Past Dues</a>
                                            <a onclick="installments_filter('yesterday')" class="btn font-16 btn-md btn-danger text-bold" href="#"><i class="fa fa-chevron-circle-left"></i> Due Yesterday</a>
                                            <a onclick="installments_filter('today')" class="btn font-16 btn-md btn-warning text-bold" href="#"><i class="fa fa-circle-o"></i> Due Today</a>
                                            <a onclick="installments_filter('tomorrow')" class="btn font-16 btn-md btn-primary text-bold" href="#"><i class="fa fa-chevron-circle-right"></i> Due Tomorrow</a>
                                            <a onclick="installments_filter('upcoming')" class="btn font-16 btn-md btn-info text-bold" href="#"><i class="fa fa-chevron-circle-right"></i> Upcoming Dues</a>
                                        </h3>
                                    </div>

                                </div>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>CODE</th>
                                            <th>Customer</th>
                                            <th>Loan Amount</th>
                                            <th>Amount Due</th>

                                            <th>Repaid</th>
                                            <th>Total Balance</th>
                                            <th>Disbursed Date</th>
                                            <th>Installment Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="installments_list">
                                        <tr>
                                            <td colspan="10">Loading data...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>CODE</th>
                                            <th>Customer</th>
                                            <th>Loan Amount</th>
                                            <th>Due Today</th>

                                            <th>Repaid</th>
                                            <th>Total Balance</th>
                                            <th>Disbursed Date</th>
                                            <th>Installment Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- /.col -->
                </div>
            </section>


            <?php
            echo "<div style='display: none ;'>" . paging_values_hidden2('uid > 0', 0, 10, 'uid', 'desc', '', 'installments_list', 'all') . "</div>";
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
        $(document).ready(function() {
            if(document.getElementById('example1')){
                installments_list();
                pager('#example1');
            }
        });
    </script>
</body>

</html>