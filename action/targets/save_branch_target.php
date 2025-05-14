<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}
/////---------End of session check

$branch = $_POST['branch'];
$target = $_POST['amount'];
$target_date = $_POST['target_date'];
$typ = $_POST['typ'];

//die(errormes($target_date));

$target_start_date = first_date_of_month($target_date);
$target_end_date = last_date_of_month($target_date);
$andbranch = "AND group_id='$branch'";

if($typ == 'ONE'){
    ///---One branch
    $andbranch = "AND group_id='$branch'";
}
else{
    ///----All
    $andbranch = "";
}


///////----------Validation
if($branch < 1 && $typ == 'ONE'){
    echo errormes("Branch is required");
    exit();
}

$exists = checkrowexists('o_targets',"target_type='DISBURSEMENTS' AND target_group='BRANCH' $andbranch AND starting_date = '$target_start_date' AND ending_date = '$target_end_date'");
//echo "[$exists]";
if($exists == 1){
    $proceed = updatedb('o_targets',"amount='$target', amount_type='FIXED',  status=1","target_type='DISBURSEMENTS' AND target_group='BRANCH' $andbranch AND starting_date = '$target_start_date' AND ending_date = '$target_end_date'");
    if($proceed == 1){
        echo sucmes("Target Updated successfully");
    }
    else{
        echo errormes("Error Updating Target");
    }
}
else{
    $fds = array('target_type', 'target_group', 'starting_date','ending_date','group_id', 'amount', 'amount_type', 'working_days', 'status');
    if($typ == 'ONE') {

        $vals = array("DISBURSEMENTS", "BRANCH", "$target_start_date","$target_end_date","$branch", "$target", "FIXED", "20", "1");
        $proceed = addtodb('o_targets', $fds, $vals);
        if ($proceed == 1) {
            echo sucmes("Target Added successfully");
        } else {
            echo errormes("Error Adding Target");
        }
    }
    else{
        $branches = fetchtable('o_branches',"status=1 AND uid > 0","uid","asc","1000","uid, name");
        $multi_st = "";
        $total_branches = 0;
        while($br = mysqli_fetch_array($branches)){
            $buid = $br['uid'];
            $total_branches+=1;
            $multi_st = "('DISBURSEMENTS', 'BRANCH', '$target_start_date','$target_end_date','$buid', $target, 'FIXED', '20', '1'),".$multi_st;
        }
        if($total_branches > 0) {
            $mass = addtodbmulti('o_targets', $fds, rtrim($multi_st, ","));
            if($mass == 1){
                echo sucmes("Target Added successfully for $total_branches branches");
            }
            else{
                echo errormes("Error Adding Target for $total_branches branches");
            }
        }
        else{
            echo errormes("No branches found");
        }



    }
}




?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
            reload();
        },1000);
    }
</script>
