<?php
session_start();
include_once("vendor/autoload.php");
include_once("php_functions/functions.php");
include_once("configs/conn.inc");
include_once("php_functions/authenticator.php");
include_once("configs/jwt.php");
include_once("php_functions/jwtAuthUtils.php");


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
    <title><?php echo $company['name']; ?> | Company Documents</title>
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
                    Documents
                    <small>List</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Settings</li>
                </ol>
            </section>
            <?php
            // $view_docs = permission($userd['uid'],'o_company_documents',"0","read_");
            $view_docs = 1;
            if ($view_docs == 1) {
            ?>
                <section class="content">
                    <div class="row">
                        <div class="col-xs-2 font-14">
                            <ol class="list-group">
                                <?php
                                $docs = fetchtable('o_company_docs', "status=1", "title", "asc", "100");
                                $group_names = table_to_obj('o_user_groups', "uid > 0", "100", "uid", "name");
                                $branches = table_to_obj('o_branches', "uid > 0", "1000", "uid", "name");
                                while ($d = mysqli_fetch_array($docs)) {
                                    $uid = $d['uid'];
                                    $did = encurl($uid);
                                    $title = $d['title'];
                                    $doc_url = $d['doc_url'];
                                    $is_external = $d['is_external'] ?? 0;
                                    $require_token = $d['require_token'] ?? 0;

                                    if ($is_external == 1 && $require_token == 1) {
                                       
                                        $userd['user_group'] = $group_names[$userd['user_group']] ?? "";
                                        $userd['branch'] = $branches[$userd['branch']] ?? "";
                                        $payload = generateBearerToken($jwt_key, ['uid' => $userd['uid'], 'username' => $userd['name'], 'email' => $userd['email'], 'user_group' => $userd['user_group'], 'branch' => $userd['branch']]);

                                        $token = $payload['token'];


                                        $doc_url = $doc_url . "?token=" . $payload['access_token'] ?? '';
                                    }

                                    //         if ($is_external == 1) {
                                    //             echo "  <li class=\"list-group-item text-black text-bold\">
                                    //     <a class=\"text-primary\" target=\"_blank\" href=\"$doc_url\"><i class=\"fa fa-file-pdf-o\"></i> $title</a>
                                    // </li>";
                                    //         } else {
                                    //             $downloadable = $d['downloadable'];
                                    //             echo "  <li class=\"list-group-item text-black text-bold\">
                                    //     <a class=\"text-primary\" href=\"company-documents?doc=$doc_url&did=$did\"><i class=\"fa fa-file-pdf-o\"></i> $title</a>
                                    // </li>";
                                    //         }

                                    $downloadable = $d['downloadable'];
                                    echo "  <li class=\"list-group-item text-black text-bold\">
                    <a class=\"text-primary\" href=\"company-documents?doc=$doc_url&did=$did\"><i class=\"fa fa-file-pdf-o\"></i> $title</a>
                </li>";
                                }
                                ?>


                            </ol>
                        </div>
                        <div class="col-xs-10">

                            <!-- /.box -->

                            <div class="box">

                                <!-- /.box-header -->
                                <div class="box-body">
                                    <?php
                                    if (isset($_GET['doc'])) {
                                        $doc = $_GET['doc'];
                                        $doc_uid = decurl($_GET['did']);
                                        $doc_det = fetchonerow('o_company_docs', "uid=$doc_uid", "uid, downloadable");
                                        if ($doc_det['downloadable'] == 1) {
                                            $toolbar = "";
                                        } else {
                                            $toolbar = "#toolbar=0";
                                        }
                                        echo "<iframe src=\"$doc$toolbar\"></iframe>";
                                    } else {
                                        echo "<h4><i class='fa fa-hand-o-left'></i>Select a document from the menu</h4>";
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
            } else {
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

    if ((isset($_GET['g'])) && (isset($_GET['t']))) {
        $load_perms = 1;
    }
    $g = $_GET['g'];
    $t = $_GET['t'];
    $u = $_GET['u'];
    $r = $_GET['r'];
    ?>
    <script>
        $(function() {
            $('#example1').DataTable()
            $('#example2').DataTable({
                'paging': true,
                'lengthChange': false,
                'searching': false,
                'ordering': true,
                'info': true,
                'autoWidth': false
            })
            if ('<?php echo $load_perms; ?>') {
                permissions('<?php echo $g; ?>', '<?php echo $u; ?>', '<?php echo $t; ?>', '<?php echo $r; ?>', '', '');
            }

            if (document.getElementById('team_members')) {
                load_std('/jresources/staff/team-members.php', '#team_members', 'leader=0');
            }
        })
    </script>
</body>

</html>