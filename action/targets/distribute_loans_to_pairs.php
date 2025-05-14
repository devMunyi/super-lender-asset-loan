<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$branch = $_POST['branch'];
if($branch > 0){
    $all_unallocated_loans = table_to_array_order('o_loans',"current_branch='$branch' AND status!=0 AND (current_lo<1 OR current_lo is null)","loan_balance","desc","1000000","uid");
    $all_bdo = table_to_array('o_pairing',"status=1","1000","lo");
    $bdo_branches = table_to_obj('o_users',"status!=0 AND branch='$branch'","100000","uid","branch");
    $username = table_to_obj('o_users',"uid > 0","100000","uid","name");
    $current_branch_lo = array();
    for($b = 0; $b < sizeof($all_bdo); ++$b){

        if($bdo_branches[$all_bdo[$b]] > 0){
            ///----Found
            array_push($current_branch_lo, $all_bdo[$b]);
        }

    }


    $total_bdos = sizeof($current_branch_lo);


    if($total_bdos == 0){
        die(errormes("No pairing found"));
        exit();
    }
    $pair_ = table_to_obj('o_pairing',"status=1","1000","lo","co");
    $bdo_line = 0;
    $new_lo = array();
    for($i=0; $i< sizeof($all_unallocated_loans); ++$i){
        $new_lo[$all_unallocated_loans[$i]] = $current_branch_lo[$bdo_line];
        $bdo_line = $bdo_line + 1;
        if($bdo_line >= $total_bdos){
            $bdo_line = 0;
        }

    }
   $updated_loans = 0;
    $total_loans = sizeof($all_unallocated_loans);
   foreach ($new_lo as $loan_id => $lo ){
       $co = $pair_[$lo];
       $updatebdo = updatedb('o_loans',"current_lo='$lo', current_co='$co'","uid='$loan_id'");
       if($updatebdo == 1) {
           $updated_loans = $updated_loans + 1;
           store_event('o_loans', $loan_id, "LOAN BDO Updated to LO:".$username[$lo]." AND CO: ". $username[$co]." on $fulldate by manual action");
       }

   }

   echo sucmes(" $updated_loans/$total_loans Loans were allocated equally to the pairs");

}
else{
    echo errormes("No branch selected");
}
