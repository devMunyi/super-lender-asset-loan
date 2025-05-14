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
    <link rel="stylesheet" href="themes/styles/login-theme3css.css?f=2">
</head>

<body>
    <div class="container">
        <div class="screen">
            <div class="screen__content">
                <form class="login" onsubmit="return false;" method="post">
                    <div class="login__field">
                        <i class="login__icon fa fa-user"></i>
                        <input type="text" class="login__input" placeholder="Email" id="inp_email">
                    </div>
                    <div class="login__field">
                        <i class="login__icon fa fa-lock"></i>
                        <input type="password" class="login__input" placeholder="Password" id="inp_password">
                    </div>
                    <button id="loginBtn" type="submit" class="button login__submit" onclick="login()">

                        <span class="spinner-border hidden"></span>
                            <span class="btn-txt text-center">LOGIN</span>
                    </button>
                </form>
                <!-- <div class="social-login">
                    <h3>log in via</h3>
                    <div class="social-icons">
                        <a href="#" class="social-login__icon fab fa-instagram"></a>
                        <a href="#" class="social-login__icon fab fa-facebook"></a>
                        <a href="#" class="social-login__icon fab fa-twitter"></a>
                    </div>
                </div> -->
            </div>
            <div class="screen__background">
                <span class="screen__background__shape screen__background__shape4"></span>
                <span class="screen__background__shape screen__background__shape3"></span>
                <span class="screen__background__shape screen__background__shape2"></span>
                <span class="screen__background__shape screen__background__shape1"></span>
            </div>
        </div>
    </div>

    <?php
    include_once "configs/20200902.php";
    ?>
    <!-- jQuery 3 -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="scripts/common.js"></script>
    <script src="scripts/authentication.js"></script>
</body>

</html>