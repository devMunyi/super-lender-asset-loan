DROP TABLE IF EXISTS `o_customer_referees`;

CREATE TABLE `o_customer_referees` (
  `uid` int(11) NOT NULL AUTO_INCREMENT, -- use default
  `customer_id` int(11) NOT NULL, -- fetch from o_customers table based on customer code
  `added_date` datetime NOT NULL, -- use $fulldate
  `referee_name` varchar(50) NOT NULL, -- get it from CSV
  `id_no` varchar(15) DEFAULT NULL, -- use default
  `mobile_no` varchar(15) NOT NULL, -- get it from CSV
  `physical_address` varchar(145) DEFAULT NULL, -- use default NULL
  `email_address` varchar(50) DEFAULT NULL, -- use default NULL
  `relationship` int(11) NOT NULL, -- to based on o_customer_referee_relationships
  `status` int(11) NOT NULL, -- use 1
  PRIMARY KEY (`uid`),
  KEY `customer_id` (`customer_id`)
);