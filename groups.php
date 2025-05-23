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
  <title><?php echo $company['name']; ?> | Groups</title>
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
      if(isset($_GET['group'])){
        ?>
        <div class="_details">
        <?php
        include_once ('widgets/group-details.php');
        ?>
        </div>

        <?php
            }
            elseif (isset($_GET['group-add-edit'])){
        ?>
      <div class="_form">
        <?php
            include_once ('forms/group-add-edit-form.php');
            ?>
      </div>
      <?php }else { ?>
      <div class="_list">
        <?php include_once ('widgets/group-list.php');?>
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


    group_list();
    pager('#example1');


    ////////-------------Doc Load Other Function


  })
  </script>
</body>

</html>