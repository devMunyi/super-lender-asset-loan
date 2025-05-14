<?php
session_start();
include_once("php_functions/functions.php");
include_once("configs/conn.inc");
include_once("php_functions/spinMobileUtil.php");

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

        #fileName {
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
                    Customer Scoring
                    <small>List</small>
                </h1>

            </section>
            <section class="content">


                <!-- /.box -->

                <div class="box">
                    <div class="box-header bg-info">
                        <ul class="nav nav-tabs nav-justified font-16">
                            <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-history"></i> Appraisal</a></li>
                            <li class="nav-item nav-100"><a href="#tab_2" data-toggle="tab" aria-expanded="false"><i class="fa fa-file-pdf-o"></i> Statement Scoring</a></li>
                            <li class="nav-item nav-100"><a href="#tab_3" onclick="spinscore_list();" data-toggle="tab" aria-expanded="false"><i class="fa fa-spin"></i> SPIN &trade; Score</a></li>
                            <li class="nav-item nav-100"><a href="#tab_4" data-toggle="tab" aria-expanded="false"><i class="fa fa-building"></i> CRB Score</a></li>
                            <li class="nav-item nav-100"><a href="#tab_5" data-toggle="tab" aria-expanded="false"><i class=""></i> SuperLender Score</a></li>

                        </ul>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <!-- <div class="row">
                            <div class="col-sm-12">
                                Coming soon...
                            </div>
                        </div> -->

                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_1">
                                <div class="row">
                                    <div class="col-sm-12">
                                        Appraisal Coming soon...
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab_2">
                                <div class="row">
                                    <div class="col-sm-12">
                                        Statement Scoring Coming soon...
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab_3">
                                <div class="row">
                                    <!-- <div class="col-md-2">
                                       
                                    </div> -->
                                    <div class="col-md-10">
                                        <table id="example2" class="table-bordered font-14 table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer</th>
                                                    <th>Branch</th>
                                                    <th>Analysis Status</th>
                                                    <th>Action</th>

                                                </tr>
                                            </thead>
                                            <tbody id="spinscore_list">
                                                <tr>
                                                    <td colspan="5" class="text-center">Loading data...</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer</th>
                                                    <th>Branch</th>
                                                    <th>Analysis Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </tfoot>
                                        </table>

                                    </div>

                                    <div class="col-md-2" style="margin-top: 52px;">
                                        <table class="table-bordered font-14 table table-hover table-condensed">
                                            <tr class="text-center">
                                                <td><button class="btn btn-success btn-block" onclick="modal_view('/forms/spinscore-add-edit','','New Upload')"><i class="fa fa-plus"></i> New Upload</button></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>


                            <div class="tab-pane" id="tab_4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        CRB Score Coming soon...
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab_5">
                                <div class="row">
                                    <div class="col-sm-12">
                                        Superlender Score Coming soon...
                                    </div>
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
        const spinScoreElement = document.getElementById('spinscore_list');
        if (spinScoreElement) {
            // spinscore_list();
            pager('#example2');
        }


        // Toggle Password Visibility
        document?.getElementById('togglePassword')?.addEventListener('click', function() {
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
        document.getElementById('mpesaFile')?.addEventListener('change', function() {
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

    <?php
    echo "<div style= 'display:none'>" . paging_values_hidden('ss.uid > 0', 0, 10, 'ss.uid', 'desc', '', 'spinscore_list') . "</div>";
    ?>
</body>

</html>