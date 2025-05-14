<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once '../configs/20200902.php';
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$_SESSION['db_name'] = $db_;


/////-------------------------------------Start by marking all loans due yesterday as overdue
$yesterday = datesub($date, 0, 0, 1);
$mark = updatedb('o_loans', "status='7'", "paid=0 AND disbursed=1 AND final_due_date <= '$yesterday' AND status=3 AND product_id=14");

///
/// -----------------------------End




///////-------------------------End of get company details
///-------Lets start here
///------Loop through addons
$ad = fetchonerow('o_addons', "status=1 AND uid = 8", "*");
$uid = $ad['uid'];
$name = $ad['name'];
$from_day = false_zero($ad['from_day']);
$to_day = false_zero($ad['to_day']);
$amount = false_zero($ad['amount']);
$amount_type = $ad['amount_type'];
$loan_stage = $ad['loan_stage'];

$addon_on = $ad['addon_on'];
$all_loans = array();

$loan_total_days_array = array();
$loans = fetchtable('o_loans', "status!=0 AND disbursed=1 AND product_id =14 AND final_due_date='$yesterday' AND  paid = 0", "uid", "asc", "10000000", "uid, given_date, $addon_on");
while ($l = mysqli_fetch_array($loans)) {

    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $days_ago = datediff3($given_date, $date);        ///////---------5
    ////-----Calculate Interests



    $action_amount = $l[$addon_on];
    $addon_on_array[$uid] = $action_amount;

    array_push($all_loans, $uid);

    //echo "$uid, $given_date \n";
}
$all_loans_string = implode(',', $all_loans);

$addon_exists = table_to_array('o_loan_addons', "loan_id in ($all_loans_string) AND status=1 AND addon_id='5'", "1000000", "loan_id");

for ($i = 0; $i < sizeof($all_loans); ++$i) {


    $addon_amount = 1 * ($amount) / 100 * $addon_on_array[$all_loans[$i]];

    if (in_array($all_loans[$i], $addon_exists)) {
    } else {
        // echo $all_loans[$i]."No exist ".$addon_on_array[$all_loans[$i]].", create $addon_amount \n";
        $fds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
        $vals = array($all_loans[$i], "8", round($addon_amount, 0), "$fulldate", "1");
        $save = addtodb('o_loan_addons', $fds, $vals);
        if ($save == 1) {
            echo "loan $all_loans[$i] added addon <br/>";
            recalculate_loan($all_loans[$i], true);
        } else {
            echo "Unable to update " . $all_loans[$i] . " $addon_amount <br/>";
        }
    }
}
