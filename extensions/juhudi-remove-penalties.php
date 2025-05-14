<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");


$due_loans = fetchtable2('o_loans', "disbursed = 1 AND status = 7", "uid", "ASC", "uid");
$loan_ids_array = [];
while ($dl = mysqli_fetch_assoc($due_loans)) {
    $loan_ids_array[] = $dl['uid'];
}


$loan_id_list = implode(',', $loan_ids_array);
$loan_addons = fetchtable2('o_loan_addons', "loan_id IN ($loan_id_list) AND status != 0 AND addon_id = 5", "uid", "ASC", "uid, addon_id, loan_id");


$removed = $skipped = 0;
while ($ld = mysqli_fetch_assoc($loan_addons)) {
    $addon_id = $ld["addon_id"];
    $loan_id = $ld["loan_id"];
    $uid = $ld["uid"];


    $remove = updatedb('o_loan_addons', "status=0", "loan_id = $loan_id AND addon_id = $addon_id");
    if ($remove == 1) {
        recalculate_loan($loan_id, true);

        ///-----Save event
        echo "SOFT DELETED UID $uid with addon_id $addon_id and loan_id $loan_id <br>";
        $removed += 1;
    } else {
        echo "SKIPPED UID $uid with addon_id $addon_id and loan_id $loan_id <br>";
        $skipped += 1;
    }
}


echo "SOFT DELETED ENTRIES $removed <br>";
echo "SKIPPED ENTRIES $skipped <br>";

include_once("../configs/close_connection.inc");