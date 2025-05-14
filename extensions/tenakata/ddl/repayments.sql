DROP TABLE IF EXISTS `o_incoming_payments`;

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
  `record_method` varchar(20) NOT NULL COMMENT 'API, MANUAL',
  `comments` varchar(100) NOT NULL DEFAULT 'Repayment',
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `transaction_code` (`transaction_code`)
);