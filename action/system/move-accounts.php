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

$user1 = $_POST['user1'];
$user2 = $_POST['user2'];
$group_cat = $_POST['group_cat'];


///////----------Validation
if($user1 < 1){
    echo errormes("First account is required");
    exit();
}
if($user2 < 1){
    echo errormes("Second account is required");
    exit();
}
if($group_cat != 'LO' &&  $group_cat != 'CO'){
    echo errormes("Select Group");
    exit();
}
if($user1 == $user2){
    echo errormes("You have selected the same user");
    exit();
}

if($group_cat == 'LO'){
    $gro = fetchrow('o_users',"uid='$user2'","user_group");
    $group_name = fetchrow('o_user_groups',"uid='$gro'","name");
    if($gro != 7 && $group_name != 'Loan Officer'){
        echo errormes("You have not select an LO to move accounts to");
        exit();
    }
    $lo_pair = fetchrow('o_pairing',"lo='$user2' AND status=1","co");
    if($lo_pair > 0){
        ////-----Change CO also
        $and_current_co = ", current_co='$lo_pair'";
    }
    else{
        $and_current_co = "";
    }

    $up = updatedb('o_loans', "current_lo='$user2' $and_current_co", "current_lo='$user1' AND disbursed=1  AND status!=0");
    ///----Update customer agent going forward
    $upp = updatedb('o_customers',"current_agent='$user2'","current_agent='$user1'");
}
elseif ($group_cat == 'CO'){
    $gro = fetchrow('o_users',"uid='$user2'","user_group");
    $group_name = fetchrow('o_user_groups',"uid='$gro'","name");
    if($gro != 8 && $group_name != 'Collections Officer'){
        echo errormes("You have not selected a CO to move accounts to");
        exit();
    }
    $co_pair = fetchrow('o_pairing',"co='$user2' AND status=1","lo");
    if($co_pair > 0){
        ////-----Change LO also
        $and_current_lo = ", current_lo='$co_pair'";
    }
    else{
        $and_current_lo = "";
    }
    $up = updatedb('o_loans', "current_co='$user2' $and_current_lo", "current_co='$user1' AND disbursed=1  AND status!=0");

}

if($up == 1){
    echo sucmes("Success updating $group_cat");
    $proceed = 1;
}
else{
    echo errormes("Error updating $group_cat $up $upp");
}
/*
$acc_1 = fetchonerow('o_users',"uid='$lo'","user_group, status");
$acc_2 = fetchonerow('o_users',"uid='$co'","user_group, status");

if($acc_2['status'] != 1){
    echo errormes("The second account is not active");
    exit();
}

if($acc_1['user_group'] != $acc_2['user_group']){
    echo errormes("Both accounts need to be in the same group");
    exit();
}

if($acc_2['user_group'] == 7) {
    $up = updatedb('o_loans', "current_lo='$co'", "current_lo='$lo' AND disbursed=1 AND paid=0 AND status!-0");
}
elseif ($acc_2['user_group'] == 8) {
    $up = updatedb('o_loans', "current_co='$co'", "current_co='$lo' AND disbursed=1 AND paid=0 AND status!=0");
}
else{
    echo errormes("User 2 is neither LO or CO");
}

if($up == 1){
    $proceed = 1;
    echo sucmes("Accounts moved successfully");
}
else{
    echo errormes("Error moving accounts");
}
*/
echo sucmes("Proceed $user1 $user2 $group_cat");
?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
            reload();
        },2500);
    }
</script>
