<?php
session_start();
include_once '../../configs/20200902.php';
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

ini_set('display_errors', 1);
error_reporting(E_ALL);


$branch = $_GET['branch'];
if($branch > 0) {
    $all_los = array();
    $all_cos = array();

    $all_unpaired_los = array();
    $all_unpaired_cos = array();

///---All branch LO's and CO's
    $all_bdos = fetchtable('o_users', "user_group in (7,8) AND status=1 AND branch='$branch'", "uid", "asc", "100000", "uid, user_group");
    while ($bd = mysqli_fetch_array($all_bdos)) {

        $bid = $bd['uid'];
        $bgroup = $bd['user_group'];
        echo "Branch Members: $bid($bgroup)<br/>";
        if ($bgroup == 7) {
            array_push($all_los, $bid);
            array_push($all_unpaired_los, $bid);
        }
        if ($bgroup == 8) {
            array_push($all_cos, $bid);
            array_push($all_unpaired_cos, $bid);
        }


    }

///----ALl branch pairings
    $correct_pairs_lo = array();
    $correct_pairs_co = array();
    $all_pairing = fetchtable('o_pairing', "branch='$branch' AND status=1", "uid", "asc", "100000", "uid, lo, co");
    while ($ap = mysqli_fetch_array($all_pairing)) {

        $pid = $ap['uid'];
        $lo = $ap['lo'];
        $co = $ap['co'];
        if ((in_array($lo, $all_los) == 1) && (in_array($co, $all_cos) == 1)) {
            ///----Correct pair
            echo "Correct $lo, $co <br/>";
            array_push($correct_pairs_lo, $lo);
            array_push($correct_pairs_co, $co);

            $lindex = array_search($lo, $all_unpaired_los);
            if($lindex !== false){
                unset($all_unpaired_los[$lindex]);  // $arr = ['b', 'c']
            }
            $cindex = array_search($co, $all_unpaired_cos);
            if($cindex !== false){
                unset($all_unpaired_cos[$cindex]);  // $arr = ['b', 'c']
            }

        } else {
            ///-----wrong pair, break
            echo "break $lo, $co <br/>";
        }

    }

    sort($all_unpaired_los);
    sort($all_unpaired_cos);

    ////------Get incorrect pairs

    ////---


    for($i=0; $i<sizeof($all_unpaired_los); ++$i){

        $lo_id = $all_unpaired_los[$i];
        $co_counterpart = $all_unpaired_cos[$i];
        if($co_counterpart > 0){
            ///---
            echo "Pair $lo_id, $co_counterpart <br/>";
        }
        else{
            ///---
            echo "No counterpart $lo_id<br/>";
        }

    }


}
else{
    echo "specify branch";
}




