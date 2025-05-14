<?php

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$customer_id = $_POST['customer_id'] ?? 0;

if ($customer_id == 0) {
    echo "<div class='row'><span class='font-18 font-italic text-black text-mute'>Customer id was not parsed!</span></div>";
    exit();
} else {
    $customer_id = decurl($customer_id);
}

$view_events = permission($userd['uid'], 'o_events', "0", "read_");
if ($view_events == 1) {
    $o_events_ = fetchtable('o_events', "tbl='o_customers' AND fld='$customer_id' AND status = 1", "uid", "asc", "100", "uid ,event_details ,event_date ,event_by ,status");

    if(mysqli_num_rows($o_events_) > 0){
        while ($d = mysqli_fetch_array($o_events_)) {
            $uid = $d['uid'];
            $event_details = $d['event_details'];
            $event_date = $d['event_date'];
            $event_by = $d['event_by'];
            $status = $d['status'];
    
            echo " <tr><td>$event_details</td><td>$event_date</td> </tr>";
        }
    } else {
        echo "<tr><td colspan='2'><i>No Records Found</i></td></tr>";
    }
}else {
    echo "<div class='row'><span class='font-18 font-italic text-black text-mute'>You do not have permission to view events</span></div>";
    exit();

}


// include close connection
include_once("../../configs/close_connection.inc");
?>