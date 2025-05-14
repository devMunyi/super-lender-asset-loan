<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$customer_id = $_POST['customer_id'] ?? 0;
$primary_mobile = $_POST['primary_mobile'] ?? '';

if ($customer_id == 0) {
    echo "<div class='row'><span class='font-18 font-italic text-black text-mute'>No customer selected</span></div>";
    exit();
} else {
    $customer_id = decurl($customer_id);
}

$o_customer_contacts_ = fetchtable('o_customer_contacts', "customer_id=$customer_id AND status = 1", "uid", "desc", "0,100", "uid ,contact_type, last_update,value ,status ");
$table_content = "<table class=\"table-bordered font-14 table table-hover\">
    <tr>
        <td class=\"text-bold\">Primary Phone</td>
        <td>$primary_mobile</td>
        <th>Last Update</th>
    </tr>";

$contact_type_names = table_to_obj("o_contact_types", "uid > 0", "1000", "uid", "name");
while ($y = mysqli_fetch_array($o_customer_contacts_)) {
    $uid = $y['uid'];
    $contact_type = $y['contact_type'];
    $contact_type_name = $contact_type_names[$contact_type] ?? "";
    $value = $y['value'];
    $status = $y['status'];
    $last_update = $y['last_update'];
    $table_content .= "<tr><td class=\"text-bold\">$contact_type_name</td><td>$value</td><td>$last_update " . fancydate($last_update) . "</td></tr>";
}
$table_content .= "</table>";
echo $table_content;


// include close connection
include_once("../../configs/close_connection.inc");
?>