
-- 1) Partitioning for o_customers table

-- Remove all unique keys first except the primary key
-- Add the partitioning key(branch) to the primary key
   -- will have to remove the primary key first using sql:
      ALTER TABLE o_customers DROP PRIMARY KEY;
    -- add the partitioning key to the primary key
      ALTER TABLE o_customers ADD PRIMARY KEY (uid, branch);
-- Now add indexes on the following columns (Ensure you don't add index twice on the same column using different key name):
  -- primary_mobile:
      ALTER TABLE o_customers ADD KEY `primary_phone_` (`primary_mobile`);
  -- national_id:
      ALTER TABLE o_customers ADD KEY `national_id_` (`national_id`);
  -- phone_number_provider:
      ALTER TABLE o_customers ADD KEY `phone_number_provider_index` (`phone_number_provider`);
  -- full_name:
      ALTER TABLE o_customers ADD KEY `idx_o_customers_full_name` (`full_name`);

-- Now add partitions:
ALTER TABLE o_customers
PARTITION BY RANGE (branch) (
    PARTITION p1 VALUES LESS THAN (2),
    PARTITION p2 VALUES LESS THAN (4),
    PARTITION p3 VALUES LESS THAN (6),
    PARTITION p4 VALUES LESS THAN (8),
    PARTITION p5 VALUES LESS THAN (10),
    PARTITION p6 VALUES LESS THAN (12),
    PARTITION p7 VALUES LESS THAN (14),
    PARTITION p8 VALUES LESS THAN (16),
    PARTITION p9 VALUES LESS THAN (18),
    PARTITION p10 VALUES LESS THAN (20),
    PARTITION p_overflow VALUES LESS THAN MAXVALUE
);

-- TAKEAWAYS:
-- Note you can not add unique keys on partitioned tables unless the unique key contains the partitioning key. 
-- Now add the unique key
  ALTER TABLE `o_customers` ADD UNIQUE KEY `unique_branch` (`uid`, `primary_mobile`,`national_id`,`branch`);
-- how to drop that key:
  ALTER TABLE `o_customers` DROP KEY `unique_branch`;
-- but you can create ordinary(BITREE) indexes on partitioned tables


-- 2) Partitioning for o_loans
-- Remove all unique keys first except the primary key
-- Create primary key on uid and paid columns:
  ALTER TABLE o_loans ADD PRIMARY KEY (uid, paid);
-- This table has column named paid which can either be 0 or 1. I want to parttion using this column. With this column, I can have two partitions using:
  ALTER TABLE o_loans PARTITION BY LIST (paid) (
    PARTITION p0 VALUES IN (0),
    PARTITION p1 VALUES IN (1)
  );

-- What if In future I now want to move partition p1 to another table? I can do that using the following sql:
  ALTER TABLE o_loans EXCHANGE PARTITION p1 WITH TABLE o_loans_paid;


-- or use current_branch column to partition the table:
ALTER TABLE o_loans PARTITION BY RANGE(current_branch)(
	  PARTITION b1 VALUES LESS THAN (2),
      PARTITION b2 VALUES LESS THAN (4),
      PARTITION b3 VALUES LESS THAN (6),
      PARTITION b4 VALUES LESS THAN (8),
      PARTITION b5 VALUES LESS THAN (10),
      PARTITION b6 VALUES LESS THAN (12),
      PARTITION b7 VALUES LESS THAN (14),
      PARTITION b8 VALUES LESS THAN (16),
      PARTITION b9 VALUES LESS THAN (18),
      PARTITION b10 VALUES LESS THAN (20),
      PARTITION b_overflow VALUES LESS THAN MAXVALUE
);

-- But to futher optimize table access in each of these partitions it would be nice to further partition using branch as partitioning key to narrow down row scan:
  ALTER TABLE o_loans PARTITION BY LIST (paid) SUBPARTITION BY RANGE (branch) (
    PARTITION p0 VALUES IN (0) (
      SUBPARTITION p0_1 VALUES LESS THAN (2),
      SUBPARTITION p0_2 VALUES LESS THAN (4),
      SUBPARTITION p0_3 VALUES LESS THAN (6),
      SUBPARTITION p0_4 VALUES LESS THAN (8),
      SUBPARTITION p0_5 VALUES LESS THAN (10),
      SUBPARTITION p0_6 VALUES LESS THAN (12),
      SUBPARTITION p0_7 VALUES LESS THAN (14),
      SUBPARTITION p0_8 VALUES LESS THAN (16),
      SUBPARTITION p0_9 VALUES LESS THAN (18),
      SUBPARTITION p0_10 VALUES LESS THAN (20),
      SUBPARTITION p0_overflow VALUES LESS THAN MAXVALUE
    ),
    PARTITION p1 VALUES IN (1) (
      SUBPARTITION p1_1 VALUES LESS THAN (2),
      SUBPARTITION p1_2 VALUES LESS THAN (4),
      SUBPARTITION p1_3 VALUES LESS THAN (6),
      SUBPARTITION p1_4 VALUES LESS THAN (8),
      SUBPARTITION p1_5 VALUES LESS THAN (10),
      SUBPARTITION p1_6 VALUES LESS THAN (12),
      SUBPARTITION p1_7 VALUES LESS THAN (14),
      SUBPARTITION p1_8 VALUES LESS THAN (16),
      SUBPARTITION p1_9 VALUES LESS THAN (18),
      SUBPARTITION p1_10 VALUES LESS THAN (20),
      SUBPARTITION p1_overflow VALUES LESS THAN MAXVALUE
    )
  );

-- Above query cannot run successfully because of the following error: 'RANGE' is not valid at this position expecting HASH, KEY
-- This is because we are trying to use RANGE partitioning on a LIST partitioned table. So we have to use LIST partitioning on the subpartitions:
  ALTER TABLE o_loans PARTITION BY LIST (paid) SUBPARTITION BY LIST (current_branch) (
    PARTITION p0 VALUES IN (0) (
      SUBPARTITION p0_1 VALUES IN (1),
      SUBPARTITION p0_2 VALUES IN (2),
      SUBPARTITION p0_3 VALUES IN (3),
      SUBPARTITION p0_4 VALUES IN (4),
      SUBPARTITION p0_5 VALUES IN (5),
      SUBPARTITION p0_6 VALUES IN (6),
      SUBPARTITION p0_7 VALUES IN (7),
      SUBPARTITION p0_8 VALUES IN (8),
      SUBPARTITION p0_9 VALUES IN (9),
      SUBPARTITION p0_10 VALUES IN (10),
      SUBPARTITION p0_overflow VALUES IN (11)
    ),
    PARTITION p1 VALUES IN (1) (
      SUBPARTITION p1_1 VALUES IN (1),
      SUBPARTITION p1_2 VALUES IN (2),
      SUBPARTITION p1_3 VALUES IN (3),
      SUBPARTITION p1_4 VALUES IN (4),
      SUBPARTITION p1_5 VALUES IN (5),
      SUBPARTITION p1_6 VALUES IN (6),
      SUBPARTITION p1_7 VALUES IN (7),
      SUBPARTITION p1_8 VALUES IN (8),
      SUBPARTITION p1_9 VALUES IN (9),
      SUBPARTITION p1_10 VALUES IN (10),
      SUBPARTITION p1_overflow VALUES IN (11)
    )
  );

-- I would have like 100 branches and I want each subpartition to have 10 branches:
  ALTER TABLE o_loans PARTITION BY LIST (paid) SUBPARTITION BY LIST (branch) (
    PARTITION p0 VALUES IN (0) (
      SUBPARTITION p0_1 VALUES IN (1),
      SUBPARTITION p0_2 VALUES IN (2),
      SUBPARTITION p0_3 VALUES IN (3),
      SUBPARTITION p0_4 VALUES IN (4),
      SUBPARTITION p0_5 VALUES IN (5),
      SUBPARTITION p0_6 VALUES IN (6),
      SUBPARTITION p0_7 VALUES IN (7),
      SUBPARTITION p0_8 VALUES IN (8),
      SUBPARTITION p0_9 VALUES IN (9),
      SUBPARTITION p0_10 VALUES IN (10),
      SUBPARTITION p0_11 VALUES IN (11),
      SUBPARTITION p0_12 VALUES IN (12),
      SUBPARTITION p0_13 VALUES IN (13),
      SUBPARTITION p0_14 VALUES IN (14),
      SUBPARTITION p0_15 VALUES IN (15),
      SUBPARTITION p0_16 VALUES IN (16),
      SUBPARTITION p0_17 VALUES IN (17)
      SUBPARTITION p0_overflow VALUES THAN MAXVALUE
    ),
    PARTITION p1 VALUES IN (1) (
      SUBPARTITION p0_1 VALUES IN (1),
      SUBPARTITION p0_2 VALUES IN (2),
      SUBPARTITION p0_3 VALUES IN (3),
      SUBPARTITION p0_4 VALUES IN (4),
      SUBPARTITION p0_5 VALUES IN (5),
      SUBPARTITION p0_6 VALUES IN (6),
      SUBPARTITION p0_7 VALUES IN (7),
      SUBPARTITION p0_8 VALUES IN (8),
      SUBPARTITION p0_9 VALUES IN (9),
      SUBPARTITION p0_10 VALUES IN (10),
      SUBPARTITION p0_11 VALUES IN (11),
      SUBPARTITION p0_12 VALUES IN (12),
      SUBPARTITION p0_13 VALUES IN (13),
      SUBPARTITION p0_14 VALUES IN (14),
      SUBPARTITION p0_15 VALUES IN (15),
      SUBPARTITION p0_16 VALUES IN (16),
      SUBPARTITION p0_17 VALUES IN (17)
      SUBPARTITION p0_overflow VALUES THAN MAXVALUE
    )
  );


-- Final table schema:
