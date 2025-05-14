<?php
session_start();


include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$ago_7 = datesub($date, 0, 0, 30);


//////----------------------Adds
?>

<?php





    $pays = fetchtable('o_incoming_payments',"payment_date >= '$ago_7'  AND status !=0","uid","asc","100000","uid, mobile_number");
    while($p = mysqli_fetch_array($pays)){

        $uid = $p['uid'];
        $mobile_number = $p['mobile_number'];
        if(validate_phone($mobile_number) == 0){
            $valid = make_phone_valid($mobile_number);
            if($mobile_number > 100){
        echo "$uid, $mobile_number, $valid<br/>";
        echo updatedb('o_incoming_payments',"mobile_number='$valid'","uid='$uid'");
            }
        }
    }


include_once("../configs/close_connection.inc");
 
    ?>
