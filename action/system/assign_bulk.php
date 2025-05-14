<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}
/////---------End of session check

$lo = $_POST['lo'];          /////------The LO in the pair
$co = $_POST['co'];          /////-------The CO in the pair
$where_type = $_POST['where_type'];   /////---------Whether we check LO and CO in the accounts with errors
$user_id = $_POST['user_id'];        /////----------Which user we are checking in the accounts with errors
$branch = $_POST['branch'];          /////----------Which branch we are updating
$branch_enc = encurl($branch);
///
if($lo > 0){}
else{
    die(errormes("Invalid LO"));
}
if($co > 0){}
else{
    die(errormes("Invalid CO"));
}
if($where_type != 'LO' && $where_type != 'CO'){
    die(errormes("Type of user unspecified"));
}
if($branch > 0){}
else{
    die(errormes("Branch not specified"));
}

$pair_ok = fetchrow('o_pairing',"lo='$lo' AND co='$co' AND branch='$branch' AND status=1","uid");
if($pair_ok > 0){}
else{
    echo errormes("The pair is invalid");
    die();
}
$anduser = 0;
if($where_type == 'LO'){
    $anduser = " AND current_lo='$user_id'";
}
elseif ($where_type == 'CO'){
    $anduser = " AND current_co ='$user_id'";
}
else{
    die("An error occurred");
}

$loans_updated = 0;
$loans_failed  = 0;
$customers_updated = 0;
$customers_failed = 0;

$mass_logging = "";
$mass_logging2 = "";

$agent_names = table_to_obj('o_users',"uid > 0","10000","uid","name");
$lo_name = $agent_names[$lo];
$co_name = $agent_names[$co];

$all_loans = fetchtable('o_loans',"disbursed=1 AND status!=0 AND current_branch='$branch' $anduser","uid","asc","1000000","uid, customer_id");
while($l = mysqli_fetch_array($all_loans)){
    $uid = $l['uid'];
    $customer = $l['customer_id'];

   // echo sucmes("Update $uid with LO $lo, CO $co and customer $customer to agent $lo");
    $update_loans = updatedb("o_loans","current_lo='$lo', current_co='$co'","uid='$uid'");
    if($update_loans == 1){
        $proceed = 1;

        $mess = "LO and CO updated via Autofix to $lo_name($lo) and $co_name($co) respectively";
        //---Too expensive
        $mass_logging = $mass_logging . ',("o_loans","'.$uid.'","'.$mess.'","'.$fulldate.'","'.$userd['uid'].'","1")';

        $loans_updated+=1;
        ///---- Mass Log
        $update_customers = updatedb("o_customers","current_agent='$lo'","uid='$customer' AND current_agent != '$lo'");
        if($update_customers == 1){
            $mass2 = "Current agent changed to $lo_name($lo) by autofix";
            $mass_logging2 = $mass_logging2 . ',("o_customers","'.$customer.'","'.$mass2.'","'.$fulldate.'","'.$userd['uid'].'","1")';

            $customers_updated+=1;
            ///----Mass Log
        }
        else{
            $customers_failed+=1;
        }
    }
    else{
        $loans_failed+=1;
    }
}

echo sucmes("$loans_updated:Loans Updated. $loans_failed:Loans failed");
echo sucmes("$customers_updated:Customers Updated. $customers_failed:Customers failed");
echo sucmes("Exit page <a class='btn btn-primary bg-black-gradient' onclick=\"load_std('/extensions/sp-pairing-3.php','#dynamic_load','b=$branch_enc'); hide_div('.feedback_');\"> Exit</a>");

$fds = array('tbl','fld','event_details','event_date','event_by','status');
$log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));
$log2 = addtodbmulti('o_events', $fds, ltrim($mass_logging2, ","));




?>

<script>
    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
            modal_hide();
            load_std('/extensions/sp-pairing-3.php','#dynamic_load','b=<?php echo $branch_enc; ?>');
            hide_div('.feedback_');
        },1000);
    }
</script>
