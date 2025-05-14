<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$loanAddons = fetchtable('o_loan_addons', "status=1 AND addon_amount = 0.00 AND DATE(added_date) = '2024-11-13'", "uid", "asc", "10000000", "loan_id, addon_id");

$count = 0;
while ($loanAddon = mysqli_fetch_array($loanAddons)) {
    $loan_id = $loanAddon['loan_id'];
    $addon_id = $loanAddon['addon_id'];

    apply_loan_addon_to_Loan($addon_id, $loan_id, true);
    $count++;
}

echo "Fixed $count loan addons";


// close the connection
mysqli_close($con);