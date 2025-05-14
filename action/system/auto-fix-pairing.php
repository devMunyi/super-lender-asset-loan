<?php
session_start();
include_once ("../../configs/conn.inc");
include_once ("../../php_functions/functions.php");

$userd = session_details();
if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}


$branch = $_POST['b'];
if($branch > 0) {

    $mass_logging = "";
    $mass_logging2 = "";
    $total_records = 0;
    $total_updated = 0;

$all_branch_los = table_to_array('o_users',"group_id=7 AND status=1 AND branch=$branch","1000","uid");
$all_users = table_to_obj('o_users',"uid>0","1000","uid","name");
$pair = table_to_obj('o_pairing',"branch='$branch' AND status=1","1000","lo",'co');
$pair_reversed = table_to_obj('o_pairing',"branch='$branch' AND status=1","1000","co",'lo');
////////------Loans with Right LO but wrong CO, because CO is in call center
$all_loans = fetchtable('o_loans',"current_branch='$branch' AND disbursed=1 AND status!=0","uid","asc","1000000","uid, current_lo, current_co, current_agent, allocation");
while($l = mysqli_fetch_array($all_loans)){
    $cuid = $l['uid'];
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    $current_agent = $l['current_agent'];

    $old_co_name = $all_users[$current_co];
    $old_lo_name = $all_users[$current_lo];
    if($pair[$current_lo] > 0 && $pair[$current_lo] != $current_co){
        $new_co = $pair[$current_lo];
        $new_co_name = $all_users[$new_co];

        ///----There is a  match, make the match LO
        $mess = "CO updated to $new_co_name ($new_co) from $old_co_name ($current_co)  respectively";
        $mass_logging = $mass_logging . ',("o_loans","'.$cuid.'","'.$mess.'","'.$fulldate.'","'.$userd['uid'].'","1")';

        $updated = updatedb('o_loans',"current_co='$new_co'","uid='$cuid'");
        $total_records+=1;
        $total_updated+=$updated;

        //echo sucmes("Loan $cuid , LO: $current_lo, $current_co NEW CO $new_co<br/>");
    }
    else{
        if($pair_reversed[$current_co] > 0 && $pair_reversed[$current_co] != $current_lo){
            ////-----The loan has a CO who is matched, find them an FIX the LO
            $new_lo = $pair_reversed[$current_co];
            $new_lo_name = $all_users[$new_lo];
            $mess = "LO updated to $new_lo_name ($new_lo) from $old_lo_name ($current_lo)  respectively";
            $mass_logging = $mass_logging . ',("o_loans","'.$cuid.'","'.$mess.'","'.$fulldate.'","'.$userd['uid'].'","1")';

            $updated = updatedb('o_loans',"current_lo='$new_lo'","uid='$cuid'");
            $total_records+=1;
            $total_updated+=$updated;

        }
        /*
        $mess = "CO removed and set to 0 from $old_co_name ($current_co)  respectively";
        $mass_logging = $mass_logging . ',("o_loans","'.$cuid.'","'.$mess.'","'.$fulldate.'","'.$userd['uid'].'","1")';
        $updated = updatedb('o_loans',"current_co='0'","uid='$cuid'");
        $total_records+=1;
        $total_updated+=$updated;
        */
       // echo errormes("Loan $cuid , Make 0 <br/>");
    }
    /////----------------Swapped pairs
    if($pair[$current_co] > 0 && $current_co > 0){
        ///-------------The person in the CO docket is an LO
        $thelo = $current_co;          $thelo_name = $all_users[$thelo];
        $theco = $pair[$current_co];   $theco_name = $all_users[$theco];
        $old_agent_name  = $theco_name[$current_co];

       // echo errormes("here");

        $mess = "LO/CO swap complete, updated to LO: $thelo_name($thelo), CO: $theco_name($theco) from CO $old_agent_name($current_co)   respectively";
        $mass_logging = $mass_logging . ',("o_loans","'.$cuid.'","'.$mess.'","'.$fulldate.'","'.$userd['uid'].'","1")';

        $updated = updatedb('o_loans',"current_lo='$thelo', current_co='$theco'","uid='$cuid'");
        $total_records+=1;
        $total_updated+=$updated;

    }
    elseif ($pair_reversed[$current_lo] > 0 && $current_lo){
        ////-------The person in the LO docket is a CO
        $theco = $current_lo;
        $thelo = $pair_reversed[$current_lo];

        $thelo_name = $all_users[$thelo];
        $theco_name = $all_users[$theco];
        $old_agent_name  = $theco_name[$current_lo];

        $mess = "LO/CO swap complete, updated to LO: $thelo_name($thelo), CO: $theco_name($theco) from LO $old_agent_name($current_co)   respectively";
        $mass_logging = $mass_logging . ',("o_loans","'.$cuid.'","'.$mess.'","'.$fulldate.'","'.$userd['uid'].'","1")';

        $updated = updatedb('o_loans',"current_lo='$thelo', current_co='$theco'","uid='$cuid'");
        $total_records+=1;
        $total_updated+=$updated;

    }


    //////////////////


}

}
else{
    echo errormes("Please select branch first");
}

echo sucmes("$total_updated/$total_records updated");


$fds = array('tbl','fld','event_details','event_date','event_by','status');
$log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));
$log2 = addtodbmulti('o_events', $fds, ltrim($mass_logging2, ","));
$proceed = 1;
?>
<script>
    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
            load_std('/extensions/sp-pairing-3.php','#dynamic_load','b=<?php echo encurl($branch); ?>');

        },1000);
    }
</script>


