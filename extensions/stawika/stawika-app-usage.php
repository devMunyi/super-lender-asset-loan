<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$jan_reg = table_to_array('o_customers',"date(added_date) BETWEEN '2023-01-01' AND '2023-01-31'","1000000","uid");
$feb_reg = table_to_array('o_customers',"date(added_date) BETWEEN '2023-02-01' AND '2023-02-28'","1000000","uid");
$mar_reg = table_to_array('o_customers',"date(added_date) BETWEEN '2023-03-01' AND '2023-03-31'","1000000","uid");


$jan_list = implode(',', $jan_reg);
$feb_list = implode(',', $feb_reg);
$mar_list = implode(',', $mar_reg);


//echo $jan_list.'<br/>';
//echo $feb_list.'<br/>';
//echo $mar_list.'<br/>';

echo "<h4>App Registrations</h4>";
echo "January App Registrations:".sizeof($jan_reg).'<br/>';
echo "February App Registrations:".sizeof($feb_reg).'<br/>';
echo "March App Registrations:".sizeof($mar_reg).'<br/>';

echo "<h4>App vs USSD Borrowers</h4>";
echo "January Borrowers: APP-> Ksh. 2,274,933.00(224 Loans) , USSD -> Ksh. 9,602,677.00(730 Loans) <br/>";
echo "February Borrowers: APP-> Ksh. 2,263,020.00(202 Loans) , USSD -> Ksh. 7,670,268.00(554 Loans) <br/>";
echo "March Borrowers: APP-> Ksh. 3,394,050.00(327 Loans) , USSD -> Ksh. 11,533,519.00(693 Loans) <br/>";

echo "<h4>App vs USSD New Borrowers</h4>";

$jan_new_loans_APP = table_to_array('o_loans',"disbursed=1 AND customer_id in ($jan_list) AND given_date BETWEEN '2023-01-01' AND '2023-01-31' AND application_mode='APP'","10000","loan_amount");
echo "January: New Borrowers Via APP: ".number_format(array_sum($jan_new_loans_APP), 2).'('.sizeof($jan_new_loans_APP).' Total Loans)<br/>';
$jan_app_repeat = 2274933.00 - array_sum($jan_new_loans_APP);
$jan_app_repeat_total = 224 - sizeof($jan_new_loans_APP);
echo "January: Repeat Borrowers Via APP: ".number_format($jan_app_repeat).'('.$jan_app_repeat_total.' Total Loans)<br/>';

echo "<br/>";

$feb_new_loans_APP = table_to_array('o_loans',"disbursed=1 AND customer_id in ($feb_list) AND given_date BETWEEN '2023-02-01' AND '2023-02-28' AND application_mode='APP'","10000","loan_amount");
echo "Feb: New Borrowers Via APP: ".number_format(array_sum($feb_new_loans_APP), 2).'('.sizeof($feb_new_loans_APP).' Total Loans)<br/>';
$feb_app_repeat = 2263020.00 - array_sum($feb_new_loans_APP);
$feb_app_repeat_total = 202 - sizeof($feb_new_loans_APP);
echo "Feb: Repeat Borrowers Via APP: ".number_format($feb_app_repeat).'('.$feb_app_repeat_total.' Total Loans)<br/>';

echo "<br/>";

$march_new_loans_APP = table_to_array('o_loans',"disbursed=1 AND customer_id in ($mar_list) AND given_date BETWEEN '2023-03-01' AND '2023-03-31' AND application_mode='APP'","10000","loan_amount");
echo "March: New Borrowers Via APP: ".number_format(array_sum($march_new_loans_APP), 2).'('.sizeof($march_new_loans_APP).' Total Loans)<br/>';
$march_app_repeat = 3394050.00 - array_sum($march_new_loans_APP);
$march_app_repeat_total = 327 - sizeof($march_new_loans_APP);
echo "March: Repeat Borrowers Via APP: ".number_format($march_app_repeat).'('.$march_app_repeat_total.' Total Loans)<br/>';
echo "<br/>";
