<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Lender | Log in</title>
    <?php
        include_once('header_includes.php');
    ?>
    <link rel="stylesheet" href="themes/styles/login-theme1css.css?v=1620">
</head>
<body>
    <div class="row login-page">
        <div class="col-sm-6 left-sect">
            <div class="login-box">
                    <div class="login-logo">
                    <img id="logo_" alt="Logo" style="font-weight: bold; height: 70px;" src="dist/img/icon.png?v=2">
                    <img id="logo_" alt="Logo" style="font-weight: bold; height: 45px;" src="dist/img/logo.png?v=2">
                    </div>
                    <!-- /.login-logo -->
                    <div class="login-box-body_custom">
                        <div class="login-box-msg font-16 text-bold bg-primary">Login to your account</div>
                        <form onsubmit="return false;" method="post">
                            <div class="form-elem">
                                <div class="form-group has-feedback">
                                    <input type="text" class="form-control" id="inp_email" placeholder="Email or Phone">
                                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                                </div>
                                <div class="form-group has-feedback">
                                    <input type="password" id="inp_password" class="form-control" placeholder="Password">
                                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                                </div>
                                <div class="row">

                                    <div class="col-xs-6">
                                        <div class="checkbox icheck">
                                            <label style="margin-left: 20px;">
                                                <input type="checkbox"> Remember Me
                                            </label>
                                        </div>
                                    </div>
                                    <!-- /.col -->
                                    <div class="col-xs-6">
                                        <button id="loginBtn" type="submit" onclick="login()" class="btn btn-primary btn-block btn-flat">
                                        <span class="spinner-border hidden"></span>
                                        <span class="btn-txt">LOGIN</span>
                                        </button>
                                    </div>
                                    <!-- /.col -->
                                </div>
                            </div>
                        </form>


                        <!-- /.social-auth-links -->


                    </div>
                    <!-- /.login-box-body -->
            </div>
        </div>
        <div class="col-sm-6">
            <!-- <img src="dist/img/close-up-woman-market.jpg" alt="WOMAN" style="height: 100vh"> -->
        </div>
    </div>

    <?php
        include_once "configs/20200902.php";
    ?>
    <!-- jQuery 3 -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="scripts/common.js?v=1620"></script>
    <script src="scripts/authentication.js?v=1620"></script>
</body>
</html>
