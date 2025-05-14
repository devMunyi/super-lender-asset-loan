<?php

// files includes
include_once ("../../../configs/conn.inc");
include_once ("../../../php_functions/functions.php");

$loans_count = [];
$loans = fetchtable2('o_loans', 'uid > 0 AND status > 0', 'uid', 'ASC');

while ($l = mysqli_fetch_assoc($loans)) {
    $customer_id = $l['customer_id'];
    $loans_count = obj_add($loans_count, $customer_id, 1);
}

$updated = 0;
$skipped = 0;
$custs = fetchtable2('o_customers', 'uid > 0 AND status > 0', 'uid', 'ASC');
while ($c = mysqli_fetch_assoc($custs)) {
    $uid = $c['uid'];
    $total_loans = intval($loans_count[$uid]) ?? 0;

    if($total_loans > 0){
        $update = updatedb('o_customers', "total_loans = $total_loans", "uid = $uid");
        echo 'Entry UID: ' . $uid . ' TABLE UPDATE RESPONSE: ' . $update . '<br>';
        if ($update == 1) {
            $updated += 1;
        } else {
            $skipped += 1;
        }
    }
}

echo "UPDATED CUSTOMERS: $updated <br>";
echo "SKIPPED CUSTOMERS:  $skipped <br>";
