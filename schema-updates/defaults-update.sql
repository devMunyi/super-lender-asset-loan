-- Troublesome columns that needs default values

ALTER TABLE `o_customer_conversations` CHANGE `next_interaction` `next_interaction` DATETIME NULL DEFAULT NULL;
ALTER TABLE `o_customers` CHANGE `passport_photo` `passport_photo` VARCHAR(250) DEFAULT NULL;
ALTER TABLE `o_customers` CHANGE `events` `events` MEDIUMTEXT DEFAULT NULL;
ALTER TABLE `o_customers` CHANGE `dob` `dob` DATE NULL DEFAULT NULL;
ALTER TABLE `o_loans` CHANGE `total_repayable_amount` `total_repayable_amount` DECIMAL(50, 2) DEFAULT 0.00;
ALTER TABLE `o_loans` CHANGE `total_repaid` `total_repaid` DECIMAL(50, 2) DEFAULT 0.00;
ALTER TABLE `o_loans` CHANGE `loan_balance` `loan_balance` DECIMAL(50, 2) DEFAULT 0.00;
ALTER TABLE `o_loans` CHANGE `total_addons` `total_addons` DECIMAL(50, 2) DEFAULT 0.00;
ALTER TABLE `o_loans` CHANGE `total_deductions` `total_deductions` DECIMAL(50, 2) DEFAULT 0.00;
ALTER TABLE `o_loans` CHANGE `current_agent` `current_agent` INT(11) DEFAULT 0;
ALTER TABLE `o_loans` CHANGE `loan_flag` `loan_flag` INT(11) DEFAULT 0 COMMENT 'from o_flags';
ALTER TABLE `o_loans` CHANGE `cleared_date` `cleared_date` datetime DEFAULT NULL;
ALTER TABLE `o_loans` CHANGE `transaction_code` `transaction_code` VARCHAR(40) DEFAULT NULL;
ALTER TABLE `o_loans` CHANGE `transaction_date` `transaction_date` DATETIME NULL DEFAULT NULL;