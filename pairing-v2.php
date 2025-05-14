<?php
session_start();
include_once ("php_functions/functions.php");
include_once ("configs/conn.inc");
include_once("php_functions/authenticator.php");

$company = company_settings();

$userd = session_details();
$user_id = $userd['uid'];
$group_tag = $userd['tag'];
$user_group = $userd['user_group'];
$user_branch = $userd['branch'];


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Pairing</title>
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
                    Pairing
                    <small>List</small>
                    <a class="btn btn-success" href="pairing">Go to V1</a>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Pairing</li>
                </ol>
            </section>
           
            
            <section class="content">
                    <div class="row well">
                        <?php
                      $upload_pairs = permission($userd['uid'],'o_pairing',"0","read_");
                  if($upload_pairs != 1){
      die(errormes("You don't have permission to view this page"));
      exit();
             }

                        ?>
                        <div class="col-md-2">
                           <a onclick="load_std('/extensions/sp-pairing-1.php','#dynamic_load','')" class="btn btn-block btn-primary">All Current Pairs</a>
                           <a onclick="load_std('/extensions/sp-pairing-2.php','#dynamic_load','')" class="btn btn-block btn-primary">All Unpaired Accounts</a>
                           <a onclick="load_std('/extensions/sp-pairing-3.php','#dynamic_load','')" class="btn btn-block btn-primary">All wrong Accounts</a>
                        </div>
                        <div class="col-md-10">
                           <div class="box">
                            <div class="box-header">

                            </div>
                               <div class="box-body">
                                   <div id="dynamic_load">

                                   </div>

                           </div>
                        </div>



                    </div>
                    <!-- /.col -->
                </div>
            </section>

        <!-- /.content -->
    </div>

    <!-- /.content-wrapper -->
   

    <!-- Control Sidebar -->

    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<?php
    include_once("footer.php");
    ?>


<?php
include_once("footer_includes.php");
?>
<script>
    $(document).ready( function () {
        document.title = "<?php echo $company['name'].'-'.$title; ?>";
        $('#tbl1').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'csv'
            ]
        } );
        $('#tbl2').DataTable({
            dom: 'Bfrtipji',
            buttons: [
                'csv'
            ]
        } );
      
    } );
</script>

</body>
</html>
