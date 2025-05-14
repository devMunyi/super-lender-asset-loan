<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// files includes
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

$sql = "SELECT 
l.uid, l.loan_amount
FROM
o_loans l
    LEFT JOIN
o_customers c ON c.uid = l.customer_id
    LEFT JOIN
o_loan_addons la ON la.loan_id = l.uid
    LEFT JOIN
o_branches b ON b.uid = l.current_branch
    LEFT JOIN
o_loan_statuses ls ON ls.uid = l.status
    LEFT JOIN
o_loan_products lp ON lp.uid = l.product_id
WHERE
product_id = 1 AND disbursed = 1
    AND l.uid NOT IN (SELECT 
        loan_id
    FROM
        o_loan_addons
    WHERE
        addon_id = 15 AND status = 1)
        AND l.uid IN (SELECT 
        loan_id
    FROM
        o_loan_addons
    WHERE
        addon_id = 14 AND status = 1)  
AND l.current_branch != 1
AND l.given_date >= '2025-02-04'
-- AND DATEDIFF('$date', l.given_date) > 14 AND l.given_date >= '2025-02-04'
AND l.status NOT IN (5 , 10, 11)
group by l.uid
ORDER BY l.uid DESC;";


$loans = mysqli_query($con, $sql);
$all_loans = array();
$addon_on_array = array();
$addonId = 15;

$total_to_save = mysqli_num_rows($loans);
$total_saved = 0;
$skipped = 0;
while ($l = mysqli_fetch_array($loans)) {
    $uid = $l['uid'];
    $action_amount = $l['loan_amount'];
    $addon_on_array[$uid] = $action_amount;

    array_push($all_loans, $uid);
}
$all_loans_string = implode(',', $all_loans);

$addon_exists = table_to_array('o_loan_addons', "loan_id in ($all_loans_string) AND status=1 AND addon_id='$addonId'", "1000000", "loan_id");

$amount_type = 'PERCENTAGE';
$mult_factor = 1;
$amountx = 10;
for ($i = 0; $i < sizeof($all_loans); ++$i) {

    if ($amount_type == 'PERCENTAGE') {
        $addon_amount = $mult_factor * ($amountx) / 100 * $addon_on_array[$all_loans[$i]];
    }

    if (in_array($all_loans[$i], $addon_exists)) {
        $skipped = $skipped + 1;
        continue;
    } else {
        $fds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
        $vals = array($all_loans[$i], $addonId, $addon_amount, "$fulldate", "1");
        $save = addtodb('o_loan_addons', $fds, $vals);
        $total_saved = $total_saved + $save;
        $total_to_save = $total_to_save + 1;
        if ($save == 1) {
            recalculate_loan($all_loans[$i], true);
        }
    }
}

// echo "$total_saved/$total_to_save Addons Created<br>";
// echo "skipped: $skipped<br>";
// echo "all_loans: $all_loans_string<br>";
    // echo "$total_updated/$total_to_update Addons Updated \n";
