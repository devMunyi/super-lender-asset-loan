ALTER TABLE `o_loans`  ADD `to_sync` INT(1) NOT NULL DEFAULT 0  AFTER `status`;
ALTER TABLE `o_incoming_payments`  ADD `to_sync` INT(1) NOT NULL DEFAULT 0  AFTER `status`;
ALTER TABLE `o_loan_addons`  ADD `to_sync` INT(1) NOT NULL DEFAULT 0  AFTER `status`;
ALTER TABLE `o_events`  ADD `to_sync` INT(1) NOT NULL DEFAULT 0  AFTER `status`;
ALTER TABLE `o_mpesa_queues`  ADD `to_sync` INT(1) NOT NULL DEFAULT 0  AFTER `status`;


-- Check and add 'pin_' column if it doesn't exist
IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'o_customers' 
    AND COLUMN_NAME = 'pin_' 
    AND TABLE_SCHEMA = DATABASE()
) THEN
    ALTER TABLE `o_customers`
    ADD COLUMN `pin_` VARCHAR(55) NULL AFTER `sec_data`;
END IF;

-- Check and add 'device_id' column if it doesn't exist
IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'o_customers' 
    AND COLUMN_NAME = 'device_id' 
    AND TABLE_SCHEMA = DATABASE()
) THEN
    ALTER TABLE `o_customers` 
    ADD COLUMN `device_id` VARCHAR(55) NULL AFTER `pin_`;
END IF;


CREATE TABLE `o_customer_sessions` (
  `uid` INT(10) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(10) NOT NULL,
  `device_id` VARCHAR(105) NULL,
  `session_code` VARCHAR(105) NULL,
  `started_date` DATETIME NULL,
  `ending_date` DATETIME NULL,
  `status` INT(1) NULL,
  PRIMARY KEY (`uid`));


CREATE TABLE `o_changelog` (
  `uid` INT(10) NOT NULL AUTO_INCREMENT,
  `tbl_` VARCHAR(45) NULL,
  `query_` TEXT(1000) NOT NULL,
  `change_by` INT(10) NULL,
  `change_date` DATETIME NULL,
  `status` INT(1) NULL,
  PRIMARY KEY (`uid`));


ALTER TABLE `o_loan_products` 
ADD COLUMN `automatic_disburse` INT(1) NULL DEFAULT 0 AFTER `disburse_method`;

ALTER TABLE `o_customers` 
ADD COLUMN `flag` INT(5) NULL AFTER `device_id`;

ALTER TABLE `o_customers` 
ADD COLUMN `total_loans` INT(5) NULL DEFAULT 0 AFTER `flag`;



CREATE TABLE `o_customer_limits` (
  `uid` INT(10) NOT NULL AUTO_INCREMENT,
  `customer_uid` INT(10) NOT NULL,
  `amount` DOUBLE(10,2) NOT NULL,
  `given_date` DATETIME NULL,
  `given_by` INT(10) NULL,
  `comments` VARCHAR(245) NULL,
  `status` INT(1) NULL,
  PRIMARY KEY (`uid`));

CREATE TABLE `o_ussd_sessions` (
  `uid` INT(10) NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(105) NOT NULL,
  `started_date` DATETIME NULL,
  `stage_` INT NULL,
  `mobile_number` VARCHAR(15) NULL,
  `info_supplied` VARCHAR(245) NULL,
  `status` INT NULL,
  PRIMARY KEY (`uid`));

ALTER TABLE `o_ussd_sessions` 
CHANGE COLUMN `info_supplied` `info_supplied` TEXT(500) NULL DEFAULT NULL ;

ALTER TABLE `o_customer_conversations` 
CHANGE COLUMN `transcript` `transcript` MEDIUMTEXT NOT NULL ;

ALTER TABLE `o_customers` 
CHANGE COLUMN `physical_address` `physical_address` VARCHAR(250) NULL ,
CHANGE COLUMN `passport_photo` `passport_photo` VARCHAR(250) NULL ,
CHANGE COLUMN `gender` `gender` VARCHAR(5) NULL COMMENT 'M, F' ,
CHANGE COLUMN `dob` `dob` DATE NULL ,
CHANGE COLUMN `added_date` `added_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ,
CHANGE COLUMN `events` `events` MEDIUMTEXT NULL ,
CHANGE COLUMN `status` `status` INT NULL DEFAULT 2 COMMENT 'From o_customer_statuses' ;




ALTER TABLE `o_customers` 
CHANGE COLUMN `loan_limit` `loan_limit` DOUBLE(100,2) NULL DEFAULT '0.00' ;

ALTER TABLE `o_permissions` 
CHANGE COLUMN `rec` `rec` INT NULL ,
CHANGE COLUMN `general_` `general_` INT NULL DEFAULT 0 ,
CHANGE COLUMN `create_` `create_` INT NULL DEFAULT 0 ,
CHANGE COLUMN `read_` `read_` INT NULL DEFAULT 0 ,
CHANGE COLUMN `update_` `update_` INT NULL DEFAULT 0 ,
CHANGE COLUMN `delete_` `delete_` INT NULL DEFAULT 0 ;


ALTER TABLE `o_customer_conversations` 
ADD COLUMN `branch` INT NULL DEFAULT 0 AFTER `customer_id`;

CREATE TABLE `o_customer_products` ( `uid` INT(20) NOT NULL AUTO_INCREMENT ,  `customer_id` INT(10) NOT NULL ,  `product_id` INT(10) NOT NULL ,  `added_date` DATETIME NOT NULL ,  `status` INT(1) NOT NULL ,    PRIMARY KEY  (`uid`)) ENGINE = InnoDB;

ALTER TABLE `o_campaigns` 
CHANGE COLUMN `target_customers` `target_customers` VARCHAR(50) NOT NULL ;

ALTER TABLE `o_campaigns` 
ADD COLUMN `total_customers` INT NULL AFTER `target_customers`;

ALTER TABLE `o_campaigns` 
CHANGE COLUMN `running_date` `running_date` DATETIME NOT NULL ;






ALTER TABLE `o_loans` 
ADD COLUMN `last_pay_date` DATE NULL AFTER `final_due_date`;






CREATE TABLE `o_mpesa_queues` (
  `uid` INT NOT NULL AUTO_INCREMENT,
  `loan_id` INT UNSIGNED NOT NULL,
  `amount` DOUBLE(50,2) NULL,
  `added_date` DATETIME NULL,
  `sent_date` DATETIME NULL,
  `feedbackcode` VARCHAR(45) NULL,
  `trials` INT NULL DEFAULT 1,
  `status` INT NULL DEFAULT 0 COMMENT '1-queed, 2-sent, 0-deleted',
  PRIMARY KEY (`uid`))
COMMENT = 'List disbursements that are to be done later';


ALTER TABLE `o_targets` 
CHANGE COLUMN `amount` `amount` BIGINT(10) NULL DEFAULT NULL ;

ALTER TABLE `o_loans` 
ADD COLUMN `allocation` VARCHAR(10) NULL DEFAULT 'BRANCH' AFTER `current_co`;

ALTER TABLE `o_users` 
ADD COLUMN `tag` VARCHAR(10) NULL  AFTER `user_group`;


CREATE TABLE `o_regions` ( `uid` INT(5) NOT NULL AUTO_INCREMENT ,  `name` VARCHAR(40) NOT NULL ,  `manager_id` INT(10) NULL ,  `status` INT(1) NOT NULL ,    PRIMARY KEY  (`uid`),    UNIQUE  (`name`)) ENGINE = InnoDB;

ALTER TABLE `o_branches` 
ADD COLUMN `region_id` INT(10) NULL DEFAULT 0 AFTER `address`;

ALTER TABLE `o_customers` 
ADD COLUMN `current_agent` INT NULL DEFAULT 0 AFTER `added_by`;

ALTER TABLE `o_loans` 
ADD COLUMN `pending_event` VARCHAR(45) NULL COMMENT 'For large events like penalties, to assist is paged actions' AFTER `loan_flag`;
ALTER TABLE `o_loans` 
CHANGE COLUMN `pending_event` `pending_event` INT NULL DEFAULT 0 COMMENT 'For large events like penalties, to assist is paged actions' ;

ALTER TABLE `o_users` 
ADD COLUMN `pair` INT NULL DEFAULT 0 AFTER `tag`;

CREATE TABLE `o_customer_groups` ( `uid` INT NOT NULL AUTO_INCREMENT ,  `group_name` VARCHAR(50) NOT NULL ,  `group_description` VARCHAR(250) NOT NULL ,  `added_date` DATETIME NOT NULL,  `added_by` INT NOT NULL , `status` INT NOT NULL ,    PRIMARY KEY  (`uid`),    UNIQUE  (`group_name`)) ENGINE = InnoDB;

ALTER TABLE `o_loans` 
ADD COLUMN `group_id` INT NULL DEFAULT 0 AFTER `customer_id`;

CREATE TABLE `o_loan_types` ( `uid` INT NOT NULL AUTO_INCREMENT ,  `icon` VARCHAR(20) NOT NULL ,  `name` VARCHAR(30) NOT NULL ,  `status` INT NOT NULL ,    PRIMARY KEY  (`uid`)) ENGINE = InnoDB;
ALTER TABLE `o_loans`  ADD `loan_type` INT NOT NULL DEFAULT 0  AFTER `product_id`;

ALTER TABLE `o_incoming_payments` 
ADD COLUMN `group_id` INT NULL DEFAULT 0 COMMENT 'For group loans' AFTER `status`;
ALTER TABLE `o_incoming_payments` 
CHANGE COLUMN `group_id` `group_id` INT(11) NULL DEFAULT 0 COMMENT 'For group loans' AFTER `branch_id`;
ALTER TABLE `o_incoming_payments` 
ADD COLUMN `split_from` INT NULL DEFAULT 0 COMMENT 'If a payment is split from another payment' AFTER `group_id`;

CREATE TABLE `o_payment_categories` (
  `uid` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `status` INT NULL,
  PRIMARY KEY (`uid`));

ALTER TABLE `o_incoming_payments` 
ADD COLUMN `payment_category` INT(11) NULL DEFAULT 1 AFTER `payment_method`;

INSERT INTO `o_payment_categories` (`name`, `status`) VALUES ('Loan Repayment', '1');

CREATE TABLE `o_archives` (
  `uid` INT NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(45) NOT NULL,
  `value` VARCHAR(145) NULL,
  `added_date` DATETIME NULL,
  `added_by` INT NULL,
  `status` INT NULL,
  PRIMARY KEY (`uid`));

CREATE TABLE `o_reports_custom` ( `uid` INT NOT NULL AUTO_INCREMENT ,  `title` VARCHAR(30) NOT NULL ,  `description` VARCHAR(100) NOT NULL ,  `report_url` VARCHAR(200) NOT NULL ,  `added_date` DATETIME NOT NULL ,  `status` INT(1) NOT NULL ,    PRIMARY KEY  (`uid`),    UNIQUE  (`title`)) ENGINE = InnoDB;

ALTER TABLE `o_pairing` 
ADD COLUMN `branch` INT NULL DEFAULT 0 AFTER `co`;

ALTER TABLE `o_customers` 
CHANGE COLUMN `physical_address` `physical_address` TEXT(1000) NOT NULL ;

CREATE TABLE `o_group_members` (
 `uid` int(11) NOT NULL AUTO_INCREMENT,
 `group_id` int(11) NOT NULL,
 `customer_id` int(11) NOT NULL,
 `added_date` datetime NOT NULL,
 `added_by` int(11) NOT NULL,
 `loan_limit` double(50,2) NOT NULL,
 `status` int(11) NOT NULL,
 PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4


ALTER TABLE `o_incoming_payments` 
ADD COLUMN `collected_by` INT NULL DEFAULT 0 AFTER `status`;
ALTER TABLE `o_incoming_payments` 
CHANGE COLUMN `collected_by` `collected_by` INT NULL DEFAULT 0 AFTER `added_by`;

CREATE TABLE `o_payment_statuses` ( `uid` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(25) NOT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;
INSERT INTO `o_payment_statuses` (`uid`, `name`) VALUES ('1', 'Successful'), ('2', 'Split'), ('3', 'Reversed Loan'), ('4', 'Reversed Payment');

-- Date: May-29-2023
-- INSERT INTO `o_reports_custom` (`uid`, `title`, `description`, `report_url`, `added_date`, `status`) VALUES (NULL, 'Taken 3 Same Loans', 'List of customers with three consecutive recent loans having status as cleared', 'sp-taken-3-same-loans.php', '2023-05-29 15:24:47.000000', '1');

-- Date: June-12-2023


-- Date June-27-2023
CREATE TABLE `o_staff_branches` ( `uid` INT NOT NULL AUTO_INCREMENT ,  `agent` INT NOT NULL ,  `branch` INT NOT NULL ,  `added_date` DATETIME NOT NULL ,  `status` INT NOT NULL ,    PRIMARY KEY  (`uid`)) ENGINE = InnoDB COMMENT = 'For agents who are assigned to multiple branches';

-- ALTER TABLE o_loans ALTER COLUMN current_instalment_amount SET DEFAULT 0;
-- UPDATE o_loans SET current_instalment_amount = 0 WHERE current_instalment_amount IS NULL;

CREATE TABLE `o_mtn_queues` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(10) UNSIGNED NOT NULL,
  `amount` double(50,2) DEFAULT NULL,
  `added_date` datetime DEFAULT NULL,
  `requeued_date` datetime DEFAULT NULL,
  `sent_date` datetime DEFAULT NULL,
  `feedbackcode` varchar(45) DEFAULT NULL,
  `trials` int(11) DEFAULT 0,
  `status` int(11) DEFAULT 1 COMMENT '1-queed, 2-sent, 0-deleted',
  PRIMARY KEY (`uid`)
);


-- 11th Jul 2023
ALTER TABLE `o_loans`  ADD `other_info` JSON NULL  AFTER `paid`;

-- 20th Jul 2023
ALTER TABLE `o_reports`
    ADD COLUMN `branch_query` TEXT NULL AFTER `row_query`;
    

-- 2nd Aug 2023
CREATE TABLE `o_savings` ( `uid` INT NOT NULL AUTO_INCREMENT ,  `client_id` INT NOT NULL ,  `group_id` INT NOT NULL ,  `amount` DOUBLE(100,2) NOT NULL ,  `date_added` DATETIME NOT NULL ,  `source_tbl` VARCHAR(30) NOT NULL ,  `source_fld` INT NOT NULL ,  `recorded_by` INT NOT NULL ,  `status` INT NOT NULL ,    PRIMARY KEY  (`uid`),    INDEX  `client` (`client_id`),    INDEX  `group_` (`group_id`)) ENGINE = InnoDB;


-- 15th Aug 2023
ALTER TABLE `o_customer_contacts` ADD `last_update` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `value`;

-- 21st Aug 2023
CREATE TABLE `o_asset_cart` ( `uid` INT NOT NULL AUTO_INCREMENT ,  `asset_id` INT NOT NULL ,  `client_id` INT NOT NULL ,  `added_by` INT NOT NULL ,  `added_date`
    DATETIME NOT NULL ,  `quantity` INT NOT NULL ,  `loan_id` INT NOT NULL, `unit_price` DOUBLE NOT NULL, `total_price` DOUBLE NOT NULL,  `status` INT NOT NULL ,    PRIMARY KEY  (`uid`)) ENGINE = InnoDB;


CREATE TABLE `o_promo_codes` ( `uid` INT(10) NOT NULL AUTO_INCREMENT ,  `campaign_id` INT(10) NOT NULL , `promocode` VARCHAR(25) NOT NULL, `tbl_` VARCHAR(30) NOT NULL ,  `fld_` INT(10) NOT NULL ,  `added_date` DATETIME NOT NULL ,  `redeemed_date` DATETIME NOT NULL ,  `expiry_date` DATETIME NOT NULL ,  `amount_` DOUBLE(100,2) NOT NULL ,  `comments_` VARCHAR(50) NOT NULL ,  `status` INT(3) NOT NULL ,    PRIMARY KEY  (`uid`)) ENGINE = InnoDB;


ALTER TABLE `o_incoming_payments`
    CHANGE COLUMN `record_method` `record_method` VARCHAR(50) NOT NULL COMMENT 'API, MANUAL' ;


ALTER TABLE `o_ussd_sessions`
    ADD COLUMN `last_choice` VARCHAR(5) NULL AFTER `info_supplied`;


ALTER TABLE `o_loan_products`
    ADD COLUMN `after_save_script` JSON NULL DEFAULT NULL COMMENT 'The script to be run after a loan or customer is created' AFTER `added_date`;
ALTER TABLE `o_loan_products` CHANGE `after_save_script` `after_save_script` JSON NULL DEFAULT NULL COMMENT 'The script to be run after a loan or customer is created';


-- 10th Sept 2023
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
);

CREATE TABLE `o_customer_guarantor_relationships` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

INSERT INTO `o_customer_guarantor_relationships` (`uid`, `name`, `status`) VALUES
(1, 'Mother', 1),
(2, 'Father', 1),
(3, 'Mother-in-law (MOM IN LAW)', 1),
(4, 'Father-in-law (DAD IN LAW)', 1),
(5, 'Son', 1),
(6, 'Daughter', 1),
(7, 'Brother', 1),
(8, 'Sister', 1),
(9, 'Sister-in-law (SIS IN LAW)', 1),
(10, 'Brother-in-law (BRO IN LAW)', 1),
(11, 'Husband', 1),
(12, 'Spouse', 1),
(13, 'Wife', 1),
(14, 'Cousin', 1),
(15, 'Niece', 1),
(16, 'Nephew', 1),
(17, 'Aunt', 1),
(18, 'Aunt-in-law', 1),
(19, 'Uncle', 1),
(20, 'Uncle-in-law', 1),
(21, 'Friend', 1),
(22, 'Grandmother', 1),
(23, 'Grandfather', 1),
(24, 'Other', 1),
(25, 'Daughter-in-law', 1),
(26, 'Son-in-law', 1);

ALTER TABLE `o_campaign_messages`
MODIFY COLUMN `message` TEXT NOT NULL;

ALTER TABLE `o_sms_outgoing`
MODIFY COLUMN `message_body` TEXT NOT NULL;


CREATE TABLE `o_airtel_ug_queues` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `loan_id` int unsigned NOT NULL,
  `amount` double(50,2) DEFAULT NULL,
  `added_date` datetime DEFAULT NULL,
  `requeued_date` datetime DEFAULT NULL,
  `sent_date` datetime DEFAULT NULL,
  `feedbackcode` varchar(45) DEFAULT NULL,
  `tried_params` json DEFAULT NULL,
  `trials` int DEFAULT 0,
  `status` int DEFAULT '1' COMMENT '1-queued, 2-sent, 0-deleted, 3-failed',
  PRIMARY KEY (`uid`),
  KEY `added_date` (`added_date`,`trials`,`status`),
  KEY `requeued_date` (`requeued_date`,`trials`,`status`)
);

ALTER TABLE o_sms_interaction
ADD COLUMN ref_id varchar(100) AFTER link_id,
ADD INDEX idx_ref_id (ref_id);

-- 2023-11-06
ALTER TABLE o_customers
ADD COLUMN geolocation TEXT DEFAULT NULL AFTER physical_address;

-- Create the o_telecomms table
CREATE TABLE o_telecomms (
    uid INT AUTO_INCREMENT PRIMARY KEY,
    name varchar(100) DEFAULT NULL,
    country_code varchar(20) DEFAULT NULL,
    status TINYINT DEFAULT 1
);

-- Create an index on the country_code column
CREATE INDEX idx_country_code ON o_telecomms (country_code);

-- Insert the data into the o_telecomms table
INSERT INTO o_telecomms (name, country_code) VALUES
    ('SAFARICOM KE', '254'),
    ('AIRTEL KE', '254'),
    ('AIRTEL UG', '256'),
    ('MTN UG', '256');

ALTER TABLE `o_customers`
ADD COLUMN `phone_number_provider` INT AFTER `primary_mobile`,
ADD INDEX `phone_number_provider_index` (`phone_number_provider`);


-- 2023-11-08 
ALTER TABLE `o_events`
ADD COLUMN `event_date_rm_time` DATE GENERATED ALWAYS AS (DATE(event_date)) STORED AFTER `event_date`,
ADD INDEX `idx_event_date_rm_time` (`event_date_rm_time`);


-- Modify the email_address column to allow NULL values
ALTER TABLE `o_customers` MODIFY `email_address` varchar(100) DEFAULT NULL;

-- Make national_id nullable
ALTER TABLE `o_customers` MODIFY `national_id` varchar(10) DEFAULT NULL;

-- Make dob nullable
ALTER TABLE `o_customers` MODIFY `dob` date DEFAULT NULL;

ALTER TABLE `o_customer_limits`
ADD INDEX `idx_customer_uid` (`customer_uid`);

ALTER TABLE `o_customer_guarantors`
MODIFY COLUMN `uid` int(11) NOT NULL AUTO_INCREMENT;

UPDATE o_customers set phone_number_provider = 1 where uid > 0;

ALTER TABLE `o_customers` CHANGE `passport_photo` `passport_photo` VARCHAR(250) DEFAULT NULL;
ALTER TABLE `o_customers` CHANGE `events` `events` MEDIUMTEXT DEFAULT NULL;
ALTER TABLE `o_customers` CHANGE `dob` `dob` DATE NULL DEFAULT NULL;

ALTER TABLE `o_loans`  ADD `enc_phone` VARCHAR(70) DEFAULT NULL  AFTER `account_number`;

ALTER TABLE `o_customers`  ADD `enc_phone` VARCHAR(70) DEFAULT NULL AFTER `primary_mobile` ;
ALTER TABLE `o_customer_contacts`  ADD `enc_phone` VARCHAR(70) DEFAULT NULL AFTER `value`;

CREATE TABLE `o_menu` ( `uid` INT NOT NULL AUTO_INCREMENT ,  `gid` INT NOT NULL ,  `sid` INT NOT NULL ,  `visible_menu` JSON NOT NULL ,  `dashboard` VARCHAR(100) NOT NULL ,  `status` INT NOT NULL ,    PRIMARY KEY  (`uid`)) ENGINE = InnoDB;


CREATE TABLE `o_branch_statuses` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `color` varchar(50) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
); 

INSERT INTO `o_branch_statuses` VALUES (1,'Active','bg-green'),(2,'Blocked','bg-red');

ALTER TABLE `o_branches`
MODIFY COLUMN `manager_id` INT DEFAULT 0,
MODIFY COLUMN `address` TEXT DEFAULT NULL,
MODIFY COLUMN `status` INT DEFAULT 1;

ALTER TABLE `o_branches`
CHANGE COLUMN `assistand_manager_id` `assistant_manager_id` INT NOT NULL DEFAULT 0;

-- ALTER TABLE `o_permissions`
-- ADD COLUMN `block_` int NOT NULL DEFAULT 0 AFTER `delete_`;
--
-- ALTER TABLE `o_permissions`
-- ADD COLUMN `unblock_` int NOT NULL DEFAULT 0 AFTER `block_`;

ALTER TABLE `o_customer_groups` ADD `chair_name` VARCHAR(50) NOT NULL AFTER `group_description`;

ALTER TABLE `o_customer_groups` ADD `group_phone` VARCHAR(15) NOT NULL AFTER `group_description`, ADD `till` VARCHAR(10) NOT NULL AFTER `group_phone`, ADD `account_number` VARCHAR(10) NOT NULL AFTER `till`;




CREATE TABLE `o_b2b_queues` ( `uid` INT(10) NOT NULL AUTO_INCREMENT , `loan_id` INT(10) NOT NULL , `amount` DOUBLE(50,2) NOT NULL , `added_date` DATETIME NOT NULL , `sent_date` DATETIME NULL , `feedback_code` VARCHAR(30) NULL , `trials` INT(1) NOT NULL , `short_code` VARCHAR(15) NOT NULL , `status` INT(1) NOT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;
INSERT INTO `o_staff_statuses` (`name`, `color`) VALUES ('Disabled', 'bg-red');

ALTER TABLE `o_loans`
ADD COLUMN `with_ni_validation` tinyint DEFAULT 1 AFTER `disbursed`;

CREATE TABLE `o_npl_categories` (
  `uid` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(20) DEFAULT NULL,
  `status` int(1) DEFAULT 1
);

INSERT INTO `o_npl_categories` (`uid`, `title`, `description`, `icon`, `status`) VALUES
(1, 'In court', NULL, NULL, 1),
(2, 'Repossessed', NULL, NULL, 1),
(3, 'Relocated both home and business', NULL, NULL, 1),
(4, 'Staff fraudulent', NULL, NULL, 1),
(5, 'Deceased client', NULL, NULL, 1);

ALTER TABLE `o_npl_categories`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `title` (`title`);


ALTER TABLE `o_npl_categories`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;


ALTER TABLE o_loans
ADD COLUMN npl_uid INT DEFAULT 0 AFTER other_info;

ALTER TABLE `o_permissions` ADD `custom_action` VARCHAR(40) NOT NULL COMMENT 'An action that is not CRUD' AFTER `delete_`, ADD `added_by` INT NOT NULL AFTER `custom_action`, ADD `last_updated_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `added_by`, ADD `status` INT NOT NULL AFTER `last_updated_date`;

ALTER TABLE `o_users` ADD `otp_` VARCHAR(10) NOT NULL AFTER `pass1`, ADD `login_trials` INT NOT NULL DEFAULT 0 AFTER `otp_`;

CREATE TABLE `o_digivas_credentials` (`uid` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                         `key_` varchar(20) NOT NULL,
                                         `value_` varchar(250) NOT NULL,
                                         `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `o_report_summary_monthly` (`uid` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(50) NOT NULL , `tbl_` VARCHAR(30) NOT NULL , `fld_` VARCHAR(30) NOT NULL , `val_` VARCHAR(10) NOT NULL , `foreign_key` INT NOT NULL, `start_date` DATE NOT NULL , `end_date` DATE NOT NULL, UNIQUE (tbl_, fld_, val_, foreign_key, start_date, end_date), `last_update` DATETIME NOT NULL , `amount_` DOUBLE NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;

CREATE TABLE `o_digivas_credentials` (`uid` INT NOT NULL AUTO_INCREMENT , `key_` VARCHAR(20) UNIQUE NOT NULL , `value_` VARCHAR(250) NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;
ALTER TABLE `o_customer_groups` 
ADD COLUMN `branch` int AFTER `group_name`;

ALTER TABLE o_mpesa_configs
ADD COLUMN security_credential mediumtext DEFAULT NULL AFTER property_value,
ADD COLUMN consumer_key varchar(255) DEFAULT NULL AFTER security_credential,
ADD COLUMN consumer_secret varchar(255) DEFAULT NULL AFTER consumer_key,
ADD COLUMN initiator_name varchar(255) DEFAULT NULL AFTER consumer_secret,
ADD COLUMN enc_token mediumtext DEFAULT NULL AFTER initiator_name,
ADD COLUMN enc_token_key mediumtext DEFAULT NULL AFTER enc_token;


ALTER TABLE `o_sms_settings`
ADD COLUMN `short_code` MEDIUMTEXT DEFAULT NULL AFTER `property_value`,
ADD COLUMN `short_code_username` MEDIUMTEXT DEFAULT NULL AFTER `property_value`,
ADD COLUMN `aft_2way_key` MEDIUMTEXT DEFAULT NULL AFTER `short_code_username`,
ADD COLUMN `aft_2way_keyword` MEDIUMTEXT DEFAULT NULL AFTER `aft_2way_key`;

CREATE TABLE `o_report_summary_monthly` (`uid` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(50) NOT NULL , `tbl_` VARCHAR(30) NOT NULL , `fld_` VARCHAR(30) NOT NULL , `val_` VARCHAR(10) NOT NULL , `foreign_key` INT NOT NULL, `start_date` DATE NOT NULL , `end_date` DATE NOT NULL, UNIQUE (tbl_, fld_, val_, foreign_key, start_date, end_date), `last_update` DATETIME NOT NULL , `amount_` DOUBLE NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;


ALTER TABLE `o_customer_conversations`
ADD COLUMN `call_duration` int DEFAULT NULL AFTER `outcome`,
ADD COLUMN `call_direction` varchar(50) DEFAULT NULL AFTER `call_duration`,
ADD COLUMN `recording_url` mediumtext DEFAULT NULL AFTER `call_direction`,
ADD COLUMN `hangup_cause_reason` mediumtext DEFAULT NULL AFTER `recording_url`;

-- find out non-unique combination of customer_id and added_date --

-- add unique key to combination of customer_id and added_date --
ALTER TABLE `o_loans`
ADD UNIQUE KEY `unique_customer_added_date` (`customer_id`, `added_date`);

-- column to keep track of cc vintages that should be omitted from syncing --
ALTER TABLE `o_loans` 
ADD COLUMN `cc_syncing_omit` tinyint(1) DEFAULT 0 AFTER `npl_uid`;


ALTER TABLE `o_sms_settings`
ADD COLUMN `bulk_code` mediumtext DEFAULT NULL AFTER `aft_2way_keyword`,
ADD COLUMN `bulk_code_username` mediumtext DEFAULT NULL AFTER `bulk_code`,
ADD COLUMN `after_bulk_key` mediumtext DEFAULT NULL AFTER `bulk_code_username`;

CREATE TABLE `o_days` (
  `uid` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `status` TINYINT DEFAULT 1,
  PRIMARY KEY (`uid`)
);

-- Insert the provided data into the `days` table --
INSERT INTO `o_days` (`uid`, `name`, `status`) VALUES
(1, 'Sunday', 1),
(2, 'Monday', 1),
(3, 'Tuesday', 1),
(4, 'Wednesday', 1),
(5, 'Thursday', 1),
(6, 'Friday', 1),
(7, 'Saturday', 1);

ALTER TABLE `o_events`
MODIFY `event_details` MEDIUMTEXT NOT NULL;


CREATE TABLE `o_call_logs` (`uid` INT NOT NULL AUTO_INCREMENT , `agent_id` INT NOT NULL , `agent_phone` VARCHAR(15) NOT NULL , `client_id` INT NOT NULL , `client_phone` VARCHAR(15) NOT NULL , `initiated_date` DATETIME NOT NULL , `call_direction` INT NOT NULL COMMENT '1-incoming, 2-Outgoing' , `session_id` VARCHAR(30) NOT NULL , `dtmf_digits` INT NOT NULL , `recording_url` VARCHAR(50) NOT NULL , `duration_seconds` INT NOT NULL , `amount_charged` DOUBLE(10,2) NOT NULL , `result` VARCHAR(20) NOT NULL COMMENT 'INITIATED, RECEIVED, TALKED, COMPLETED', `status` INT NOT NULL , PRIMARY KEY (`uid`), UNIQUE (`session_id`)) ENGINE = InnoDB;

CREATE TABLE `o_team_leaders` (`uid` INT NOT NULL AUTO_INCREMENT , `leader_id` INT NOT NULL , `agent_id` INT NOT NULL , `added_by` INT NOT NULL , `added_date` DATETIME NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;


ALTER TABLE `o_branches` 
ADD COLUMN `freeze` VARCHAR(20) DEFAULT 'NONE' AFTER `region_id`;

ALTER TABLE `o_loans` 
ADD COLUMN `addons_count` int DEFAULT 0 AFTER `total_addons`;

ALTER TABLE `o_loans` 
ADD COLUMN `deductions_count` int DEFAULT 0 AFTER `total_deductions`;

ALTER TABLE `o_deductions` 
ADD COLUMN `deduction_on` VARCHAR(45) DEFAULT NULL AFTER `automatic`;

ALTER TABLE `o_loans` 
ADD COLUMN `cleared_date` datetime DEFAULT NULL AFTER `final_due_date`;



CREATE TABLE `o_conversation_purpose` (`uid` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(90) NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`), UNIQUE (`name`)) ENGINE = InnoDB;
ALTER TABLE `o_customer_conversations` ADD `promised_amount` DOUBLE(10,2) NOT NULL AFTER `next_steps`;
ALTER TABLE `o_customer_conversations` ADD `conversation_purpose` INT NOT NULL COMMENT 'From o_conversation_purpose' AFTER `promised_amount`;


ALTER TABLE `o_forms` ADD `product_id` INT NOT NULL DEFAULT 0 AFTER `form_name`;

ALTER TABLE `o_campaigns` ADD `ending_date` DATETIME NOT NULL AFTER `running_date`;

ALTER TABLE `o_campaigns` ADD `waiver_addon` INT NOT NULL AFTER `added_by`, ADD `waiver_amount` INT NOT NULL AFTER `waiver_addon`, ADD `waiver_deduction` INT NOT NULL AFTER `waiver_amount`;

CREATE TABLE `o_product_forms` (`uid` INT NOT NULL AUTO_INCREMENT , `product_id` INT NOT NULL , `form_id` INT NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;
INSERT INTO `o_conversation_outcome` (`uid`,`name`,`details`,`status`) VALUES (1,'Not Found On Call','fa-ban',1);
INSERT INTO `o_conversation_outcome` (`uid`,`name`,`details`,`status`) VALUES (2,'Repayment Plan','fa-clock-o',1);

-- 2024-09-12 --

INSERT INTO `o_conversation_purpose` (`uid`,`name`,`status`) VALUES (1,'Customer reactivation/follow up',1);
INSERT INTO `o_conversation_purpose` (`uid`,`name`,`status`) VALUES (2,'Customer Follow up',1);
INSERT INTO `o_conversation_purpose` (`uid`,`name`,`status`) VALUES (3,'Promise to pay',1);
INSERT INTO `o_conversation_purpose` (`uid`,`name`,`status`) VALUES (4,'Spoof calling',1);
INSERT INTO `o_conversation_purpose` (`uid`,`name`,`status`) VALUES (5,'Surprise visit',1);
INSERT INTO `o_conversation_purpose` (`uid`,`name`,`status`) VALUES (6,'Collateral list confirmation',1);

INSERT INTO `o_next_steps` (`uid`,`name`,`details`,`status`) VALUES (1,'Pay Visit','#788888',1);
INSERT INTO `o_next_steps` (`uid`,`name`,`details`,`status`) VALUES (2,'Reposession','#FF0000',1);
INSERT INTO `o_next_steps` (`uid`,`name`,`details`,`status`) VALUES (3,'Call again','#333333',1);
INSERT INTO `o_next_steps` (`uid`,`name`,`details`,`status`) VALUES (4,'Call Referees','#333333',1);
INSERT INTO `o_next_steps` (`uid`,`name`,`details`,`status`) VALUES (5,'Move to another collector','#333333',1);
INSERT INTO `o_next_steps` (`uid`,`name`,`details`,`status`) VALUES (6,'Write off','#333333',1);
INSERT INTO `o_next_steps` (`uid`,`name`,`details`,`status`) VALUES (7,'Other','#333333',1);

ALTER TABLE `o_campaigns` CHANGE `ending_date` `ending_date` INT(11) NULL DEFAULT NULL;
ALTER TABLE `o_campaigns` CHANGE `waiver_addon` `waiver_addon` INT(11) NULL DEFAULT NULL;
ALTER TABLE `o_campaigns` CHANGE `waiver_amount` `waiver_amount` INT(11) NULL DEFAULT NULL;
ALTER TABLE `o_campaigns` CHANGE `waiver_deduction` `waiver_deduction` INT(11) NULL DEFAULT NULL;
ALTER TABLE `o_loans` CHANGE `loan_code` `loan_code` VARCHAR(36) NULL DEFAULT NULL COMMENT 'Custom loan code';
ALTER TABLE `o_customers` ADD UNIQUE(`national_id`);
ALTER TABLE `o_customers` CHANGE `national_id` `national_id` VARCHAR(10) DEFAULT NULL;

ALTER TABLE `o_incoming_payments` ADD `other_info` JSON NULL AFTER `comments`;


ALTER TABLE `o_customers` ADD INDEX(`full_name`);

-- 11th Aug 2023 --
CREATE TABLE `o_badges` ( `uid` INT NOT NULL AUTO_INCREMENT ,  `title` VARCHAR(20) NOT NULL ,  `description` VARCHAR(100) NOT NULL ,  `icon` VARCHAR(20) NOT NULL ,  `status` INT(1) NOT NULL ,    PRIMARY KEY  (`uid`)) ENGINE = InnoDB;


INSERT INTO `o_badges` (`title`, `description`, `icon`, `status`) VALUES ('10 Loans', 'Client has taken and repaid 10 Loans', '10.png', '1');
INSERT INTO `o_badges` (`title`, `description`, `icon`, `status`) VALUES ('Good client', 'Client has proven to be a valuable part of the business', 'badge.png', '1');
INSERT INTO `o_badges` (`title`, `description`, `icon`, `status`) VALUES ('BLC', 'This is a bad luck customer', 'caution.png', '1');
INSERT INTO `o_badges` (`title`, `description`, `icon`, `status`) VALUES ('Platinum Member', 'This client has brought has brought a lot of revenue to the business', 'gem.png', '1');
INSERT INTO `o_badges` (`title`, `description`, `icon`, `status`) VALUES ('Attention', 'This client needs attention', 'info.png', '1');
INSERT INTO `o_badges` (`title`, `description`, `icon`, `status`) VALUES ('New', 'This is a new customer', 'new.png', '1');
INSERT INTO `o_badges` (`title`, `description`, `icon`, `status`) VALUES ('Top Client', 'This is one of your top client in terms of loans taken, time they have been with you money made', 'top.png', '1');
INSERT INTO `o_badges` (`title`, `description`, `icon`, `status`) VALUES ('BFC', 'This is a bad faith customer', 'warning.png', '1');
INSERT INTO `o_badges` (`title`, `description`, `icon`, `status`) VALUES ('Visit', 'The client needs a visit', 'map.png', '1');

ALTER TABLE `o_customers`  ADD `badge_id` INT(1) NOT NULL DEFAULT 0  AFTER `total_loans`;
ALTER TABLE `o_addons` ADD `deducted_upfront` INT NOT NULL DEFAULT 0 AFTER `paid_upfront`;

ALTER TABLE `o_reports` ADD UNIQUE(`title`);

ALTER TABLE `o_customer_conversations` ADD `default_reason` INT NOT NULL COMMENT 'From o_default_reasons' AFTER `conversation_purpose`;

CREATE TABLE `o_default_reasons` (`uid` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(75) NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`), UNIQUE (`name`)) ENGINE = InnoDB;
INSERT INTO `o_default_reasons` (`uid`, `name`, `status`) VALUES (NULL, 'Illness', '1'), (NULL, 'Overborrowing', '1'),  (NULL, 'Poor Sales', '1'), (NULL, 'Delayed Payments', '1'), (NULL, 'Business/Work Relocation', '1'), (NULL, 'Bad Faith', '1'), (NULL, 'Other', '1');
 -- 26th Sep 2024
CREATE TABLE `o_expenses` (`uid` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(70) NOT NULL , `description` MEDIUMTEXT NOT NULL , `category` INT NOT NULL DEFAULT 0 , `expense_date` DATE NOT NULL , `branch_id` INT NOT NULL DEFAULT 0 , `product_id` INT NOT NULL DEFAULT 0 , `amount` DOUBLE NOT NULL DEFAULT '0.00' , `added_by` INT NOT NULL DEFAULT 0 , `approved_by` INT NOT NULL DEFAULT 0 , `added_date` DATETIME NOT NULL , `recurring_expense` INT NOT NULL DEFAULT 0 ,`status` INT NOT NULL DEFAULT 0 , PRIMARY KEY (`uid`)) ENGINE = InnoDB;
CREATE TABLE `o_expense_categories` (`uid` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(30) NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`), UNIQUE `expense_category_name` (`name`)) ENGINE = InnoDB;



-- 29 Oct 2024 --
CREATE TABLE `o_ai_conversations` (`uid` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `session_id` VARCHAR(20) NOT NULL , `ask_date` DATETIME NOT NULL , `answer_date` DATETIME NULL , `question` TEXT NOT NULL , `full_response` TEXT NULL , `sql_response` TEXT NULL , `result` VARCHAR(25) NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;

-- 2024-11-06 --
ALTER TABLE `o_customer_conversations`
ADD COLUMN `conversation_date_rm_time` DATE GENERATED ALWAYS AS (DATE(conversation_date)) STORED AFTER `conversation_date`,
ADD INDEX `idx_conversation_date_rm_time` (`conversation_date_rm_time`);

-- 2024-11-15 --
ALTER TABLE `o_customer_groups`
  ADD COLUMN `meeting_day` tinyint(3) UNSIGNED DEFAULT NULL AFTER `chair_name`,
  ADD COLUMN `meeting_time` varchar(10) DEFAULT NULL AFTER `meeting_day`,
  ADD COLUMN `meeting_venue` varchar(255) DEFAULT NULL AFTER `meeting_time`;

ALTER TABLE `o_call_logs`
    CHANGE `session_id` `session_id` VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    CHANGE `recording_url` `recording_url` VARCHAR(240) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

CREATE TABLE `o_company_docs` (`uid` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(100) NOT NULL , `description` VARCHAR(250) NOT NULL , `added_date` DATETIME NOT NULL , `doc_url` VARCHAR(200) NOT NULL , `downloadable` INT NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`uid`)) ENGINE = InnoDB;
INSERT INTO `o_badges` (`uid`, `title`, `description`, `icon`, `status`) VALUES (NULL, 'Defaulter', 'This client has defaulted', 'warning.png', '1');

INSERT INTO o_expense_categories (name, status) VALUES
                                                    ('Airtime', 1),
                                                    ('Rent', 1),
                                                    ('Internet', 1),
                                                    ('Electricity', 1),
                                                    ('Marketing', 1),
                                                    ('Transport/Logistics', 1),
                                                    ('Salaries and Wages', 1),
                                                    ('Stationary and Printing', 1),
                                                    ('Mobile Phones and Simcards', 1),
                                                    ('Legal Fees', 1),
                                                    ('MPESA and Bank Charges', 1),
                                                    ('Furniture and Fittings', 1),
                                                    ('Computer Repairs and Purchases', 1),
                                                    ('Licences and Permits', 1),
                                                    ('Bonuses', 1),
                                                    ('Cleaning and Waste Management', 1),
                                                    ('Staff Training', 1),
                                                    ('IT Expenses', 1),
                                                    ('Other', 1);


-- Now add the unique constraint ON lona_id alone
ALTER TABLE `o_mpesa_queues`
ADD CONSTRAINT `uq_loan_id` UNIQUE (`loan_id`);

-- 2025-02-13
CREATE TABLE o_customer_dormancy (
    uid INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    dormant_date DATE NOT NULL,
    reactivation_date DATE NULL,
    created_by int DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `status` INT DEFAULT 1,
    KEY customer_id (customer_id),
    KEY dormant_date (dormant_date)
);

-- Meant to solve problem of invalid json on trying to use sec_data column
ALTER TABLE `o_customers` 
ADD COLUMN `other_info` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin AFTER `sec_data`;

-- 2024-02-19
ALTER TABLE `o_users` 
ADD COLUMN `two_fa_enforced` TINYINT NOT NULL DEFAULT 0 
AFTER `otp_`;

ALTER TABLE o_users 
MODIFY COLUMN `otp_` VARCHAR(40) COLLATE utf8mb4_general_ci NULL DEFAULT 0;

ALTER TABLE `o_users` CHANGE `otp_` `otp_` VARCHAR(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE o_campaign_messages 
ADD COLUMN type VARCHAR(50) NOT NULL DEFAULT 'GENERAL' 
AFTER message 
COMMENT 'GENERAL, PERSONALIZED';

CREATE TABLE `o_spin_scoring` (
  `uid` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,
  `doc` MEDIUMTEXT NOT NULL,
  `doc_reference_id` VARCHAR(50) NOT NULL,
  `result` LONGTEXT DEFAULT NULL,
  `spin_type` VARCHAR(20) DEFAULT NULL COMMENT 'INDIVIDUAL,COMBINED',
  `score_type` VARCHAR(20) NOT NULL DEFAULT 'MPESA' COMMENT 'MPESA,BANK,TILL,PAYBILL',
  `spin_status` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '1 => Processing, 2 => Completed, 3 => Failed',
  `record_status` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '0 => Soft Deleted, 1 => Active',
  `processed_date` DATETIME DEFAULT NULL,
  `added_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `added_by` INT(11) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `doc_reference_id` (`doc_reference_id`),
  KEY `idx_doc_reference_id` (`doc_reference_id`),
  KEY `idx_added_date` (`added_date`),
  KEY `idx_processed_date` (`processed_date`),
  KEY `idx_spin_status` (`spin_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `o_customer_conversations` ADD `tag` VARCHAR(50) NULL AFTER `flag`;



INSERT INTO `o_loan_statuses` (`uid`, `name`, `active_loan`, `description`, `color_code`, `status`) VALUES (24, 'Failed', 0, '', '#ff0000', '1');

ALTER TABLE `o_incoming_payments` 
ADD COLUMN `updated_date` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `recorded_date`;

ALTER TABLE `o_customers` CHANGE `pin_` `pin_` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `o_spin_scoring`
ADD COLUMN `decrypter` varchar(50) COLLATE utf8mb4_general_ci AFTER `doc`;

ALTER TABLE `o_users` ADD `passport_photo` VARCHAR(100) NULL AFTER `name`;
ALTER TABLE `o_users` ADD `pass_expiry` DATE NOT NULL DEFAULT (CURRENT_DATE) AFTER `pass1`;


-- 2025-04-23
CREATE TABLE o_allocation_history (
    uid INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    allocation_type VARCHAR(20) NOT NULL,
    allocation_date DATE DEFAULT (CURRENT_DATE),
    allocation_datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
    allocation_amount DECIMAL(10,2) DEFAULT 0,
    allocated_agent INT DEFAULT NULL,
    status TINYINT DEFAULT 1,
    
    UNIQUE KEY uniq_loan_date_agent (loan_id, allocation_date, allocated_agent),
    INDEX idx_loan_id (loan_id),
    INDEX idx_allocation_type (allocation_type),
    INDEX idx_allocated_agent (allocated_agent)
);

