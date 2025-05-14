<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../configs/archive_conn.php");
include_once ("../php_functions/functions.php");

///////////--------------remove all wrong LO pairs
$month_ago = datesub($date, 0,0, 1);

$recent_clients = table_to_array('o_loans',"disbursed=1 AND given_date >= '$month_ago'","10000000000","customer_id");
$recent_clients_string = implode(',', $recent_clients);

$all_cust = array();
$active = fetchtable('o_customers',"status = 1 AND uid in ($recent_clients_string)","uid","asc","0,500000","uid");
while($c = mysqli_fetch_array($active)){
    $cid = $c['uid'];
   // echo "$cid, <br/>";
    array_push($all_cust, $cid);

}

$cust = implode(',', $all_cust);
//echo $cust;
$customer_loans = array();
$customer_phones = array();

$loans = fetchtable('o_loans',"disbursed=1 AND status!=0 AND customer_id in ($cust)","uid","desc","10000000","uid, customer_id, account_number");
while($l = mysqli_fetch_array($loans)){
    $cuid = $l['uid'];
    $customer_id = $l['customer_id'];
    $primary_mobile = $l['account_number'];
    $customer_phones[$customer_id] = $primary_mobile;
    $customer_loans = obj_add($customer_loans, $customer_id, 1);
}

///////////-----------------Fetch from archives
if($has_archive == 1) {

    $query = "SELECT uid, customer_id FROM o_loans WHERE  disbursed=1 AND status!=0 AND customer_id in ($cust) LIMIT 1000000";
    $result = mysqli_query($con1, $query);
    while ($r = mysqli_fetch_array($result)) {
        $cuid = $r['uid'];
        $customer_id = $r['customer_id'];
        $customer_loans = obj_add($customer_loans, $customer_id, 1);
        // echo "jj";
    }
}
//var_dump($customer_loans);
$total = 0;
foreach ($customer_loans as $cus => $loans) {
    $cus_phone = $customer_phones[$cus];
  // echo "$cus - $loans :  $cus_phone<br/>";
   $up = updatedb('o_customers',"total_loans='$loans'","uid='$cus'");
   $total = $total + $up;
}

echo "Total updates: $total <br/>";
?>