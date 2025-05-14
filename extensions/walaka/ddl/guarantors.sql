DROP TABLE IF EXISTS `o_guarantors`;
DROP TABLE IF EXISTS `o_customer_guarantors`;

CREATE TABLE `o_customer_guarantors` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `guarantor_name` varchar(255) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `national_id` varchar(15) NOT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `physical_address` text DEFAULT NULL,
  `amount_guaranteed` double(100,2) NOT NULL DEFAULT 0.00,
  `added_date` datetime NOT NULL,
  `relationship` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `customer_id` (`customer_id`),
  KEY `mobile_no` (`mobile_no`),
  KEY `relationship` (`relationship`)
)