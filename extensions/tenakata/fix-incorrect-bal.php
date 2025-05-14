<?php 
include_once ("../configs/20200902.php");
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$numbers = [
    24542, 27087, 40032, 41489, 41918, 45321, 45591, 48519, 49752, 49759,
    50117, 52310, 53374, 53466, 55197, 55791, 55890, 56106, 57548, 57604,
    58568, 58709, 60022, 60656, 61909, 62158, 62400, 63228, 63555, 65379,
    66170, 66703, 67024, 67188, 67659, 68724, 69022, 69364, 69739, 71170,
    71607, 76427
];



$i = 1;
foreach ($numbers as $loan_id) {
    echo "$i) Recalculating $loan_id <br/>";
    recalculate_loan($loan_id, true);
    $i++;
}
?>
