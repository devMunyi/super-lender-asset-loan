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
    <style>
        .input-container {
            position: relative;
        }

        #togglePassword {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 1;
        }
    </style>
    <link rel="stylesheet" href="themes/styles/login-theme2css.css?f=2">

</head>

<body>
    <nav class="navbar">
        <div class="pull-left" style="background: #ffffffcc;
    padding: 10px;
    border-bottom-right-radius: 65px;
    border-bottom: 6px solid #346cbc;
">
            <img id="logo_" alt="Logo" style="    font-weight: bold;    height: 100px;    text-align: center;    margin-top: 9px;" src="<?php echo ($icon && $has_remote_cdn == 1) ? $icon : 'dist/img/icon.png'; ?>">
            <img id="logo_" alt="Logo" style="font-weight: bold; height: 100px;" src="<?php echo ($icon && $has_remote_cdn == 1) ? $logo : 'dist/img/logo.png'; ?>">
        </div>
    </nav>
    <div class="container">


        <div class="brand-title">
            <h3>Login to Account</h3>
        </div>
        <form onsubmit="return false;" method="post">
            <div class="inputs">
                <label>EMAIL</label>
                <input type="email" id="inp_email" />
                <label>PASSWORD</label>
                <div class="input-container">
                    <input type="password" id="inp_password" />
                    <i class="fa fa-eye-slash" id="togglePassword"></i>
                </div>

                <button id="loginBtn" type="submit" onclick="login()">
                    <span class="spinner-border hidden"></span>
                    <span class="btn-txt">LOGIN</span>
                </button>
            </div>
        </form>
    </div>


    <?php
    include_once "configs/20200902.php";
    ?>
    <!-- jQuery 3 -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="scripts/common.js?v=1353"></script>
    <script src="scripts/authentication.js?v=1353"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#inp_password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });


        // function toggleBtnState(btnId, btnText = "") {
        //     const button = document.getElementById(btnId);
        //     button.disabled = !button?.disabled;
        //     // const originalBtnText = button.innerHTML;

        //     if (btnText) {
        //         button.innerHTML = btnText;
        //     }
        // }
    </script>

</body>

</html>