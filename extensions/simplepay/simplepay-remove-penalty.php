<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");



$loans = fetchtable('o_loans',"disbursed=1 AND final_due_date = '2022-10-24' AND status=3","uid","asc","100000","uid");
while($l = mysqli_fetch_array($loans)){
    $loan = $l['uid'];
    echo $loan.'<br/>';
 echo  remove_addon(4, $loan, 1);
}


include_once("../configs/close_connection.inc");
?>