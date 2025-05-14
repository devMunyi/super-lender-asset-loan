<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon"  sizes="32x32" href="favicon.ico">
    <title>Super Lender | Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <?php
    include_once ('header_includes.php');
    ?>
    <link rel="stylesheet" href="themes/styles/default-theme.css">
</head>
<body class="hold-transition login-page" style="background:<?php echo $primary_color; ?> !important;">
<div class="login-box">
    <div class="login-logo">
        <div class="logo-back">
        <img  id="logo_"   alt="Logo" style="font-weight: bold; display: none; width: 100%;">
        </div>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg font-16 text-bold">Login to your account</p>
        <form onsubmit="return false;" method="post">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" id="inp_email" placeholder="Email or Phone">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" id="inp_password" class="form-control" placeholder="Password">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">

                <div class="col-xs-7">
                    <div class="checkbox icheck">
                        <label style="margin-left: 20px;">
                            <input type="checkbox"> Remember Me
                        </label>
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-xs-5">
                    <button type="submit" onclick="login()" class="btn btn-primary btn-block btn-flat">Sign In</button>
                </div>
                <!-- /.col -->
            </div>
        </form>


        <!-- /.social-auth-links -->


    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
<?php
include_once "configs/20200902.php";
?>
<!-- jQuery 3 -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- iCheck -->
<script src="plugins/iCheck/icheck.min.js"></script>
<script src="scripts/common.js"></script>
<script src="scripts/authentication.js"></script>
<script>

    $('document').ready(function(){

        logo = 'icon.png?v=1';

        if(logo)
        {
            $('#logo_').attr('src', 'dist/img/'+logo).fadeIn('slow');
        }
    });
</script>

</body>
</html>