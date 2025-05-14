<script src="https://cdn.jsdelivr.net/npm/darkreader@4.9.101/darkreader.min.js"></script>
<script>
    // Ensure DarkReader fetches styles properly
    DarkReader.setFetchMethod(window.fetch);

    // Check localStorage for dark mode preference
    const isDarkMode = localStorage.getItem("slDarkMode") === "true";

    if (isDarkMode) {
        DarkReader.enable({
            brightness: 100,
            contrast: 90,
            sepia: 10
        });
    } else {
        DarkReader.disable();
    }
</script>
<?php
session_start();
if (isset($_SESSION['o-token'])) {

    $token = $_SESSION['o-token'];
    $valid = validatetoken($token);
    if ($valid == 0) {
        header("location:login");
        die("<h3>Session Expired<a href='logout'>Login Again</a></h3>");
    } else {
        $token_user = fetchrow('o_tokens', "token='$token'", "userid");
        $userd = fetchonerow('o_users', "uid='$token_user'", "uid, name, email, phone, join_date, user_group, branch, pass_expiry,status");
        $group_name = fetchrow('o_user_groups', "uid='" . $userd['user_group'] . "'", "name");
        $pass_expiry = $userd['pass_expiry'];
        ///-----Check if password is expired
        if(date_greater($date,datesub($pass_expiry, 0,0, 1))) {
            $currentPage = basename(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
            if($currentPage != "profile") {
                header("location:profile?password");
            }
            else{
              $_message_ = "<span class='font-18 text-red text-bold'><i class='fa fa-info-circle'></i> You need to change your password</span>";
            }
        }
    }
} else {
    header("location:login");
    die("<h3>Session Expired<a href='logout'>Login Again</a></h3>");
}
if ($_SESSION['archives'] == 1) {
    $header_color = "#6d4c41";
    $wtitle = "";
} else {
    $header_color = "#2c3e50";
    $wtitle = "";
}

?>
<header class="main-header">

    <div id="standardnotif" class="alert alert-dismissible"></div>
    <!-- Logo -->
    <a href="index" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b><img src="<?php echo ($icon && $has_remote_cdn == 1) ? $icon : 'dist/img/icon.png?v20240927'; ?>" height="40px"> </b></span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><img src="<?php echo ($icon && $has_remote_cdn == 1) ? $icon : 'dist/img/icon.png?v20240927'; ?>" height="40px"> <img src="<?php echo ($logo && $has_remote_cdn == 1) ? $logo : 'dist/img/logo.png?v20240927'; ?>" style="width:100%; max-width:150px;"> </span>
    </a>

    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" style="background-color:<?php echo $header_color; ?>;">

        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <span href="#" class="font-light font-bold" style="float:left;background-color:transparent;background-image:none;padding:15px 15px;">
            <?php
            echo $wtitle;
            ?>
        </span>



        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav" style="display: flex; justify-content: center; align-items: center;">

                <li class="nav-item">
                    <a id="slDarkModeToggle" href="#" style="cursor: pointer; padding: 10px 8px;"></a>
                </li>
                <li class="nav-item search_bar">
                    <input type="search" id="inp_search_everything" placeholder="Search everything ..." class="form-control">
                </li>
                <li class="nav-item dropdown notifications-menu">
                    <a class="nav-link btn bg-blue-gradient dropdown-toggle" data-widget="navbar-search" href="#" role="dropdown-toggle" style="background: #dadee317;" onclick="search_everything()" data-toggle="dropdown">
                        <i class="fa fa-search"></i>
                    </a>
                    <ul class="dropdown-menu" id="result_drop">

                    </ul>

                </li>
                </li>








                <li style="display: none;" class="dropdown messages-menu">
                    <a href="#" onclick="message_list();" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-envelope-o"></i>
                        <label class="label label-lg label-danger" id="msg_count"></label>
                    </a>
                    <ul class="dropdown-menu" id="message_drop">

                    </ul>
                </li>

                <li style="display: none;" class="dropdown messages-menu">
                    <ul class="dropdown-menu" id="message_drop">
                        Results
                    </ul>
                </li>



                <!-- <li class="nav-tabs">
                    <?php
                    if ($has_archive == 1) {
                        $view_archives = permission($userd['uid'], 'o_archives', "0", "read_");
                        if ($view_archives == 1) {
                    ?>
                            <span id="archives_"></span>
                            <?php
                            if (isset($_SESSION['archives'])) {
                                ///---
                            } else {
                            ?>
                                <a title="Viewing current data, click to view archive" onclick="archives_toggle('OPEN');" class="btn"><i class="fa fa-circle text-green"></i>C</a>
                            <?php
                            }
                            ?>
                        <?php
                        }
                        if (isset($_SESSION['archives'])) {
                        ?>
                            <a title="Viewing archive data, click to view current" onclick="archives_toggle('CLOSE');" class="btn"><i class="fa fa-file-zip-o text-gray"></i>A</a>
                    <?php
                        }
                    }
                    ?>

                </li> -->
                <!-- Notifications: style can be found in dropdown.less -->
                <li class="dropdown notifications-menu">
                    <a href="#" onclick="notif_list('#notif_drop',0,5);" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <label class="label font-16 label-danger" id="notif_count"></label>
                    </a>
                    <ul class="dropdown-menu" id="notif_drop">

                    </ul>
                </li>

                <!-- Tasks: style can be found in dropdown.less -->

                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" onclick="notif_list();" class="dropdown-toggle" data-toggle="dropdown">
                        <?php
                        $parts = explode(' ', $userd['name']);
                        $initials = '';
                        foreach ($parts as $part) {
                            $initials .= strtoupper($part[0]);
                        }

                        ?>
                        <img src="<?php echo ($user_icon && $has_remote_cdn == 1) ? $user_icon : 'dist/img/user.png'; ?>" class="user-image" alt="User Image"> <?php echo $initials; ?>
                        <span class="hidden-xs">

                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?php echo ($user_icon && $has_remote_cdn == 1) ? $user_icon : 'dist/img/user.png'; ?>" class="img-circle" alt="User Image">

                            <p>
                                <?php


                                echo $userd['name'];
                                ?>
                                <small><?php echo $group_name; ?></small>
                            </p>
                        </li>
                        <!-- Menu Body -->
                        <li class="user-body">

                            <!-- /.row -->
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="profile?profile" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="logout" class="btn btn-default btn-flat">Sign out</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa text-yellow  fa-hand-o-up"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>