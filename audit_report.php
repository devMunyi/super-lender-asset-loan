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
    <title><?php echo $company['name']; ?> | Scoring</title>
    <!-- Tell the browser to be responsive to screen width -->
    <?php
    include_once('header_includes.php');
    ?>

    <style>
        .file-upload {
            border: 2px dashed #ccc;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            background-color: #f9f9f9;
            cursor: pointer;
            position: relative;
        }

        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload:hover {
            background-color: #f1f1f1;
        }
        #fileName{
            color: #0a568c;
            font-style: italic;
        }
    </style>
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
                    System Audits
                    <small>List</small>
                </h1>

            </section>
            <section class="content">
                <?php

                if($userd['user_group'] != 1){
                    die(errormes("You don't have permission to view this page"));
                }
                ?>


                        <!-- /.box -->

                        <div class="box">
                            <div class="box-header bg-info">
                                <ul class="nav nav-tabs nav-justified font-16">
                                    <li class="nav-item nav-100 active font-bold"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-send"></i> Disbursements</a></li>
                                    <li class="nav-item nav-100 active font-bold"><a href="#tab_2" data-toggle="tab" aria-expanded="false"><i class="fa fa-save"></i> Collections</a></li>
                                    <li class="nav-item nav-100 active font-bold"><a href="#tab_3" data-toggle="tab" aria-expanded="false"><i class="fa fa-clock-o"></i> Events</a></li>



                                </ul>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="tab-content">

                                    <div class="tab-pane" id="tab_1">
                                        <div class="row">
                                            <div class="col-md-5">
                                                Disbursements
                                            </div>
                                            <div class="col-md-7">
                                                Coming soon
                                            </div>

                                        </div>
                                    </div>



                                    <div class="tab-pane active" id="tab_2">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h4 class="text-purple"><i class="fa fa-info-circle"></i> Upload M-Pesa Statement</h4>
                                                <form id="cus-upload" method="POST" action="action/payments/audit-upload.php" enctype="multipart/form-data" class="border-right">
                                                    <div class="box-body">

                                                        <div class="form-group">
                                                            <label for="file_" class="col-sm-3 control-label">B2C File</label>

                                                            <div class="col-sm-9">
                                                                <input type="file" class="form-control" id="file_" name="file_">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <br/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="file_" class="col-sm-3 control-label">Statement Date</label>

                                                            <div class="col-sm-4">
                                                                <input type="date" class="form-control" id="start_date" name="start_date">
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input type="date" class="form-control" id="end_date" name="end_date">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3"></div>
                                                        <div class="col-sm-9">
                                                            <div class="box-footer">
                                                                <br>
                                                                <div class="prgress">
                                                                    <div class="progresscus-upload" id="progress">
                                                                        <div class="barcus-upload" id="bar"></div>
                                                                        <br>
                                                                        <div class="percentcus-upload" id="percent"></div>
                                                                    </div>
                                                                </div>

                                                                <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                                                <button type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('cus-upload');">Upload
                                                                </button>

                                                            </div>

                                                        </div>

                                                    </div>
                                                    <!-- /.box-body -->

                                                    <!-- /.box-footer -->
                                                </form>

                                            </div>
                                            <div class="col-md-8">
                                                <div class="messagecus-upload" id="message"></div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="tab-pane" id="tab_2">
                                        <div class="row">
                                            <div class="col-md-4">
                                                Events
                                            </div>
                                            <div class="col-md-8">
                                                Coming soon
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /.box-body -->

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
<script>
    // Toggle Password Visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        var passwordField = document.getElementById('documentPassword');
        var passwordFieldType = passwordField.getAttribute('type');
        if (passwordFieldType === 'password') {
            passwordField.setAttribute('type', 'text');
            this.innerHTML = '<i class="fa fa-eye-slash"></i>';
        } else {
            passwordField.setAttribute('type', 'password');
            this.innerHTML = '<i class="fa fa-eye"></i>';
        }
    });

    // Display Selected File Name with Attachment Icon
    document.getElementById('mpesaFile').addEventListener('change', function() {
        var fileName = '';
        if (this.files && this.files.length > 1) {
            fileName = this.files.length + ' files selected';
        } else if (this.files.length === 1) {
            fileName = '<i class="fa fa-paperclip"></i> ' + this.files[0].name;
        } else {
            fileName = '';
        }
        document.getElementById('fileName').innerHTML = fileName;
    });
</script>

</body>
</html>
