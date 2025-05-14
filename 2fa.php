<?php
session_start();
include_once("configs/conn.inc");
include_once("php_functions/functions.php");

// check for key one-factor from $_SESSION if is and is 1 if not redirect to login
if (!isset($_SESSION['one-factor']) || $_SESSION['one-factor'] != 1) {
    echo "<meta http-equiv=\"refresh\" content= \"0, URL=login\" />";
    exit();
}

$uid = intval($_GET['ut'] ?? 0);

if(empty($uid)){
    exit(errormes("Invalid User ID!"));
}

$uid = decurl($uid);
$username = strtolower(fetchrow('o_users', "uid=$uid", "email"));


if (empty($username)) {
    exit(errormes("Invalid User"));
}

try {
    $hasPasskey = has_passkey($username);
} catch (Exception $e) {
    $message = $e->getMessage();
    exit(errormes($message));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Lender | 2FA</title>
    <?php
    include_once('header_includes.php');
    ?>
    <style>
        .title {
            color: #1a1a1a;
            font-size: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .lock-icon {
            width: 20px;
            height: 20px;
        }

        .fingerprint {
            width: 80px;
            height: 80px;
            margin: 20px auto;
            opacity: 0.7;
        }
    </style>
    <link rel="stylesheet" href="themes/styles/login-theme2css.css?f=5">

</head>

<body>
    <nav class="navbar">
        <div class="pull-left" style="background: #ffffffcc;
    padding: 10px;
    border-bottom-right-radius: 65px;
    border-bottom: 6px solid #346cbc;">
            <img id="logo_" alt="Logo" style="    font-weight: bold;    height: 100px;    text-align: center;    margin-top: 9px;" src="<?php echo ($icon && $has_remote_cdn == 1) ? $icon : 'dist/img/icon.png'; ?>">
            <img id="logo_" alt="Logo" style="font-weight: bold; height: 100px;" src="<?php echo ($icon && $has_remote_cdn == 1) ? $logo : 'dist/img/logo.png'; ?>">
        </div>
    </nav>
    <div class="container">
        <h3 class="title">
            <svg class="lock-icon" viewBox="0 0 24 24" fill="#1a1a1a">
                <path d="M12 1C8.676 1 6 3.676 6 7v2H4v14h16V9h-2V7c0-3.324-2.676-6-6-6zm0 2c2.276 0 4 1.724 4 4v2H8V7c0-2.276 1.724-4 4-4z" />
            </svg>
            2 Factor Authentication
        </h3>

        <form onsubmit="return false;" method="post">
            <input type="hidden" id="username" value="<?php echo htmlspecialchars($username); ?>">
            <div onclick="<?php echo $hasPasskey ? 'login()' : 'signup()'; ?>">
                <svg class="fingerprint" viewBox="0 0 24 24" fill="#1a1a1a">
                    <path d="M17.81 4.47c-.08 0-.16-.02-.23-.06C15.66 3.42 14 3 12.01 3c-1.98 0-3.86.47-5.57 1.41-.24.13-.54.04-.68-.2-.13-.24-.04-.54.2-.68C7.82 2.52 9.86 2 12.01 2c2.13 0 3.99.47 6.03 1.52.25.13.34.43.21.67-.09.18-.26.28-.44.28zM3.5 9.72c-.1 0-.2-.03-.29-.09-.23-.16-.28-.47-.12-.7.99-1.4 2.25-2.5 3.75-3.27C9.98 4.04 14 4.03 17.15 5.65c1.5.77 2.76 1.86 3.75 3.25.16.22.11.54-.12.7-.23.16-.54.11-.7-.12-.9-1.26-2.04-2.25-3.39-2.94-2.87-1.47-6.54-1.47-9.4.01-1.36.7-2.5 1.7-3.4 2.96-.08.14-.23.21-.39.21zm6.25 12.07c-.13 0-.26-.05-.35-.15-.87-.87-1.34-1.43-2.01-2.64-.69-1.23-1.05-2.73-1.05-4.34 0-2.97 2.54-5.39 5.66-5.39s5.66 2.42 5.66 5.39c0 .28-.22.5-.5.5s-.5-.22-.5-.5c0-2.42-2.09-4.39-4.66-4.39-2.57 0-4.66 1.97-4.66 4.39 0 1.44.32 2.77.93 3.85.64 1.15 1.08 1.64 1.85 2.42.19.2.19.51 0 .71-.11.1-.24.15-.37.15zm7.17-1.85c-1.19 0-2.24-.3-3.1-.89-1.49-1.01-2.38-2.65-2.38-4.39 0-.28.22-.5.5-.5s.5.22.5.5c0 1.41.72 2.74 1.94 3.56.71.48 1.54.71 2.54.71.24 0 .64-.03 1.04-.1.27-.05.53.13.58.41.05.27-.13.53-.41.58-.57.11-1.07.12-1.21.12zM14.91 22c-.04 0-.09-.01-.13-.02-1.59-.44-2.63-1.03-3.72-2.1-1.4-1.39-2.17-3.24-2.17-5.22 0-1.62 1.38-2.94 3.08-2.94 1.7 0 3.08 1.32 3.08 2.94 0 1.07.93 1.94 2.08 1.94s2.08-.87 2.08-1.94c0-3.77-3.25-6.83-7.25-6.83-2.84 0-5.44 1.58-6.61 4.03-.39.81-.59 1.76-.59 2.8 0 .78.07 2.01.67 3.61.1.26-.03.55-.29.64-.26.1-.55-.04-.64-.29-.49-1.31-.73-2.61-.73-3.96 0-1.2.23-2.29.68-3.24 1.33-2.79 4.28-4.6 7.51-4.6 4.55 0 8.25 3.51 8.25 7.83 0 1.62-1.38 2.94-3.08 2.94s-3.08-1.32-3.08-2.94c0-1.07-.93-1.94-2.08-1.94s-2.08.87-2.08 1.94c0 1.71.66 3.31 1.87 4.51.95.94 1.86 1.46 3.27 1.85.27.07.42.35.35.61-.05.23-.26.38-.47.38z" />
                </svg>
                <button id="<?php echo $hasPasskey ? 'webauthn-login-btn' : 'webauthn-register-btn'; ?>" class="">
                    <span class="spinner-border hidden"></span>
                    <span class="btn-txt"><?php echo $hasPasskey ? 'Verify Passkey' : 'Register Passkey'; ?></span>
                </button>
            </div>
        </form>
        <!-- Add the powered by text here -->
        <div style="margin-top:20px; font-size:13px; color:#888; text-align:center;">Powered by Superlender Secure Plus</div>
        <div style="display: flex; justify-content: center; align-items: center;">
            <img alt="Logo" style="font-weight: bold; height: 50px;" src="dist/img/sl-plus-icon.png">
        </div>
    </div>


    <?php
    include_once "configs/20200902.php";
    ?>
    <!-- jQuery 3 -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="scripts/common.js?v=2122"></script>
    <script src="dist/js/simplewebauthn.min.js"></script>
    <!-- <script src="https://unpkg.com/@simplewebauthn/browser/dist/bundle/index.es5.umd.min.js"></script> -->
    <script src="scripts/webauthn.js?v=18"></script>

</body>
</html>