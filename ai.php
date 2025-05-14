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
    <title><?php echo $company['name']; ?> | AI</title>
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
                    Artificial Intelligence
                    <small>AI</small>
                </h1>

            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">

                        <!-- /.box -->

                        <div class="box" style="background: linear-gradient(#ffffdb,#FFFFFF);">

                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="container p-3 my-3">
                                    <div class="row">
                                        <div class="col-lg-2 border-right">

                                        </div>
                                        <div class="col-lg-9">
                                    <div class="conversation_box" id="conv_" style="min-height: 200px;">
                                    <h3 class="text-black  font-light" style="text-align: center;"><i class="fa fa-magic"></i> Ask Your System a Question. e.g. How much was disbursed today </h3>
                                    </div>
                                            <div class="box-footer bg-gray-active" id="search_area">
                                                <div class="form-group">
                                                    <div class="col-sm-11">
                                                        <input type="search" style="border-radius: 30px;    font-size: 18px;    font-weight: bold;    color: #000000;    padding: 22px;" class="form-control" id="inp_q" placeholder="Type your question here">
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <button style="margin-top: 6px;" onclick="ai_chat()" class="btn bg-blue-gradient"><i class="fa fa-paper-plane"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>


                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- /.col -->
                </div>
            </section>

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

</body>
</html>
