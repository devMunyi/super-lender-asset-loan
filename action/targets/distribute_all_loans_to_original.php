<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
//$pair_ = table_to_obj('o_pairing',"status=1","1000","lo","co");


$branch = $_POST['branch'];
if($branch > 0){
    //////////-------------We un-allocate all loans first
    $custs = fetchtable('o_customers',"branch='$branch'","uid","asc","100000","uid, added_by");
    echo sucmes(" Total customers found: ".mysqli_num_rows($custs));
    while($c = mysqli_fetch_array($custs)){
        $uid = $c['uid'];
        $added_by = $c['added_by'];
        if($added_by > 0){
            $lo = $added_by;
            $update_lo = updatedb('o_loans',"current_lo='$lo'","customer_id='$uid'");
            $total_updates = $total_updates + $update_lo;
            $total_found = $total_found + 1;
        }

    }
    echo sucmes("Complete: Total assignments: $total_updates/$total_found");


}
else{
    echo errormes("No branch selected");
}
