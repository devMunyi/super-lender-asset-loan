<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


/////------Remove all bad pairs

////--------Make new pairs

////--------Move Loans with bad pairs to new pairs

$all_staff = fetchtable('o_users',"uid > 0","uid","asc","100000","uid, branch");
while($a = mysqli_fetch_array($all_staff)){
    $uid = $a['uid'];
    $branch = $a['branch'];
    if($branch > 0){
        //echo "UPDATE `maria_simple`.`o_pairing` SET `branch` = '$branch' WHERE (`uid` > 0 AND lo = '$uid');<br/>";
       echo updatedb('o_pairing',"branch='$branch'","lo='$uid'");
    }
}
///---------Move customers to current LO and CO

include_once("../configs/close_connection.inc");
?>


