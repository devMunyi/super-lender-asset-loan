<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");


$addon_amount = fetchrow('o_addons',"uid=1","amount");

$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status=3","uid","asc","1000000","uid, loan_amount, given_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $loan_amount = $l['loan_amount'];
    $given_date = $l['given_date'];
    $ago = intval(datediff($given_date, $date));



    $numMultiples = countMultiplesOfSeven($ago);

    if($ago <= 7){
        $numMultiples = 1;
    }
    elseif ($ago > 7 && $ago <= 14){
        $numMultiples = 2;
    }
    elseif ($ago > 14 && $ago <= 21){
        $numMultiples = 3;
    }
    elseif ($ago > 21){
        $numMultiples = 4;
    }

    $expected_interest = $loan_amount * ($addon_amount/100);
    if($numMultiples == 2){
        $expected_interest = $loan_amount * (0.12);
    }
    elseif ($numMultiples == 3){
        $expected_interest = $loan_amount * (0.15);
    }
    elseif ($numMultiples == 4){
        $expected_interest = $loan_amount * (0.19);
    }

    $current_interest = fetchrow('o_loan_addons',"loan_id='$uid' AND status=1 AND addon_id=1","addon_amount");

    echo   "UID: $uid, Date:$given_date, Ago: $ago, Multiples: $numMultiples , Update Interest Loan:$uid, AMt: $loan_amount from $current_interest to $expected_interest <br/>";


    if(round($expected_interest) > round($current_interest+5)){

        ///-----Update interest
     echo   "UID: $uid, Date:$given_date, Ago: $ago, Multiples: $numMultiples , Update Interest Loan:$uid, AMt: $loan_amount from $current_interest to $expected_interest <br/>";
        $update_interest = updatedb('o_loan_addons',"addon_amount='$expected_interest', added_date='$fulldate'","loan_id='$uid' AND status=1 AND addon_id=1");
        if($update_interest == 1){
            $event = "Loan interest updated to $expected_interest because loan is $numMultiples weeks old  by system cron process";
            store_event('o_loans', $uid,"$event");
            recalculate_loan($uid);
        }


    }


   // echo "UID: $uid, Date:$given_date, Ago: $ago, Multiples: $numMultiples <br/>";


}

function countMultiplesOfSeven(int $x): int
{
    if ($x < 0) {
        return 0;
    }
    // Integer division by 7 gives the largest multiple of 7 less than or equal to $x
    return (int)floor($x / 7);
}
include_once("../configs/close_connection.inc");