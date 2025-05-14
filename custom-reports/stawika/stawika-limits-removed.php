<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

?>


<table class="table table-condensed table-striped" id="example2">
    <thead>
    <tr><th>UID</th>
        <th>Full Name</th>
        <th>Last Limit</th>
        <th>Phone</th>

    </tr>
    </thead>
<?php

echo "<tbody>";
$limits = fetchtable('o_customer_limits',"comments = 'Zeroed automatically by system'","uid","asc","10000000","uid, customer_uid, date(given_date) as given_date");
while($l = mysqli_fetch_array($limits)){
    $uid = $l['uid'];
    $customer_id = $l['customer_uid'];
     $customer = fetchonerow('o_customers',"uid='$customer_id'","full_name, primary_mobile");
     $last_limit = fetchrow('o_customer_limits',"customer_uid='$customer_id' AND status=1 AND comments='Added Manually'","amount");
     $full_name = $customer['full_name'];
     $primary_mobile = $customer['primary_mobile'];

    echo " <tr><th>$uid</th>
        <td>$full_name</td>
        <td>$last_limit</td>
        <td>$primary_mobile</td>
       
    </tr>";

}
echo "</tbody>";
?>
</table>


