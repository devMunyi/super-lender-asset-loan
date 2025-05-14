<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$b = $_POST['b'];
$user = $_POST['user'];

$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$u = fetchonerow('o_users',"uid='$user'","*");
$full_name = $u['name'];
$email = $u['email'];
$user_group = $u['user_group'];
$status = $u['status'];
$branch = $u['branch'];

if($user_group == 7){
    echo "<h5><b>$full_name</b> is an LO, find them a new CO pair below,</h5>";
    $already_paired = implode(',', table_to_array('o_pairing',"branch='$branch' AND status=1","100","co"));
    if(input_length($already_paired, 1) == 0){
        $already_paired = 0;
    }

    echo "<table class='table table-bordered table-striped'>";

    $all_users = fetchtable('o_users',"user_group = '8' AND status=1 AND branch='$branch' AND uid not in ($already_paired)","uid","asc","100000","uid, name, email");
    $total = mysqli_num_rows($all_users);


    if($total == 0){
        echo "<tr><td colspan='4'>No unpaired users found</td></tr>";
    }
    else {
        while ($al = mysqli_fetch_array($all_users)) {

            $uid = $al['uid'];
            $name = $al['name'];
            $email = $al['email'];
            $action = "<button title=\"Pair with $name\" class=\"btn btn-success bg-green-gradient\" onclick=\"pair_users2($user,$uid);\">Pair</button>";

            echo "<tr><td>$uid</td><td>$name</td><td>$email</td><td>$action</td></tr>";

        }
    }

    echo "</table>";
}
else if($user_group == 8){
    echo "<h5><b>$full_name</b> is an CO, find them a new LO to pair below,</h5>";
    $already_paired = implode(',', table_to_array('o_pairing',"branch='$branch' AND status=1","100","lo"));
    if(input_length($already_paired, 1) == 0){
        $already_paired = 0;
    }
    echo "<table class='table table-bordered table-striped'>";
    $all_users = fetchtable('o_users',"user_group = '7' AND status=1 AND branch='$branch' AND uid not in ($already_paired)","uid","asc","100000","uid, name, email");
    $total = mysqli_num_rows($all_users);


    if($total == 0){
        echo "<tr><td colspan='4'>No unpaired users found</td></tr>";
    }
    else {
        while ($al = mysqli_fetch_array($all_users)) {
            $uid = $al['uid'];
            $name = $al['name'];
            $email = $al['email'];
            $action = "<button title=\"Pair with $name\" class=\"btn btn-success bg-green-gradient\" onclick=\"pair_users2($uid, $user);\">Pair</button>";
            echo "<tr><td>$uid</td><td>$name</td><td>$email</td><td>$action</td></tr>";

        }
    }
    echo "</table>";
}
else{
    echo errormes("This is neither an LO nor a CO.");
}
