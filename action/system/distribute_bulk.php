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

$where_type = $_POST['where_type'];   /////---------Whether we check LO and CO in the accounts with errors
$user_id = $_POST['user_id'];        /////----------Which user we are checking in the accounts with errors
$branch = $_POST['branch'];          /////----------Which branch we are updating
$branch_enc = encurl($branch);

if($where_type != 'LO' && $where_type != 'CO'){
    die(errormes("Type of user unspecified"));
}
if($branch > 0){}
else{
    die(errormes("Branch not specified"));
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
$agent_pairs = table_to_obj('o_pairing',"branch='$branch' AND status=1","1000","lo","co");
$all_los = table_to_array('o_pairing',"branch='$branch' AND status=1","1000","lo");

$total_los = sizeof($all_los);
$ind = 0;


$all_loans = fetchtable('o_loans',"disbursed=1 AND status!=0 AND current_branch='$branch' $anduser","loan_amount","asc","1000000","uid, customer_id, loan_amount");
while($l = mysqli_fetch_array($all_loans)){
    $uid = $l['uid'];
    $customer = $l['customer_id'];
    $loan_amount = $l['loan_amount'];

   if ($ind > $total_los-1){
        $ind = 0;
    }


    $lo = $all_los[$ind];
    $co = $agent_pairs[$lo];

    $lo_name = $agent_names[$lo];
    $co_name = $agent_names[$co];
   // echo '('.$all_los[$ind].')';

  //  echo "UID: $uid LO: $lo, CO: $co, Amount: $loan_amount ,[$ind]<br/>";

   // echo sucmes("Update $uid with LO $lo, CO $co and customer $customer to agent $lo");
    $update_loans = updatedb("o_loans","current_lo='$lo', current_co='$co'","uid='$uid'");
    if($update_loans == 1){
        $proceed = 1;

        $mess = "LO and CO updated via upload to $lo_name($lo) and $co_name($co) respectively";
        //---Too expensive
        $mass_logging = $mass_logging . ',("o_loans","'.$uid.'","'.$mess.'","'.$fulldate.'","'.$userd['uid'].'","1")';

        $loans_updated+=1;
        ///---- Mass Log
        $update_customers = updatedb("o_customers","current_agent='$lo'","uid='$customer' AND current_agent != '$lo'");
        if($update_customers == 1){
            $mass2 = "Current agent changed to $lo_name($lo)";
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

    $ind+=1;

}

echo sucmes("$loans_updated:Loans Updated. $loans_failed:Loans failed");
echo sucmes("$customers_updated:Customers Updated. $customers_failed:Customers failed");
//echo sucmes("Exit page <a class='btn btn-primary bg-black-gradient' onclick=\"load_std('/extensions/sp-pairing-3.php','#dynamic_load','b=$branch_enc'); hide_div('.feedback_');\"> Exit</a>");

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
