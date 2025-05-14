<?php
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$yesterday = datesub($date, 0, 0, 1);
$mark = updatedb('o_loans', "status='7'", "paid=0 AND disbursed=1 AND final_due_date <= '$yesterday' AND status=3");


// close connection
include_once("../configs/close_connection.inc");
