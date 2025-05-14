<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$customer =  intval($_POST['customer']);
$action = $_POST['action'];


if ($customer > 0) {
    $o_customer_guarantors_ = fetchtable('o_customer_guarantors', "customer_id = " . decurl($customer) . " AND status = 1", "uid", "asc", "0,100", "uid, guarantor_name, national_id, mobile_no, physical_address, amount_guaranteed, relationship, status");
    if ((mysqli_num_rows($o_customer_guarantors_)) > 0) {

        while ($t = mysqli_fetch_array($o_customer_guarantors_)) {
            $uid = $t['uid'];
            $guarantor_name = $t['guarantor_name'];
            $guarantor_relationship = $t['relationship'];
            $relationship_name = fetchrow('o_customer_guarantor_relationships', "uid = $guarantor_relationship", 'name');

            if ($action == 'EDIT') {
                $act = "<a href=\"customers?customer-add-edit=$customer&guarantors=" . encurl($uid) . "\" title='Edit' class='pointer text-blue'><i class='fa fa-edit'></i></a> " . "<a onclick=\"delete_guarantor('" . encurl($uid) . "')\" title='Delete' class='pointer text-red'><i class='fa fa-trash'></i></a>" . " <a onclick=\"view_guarantor('" . encurl($uid) . "')\" title='View' class='pointer text-green'><i class='fa fa-eye'></i></a>";
            } else {
                $act = "--";
            }
            $guarantor_row .= "<tr id='guarantor" . encurl($uid) . "'><td>$guarantor_name</td><td>$relationship_name</td><td>$act</td></tr>";
        }
    } else {
        $guarantor_row = "<tr><td colspan='3' class='font-italic'>No Guarantors </td></tr>";
    }
    echo "<table class='table table-condensed table-striped table-bordered' style='width: 99%;'>
<tr><th>Name</th><th>Relationship</th><th>_Action_</th></tr>$guarantor_row<tr><th>Name</th><th>Relationship</th><th>Action</th></tr></table>";
} else {
    echo errormes("Customer not selected");
}

mysqli_close($con);