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

$relationship_names = table_to_obj("o_customer_guarantor_relationships", "uid > 0", "1000", "uid", "name");
$o_customer_guarantor_ = fetchtable('o_customer_guarantors', "status=1 AND customer_id=$customer_id", "uid", "desc", "0,10", "uid, guarantor_name, national_id, mobile_no, amount_guaranteed, relationship, status ");
if (mysqli_num_rows($o_customer_guarantor_) > 0) {
    while ($g = mysqli_fetch_array($o_customer_guarantor_)) {
        $uid = $g['uid'];
        $guarantor_name = $g['guarantor_name'];
        $national_id = $g['national_id'];
        $mobile_no = $g['mobile_no'];
        $amount_guaranteed = $g['amount_guaranteed'];
        $email_address = $g['email_address'];
        $relationship = $g['relationship'];
        $relationship_name = $relationship_names[$relationship] ?? "";
        $status = $g['status'];
        echo "  <tr><td>$guarantor_name</td><td>$national_id </td><td>$mobile_no</td><td>$amount_guaranteed</td><td>$relationship_name</td> </tr>";
    }
} else {
    echo "<tr><td colspan='8'><i>No Records Found</i></td></tr>";
}

// include close connection
include_once("../../configs/close_connection.inc");
?>