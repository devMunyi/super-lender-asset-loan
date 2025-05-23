<?php
session_start();
include_once("php_functions/functions.php");
include_once("configs/conn.inc");

$userd = session_details();
$phone = $userd['phone'];
$phone_enc = hideMiddleDigits($phone);


?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Superlender | Lockscreen</title>

    <?php
    include_once('header_includes.php');
    ?>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="hold-transition lockscreen">
<!-- Automatic element centering -->
<div class="lockscreen-wrapper">
  <div class="lockscreen-logo">
    <a><b><i class="fa fa-lock"></i>2 </b>Factor Authentication</a>
  </div>
  <!-- User name -->
  <div class="lockscreen-name">
      <h3>Where do you want OTP Sent? <br/> <button class="btn btn-primary" onclick="resend_otp('SMS')"><i class="fa fa-comment-o"></i> SMS</button> <button class="btn btn-success" onclick="resend_otp('EMAIL')"><i class="fa fa-envelope"></i> Email</button></h3>
      <br/>
  </div>

  <!-- START LOCK SCREEN ITEM -->
  <div class="lockscreen-item">
    <!-- lockscreen image -->

    <!-- /.lockscreen-image -->

    <!-- lockscreen credentials (contains the form) -->
    <form class="lockscreen-credentialsh">
      <div class="container-fluid">
        <input type="text" id="otp_" style="margin: 10px 0;" class="form-control" placeholder="Enter OTP">


      </div>
        <div class="container-fluid">
            <button type="button" style="margin: 10px 0;" onclick="confirm_2f();" class="form-control btn btn-primary bg-purple-gradient">Confirm</button>
        </div>
    </form>
    <!-- /.lockscreen credentials -->

  </div>
  <!-- /.lockscreen-item -->
  <div class="help-block text-center font-14 text-black">
    Didn't receive it? Click [SMS] or [Email] above again or <a class="text-bold" href="logout">Start Over</a>
  </div>


</div>
<!-- /.center -->
<?php
include_once "configs/20200902.php";
?>
<!-- jQuery 3 -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="scripts/common.min.js?v=2024-06-08"></script>
<script src="scripts/authentication.js?v=2024-06-08"></script>

</body>
</html>
