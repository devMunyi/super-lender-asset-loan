<?php
session_start();
include_once ("php_functions/functions.php");
include_once ("configs/conn.inc");

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

    <div class="row">
        <?php
        if(isset($_GET['customers'])){
        ?>
        <div class="col-sm-12">
    <h3>Customers</h3>


        <form class="form-horizontal" id="cus-upload" method="POST" action="extensions/members-upload.php" enctype="multipart/form-data">
            <div class="box-body">

                <div class="form-group">
                    <label for="file_" class="col-sm-3 control-label">File</label>

                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="file_" name="file_">
                    </div>
                </div>
                <div class="col-sm-3"></div>
                <div class="col-sm-9">
                    <div class="box-footer">
                        <br>
                        <div class="prgress">
                            <div class="messagecus-upload" id="message"></div>
                            <div class="progresscus-upload" id="progress">
                                <div class="barcus-upload" id="bar"></div>
                                <br>
                                <div class="percentcus-upload" id="percent"></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                        <button type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('cus-upload');">Upload
                        </button>
                        <a href="extensions/newark/fix-customers" target="_blank">Fix Customers</a>
                    </div>

                </div>

            </div>
            <!-- /.box-body -->

            <!-- /.box-footer -->
        </form>
        </div>
        <?php
        }
       if(isset($_GET['loans'])){
        ?>
        <div class="col-sm-12">
            <h3>Loans</h3>
            <form class="form-horizontal" id="doc-upload" method="POST" action="extensions/loans-upload.php" enctype="multipart/form-data">
                <div class="box-body">

                    <div class="form-group">
                        <label for="file_" class="col-sm-3 control-label">File</label>

                        <div class="col-sm-9">
                            <input type="file" class="form-control" id="file_" name="file_">
                        </div>
                    </div>
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9">
                        <div class="box-footer">
                            <br>
                            <div class="prgress">
                                <div class="messagedoc-upload" id="message"></div>
                                <div class="progressdoc-upload" id="progress">
                                    <div class="bardoc-upload" id="bar"></div>
                                    <br>
                                    <div class="percentdoc-upload" id="percent"></div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                            <button type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('doc-upload');">Upload
                            </button>

                        </div>

                    </div>

                </div>
                <!-- /.box-body -->

                <!-- /.box-footer -->
            </form>

        </div>
        <?php
       }
       if(isset($_GET['payments'])){
        ?>

        <div class="col-sm-12">
            <h3>Pay</h3>
            <form class="form-horizontal" id="pay-upload" method="POST" action="extensions/payments-upload.php" enctype="multipart/form-data">
                <div class="box-body">

                    <div class="form-group">
                        <label for="file_" class="col-sm-3 control-label">File</label>

                        <div class="col-sm-9">
                            <input type="file" class="form-control" id="file_" name="file_">
                        </div>
                    </div>
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9">
                        <div class="box-footer">
                            <br>
                            <div class="prgress">
                                <div class="messagepay-upload" id="message"></div>
                                <div class="progresspay-upload" id="progress">
                                    <div class="barpay-upload" id="bar"></div>
                                    <br>
                                    <div class="percentpay-upload" id="percent"></div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                            <button type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('pay-upload');">Upload
                            </button>
                            <a href="extensions/newark/fix-pays" target="_blank">Fix Pays</a>
                        </div>

                    </div>

                </div>
                <!-- /.box-body -->

                <!-- /.box-footer -->
            </form>

        </div>

        <?php
       }
        ?>

    </div>

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






