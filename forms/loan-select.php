<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
$pid = $_GET['pid'];
?>

<div class="row">
    <div class="col-md-10">
        <table class="table table-bordered table-condensed">
            <?php
            $pay = fetchonerow('o_incoming_payments',"uid='$pid'","mobile_number, loan_id, comments");
            $mobile_number = $pay['mobile_number'];
            $loan_id = $pay['loan_id'];
            $comments = $pay['comments'];

            ////----Find customers who have paid with this number but it didn't go to loan
            ////----Find customers whose phone number matches the concealed number
            ////----Find customers whose phone number is an alternative number
            ////----Find customers whose phone number has paid before

            ?>


        </table>
    </div>

</div>

