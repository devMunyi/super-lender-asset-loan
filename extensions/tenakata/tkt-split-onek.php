<?php 

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$result = fetchtable2("o_incoming_payments", "status = 1 AND loan_id IN (1, 2) AND amount = 1000", "uid", "ASC");

$count = 0;
while($r = mysqli_fetch_assoc($result)){

    // if($count == 2){
    //     // break;
    // }

    $uid = $r['uid'];
    $mobile_number = $r['mobile_number'];
    $transaction_code_1 = $r['transaction_code'] ."_split1";

    // set first split to payment_category = 2, amount = 500 and retain status = 2
    $update_payment = updatedb(
        'o_incoming_payments',
        "payment_category = 2, amount = 500, payment_method = 3, transaction_code = '$transaction_code_1'",
        "uid = $uid"
    );

    if($update_payment == 1){
        echo "CUSTOMER PHONE $mobile_number REDISTRATION SPLIT FEE SET <br>";
    }else {
        echo "CUSTOMER PHONE $mobile_number REDISTRATION SPLIT FEE NOT SET <br>";
    }

    $fds = array('customer_id','branch_id','group_id','payment_method','payment_category','mobile_number','amount','transaction_code','loan_id', 'payment_date','record_method','added_by','comments','status');
    

    $customer_id = $r['customer_id'];
    $branch_id = $r['branch_id'];
    $group_id = $r['group_id'];
    $payment_method = 3;
    $payment_for = 4; // split1 => 2, split2 => 4
    $amount = 500;
    $transaction_code_2 = $r['transaction_code'] ."_split2";
    $loan_id = 0;
    $payment_date = $r['payment_date'];
    $record_method = $r['record_method'];
    $added_by = $r['added_by'];
    $comments = "Carried Forward";
    $status = $r['status'];
    
    $vals = array("$customer_id","$branch_id","$group_id","$payment_method","$payment_for","$mobile_number","$amount","$transaction_code_2","$loan_id","$payment_date","$record_method","$added_by","$comments","$status");

    $create = addtodb('o_incoming_payments',$fds,$vals);
    if($create == 1){
        echo "CUSTOMER PHONE $mobile_number PROCESSING FEE SPLIT SET <br>";
    }else {
        echo "CUSTOMER PHONE $mobile_number PROCESSING FEE NOT SPLIT SET <br>";
    }

    $count += 1;
}

echo "ACCOUNTS AFFECTED => ".$count;

/*

CREATE TABLE `o_incoming_payments` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `branch_id` int NOT NULL DEFAULT '0',
  `group_id` int DEFAULT '0' COMMENT 'For group loans',
  `split_from` int DEFAULT '0' COMMENT 'If a payment is split from another payment',
  `payment_method` int NOT NULL,
  `payment_category` int DEFAULT '1',
  `mobile_number` varchar(20) NOT NULL,
  `amount` double(50,2) NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `loan_id` int NOT NULL,
  `loan_code` varchar(65) DEFAULT NULL COMMENT 'Temporally',
  `loan_balance` double(8,2) NOT NULL DEFAULT '0.00',
  `payment_date` date NOT NULL,
  `recorded_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `added_by` int NOT NULL,
  `collected_by` int DEFAULT '0',
  `record_method` varchar(50) NOT NULL COMMENT 'API, MANUAL',
  `comments` varchar(100) NOT NULL DEFAULT 'Repayment',
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `transaction_code` (`transaction_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2946067 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

*/