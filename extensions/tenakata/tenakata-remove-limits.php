<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$customers_list = array();

$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status !=0 ","uid","desc","100000","uid, given_date, final_due_date, customer_id");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $customer_id = $l['customer_id'];

    $ago = datediff($final_due_date, $date);

    if($ago >= 7){
        array_push($customers_list, $customer_id);
    }


}
$customers_list_string = implode(',', $customers_list);
$total_accounts = sizeof($customers_list);
$total_accounts_updated = 0;
$customers = fetchtable('o_customers',"uid in ($customers_list_string) AND loan_limit > 0","uid","asc","10000","uid");
while($c = mysqli_fetch_array($customers)){

    $cust_id = $c['uid'];
    //echo "$cust_id <br/>";
    $upd = updatedb('o_customers',"loan_limit=0","uid='$cust_id' AND loan_limit > 0");
    if($upd == 1){
        store_event('o_customers', $cust_id,"Limit removed by system because loan is DD+7 overdue");
        $total_accounts_updated+=1;
    }
}

echo "$total_accounts_updated/$total_accounts limits removed";



