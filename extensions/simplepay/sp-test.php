<?php
session_start();
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$limit = $_GET['limit'];
//echo total_customer_loans(15769);

$loans = fetchtable('o_loans',"enc_phone is null","uid","desc","$limit","uid, account_number, enc_phone");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $account_number = $l['account_number'];
    $enc_phone = hash('sha256', $account_number);

    $up = updatedb('o_loans',"enc_phone='$enc_phone'","uid='$uid'");
    echo $up;
}


$customers = fetchtable('o_customers',"enc_phone is null","uid","asc","$limit","uid, primary_mobile, enc_phone");
while($c = mysqli_fetch_array($customers)){
    $cuid = $c['uid'];
    $primary_mobile = $c['primary_mobile'];

    $enc_phone = hash('sha256', $primary_mobile);

    $up = updatedb('o_customers',"enc_phone='$enc_phone'","uid='$cuid'");
    echo $up;

}


$alt = fetchtable('o_customer_contacts',"enc_phone is null AND contact_type=1","uid","asc","$limit","uid, value, enc_phone");
while($a = mysqli_fetch_array($alt)){
    $uid = $a['uid'];
    $primary_mobile = $a['value'];
    $enc_phone = hash('sha256', $primary_mobile);

    $up = updatedb('o_customer_contacts',"enc_phone='$enc_phone'","uid='$uid'");
    echo $up;

}

?>