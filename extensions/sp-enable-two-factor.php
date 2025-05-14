<?php 

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

// 2, 6, 5, 9, 11, 15, 16, 18, 19, 23
$sql = "UPDATE o_users SET two_fa_enforced = 1 WHERE user_group IN (7, 8) AND two_fa_enforced = 0";

$result = mysqli_query($con, $sql);


if(!$result){
    echo "Error: ".mysqli_error($con);
    exit();
}else{
    echo "Success";
}