
DROP TABLE IF EXISTS `o_addons`;
CREATE TABLE `o_addons` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `amount` double(50,2) NOT NULL,
  `amount_type` varchar(20) NOT NULL COMMENT 'PERCENTAGE, FIXED',
  `loan_stage` varchar(50) NOT NULL,
  `automatic` int NOT NULL COMMENT 'Applied automatically on loan creation',
  `addon_on` varchar(45) DEFAULT NULL COMMENT 'The field name on o_loans',
  `from_day` int DEFAULT NULL,
  `to_day` int DEFAULT NULL,
  `apply_frequency` varchar(15) DEFAULT 'ONCE' COMMENT 'ONCE, DAILY, WEEKLY, BYWEEKLY, MONTHLY, YEARLY',
  `notify_user` int DEFAULT NULL,
  `applicable_loan` varchar(45) DEFAULT NULL COMMENT 'comma separated e.g. 1,2,3',
  `paid_upfront` int DEFAULT '0',
  `deducted_upfront` int NOT NULL DEFAULT '0',
  `addon_category` varchar(45) DEFAULT NULL COMMENT 'INTEREST, PENALTY',
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_archives`
--

DROP TABLE IF EXISTS `o_archives`;
CREATE TABLE `o_archives` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `key` varchar(45) NOT NULL,
  `value` varchar(145) DEFAULT NULL,
  `added_date` datetime DEFAULT NULL,
  `added_by` int DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_asset_categories`
--

DROP TABLE IF EXISTS `o_asset_categories`;
CREATE TABLE `o_asset_categories` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name_` (`name`)
);

--
-- Table structure for table `o_assets`
--

DROP TABLE IF EXISTS `o_assets`;
CREATE TABLE `o_assets` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `description` varchar(250) NOT NULL,
  `category_` int NOT NULL,
  `added_by` int NOT NULL,
  `added_date` datetime NOT NULL,
  `buying_price` double NOT NULL,
  `selling_price` double NOT NULL,
  `photo` varchar(50) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_b2b_queues`
--

DROP TABLE IF EXISTS `o_b2b_queues`;
CREATE TABLE `o_b2b_queues` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `loan_id` int NOT NULL,
  `amount` double(50,2) NOT NULL,
  `added_date` datetime NOT NULL,
  `sent_date` datetime DEFAULT NULL,
  `feedback_code` varchar(30) DEFAULT NULL,
  `trials` int NOT NULL,
  `short_code` varchar(15) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_badges`
--

DROP TABLE IF EXISTS `o_badges`;
CREATE TABLE `o_badges` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  `icon` varchar(20) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_branch_statuses`
--

DROP TABLE IF EXISTS `o_branch_statuses`;
CREATE TABLE `o_branch_statuses` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `color` varchar(50) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_branches`
--

DROP TABLE IF EXISTS `o_branches`;
CREATE TABLE `o_branches` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `added_date` datetime NOT NULL,
  `manager_id` int DEFAULT '0',
  `assistant_manager_id` int NOT NULL DEFAULT '0',
  `address` text,
  `region_id` int DEFAULT '0',
  `freeze` varchar(20) DEFAULT 'NONE',
  `status` int DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `branch_name` (`name`)
);

--
-- Table structure for table `o_call_logs`
--

DROP TABLE IF EXISTS `o_call_logs`;
CREATE TABLE `o_call_logs` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `agent_id` int NOT NULL,
  `agent_phone` varchar(15) NOT NULL,
  `client_id` int NOT NULL,
  `client_phone` varchar(15) NOT NULL,
  `initiated_date` datetime NOT NULL,
  `call_direction` int NOT NULL COMMENT '1-incoming, 2-Outgoing',
  `session_id` varchar(30) NOT NULL,
  `dtmf_digits` int NOT NULL,
  `recording_url` varchar(50) NOT NULL,
  `duration_seconds` int NOT NULL,
  `amount_charged` double(10,2) NOT NULL,
  `result` varchar(20) NOT NULL COMMENT 'INITIATED, RECEIVED, TALKED, COMPLETED',
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `session_id` (`session_id`)
);

--
-- Table structure for table `o_campaign_frequencies`
--

DROP TABLE IF EXISTS `o_campaign_frequencies`;
CREATE TABLE `o_campaign_frequencies` (
  `uid` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` int NOT NULL
);

--
-- Table structure for table `o_campaign_messages`
--

DROP TABLE IF EXISTS `o_campaign_messages`;
CREATE TABLE `o_campaign_messages` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `campaign_id` int NOT NULL,
  `message` text NOT NULL,
  `added_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `added_by` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_campaign_running_statuses`
--

DROP TABLE IF EXISTS `o_campaign_running_statuses`;
CREATE TABLE `o_campaign_running_statuses` (
  `uid` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `color_code` varchar(50) NOT NULL,
  `status` int NOT NULL
);

--
-- Table structure for table `o_campaign_statuses`
--

DROP TABLE IF EXISTS `o_campaign_statuses`;
CREATE TABLE `o_campaign_statuses` (
  `uid` int NOT NULL,
  `code` int NOT NULL,
  `name` varchar(30) NOT NULL,
  `color` varchar(10) DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1'
);

--
-- Table structure for table `o_campaign_target_customers`
--

DROP TABLE IF EXISTS `o_campaign_target_customers`;
CREATE TABLE `o_campaign_target_customers` (
  `uid` int NOT NULL,
  `name` varchar(40) NOT NULL,
  `description` varchar(250) NOT NULL,
  `custom_query` text NOT NULL,
  `status` int NOT NULL
);

--
-- Table structure for table `o_campaigns`
--

DROP TABLE IF EXISTS `o_campaigns`;
CREATE TABLE `o_campaigns` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  `running_date` datetime NOT NULL,
  `ending_date` datetime NOT NULL,
  `running_status` int NOT NULL DEFAULT '1',
  `frequency` int NOT NULL,
  `repetitive` int NOT NULL,
  `target_customers` varchar(50) NOT NULL,
  `total_customers` int DEFAULT NULL,
  `added_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `added_by` int NOT NULL,
  `waiver_addon` int NOT NULL,
  `waiver_amount` int NOT NULL,
  `waiver_deduction` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_campaigns_repetition_status`
--

DROP TABLE IF EXISTS `o_campaigns_repetition_status`;
CREATE TABLE `o_campaigns_repetition_status` (
  `uid` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` int NOT NULL
);

--
-- Table structure for table `o_changelog`
--

DROP TABLE IF EXISTS `o_changelog`;
CREATE TABLE `o_changelog` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `tbl_` varchar(45) DEFAULT NULL,
  `query_` text NOT NULL,
  `change_by` int DEFAULT NULL,
  `change_date` datetime DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_collateral`
--

DROP TABLE IF EXISTS `o_collateral`;
CREATE TABLE `o_collateral` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL COMMENT 'From o_customers table',
  `category` int NOT NULL COMMENT 'From o_asset_categories',
  `title` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  `money_value` double(50,2) DEFAULT NULL,
  `document_scan_address` varchar(10) NOT NULL COMMENT 'From o_documents',
  `doc_reference_no` varchar(100) NOT NULL COMMENT 'A unique number to identify the document',
  `filling_reference_no` varchar(100) NOT NULL COMMENT 'If a physical document is kept, the address of the file in filling cabinet',
  `added_date` datetime NOT NULL,
  `added_by` int NOT NULL,
  `loan_id` int NOT NULL,
  `status` int NOT NULL COMMENT 'From o_collateral_statuses',
  UNIQUE KEY `collateral_id` (`uid`),
  KEY `idx_o_collateral_customer_id` (`customer_id`),
  KEY `idx_o_collateral_category` (`category`)
);

--
-- Table structure for table `o_collateral_statuses`
--

DROP TABLE IF EXISTS `o_collateral_statuses`;
CREATE TABLE `o_collateral_statuses` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
) COMMENT='The status of collateral items e.g. ';

--
-- Table structure for table `o_contact_types`
--

DROP TABLE IF EXISTS `o_contact_types`;
CREATE TABLE `o_contact_types` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'e.g. Alternative Phone',
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_conversation_methods`
--

DROP TABLE IF EXISTS `o_conversation_methods`;
CREATE TABLE `o_conversation_methods` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `details` varchar(100) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_conversation_outcome`
--

DROP TABLE IF EXISTS `o_conversation_outcome`;
CREATE TABLE `o_conversation_outcome` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `details` varchar(30) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_conversation_purpose`
--

DROP TABLE IF EXISTS `o_conversation_purpose`;
CREATE TABLE `o_conversation_purpose` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(90) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_customer_contacts`
--

DROP TABLE IF EXISTS `o_customer_contacts`;
CREATE TABLE `o_customer_contacts` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `contact_type` int NOT NULL COMMENT 'From o_contact_types table',
  `value` varchar(250) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `idx_o_customer_contacts_customer_id` (`customer_id`),
  KEY `idx_o_customer_contacts_value` (`value`)
);

--
-- Table structure for table `o_customer_conversations`
--

DROP TABLE IF EXISTS `o_customer_conversations`;
CREATE TABLE `o_customer_conversations` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `branch` int DEFAULT '0',
  `agent_id` int NOT NULL,
  `loan_id` int NOT NULL,
  `transcript` text NOT NULL,
  `conversation_method` int NOT NULL COMMENT 'From o_conversation_methods',
  `conversation_date` datetime NOT NULL,
  `next_interaction` date DEFAULT NULL,
  `next_steps` int NOT NULL COMMENT 'From o_next_steps',
  `promised_amount` double(10,2) NOT NULL,
  `conversation_purpose` int NOT NULL COMMENT 'From o_conversation_purpose',
  `default_reason` int NOT NULL COMMENT 'From o_default_reasons',
  `flag` int NOT NULL,
  `outcome` int NOT NULL,
  `call_duration` int DEFAULT NULL,
  `call_direction` varchar(50) DEFAULT NULL,
  `recording_url` mediumtext,
  `hangup_cause_reason` mediumtext,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `idx_o_customer_conversations_customer_id` (`customer_id`),
  KEY `idx_o_customer_conversations_branch` (`branch`),
  KEY `idx_o_customer_conversations_agent_id` (`agent_id`),
  KEY `idx_o_customer_conversations_loan_id` (`loan_id`),
  KEY `idx_o_customer_conversations_conversation_date` (`conversation_date`),
  KEY `idx_o_customer_conversations_next_interaction` (`next_interaction`),
  KEY `idx_o_customer_conversations_flag` (`flag`)
);

--
-- Table structure for table `o_customer_document_categories`
--

DROP TABLE IF EXISTS `o_customer_document_categories`;
CREATE TABLE `o_customer_document_categories` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `formats` varchar(250) NOT NULL COMMENT 'comma delimited extensions',
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_customer_groups`
--

DROP TABLE IF EXISTS `o_customer_groups`;
CREATE TABLE `o_customer_groups` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) NOT NULL,
  `branch` int DEFAULT NULL,
  `group_description` varchar(250) NOT NULL,
  `group_phone` varchar(15) NOT NULL,
  `till` varchar(10) NOT NULL,
  `account_number` varchar(10) NOT NULL,
  `chair_name` varchar(50) NOT NULL,
  `meeting_day` tinyint unsigned DEFAULT NULL,
  `meeting_time` varchar(10) DEFAULT NULL,
  `meeting_venue` varchar(255) DEFAULT NULL,
  `added_date` datetime NOT NULL,
  `added_by` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `group_name` (`group_name`)
);

--
-- Table structure for table `o_customer_limits`
--

DROP TABLE IF EXISTS `o_customer_limits`;
CREATE TABLE `o_customer_limits` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `customer_uid` int NOT NULL,
  `amount` double(10,2) NOT NULL,
  `given_date` datetime DEFAULT NULL,
  `given_by` int DEFAULT NULL,
  `comments` varchar(245) DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `idx_customer_uid` (`customer_uid`),
  KEY `idx_o_customer_limits_status` (`status`)
);

--
-- Table structure for table `o_customer_referee_relationships`
--

DROP TABLE IF EXISTS `o_customer_referee_relationships`;
CREATE TABLE `o_customer_referee_relationships` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_customer_referees`
--

DROP TABLE IF EXISTS `o_customer_referees`;
CREATE TABLE `o_customer_referees` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `added_date` datetime NOT NULL,
  `referee_name` varchar(50) NOT NULL,
  `id_no` varchar(15) NOT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `physical_address` varchar(145) DEFAULT NULL,
  `email_address` varchar(50) NOT NULL,
  `relationship` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `idx_o_customer_referees_customer_id` (`customer_id`),
  KEY `idx_o_customer_referees_id_no` (`id_no`),
  KEY `idx_o_customer_referees_mobile_no` (`mobile_no`),
  KEY `idx_o_customer_referees_referee_name` (`referee_name`)
);

--
-- Table structure for table `o_customer_sessions`
--

DROP TABLE IF EXISTS `o_customer_sessions`;
CREATE TABLE `o_customer_sessions` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `device_id` varchar(105) DEFAULT NULL,
  `session_code` varchar(105) DEFAULT NULL,
  `started_date` datetime DEFAULT NULL,
  `ending_date` datetime DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_customer_statuses`
--

DROP TABLE IF EXISTS `o_customer_statuses`;
CREATE TABLE `o_customer_statuses` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `code` int NOT NULL,
  `name` varchar(30) NOT NULL,
  `color` varchar(10) DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `name` (`name`)
) COMMENT='Store the statuses of customers';

--
-- Table structure for table `o_customers`
--

DROP TABLE IF EXISTS `o_customers`;
CREATE TABLE `o_customers` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(145) DEFAULT NULL COMMENT 'A unique customer identify apart from the UID',
  `full_name` varchar(100) NOT NULL,
  `primary_mobile` varchar(15) NOT NULL,
  `enc_phone` varchar(70) DEFAULT NULL,
  `phone_number_provider` int DEFAULT NULL,
  `email_address` varchar(60) DEFAULT NULL,
  `physical_address` text NOT NULL,
  `geolocation` text,
  `town` int DEFAULT NULL,
  `passport_photo` varchar(250) DEFAULT NULL,
  `national_id` varchar(10) NOT NULL,
  `gender` varchar(5) NOT NULL COMMENT 'M, F',
  `dob` date DEFAULT NULL,
  `added_by` int NOT NULL,
  `current_agent` int DEFAULT '0',
  `added_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `branch` int NOT NULL COMMENT 'From o_branches',
  `primary_product` int NOT NULL COMMENT 'From o_products',
  `loan_limit` double(100,2) NOT NULL DEFAULT '0.00',
  `events` mediumtext,
  `sec_data` json DEFAULT NULL,
  `pin_` varchar(55) DEFAULT NULL,
  `device_id` varchar(55) DEFAULT NULL,
  `flag` int DEFAULT NULL,
  `total_loans` int DEFAULT '0',
  `badge_id` int NOT NULL DEFAULT '0',
  `status` int NOT NULL COMMENT 'From o_customer_statuses',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `primary_phone_` (`primary_mobile`),
  UNIQUE KEY `national_id_` (`national_id`),
  KEY `idx_o_customers_full_name` (`full_name`),
  KEY `idx_o_customers_branch` (`branch`),
  KEY `idx_o_customers_current_agent` (`current_agent`),
  KEY `idx_o_customers_enc_phone` (`enc_phone`),
  KEY `idx_o_customers_email_address` (`email_address`),
  KEY `idx_o_customers_total_loans` (`total_loans`)
);

--
-- Table structure for table `o_days`
--

DROP TABLE IF EXISTS `o_days`;
CREATE TABLE `o_days` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `status` tinyint DEFAULT '1',
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_deductions`
--

DROP TABLE IF EXISTS `o_deductions`;
CREATE TABLE `o_deductions` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `amount` double(50,2) NOT NULL,
  `amount_type` varchar(50) NOT NULL COMMENT 'PERCENTAGE, FIXED',
  `loan_stage` varchar(50) NOT NULL,
  `automatic` int NOT NULL COMMENT 'Applied automatically on loan creation',
  `deduction_on` varchar(45) DEFAULT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_default_reasons`
--

DROP TABLE IF EXISTS `o_default_reasons`;
CREATE TABLE `o_default_reasons` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_disburse_methods`
--

DROP TABLE IF EXISTS `o_disburse_methods`;
CREATE TABLE `o_disburse_methods` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `via_api` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_documents`
--

DROP TABLE IF EXISTS `o_documents`;
CREATE TABLE `o_documents` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `code_name` varchar(50) NOT NULL COMMENT 'An easy way to identify documents e.g. NATIONAL_ID',
  `title` varchar(75) NOT NULL,
  `description` varchar(250) NOT NULL,
  `category` int NOT NULL,
  `added_by` int NOT NULL,
  `added_date` datetime NOT NULL,
  `tbl` varchar(50) NOT NULL COMMENT 'Table it belongs to e.g. o_customers',
  `rec` int NOT NULL COMMENT 'record it belongs to e.g. 56',
  `stored_address` varchar(250) NOT NULL COMMENT 'Localtion in the server where image is stored',
  `status` int NOT NULL COMMENT '0-unlinked, 1-available',
  PRIMARY KEY (`uid`),
  KEY `idx_o_documents_rec` (`rec`),
  KEY `idx_o_documents_tbl` (`tbl`),
  KEY `idx_o_documents_status` (`status`),
  KEY `idx_o_documents_category` (`category`)
);

--
-- Table structure for table `o_events`
--

DROP TABLE IF EXISTS `o_events`;
CREATE TABLE `o_events` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `tbl` varchar(30) NOT NULL,
  `fld` int NOT NULL,
  `event_details` mediumtext NOT NULL,
  `event_date` datetime NOT NULL,
  `event_date_rm_time` date GENERATED ALWAYS AS (cast(`event_date` as date)) STORED,
  `event_by` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `idx_event_by` (`event_by`),
  KEY `idx_o_events_fld` (`fld`),
  KEY `idx_o_events_tbl` (`tbl`),
  KEY `idx_o_events_event_date` (`event_date`)
) COMMENT='Store change occurrences';

--
-- Table structure for table `o_expense_categories`
--

DROP TABLE IF EXISTS `o_expense_categories`;
CREATE TABLE `o_expense_categories` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `expense_category_name` (`name`)
);

--
-- Table structure for table `o_expenses`
--

DROP TABLE IF EXISTS `o_expenses`;
CREATE TABLE `o_expenses` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `title` varchar(70) NOT NULL,
  `description` mediumtext NOT NULL,
  `category` int NOT NULL DEFAULT '0',
  `expense_date` date NOT NULL,
  `branch_id` int NOT NULL DEFAULT '0',
  `product_id` int NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `added_by` int NOT NULL DEFAULT '0',
  `approved_by` int NOT NULL DEFAULT '0',
  `added_date` datetime NOT NULL,
  `recurring_expense` int NOT NULL DEFAULT '0',
  `status` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_flags`
--

DROP TABLE IF EXISTS `o_flags`;
CREATE TABLE `o_flags` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` varchar(250) NOT NULL,
  `color_code` varchar(10) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_form_fields`
--

DROP TABLE IF EXISTS `o_form_fields`;
CREATE TABLE `o_form_fields` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `form_id` int NOT NULL COMMENT 'From o_forms',
  `tbl` varchar(30) NOT NULL,
  `field_name` varchar(80) NOT NULL,
  `field_type` varchar(50) NOT NULL,
  `field_options` text COMMENT 'A JSON with key-value pairs',
  `caption_` varchar(150) NOT NULL,
  `required_` int NOT NULL,
  `condition_` varchar(250) DEFAULT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `form_id` (`form_id`)
);

--
-- Table structure for table `o_forms`
--

DROP TABLE IF EXISTS `o_forms`;
CREATE TABLE `o_forms` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `tbl` varchar(40) NOT NULL,
  `form_name` varchar(75) NOT NULL,
  `product_id` int NOT NULL DEFAULT '0',
  `added_date` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_group_members`
--

DROP TABLE IF EXISTS `o_group_members`;
CREATE TABLE `o_group_members` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `added_date` datetime NOT NULL,
  `added_by` int NOT NULL,
  `loan_limit` double(50,2) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_guarantors`
--

DROP TABLE IF EXISTS `o_guarantors`;
CREATE TABLE `o_guarantors` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `national_id` varchar(15) NOT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `amount_guaranteed` double(100,2) NOT NULL,
  `added_date` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_incoming_payments`
--

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
  `record_method` varchar(50) NOT NULL COMMENT 'API, MANUAL',
  `comments` varchar(100) NOT NULL DEFAULT 'Repayment',
  `other_info` json DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `transaction_code` (`transaction_code`),
  KEY `idx_o_incoming_payments_customer_id` (`customer_id`),
  KEY `idx_o_incoming_payments_branch_id` (`branch_id`),
  KEY `idx_o_incoming_payments_payment_method` (`payment_method`),
  KEY `idx_o_incoming_payments_mobile_number` (`mobile_number`),
  KEY `idx_o_incoming_payments_loan_id` (`loan_id`),
  KEY `idx_o_incoming_payments_payment_date` (`payment_date`),
  KEY `idx_o_incoming_payments_status` (`status`),
  KEY `idx_o_incoming_payments_collected_by` (`collected_by`),
  KEY `idx_o_incoming_payments_amount` (`amount`)
);

--
-- Table structure for table `o_key_values`
--

DROP TABLE IF EXISTS `o_key_values`;
CREATE TABLE `o_key_values` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `tbl` varchar(20) NOT NULL,
  `record` int NOT NULL,
  `key_` varchar(50) NOT NULL,
  `value_` varchar(250) NOT NULL,
  `added_by` int NOT NULL,
  `added_date` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
) COMMENT='Save key and values for anything';

--
-- Table structure for table `o_last_service`
--

DROP TABLE IF EXISTS `o_last_service`;
CREATE TABLE `o_last_service` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `last_date` datetime DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_loan_addons`
--

DROP TABLE IF EXISTS `o_loan_addons`;
CREATE TABLE `o_loan_addons` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `loan_id` int NOT NULL,
  `addon_id` int NOT NULL,
  `addon_amount` double(20,2) NOT NULL,
  `added_by` int DEFAULT '0',
  `added_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` int DEFAULT '1',
  PRIMARY KEY (`uid`),
  KEY `idx_o_loan_addons_loan_id` (`loan_id`),
  KEY `idx_o_loan_addons_addon_id` (`addon_id`),
  KEY `idx_o_loan_addons_status` (`status`)
);

--
-- Table structure for table `o_loan_deductions`
--

DROP TABLE IF EXISTS `o_loan_deductions`;
CREATE TABLE `o_loan_deductions` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `loan_id` int NOT NULL,
  `deduction_id` int NOT NULL,
  `deduction_amount` double(20,2) NOT NULL,
  `added_by` int NOT NULL,
  `added_date` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_loan_products`
--

DROP TABLE IF EXISTS `o_loan_products`;
CREATE TABLE `o_loan_products` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` varchar(250) NOT NULL,
  `period` int NOT NULL,
  `period_units` varchar(10) NOT NULL COMMENT 'DAYS, WEEKS, MONTHS, YEARS',
  `min_amount` double(50,2) NOT NULL,
  `max_amount` double(50,2) NOT NULL,
  `pay_frequency` varchar(20) NOT NULL COMMENT 'DAILY, WEEKLY, BI-WEEKLY, MONTHLY, BI-MONTHLY, QUARTERLY, BI-ANNUALY, ANNUALY',
  `percent_breakdown` varchar(250) NOT NULL COMMENT 'Payment Breakdown. comma delimited without the % sign. Leave blank for equal percentage',
  `disburse_method` int NOT NULL COMMENT 'from o_disburse_methods',
  `automatic_disburse` int DEFAULT '0',
  `added_date` datetime NOT NULL,
  `after_save_script` json DEFAULT NULL COMMENT 'The script to be run after a loan or customer is created',
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_loan_stages`
--

DROP TABLE IF EXISTS `o_loan_stages`;
CREATE TABLE `o_loan_stages` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` varchar(250) NOT NULL,
  `stage_order` int NOT NULL,
  `permissions` varchar(250) NOT NULL,
  `can_addon` int NOT NULL,
  `can_deduct` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_loan_statuses`
--

DROP TABLE IF EXISTS `o_loan_statuses`;
CREATE TABLE `o_loan_statuses` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `active_loan` int NOT NULL,
  `description` varchar(250) NOT NULL,
  `color_code` varchar(10) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_loan_types`
--

DROP TABLE IF EXISTS `o_loan_types`;
CREATE TABLE `o_loan_types` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `icon` varchar(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_loans`
--

DROP TABLE IF EXISTS `o_loans`;
CREATE TABLE `o_loans` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `loan_code` varchar(30) DEFAULT NULL COMMENT 'Custom loan code',
  `customer_id` int NOT NULL,
  `group_id` int DEFAULT '0',
  `asset_id` int NOT NULL DEFAULT '0',
  `account_number` varchar(30) NOT NULL COMMENT 'E.g. mobile, bank',
  `enc_phone` varchar(70) DEFAULT NULL,
  `product_id` int NOT NULL,
  `loan_type` int NOT NULL DEFAULT '0',
  `loan_amount` double(50,2) NOT NULL,
  `disbursed_amount` double(50,2) NOT NULL,
  `total_repayable_amount` decimal(50,2) DEFAULT '0.00',
  `total_repaid` decimal(50,2) DEFAULT '0.00',
  `loan_balance` decimal(50,2) DEFAULT '0.00',
  `period` int NOT NULL,
  `period_units` varchar(30) NOT NULL,
  `payment_frequency` varchar(30) NOT NULL,
  `payment_breakdown` varchar(75) NOT NULL,
  `total_addons` decimal(50,2) DEFAULT '0.00',
  `addons_count` int DEFAULT '0',
  `total_deductions` decimal(50,2) DEFAULT '0.00',
  `deductions_count` int DEFAULT '0',
  `total_instalments` int NOT NULL COMMENT 'Total installments in this loan',
  `total_instalments_paid` int NOT NULL COMMENT 'How many instalments have been paid',
  `current_instalment` int NOT NULL COMMENT 'We are in instalment number?',
  `current_instalment_amount` double(50,2) DEFAULT NULL,
  `income_earned` double(50,2) DEFAULT '0.00',
  `given_date` date NOT NULL,
  `next_due_date` date NOT NULL,
  `final_due_date` date NOT NULL,
  `cleared_date` datetime DEFAULT NULL,
  `added_by` int NOT NULL,
  `current_agent` int DEFAULT '0',
  `current_lo` int DEFAULT NULL,
  `current_co` int DEFAULT NULL,
  `allocation` varchar(10) DEFAULT 'BRANCH',
  `current_branch` int NOT NULL,
  `added_date` datetime NOT NULL,
  `loan_stage` int NOT NULL,
  `loan_flag` int DEFAULT '0' COMMENT 'from o_flags',
  `transaction_code` varchar(40) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `application_mode` varchar(40) NOT NULL COMMENT 'MANUAL, APP, USSD, SMS',
  `disburse_state` varchar(45) DEFAULT 'NONE' COMMENT 'NONE, INITIATED, DELIVERED, FAILED, REVERSED',
  `disbursed` int DEFAULT '0' COMMENT 'Whether the loan was given or not',
  `with_ni_validation` tinyint DEFAULT '1',
  `paid` int DEFAULT '0' COMMENT 'paid or not. each state encapsulates multiple statuses',
  `other_info` json DEFAULT NULL,
  `npl_uid` int DEFAULT '0',
  `status` int NOT NULL COMMENT 'From o_loan_statuses',
  PRIMARY KEY (`uid`),
  KEY `customer_id` (`customer_id`),
  KEY `idx_o_loans_current_branch` (`current_branch`),
  KEY `idx_o_loans_current_agent` (`current_agent`),
  KEY `idx_o_loans_given_date` (`given_date`),
  KEY `idx_o_loans_product_id` (`product_id`),
  KEY `idx_o_loans_account_number` (`account_number`),
  KEY `idx_o_loans_enc_phone` (`enc_phone`),
  KEY `idx_o_loans_loan_amount` (`loan_amount`),
  KEY `idx_o_loans_total_repaid` (`total_repaid`),
  KEY `idx_o_loans_final_due_date` (`final_due_date`),
  KEY `idx_o_loans_loan_stage` (`loan_stage`),
  KEY `idx_o_loans_current_lo` (`current_lo`),
  KEY `idx_o_loans_status` (`status`),
  KEY `idx_o_loans_transaction_code` (`transaction_code`),
  KEY `idx_o_loans_current_co` (`current_co`),
  CONSTRAINT `o_loans_chk_1` CHECK (json_valid(`other_info`))
);

--
-- Table structure for table `o_log_updates`
--

DROP TABLE IF EXISTS `o_log_updates`;
CREATE TABLE `o_log_updates` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `table_` varchar(50) NOT NULL,
  `query_` text NOT NULL,
  `record_date` datetime NOT NULL,
  `update_by` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_mpesa_configs`
--

DROP TABLE IF EXISTS `o_mpesa_configs`;
CREATE TABLE `o_mpesa_configs` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `property_name` varchar(45) NOT NULL,
  `property_value` mediumtext,
  `security_credential` mediumtext,
  `consumer_key` varchar(255) DEFAULT NULL,
  `consumer_secret` varchar(255) DEFAULT NULL,
  `initiator_name` varchar(255) DEFAULT NULL,
  `enc_token` mediumtext,
  `enc_token_key` mediumtext,
  `last_update` datetime DEFAULT NULL,
  `status` int DEFAULT '1',
  PRIMARY KEY (`uid`)
) COMMENT='Store mpesa configurations';

--
-- Table structure for table `o_mpesa_queues`
--

DROP TABLE IF EXISTS `o_mpesa_queues`;
CREATE TABLE `o_mpesa_queues` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `loan_id` int unsigned NOT NULL,
  `amount` double(50,2) DEFAULT NULL,
  `added_date` datetime DEFAULT NULL,
  `requeued_date` datetime DEFAULT NULL,
  `sent_date` datetime DEFAULT NULL,
  `feedbackcode` varchar(45) DEFAULT NULL,
  `trials` int DEFAULT '1',
  `status` int DEFAULT '0' COMMENT '1-queed, 2-sent, 0-deleted',
  PRIMARY KEY (`uid`),
  KEY `idx_o_mpesa_queues_loan_id` (`loan_id`),
  KEY `idx_o_mpesa_queues_trials` (`trials`),
  KEY `idx_o_mpesa_queues_status` (`status`),
  KEY `idx_o_mpesa_queues_added_date` (`added_date`)
) COMMENT='List disbursements that are to be done later';

--
-- Table structure for table `o_next_steps`
--

DROP TABLE IF EXISTS `o_next_steps`;
CREATE TABLE `o_next_steps` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `details` varchar(50) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_notifications`
--

DROP TABLE IF EXISTS `o_notifications`;
CREATE TABLE `o_notifications` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `sent_date` datetime NOT NULL,
  `source_details` varchar(20) NOT NULL,
  `title` varchar(80) NOT NULL,
  `details` varchar(250) NOT NULL,
  `link` varchar(200) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_npl_categories`
--

DROP TABLE IF EXISTS `o_npl_categories`;
CREATE TABLE `o_npl_categories` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `icon` varchar(20) DEFAULT NULL,
  `status` int DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `title` (`title`)
);

--
-- Table structure for table `o_pairing`
--

DROP TABLE IF EXISTS `o_pairing`;
CREATE TABLE `o_pairing` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `lo` int NOT NULL,
  `co` int NOT NULL,
  `branch` int DEFAULT '0',
  `paired_date` datetime NOT NULL,
  `supervisor` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_passes`
--

DROP TABLE IF EXISTS `o_passes`;
CREATE TABLE `o_passes` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `user` int NOT NULL,
  `pass` varchar(200) NOT NULL,
  `pass_reset_token` varchar(255) DEFAULT NULL,
  `reset_status` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `user` (`user`)
);

--
-- Table structure for table `o_payment_categories`
--

DROP TABLE IF EXISTS `o_payment_categories`;
CREATE TABLE `o_payment_categories` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_payment_methods`
--

DROP TABLE IF EXISTS `o_payment_methods`;
CREATE TABLE `o_payment_methods` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `account_details` varchar(250) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_payment_schedule`
--

DROP TABLE IF EXISTS `o_payment_schedule`;
CREATE TABLE `o_payment_schedule` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `loan_code` int NOT NULL COMMENT 'From o_loans table',
  `schedule_date` date NOT NULL,
  `pay_percent` double(10,2) NOT NULL,
  `pay_amount` double(50,2) NOT NULL,
  `amount_paid` double(50,2) NOT NULL,
  `balance` double(50,2) NOT NULL,
  `status` int NOT NULL COMMENT '1-scheduled, 2-cleared, 3-partial, 4-defaulted',
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_payment_statuses`
--

DROP TABLE IF EXISTS `o_payment_statuses`;
CREATE TABLE `o_payment_statuses` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_permissions`
--

DROP TABLE IF EXISTS `o_permissions`;
CREATE TABLE `o_permissions` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `tbl` varchar(50) NOT NULL,
  `rec` int NOT NULL,
  `general_` int NOT NULL DEFAULT '0',
  `create_` int NOT NULL DEFAULT '0',
  `read_` int NOT NULL DEFAULT '0',
  `update_` int NOT NULL DEFAULT '0',
  `delete_` int NOT NULL DEFAULT '0',
  `custom_action` varchar(40) NOT NULL COMMENT 'An action that is not CRUD',
  `added_by` int NOT NULL,
  `last_updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int NOT NULL,
  `block_` int NOT NULL DEFAULT '0',
  `unblock_` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_product_addons`
--

DROP TABLE IF EXISTS `o_product_addons`;
CREATE TABLE `o_product_addons` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `addon_id` int NOT NULL COMMENT 'From o_addons table',
  `product_id` int NOT NULL COMMENT 'From o_loan_products table',
  `date_added` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_product_deductions`
--

DROP TABLE IF EXISTS `o_product_deductions`;
CREATE TABLE `o_product_deductions` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `deduction_id` int NOT NULL COMMENT 'From o_addons table',
  `product_id` int NOT NULL COMMENT 'From o_loan_products table',
  `date_added` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_product_forms`
--

DROP TABLE IF EXISTS `o_product_forms`;
CREATE TABLE `o_product_forms` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `form_id` int NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_product_reminders`
--

DROP TABLE IF EXISTS `o_product_reminders`;
CREATE TABLE `o_product_reminders` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `loan_day` int NOT NULL,
  `custom_event` varchar(45) DEFAULT NULL COMMENT 'PARTIAL_PAYMENT, FINAL_PAYMENT',
  `loan_status` int NOT NULL,
  `message_body` text NOT NULL,
  `added_date` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_product_stages`
--

DROP TABLE IF EXISTS `o_product_stages`;
CREATE TABLE `o_product_stages` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `stage_id` int NOT NULL COMMENT 'From o_loan_stages table',
  `stage_order` int NOT NULL,
  `is_final_stage` int NOT NULL,
  `product_id` int NOT NULL COMMENT 'From o_loan_products table',
  `date_added` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_regions`
--

DROP TABLE IF EXISTS `o_regions`;
CREATE TABLE `o_regions` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `manager_id` int DEFAULT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_reports`
--

DROP TABLE IF EXISTS `o_reports`;
CREATE TABLE `o_reports` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` varchar(250) NOT NULL,
  `row_query` text NOT NULL,
  `branch_query` text,
  `added_by` int NOT NULL,
  `added_date` datetime DEFAULT NULL,
  `viewable_by` varchar(50) DEFAULT NULL COMMENT '0 for all',
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `title` (`title`)
);

--
-- Table structure for table `o_reports_custom`
--

DROP TABLE IF EXISTS `o_reports_custom`;
CREATE TABLE `o_reports_custom` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `description` mediumtext,
  `report_url` varchar(200) NOT NULL,
  `added_date` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `title` (`title`)
);

--
-- Table structure for table `o_sec_data`
--

DROP TABLE IF EXISTS `o_sec_data`;
CREATE TABLE `o_sec_data` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `tbl` varchar(30) NOT NULL,
  `fld` int NOT NULL,
  `field_id` int NOT NULL COMMENT 'From o_form_fields',
  `user_input` varchar(250) NOT NULL COMMENT 'Data from the form',
  `added_date` datetime NOT NULL,
  `added_by` int NOT NULL COMMENT 'From o_users table',
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_sms_interaction`
--

DROP TABLE IF EXISTS `o_sms_interaction`;
CREATE TABLE `o_sms_interaction` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `shortcode` int NOT NULL,
  `keyword` varchar(25) NOT NULL,
  `sender_phone` varchar(15) NOT NULL,
  `customer_id` int NOT NULL,
  `message` varchar(250) NOT NULL,
  `transdate` datetime NOT NULL,
  `direction` int NOT NULL COMMENT '1-incoming, 2-outgoing',
  `link_id` varchar(100) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_sms_outgoing`
--

DROP TABLE IF EXISTS `o_sms_outgoing`;
CREATE TABLE `o_sms_outgoing` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(15) NOT NULL,
  `message_body` text NOT NULL,
  `queued_date` datetime DEFAULT NULL,
  `sent_date` datetime DEFAULT NULL,
  `delivered` int DEFAULT NULL,
  `delivered_date` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `source_tbl` varchar(55) DEFAULT NULL COMMENT 'Source of message e.g. o_campaigns, o_loan e.tc',
  `source_record` int DEFAULT NULL COMMENT 'Specific record e.g A campaign Id',
  `status` int DEFAULT '1' COMMENT '0-Deleted, 1-Queued, 2-Sent, 3-Delivered, 4-Failed',
  PRIMARY KEY (`uid`),
  KEY `idx_o_sms_outgoing_phone` (`phone`),
  KEY `idx_o_sms_outgoing_status` (`status`)
);

--
-- Table structure for table `o_sms_settings`
--

DROP TABLE IF EXISTS `o_sms_settings`;
CREATE TABLE `o_sms_settings` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `property_name` varchar(45) DEFAULT NULL,
  `property_value` mediumtext,
  `last_update` datetime DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_staff_branches`
--

DROP TABLE IF EXISTS `o_staff_branches`;
CREATE TABLE `o_staff_branches` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `agent` int NOT NULL,
  `branch` int NOT NULL,
  `added_date` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
) COMMENT='For agents who are assigned to multiple branches';

--
-- Table structure for table `o_staff_statuses`
--

DROP TABLE IF EXISTS `o_staff_statuses`;
CREATE TABLE `o_staff_statuses` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `color` varchar(50) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_summaries`
--

DROP TABLE IF EXISTS `o_summaries`;
CREATE TABLE `o_summaries` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `value_` varchar(45) DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`)
) COMMENT='Stores various summaries';

--
-- Table structure for table `o_targets`
--

DROP TABLE IF EXISTS `o_targets`;
CREATE TABLE `o_targets` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `target_type` varchar(45) DEFAULT NULL COMMENT 'DISBURSEMENTS, COLLECTIONS',
  `starting_date` date DEFAULT NULL,
  `ending_date` date DEFAULT NULL,
  `target_group` varchar(45) DEFAULT 'BRANCH' COMMENT 'BRANCH, STAFF, PRODUCT',
  `group_id` int DEFAULT NULL,
  `amount` double(50,2) DEFAULT NULL,
  `amount_type` varchar(45) DEFAULT 'FIXED_VALUE' COMMENT 'PERCENTAGE, FIXED_VALUE',
  `working_days` int NOT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_team_leaders`
--

DROP TABLE IF EXISTS `o_team_leaders`;
CREATE TABLE `o_team_leaders` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `leader_id` int NOT NULL,
  `agent_id` int NOT NULL,
  `added_by` int NOT NULL,
  `added_date` datetime NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `o_telecomms`
--

DROP TABLE IF EXISTS `o_telecomms`;
CREATE TABLE `o_telecomms` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `country_code` varchar(20) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  PRIMARY KEY (`uid`),
  KEY `idx_country_code` (`country_code`)
);

--
-- Table structure for table `o_tokens`
--

DROP TABLE IF EXISTS `o_tokens`;
CREATE TABLE `o_tokens` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `userid` int NOT NULL,
  `token` varchar(245) NOT NULL,
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` datetime NOT NULL,
  `device_id` varchar(245) DEFAULT NULL,
  `browsername` varchar(250) DEFAULT NULL,
  `IPAddress` varchar(45) DEFAULT NULL,
  `OS` varchar(55) DEFAULT NULL,
  `usages` int DEFAULT '0',
  `status` int NOT NULL COMMENT '1-valid, 2-expired',
  PRIMARY KEY (`uid`),
  KEY `idx_o_tokens_token` (`token`),
  KEY `idx_o_tokens_userid` (`userid`),
  KEY `idx_o_tokens_expiry_date` (`expiry_date`),
  KEY `idx_o_tokens_status` (`status`)
);

--
-- Table structure for table `o_towns`
--

DROP TABLE IF EXISTS `o_towns`;
CREATE TABLE `o_towns` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `county` varchar(30) NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_user_groups`
--

DROP TABLE IF EXISTS `o_user_groups`;
CREATE TABLE `o_user_groups` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` varchar(250) NOT NULL,
  `kpi_measured` int DEFAULT '1' COMMENT 'Show user in reports',
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

--
-- Table structure for table `o_users`
--

DROP TABLE IF EXISTS `o_users`;
CREATE TABLE `o_users` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `national_id` varchar(15) NOT NULL,
  `join_date` datetime NOT NULL,
  `pass1` varchar(128) NOT NULL,
  `otp_` varchar(10) NOT NULL,
  `login_trials` int NOT NULL DEFAULT '0',
  `user_group` int NOT NULL,
  `tag` varchar(10) DEFAULT 'BRANCH',
  `pair` int DEFAULT '0',
  `branch` int NOT NULL,
  `company` int NOT NULL COMMENT 'From companies database',
  `status` int NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `phone` (`phone`),
  KEY `idx_o_users_name` (`name`),
  KEY `idx_o_users_email` (`email`),
  KEY `idx_o_users_national_id` (`national_id`),
  KEY `idx_o_users_branch` (`branch`),
  KEY `idx_o_users_otp_` (`otp_`),
  KEY `idx_o_users_user_group` (`user_group`)
);

--
-- Table structure for table `o_ussd_sessions`
--

DROP TABLE IF EXISTS `o_ussd_sessions`;
CREATE TABLE `o_ussd_sessions` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(105) NOT NULL,
  `started_date` datetime DEFAULT NULL,
  `stage_` int DEFAULT NULL,
  `mobile_number` varchar(15) DEFAULT NULL,
  `info_supplied` text,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`uid`)
);

--
-- Table structure for table `platform_settings`
--

DROP TABLE IF EXISTS `platform_settings`;
CREATE TABLE `platform_settings` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `company_id` int DEFAULT '0',
  `logo` varchar(50) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `link` varchar(50) NOT NULL,
  PRIMARY KEY (`uid`)
);