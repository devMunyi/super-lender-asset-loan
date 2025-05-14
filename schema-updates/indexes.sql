-- 1) TABLE o_incoming_payments (4 indexes added, 1 potential index NOT ADDED) => Total 5
-- ALTER TABLE `o_incoming_payments` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_incoming_payments` ADD INDEX(`transaction_code`); -- ADDED DURING DEV
ALTER TABLE `o_incoming_payments` ADD INDEX(`customer_id`); -- ADDED LATER
ALTER TABLE `o_incoming_payments` ADD INDEX(`loan_id`); -- ADDED LATER
ALTER TABLE `o_incoming_payments` ADD INDEX(`payment_date`); -- (NOT ADDED)
ALTER TABLE `o_incoming_payments` ADD INDEX(`mobile_number`); 
ALTER TABLE `o_incoming_payments` ADD INDEX(`enc_phone`); 


-- 2) TABLE o_loans (2 indexes added, 2 potential indexes NOT ADDED) => Total 4;
-- ALTER TABLE `o_loans` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_loans` ADD INDEX(`customer_id`); -- ADDED LATER
ALTER TABLE `o_loans` ADD INDEX(`given_date`); -- (NOT ADDED)
ALTER TABLE `o_loans` ADD INDEX(`final_due_date`);
ALTER TABLE `o_loans` ADD INDEX(`account_number`);
ALTER TABLE `o_loans` ADD INDEX(`enc_phone`);

-- 3) TABLE o_loans (3 indexes added, 1 potential indexes NOT ADDED) => Total 4;
-- ALTER TABLE `o_loan_addons` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_loan_addons` ADD INDEX(`addon_id`); -- ADDED LATER
ALTER TABLE `o_loan_addons` ADD INDEX(`loan_id`);

-- 4) TABLE o_permissions (3 indexes added, 0 potential indexes NOT ADDED) => Total 3;
-- ALTER TABLE `o_permissions` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_permissions` ADD INDEX(`group_id`); -- ADDED LATER
ALTER TABLE `o_permissions` ADD INDEX(`user_id`); -- ADDED LATER


-- 5) TABLE o_tokens (3 indexes added, 0 potential indexes NOT ADDED) => Total 3;
-- ALTER TABLE `o_tokens` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_tokens` ADD INDEX(`token`); -- ADDED LATER
ALTER TABLE `o_tokens` ADD INDEX(`expiry_date`); 


-- 6) TABLE o_customer_conversations (5 indexes added, 1 potential index NOT ADDED, 1 can be removed) => Total 6;
-- ALTER TABLE `o_customer_conversations` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_customer_conversations` ADD INDEX(`customer_id`); -- ADDED LATER
ALTER TABLE `o_customer_conversations` ADD INDEX(`loan_id`); -- ADDED LATER
ALTER TABLE `o_customer_conversations` ADD INDEX(`branch`); -- ADDED LATER (with cardinality of 119)
ALTER TABLE `o_customer_conversations` ADD INDEX(`conversation_method`); -- ADDED LATER (with cardinality of 4 can be removed)
ALTER TABLE `o_customer_conversations` ADD INDEX(`agent_id`); -- (ADDED)
ALTER TABLE `o_customer_conversations` ADD INDEX(`conversation_date`);
ALTER TABLE `o_customer_conversations` ADD INDEX(`next_interaction`);


-- 7) TABLE o_sms_outgoing (1 index added, 2 potential indexes NOT ADDED) => Total 3;
-- ALTER TABLE `o_sms_outgoing` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_sms_outgoing` ADD INDEX(`phone`);


-- 8) TABLE o_sms_interaction (1 index added, 1 potential index NOT ADDED) => Total 2;
-- ALTER TABLE `o_sms_interaction` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_sms_interaction` ADD INDEX(`sender_phone`); -- (NOT ADDED)


-- 9) TABLE o_events (1 index added, 1 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_events` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_events` ADD INDEX(`fld`);
ALTER TABLE `o_events` ADD INDEX(`tbl`);


-- 10) TABLE o_mpesa_queues (1 index added, 1 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_mpesa_queues` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_mpesa_queues` ADD INDEX(`added_date`);
ALTER TABLE `o_mpesa_queues` ADD INDEX(`trials`);

-- 11) TABLE o_campaign_messages (1 index added, 2 potential indexes NOT ADDED) => Total 2
-- ALTER TABLE `o_campaign_messages` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_campaign_messages` ADD INDEX(`campaign_id`);


-- 12) TABLE o_customers (4 index added, 1 potential index NOT ADDED) => Total 5
-- ALTER TABLE `o_customers` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_customers` ADD INDEX(`primary_mobile`); -- ADDED DURING DEV
ALTER TABLE `o_customers` ADD INDEX(`national_id`); -- ADDED DURING DEV
ALTER TABLE `o_customers` ADD INDEX(`full_name`); -- (UPDATED)
ALTER TABLE `o_customers` ADD INDEX(`branch`); -- (UPDATED)
ALTER TABLE `o_customers` ADD INDEX(`enc_phone`); 
ALTER TABLE `o_customers` ADD INDEX(`current_agent`);
ALTER TABLE `o_customers` ADD INDEX(`email_address`);


-- 13) TABLE o_targets (1 index added, 1 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_targets` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key

-- 14) TABLE o_branches (1 index added, 1 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_branches` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key


-- 15) TABLE o_product_reminders (1 index added, 1 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_product_reminders` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_product_reminders` ADD INDEX(`loan_day`); -- (Updated)


-- 16) TABLE o_customer_contacts (1 index added, 2 potential index NOT ADDED) => Total 3
-- ALTER TABLE `o_customer_contacts` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_customer_contacts` ADD INDEX(`value`); -- (ADDED)
ALTER TABLE `o_customer_contacts` ADD INDEX(`customer_id`); -- (ADDED)


-- 17) TABLE o_documents (1 index added, 1 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_documents` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_documents` ADD INDEX(`rec`);
ALTER TABLE `o_documents` ADD INDEX(`tbl`);
ALTER TABLE `o_documents` ADD INDEX(`loan_id`);


-- 18) TABLE o_customer_referees (1 index added, 2 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_customer_referees` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_customer_referees` ADD INDEX(`customer_id`);

-- 19) TABLE o_collateral (1 index added, 2 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_collateral` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_collateral` ADD INDEX(`customer_id`); -- (ADDED)

-- 20) TABLE o_customer_limits (1 index added, 2 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_customer_limits` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_customer_limits` ADD INDEX(`customer_uid`); -- (ADDED)

-- NEW (as at 2023-07-20)
--21) TABLE o_notifications (1 index added, 2 potential index NOT ADDED) => Total 2
-- ALTER TABLE `o_notifications` ADD INDEX(`uid`); -- ADDED BY DEFAULT for a primary key
ALTER TABLE `o_notifications` ADD INDEX(`staff_id`); -- (NOT ADDED)


--- 2023-10-30
ALTER TABLE o_events
ADD INDEX idx_event_by (event_by);

--- 2023-10-31
ALTER TABLE o_events
ADD INDEX idx_event_date (event_date);


--- 2024-01-30

-- #I have updated indexes on the following tables that were missing indexes:
-- o_loans enc_phone  => account_number
-- o_incoming_payments => enc_phone
-- o_customers => enc_phone
-- o_events


-- #More indexes to added on the following tables:
-- o_customer_referees => mobile_no
-- o_mpesa_queues => loan_id
-- o_collateral => loan_id
-- o_customer_contacts => enc_phone
-- o_tokens => expiry_date
-- o_passes => pass

-- #It's likely enc_phone recently added column and was not indexed. Yet is being used to reference these hotspot tables.

-- #indexes Table Maintenance(rebuilding) Most Targeted Tables:
ALTER TABLE o_events FORCE;
ALTER TABLE o_documents FORCE;

ALTER TABLE o_customer_conversations FORCE;
ALTER TABLE o_incoming_payments FORCE;
ALTER TABLE o_loan_addons FORCE;
ALTER TABLE o_customers FORCE;

ALTER TABLE o_customer_referees FORCE;
ALTER TABLE o_collateral FORCE;
ALTER TABLE o_loans FORCE;

ALTER TABLE o_sms_outgoing FORCE;
ALTER TABLE o_customer_limits FORCE;
ALTER TABLE o_mpesa_queues FORCE;

ALTER TABLE o_customer_contacts FORCE;
ALTER TABLE o_tokens FORCE;
ALTER TABLE o_passes FORCE;

ALTER TABLE o_events DROP INDEX idx_event_date;
DROP INDEX `email_address_` ;