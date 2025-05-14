DROP TABLE IF EXISTS `o_loan_addons`;

CREATE TABLE `o_loan_addons` (
  `uid` int NOT NULL AUTO_INCREMENT, -- use default
  `loan_id` int NOT NULL, -- fetch based on loan code K1-1, N1-1
  `addon_id` int NOT NULL, -- either 1(base interest) or 2(Penalty 1)
  `addon_amount` double(20,2) NOT NULL, -- insert only when value > 0
  `added_by` int DEFAULT '0', -- default = 0
  `added_date` datetime DEFAULT CURRENT_TIMESTAMP, -- use '0000-00-00 00:00:00'
  `status` int DEFAULT 1, -- use default
  PRIMARY KEY (`uid`),
  KEY `addon_id` (`addon_id`),
  KEY `loan_id` (`loan_id`,`addon_id`)
);