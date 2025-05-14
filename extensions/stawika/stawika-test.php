<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$pen = "#	Statement Type	File Name	Status	Duration	Upload Date	Uploader	Actions
MOBILEMPESA	MPESA_Statement_2023-03-12_to_2023-09-12_2547xxxxxx443 (1).pdf	Completed	Months	
13/09/2023 05:36 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-11_to_2023-09-11_2547xxxxxx496.pdf	Completed	Months	
13/09/2023 05:15 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-09-08_to_2023-09-08_2547xxxxxx564.pdf	Completed	Months	
12/09/2023 06:26 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-11_to_2023-09-11_2547xxxxxx396.pdf	Completed	Months	
11/09/2023 07:26 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-11_to_2023-09-11_2547xxxxxx185.pdf	Completed	Months	
11/09/2023 07:18 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-11_to_2023-09-11_2547xxxxxx250.pdf	Completed	Months	
11/09/2023 07:10 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-08_to_2023-09-08_2547xxxxxx890 (2).pdf	Completed	Months	
08/09/2023 01:12 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-06-07_to_2023-09-07_2547xxxxxx245.pdf	Completed	Months	
08/09/2023 01:06 PM

admin-services admin-services

MOBILEMPESA	MPESA_Statement_2023-03-06_to_2023-09-06_2547xxxxxx152 (1).pdf	Completed	Months	
07/09/2023 08:23 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-06-07_to_2023-09-07_2547xxxxxx736.pdf	Completed	Months	
07/09/2023 08:02 AM

admin-services admin-services

MOBILEMPESA	MPESA_Statement_2022-09-06_to_2023-09-06_2547xxxxxx250 (1).pdf	Completed	Months	
06/09/2023 09:20 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-06_to_2023-09-06_2547xxxxxx079.pdf	Completed	Months	
06/09/2023 09:15 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-05_to_2023-09-05_2547xxxxxx813.pdf	Completed	Months	
06/09/2023 07:11 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-25_to_2023-08-25_2547xxxxxx242.pdf	Completed	Months	
05/09/2023 12:40 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-04_to_2023-09-04_2547xxxxxx799 (1).pdf	Completed	Months	
06/09/2023 11:25 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-04_to_2023-09-04_2547xxxxxx567.pdf	Completed	Months	
05/09/2023 11:54 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-04_to_2023-09-04_2547xxxxxx595 (1).pdf	Completed	Months	
05/09/2023 11:49 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-04_to_2023-09-04_2547xxxxxx676 (1).pdf	Completed	Months	
05/09/2023 10:47 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-05_to_2023-09-05_2547xxxxxx020 (1) (2).pdf	Completed	Months	
06/09/2023 11:20 AM

Alex Muriithi

MOBILEMPESA	MPESA_Statement_2023-03-05_to_2023-09-05_2547xxxxxx746.pdf	Completed	Months	
05/09/2023 10:13 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-05_to_2023-09-05_2547xxxxxx216.pdf	Completed	Months	
05/09/2023 10:06 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-03-04_to_2023-09-04_2547xxxxxx575.pdf	Completed	Months	
04/09/2023 01:54 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-06-04_to_2023-09-04_2547xxxxxx210.pdf	Completed	Months	
04/09/2023 11:51 AM

admin-services admin-services

MOBILEMPESA	MPESA_Statement_2023-03-02_to_2023-09-02_2547xxxxxx348.pdf	Completed	Months	
04/09/2023 08:03 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-28_to_2023-08-31_2547xxxxxx065.pdf	Completed	Months	
31/08/2023 11:43 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-28_to_2023-08-31_2547xxxxxx755.pdf	Completed	Months	
31/08/2023 11:14 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-28_to_2023-08-29_2547xxxxxx188 (1).pdf	Completed	Months	
31/08/2023 11:09 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-28_to_2023-08-30_2547xxxxxx272.pdf	Completed	Months	
30/08/2023 07:01 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-28_to_2023-08-30_2547xxxxxx535 (1).pdf	Completed	Months	
30/08/2023 11:17 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-28_to_2023-08-29_2547xxxxxx612.pdf	Completed	Months	
30/08/2023 08:48 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-28_to_2023-08-28_2547xxxxxx946.pdf	Completed	Months	
30/08/2023 06:35 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-27_to_2023-08-27_2547xxxxxx831.pdf	Completed	Months	
29/08/2023 01:24 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-28_to_2023-08-28_2547xxxxxx185.pdf	Completed	Months	
29/08/2023 12:47 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-08-29_to_2023-08-29_2547xxxxxx013.pdf	Completed	Months	
29/08/2023 10:13 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-28_to_2023-08-29_2547xxxxxx957.pdf	Completed	Months	
29/08/2023 10:01 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-17_to_2023-08-17_2547xxxxxx655.pdf	Completed	Months	
29/08/2023 09:19 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-08-23_to_2023-08-23_2547xxxxxx788 (3).pdf	Completed	Months	
28/08/2023 01:05 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-25_to_2023-08-25_2547xxxxxx567.pdf	Completed	Months	
28/08/2023 12:10 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-25_to_2023-08-25_2547xxxxxx685.pdf	Completed	Months	
28/08/2023 11:21 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-26_to_2023-08-26_2547xxxxxx960.pdf	Completed	Months	
28/08/2023 09:40 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-08-25_to_2023-08-25_2547xxxxxx182 (1).pdf	Completed	Months	
28/08/2023 09:37 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-24_to_2023-08-24_2547xxxxxx079 (1).pdf	Completed	Months	
25/08/2023 08:51 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-08-25_to_2023-08-25_2547xxxxxx771.pdf	Completed	Months	
25/08/2023 07:06 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-05-18_to_2023-08-18_2547xxxxxx277.pdf	Completed	Months	
23/08/2023 07:54 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-23_to_2023-08-23_2547xxxxxx404.pdf	Completed	Months	
23/08/2023 07:40 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-16_to_2023-08-16_2547xxxxxx766-1.pdf	Completed	Months	
16/08/2023 10:15 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-16_to_2023-08-16_2547xxxxxx812.pdf	Completed	Months	
16/08/2023 10:07 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-16_to_2023-08-16_2547xxxxxx655.pdf	Completed	Months	
16/08/2023 09:14 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-15_to_2023-08-15_2547xxxxxx916.pdf	Completed	Months	
16/08/2023 06:39 AM

Jonah Ngarama

MOBILEMPESA	mpesa_statement2023.pdf	Failed	Months	
11/08/2023 08:10 AM

admin-services admin-services

MOBILEMPESA	MPESA_Statement_2023-05-09_to_2023-08-09_2547xxxxxx060.pdf	Processing	Months	
11/08/2023 07:58 AM

admin-services admin-services

MOBILEMPESA	MPESA_Statement_2023-02-10_to_2023-08-10_2547xxxxxx661.pdf	Completed	Months	
10/08/2023 12:46 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-02-10_to_2023-08-10_2547xxxxxx907.pdf	Completed	Months	
10/08/2023 09:51 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-05-09_to_2023-08-09_2547xxxxxx060.pdf	Completed	Months	
09/08/2023 02:15 PM

admin-services admin-services

MOBILEMPESA	MPESA_Statement_2023-02-09_to_2023-08-09_2547xxxxxx573.pdf	Completed	Months	
09/08/2023 12:11 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-07-31_to_2023-07-31_2547xxxxxx801.pdf	Completed	Months	
09/08/2023 11:05 AM

Jonah Ngarama

MOBILEMPESA	mpesa.pdf	Completed	Months	
09/08/2023 10:59 AM

admin-services admin-services

MOBILEMPESA	MPESA_Statement_2023-02-01_to_2023-08-01_2547xxxxxx378.pdf	Completed	Months	
01/08/2023 07:53 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-27_to_2023-07-27_2547xxxxxx668.pdf	Completed	Months	
27/07/2023 11:38 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-07-27_to_2023-07-27_2547xxxxxx492.pdf	Completed	Months	
27/07/2023 10:36 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-27_to_2023-07-27_2547xxxxxx732.pdf	Completed	Months	
27/07/2023 10:00 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-07-26_to_2023-07-26_2547xxxxxx053.pdf	Completed	Months	
26/07/2023 01:44 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-07-26_to_2023-07-26_2547xxxxxx664.pdf	Completed	Months	
26/07/2023 01:26 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-25_to_2023-07-25_2547xxxxxx209.pdf	Processing	Months	
25/07/2023 02:58 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-24_to_2023-07-24_2547xxxxxx218.pdf	Completed	Months	
25/07/2023 01:25 PM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-25_to_2023-07-25_2547xxxxxx209.pdf	Processing	Months	
25/07/2023 11:39 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-07-18_to_2023-07-18_2547xxxxxx778.pdf	Completed	Months	
25/07/2023 11:06 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-25_to_2023-07-25_2547xxxxxx814.pdf	Completed	Months	
25/07/2023 11:02 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-23_to_2023-07-23_2547xxxxxx131.pdf	Completed	Months	
25/07/2023 10:49 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-25_to_2023-07-25_2547xxxxxx083.pdf	Completed	Months	
25/07/2023 10:32 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-25_to_2023-07-25_2547xxxxxx209.pdf	Completed	Months	
25/07/2023 10:27 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-25_to_2023-07-25_2547xxxxxx942.pdf	Completed	Months	
25/07/2023 10:22 AM

Jonah Ngarama

MOBILEMPESA	Mary MPESA_Statement_2022-07-22_to_2023-07-22_2547xxxxxx923.pdf	Completed	Months	
25/07/2023 10:22 AM

Jonah Ngarama

MOBILEMPESA	Ken kihara 2.pdf	Processing	Months	
25/07/2023 10:16 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-24_to_2023-07-24_2547xxxxxx404.pdf	Completed	Months	
25/07/2023 10:15 AM

Jonah Ngarama

MOBILEMPESA	Ken kihara 2.pdf	Processing	Months	
25/07/2023 10:13 AM

Jonah Ngarama

MOBILEMPESA	Ken kihara 2 254722201964_2023-05-01_2022-11-01_1682973176204.pdf	Processing	Months	
25/07/2023 10:10 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-07-24_to_2023-07-24_2547xxxxxx876.pdf	Completed	Months	
25/07/2023 10:09 AM

Jonah Ngarama

MOBILEMPESA	461220 pin MPESA_Statement_2022-07-11_to_2023-01-11_2547xxxxxx244.pdf	Completed	Months	
25/07/2023 10:05 AM

Jonah Ngarama

MOBILEMPESA	Ken kihara 2 254722201964_2023-05-01_2022-11-01_1682973176204.pdf	Waiting	Months	
25/07/2023 10:04 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-17_to_2023-07-17_2547xxxxxx065.pdf	Completed	Months	
17/07/2023 09:42 AM

Jonah Ngarama

MOBILEMPESA	Statement_All_Transactions_20230701_20230717.pdf	Completed	Months	
17/07/2023 08:48 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2022-12-21_to_2023-06-21_2547xxxxxx450_2.pdf	Completed	Months	
17/07/2023 08:30 AM

Jonah Ngarama

MOBILEMPESA	MPESA_Statement_2023-01-17_to_2023-07-17_2547xxxxxx450.pdf	error	Months	
17/07/2023 08:24 AM

Jonah Ngarama";

$loans_array = array();

$one_loan = table_to_array('o_customers',"total_loans <= 2","10000000","uid","uid","asc");
$one_list = implode(',', $one_loan);

$loans = fetchtable('o_loans', "customer_id in ($one_list) AND given_date >= '2023-07-25' AND disbursed=1 AND loan_amount < 5000","uid","asc","1000","uid, account_number" );
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $account_number = $l['account_number'];
    $last_3 = substr($account_number, -3);
   $loans_array[$last_3] = $uid;
   // echo $last_3.'<br/>';
}
$phones = extract_words_between('254','.pdf', $pen);
for($i = 1; $i <= sizeof($phones); ++$i){
    // echo $phones[$i].'<br/>';
    $last_ = explode('xxxxxx', $phones[$i]);
    $last_digits = $last_[1];
    $strip = explode(' ', $last_digits);
   echo $loans_array[$strip[0]].',';
}