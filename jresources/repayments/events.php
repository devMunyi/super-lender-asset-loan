<?php

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$payment_id = $_POST['payment_id'] ?? 0;

if ($payment_id == 0) {
    echo "<div class='row'><span class='font-18 font-italic text-black text-mute'>Payment id was not parsed!</span></div>";
    exit();
} else {
    $payment_id = decurl($payment_id);
}

$view_events = permission($userd['uid'], 'o_events', "0", "read_");
if ($view_events == 1) {

    $o_events_ = fetchtable('o_events', "tbl='o_incoming_payments' AND fld=$payment_id AND status = 1", "uid", "asc", "100", "uid, event_details, event_date ");
    if (mysqli_num_rows($o_events_) > 0) {
        while ($k = mysqli_fetch_array($o_events_)) {
            $uid = $k['uid'];
            $event_details = $k['event_details'];
            $event_date = $k['event_date'];
            echo "<tr><td><span class='font-16 text-bold'>$event_date</span> " . fancydate($event_date) . "</td><td>$event_details</td><td>$uid</td> </tr>";
        }
    } else {
        echo "<tr><td colspan='3'> <i>No Records Found</i></td></tr>";
    }
}


// include close connection
include_once("../../configs/close_connection.inc");