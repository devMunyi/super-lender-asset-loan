<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$guarantor_id = intval($_POST['guarantor_id']);

echo "<table class='table table-bordered'>";
if($guarantor_id > 0){
    $l = fetchonerow('o_customer_guarantors', "uid=".decurl($guarantor_id),"*");
    $added_date = $l['added_date'];
    $guarantor_name = $l['guarantor_name'];
    $national_id = $l['national_id'];
    $mobile_no = $l['mobile_no'];
    $physical_address = $l['physical_address'];
    $amount_guaranteed = $l['amount_guaranteed'];
    $relationship = $l['relationship'];  $relationship_name = fetchrow('o_customer_guarantor_relationships',"uid='$relationship'","name");

    echo "<tr><td>Name</td><td class='font-bold'>$guarantor_name</td></tr>";
    echo "<tr><td>ID Number</td><td class='font-bold'>$national_id</td></tr>";
    echo "<tr><td>Mobile No.</td><td class='font-bold'>$mobile_no</td></tr>";
    echo "<tr><td>Physical Address</td><td class='font-bold'>$physical_address</td></tr>";
    echo "<tr><td>Amount Guaranteed</td><td class='font-bold'>$amount_guaranteed</td></tr>";
    echo "<tr><td>Relationship</td><td class='font-bold'>$relationship_name</td></tr>";
}
else{
    exit(errormes("Guarantor invalid"));
}
echo "</table>";