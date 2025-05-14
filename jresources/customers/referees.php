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

$o_customer_referees_ = fetchtable('o_customer_referees', "status=1 AND customer_id=$customer_id", "uid", "desc", "0,10", "uid ,referee_name ,id_no ,mobile_no ,physical_address ,email_address ,relationship ,status ");
$relationship_names =  table_to_obj("o_customer_referee_relationships", "uid > 0", "1000", "uid", "name");
if (mysqli_num_rows($o_customer_referees_) > 0) {
    while ($y = mysqli_fetch_array($o_customer_referees_)) {
        $uid = $y['uid'];
        $referee_name = $y['referee_name'];
        $id_no = $y['id_no'];
        $mobile_no = $y['mobile_no'];
        $physical_address = $y['physical_address'];
        $email_address = $y['email_address'];
        $relationship = $y['relationship'];
        $relationship_name = $relationship_names[$relationship] ?? "";
        $status = $y['status'];
        echo "  <tr><td>$referee_name</td><td style='display: none;'>$id_no </td><td>$mobile_no</td><td style='display: none;'>$email_address</td><td>$physical_address</td><td>$relationship_name</td> </tr>";
    }
} else {
    echo "<tr><td colspan='6'><i>No Records Found</i></td></tr>";
}


// include close connection
include_once("../../configs/close_connection.inc");
?>