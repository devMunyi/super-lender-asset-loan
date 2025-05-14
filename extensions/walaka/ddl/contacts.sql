DROP TABLE IF EXISTS `o_customer_contacts`;


CREATE TABLE `o_customer_contacts` (
  `uid` int(11) NOT NULL AUTO_INCREMENT, -- use default
  `customer_id` int(11) NOT NULL, -- fetch from o_customers tbl based on customer code
  `contact_type` int(11) NOT NULL COMMENT 'From o_contact_types table', -- 1 for first alternative, 2 for second alternative
  `value` varchar(250) NOT NULL, -- the number
  `status` int(11) NOT NULL, -- 1
  PRIMARY KEY (`uid`),
  KEY `value` (`value`,`status`),
  KEY `customer_id` (`customer_id`)
);