-- 23-june-2024
ALTER TABLE customer_details ADD COLUMN alternate_mobile_number varchar(50) NOT NULL after company_contact_no;
ALTER TABLE invoices
ADD COLUMN `enquiry_id` INT(11) NULL AFTER `invoice_due_date`,
ADD COLUMN `gst_type` enum('GST', 'IGST') NOT NULL default 'GST' AFTER `enquiry_id`;

ALTER TABLE stock_outward ADD COLUMN igst varchar(20) NOT NULL after sgst;

-- 23-may-2026 GPS clock-in / clock-out validation

-- 1. Add lat/lng to branch_details (company location)
ALTER TABLE `branch_details`
  ADD COLUMN `latitude`  DECIMAL(10,8) NULL AFTER `branch_contact_no`,
  ADD COLUMN `longitude`  DECIMAL(11,8) NULL AFTER `latitude`;

-- 2. Add GPS columns to daily_attendance (clock-in location)
ALTER TABLE `daily_attendance`
  ADD COLUMN `clock_lat`       DECIMAL(10,8) NULL          AFTER `created_by`,
  ADD COLUMN `clock_lng`       DECIMAL(11,8) NULL          AFTER `clock_lat`,
  ADD COLUMN `gps_distance`    DECIMAL(10,2) NULL          AFTER `clock_lng`,
  ADD COLUMN `gps_status`      ENUM('OK','OUT_OF_RANGE','NO_GPS') NOT NULL DEFAULT 'NO_GPS' AFTER `gps_distance`,
  ADD COLUMN `gps_approved`    TINYINT(1)    NOT NULL DEFAULT 0   AFTER `gps_status`,
  ADD COLUMN `gps_approved_by` INT(11)       NULL                 AFTER `gps_approved`,
  ADD COLUMN `out_clock_lat`       DECIMAL(10,8) NULL AFTER `gps_approved_by`,
  ADD COLUMN `out_clock_lng`       DECIMAL(11,8) NULL AFTER `out_clock_lat`,
  ADD COLUMN `out_gps_distance`    DECIMAL(10,2) NULL AFTER `out_clock_lng`,
  ADD COLUMN `out_gps_status`      ENUM('OK','OUT_OF_RANGE','NO_GPS') NOT NULL DEFAULT 'NO_GPS' AFTER `out_gps_distance`,
  ADD COLUMN `out_gps_approved`    TINYINT(1) NOT NULL DEFAULT 0 AFTER `out_gps_status`;

-- 3. Add GPS_RADIUS_METERS to app_config (default 100 metres)
--    Adjust column names if your app_config uses different names (e.g. `name`/`value`)
INSERT INTO `app_config` (`config_key`, `config_value`)
VALUES ('GPS_RADIUS_METERS', '100')
ON DUPLICATE KEY UPDATE `config_value` = `config_value`;

-- 23-may-2026 One-time barcode login
-- Track whether a barcode has already been used to log in
ALTER TABLE `user_device_details`
  ADD COLUMN `barcode_is_used`  TINYINT(1)   NOT NULL DEFAULT 0       AFTER `barcode`,
  ADD COLUMN `barcode_used_at`  DATETIME     NULL                     AFTER `barcode_is_used`;

-- 14-jun-2026 Job management tables
CREATE TABLE IF NOT EXISTS `job_header` (
  `job_id`       INT(11)      NOT NULL AUTO_INCREMENT,
  `branch_id`    INT(11)      NOT NULL DEFAULT 1,
  `title`        VARCHAR(250) NOT NULL,
  `description`  TEXT         NOT NULL,
  `job_type`     ENUM('JOB','INS','OTHERS') NOT NULL DEFAULT 'JOB',
  `priority`     ENUM('LOW','MEDIUM','HIGH') NOT NULL DEFAULT 'MEDIUM',
  `job_date`     DATE         NOT NULL,
  `status`       ENUM('OPEN','IN_PROGRESS','COMPLETED','CANCELLED') NOT NULL DEFAULT 'OPEN',
  `created_by`   INT(11)      NOT NULL DEFAULT 0,
  `created_date` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`job_id`),
  KEY `idx_job_date` (`job_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `job_activity` (
  `activity_id`  INT(11)      NOT NULL AUTO_INCREMENT,
  `job_id`       INT(11)      NOT NULL,
  `employee_id`  INT(11)      NOT NULL,
  `start_time`   DATETIME     NULL,
  `end_time`     DATETIME     NULL,
  `status`       ENUM('ASSIGNED','IN_PROGRESS','COMPLETED','AUTO_CLOSED') NOT NULL DEFAULT 'ASSIGNED',
  `remarks`      TEXT         NOT NULL,
  `photo`        VARCHAR(500) NOT NULL DEFAULT '',
  `updated_by`   INT(11)      NOT NULL DEFAULT 0,
  `created_by`   INT(11)      NOT NULL DEFAULT 0,
  `created_date` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`activity_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_job_id` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 14-jun-2026 Employee leave request table
CREATE TABLE IF NOT EXISTS `employee_leave_request` (
  `leave_id`    INT(11)      NOT NULL AUTO_INCREMENT,
  `emp_id`      INT(11)      NOT NULL,
  `leave_type`  ENUM('CASUAL','SICK','EARNED','UNPAID') NOT NULL DEFAULT 'CASUAL',
  `leave_from`  DATE         NOT NULL,
  `leave_to`    DATE         NOT NULL,
  `total_days`  INT(11)      NOT NULL DEFAULT 1,
  `reason`      TEXT         NOT NULL,
  `status`      ENUM('PENDING','APPROVED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `approved_by` INT(11)      NULL,
  `approved_at` DATETIME     NULL,
  `updated_at`  DATETIME     NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by`  INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`leave_id`),
  KEY `idx_emp_id` (`emp_id`),
  KEY `idx_leave_dates` (`leave_from`, `leave_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Add is_employee flag, designation, dob and marriage_date to users if not present
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `is_employee`    TINYINT(1)   NOT NULL DEFAULT 1    AFTER `status`,
  ADD COLUMN IF NOT EXISTS `designation`    VARCHAR(150) NOT NULL DEFAULT ''   AFTER `is_employee`,
  ADD COLUMN IF NOT EXISTS `dob`            DATE         NULL                  AFTER `designation`,
  ADD COLUMN IF NOT EXISTS `marriage_date`  DATE         NULL                  AFTER `dob`;

-- 14-jun-2026 Employee joining / position tracking
CREATE TABLE IF NOT EXISTS `employee_joining` (
  `ej_id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `branch_id`    INT(11)      NOT NULL DEFAULT 1,
  `employee_id`  INT(11)      NOT NULL,
  `doj_date`     DATE         NULL COMMENT 'Date of joining',
  `doe_date`     DATE         NULL COMMENT 'Date of exit / relieving',
  `position`     VARCHAR(150) NOT NULL DEFAULT '',
  `department`   VARCHAR(150) NOT NULL DEFAULT '',
  `created_by`   INT(11)      NOT NULL DEFAULT 0,
  `created_date` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ej_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_doj_date`    (`doj_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 14-jun-2026 Expand job_header for AC service / dealer context
-- (also covers ALL base columns in case table was created without them)
ALTER TABLE `job_header`
  ADD COLUMN IF NOT EXISTS `branch_id`      INT(11)       NOT NULL DEFAULT 1      AFTER `job_id`,
  ADD COLUMN IF NOT EXISTS `title`          VARCHAR(250)  NOT NULL DEFAULT ''      AFTER `branch_id`,
  ADD COLUMN IF NOT EXISTS `description`    TEXT          NOT NULL                 AFTER `title`,
  ADD COLUMN IF NOT EXISTS `job_type`       ENUM('JOB','INS','OTHERS') NOT NULL DEFAULT 'JOB' AFTER `description`,
  ADD COLUMN IF NOT EXISTS `priority`       ENUM('LOW','MEDIUM','HIGH') NOT NULL DEFAULT 'MEDIUM' AFTER `job_type`,
  ADD COLUMN IF NOT EXISTS `job_date`       DATE          NOT NULL DEFAULT '2000-01-01' AFTER `priority`,
  ADD COLUMN IF NOT EXISTS `status`         ENUM('OPEN','IN_PROGRESS','COMPLETED','CANCELLED') NOT NULL DEFAULT 'OPEN' AFTER `job_date`,
  ADD COLUMN IF NOT EXISTS `created_by`     INT(11)       NOT NULL DEFAULT 0       AFTER `status`,
  ADD COLUMN IF NOT EXISTS `created_date`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_by`,
  ADD COLUMN IF NOT EXISTS `service_type`   ENUM('SERVICE','INSTALLATION','REPAIR','GAS_REFILL','AMC','INSPECTION','DEALER_VISIT','REWORK') NOT NULL DEFAULT 'SERVICE' AFTER `job_type`,
  ADD COLUMN IF NOT EXISTS `customer_id`    INT(11)       NULL                     AFTER `service_type`,
  ADD COLUMN IF NOT EXISTS `customer_name`  VARCHAR(200)  NOT NULL DEFAULT ''      AFTER `customer_id`,
  ADD COLUMN IF NOT EXISTS `customer_phone` VARCHAR(20)   NOT NULL DEFAULT ''      AFTER `customer_name`,
  ADD COLUMN IF NOT EXISTS `service_address` TEXT         NOT NULL                 AFTER `customer_phone`,
  ADD COLUMN IF NOT EXISTS `service_lat`    DECIMAL(10,8) NULL                     AFTER `service_address`,
  ADD COLUMN IF NOT EXISTS `service_lng`    DECIMAL(11,8) NULL                     AFTER `service_lat`,
  ADD COLUMN IF NOT EXISTS `ac_brand`       VARCHAR(100)  NOT NULL DEFAULT ''      AFTER `service_lng`,
  ADD COLUMN IF NOT EXISTS `ac_model`       VARCHAR(100)  NOT NULL DEFAULT ''      AFTER `ac_brand`,
  ADD COLUMN IF NOT EXISTS `ac_serial_no`   VARCHAR(100)  NOT NULL DEFAULT ''      AFTER `ac_model`,
  ADD COLUMN IF NOT EXISTS `ac_tonnage`     DECIMAL(4,2)  NULL                     AFTER `ac_serial_no`,
  ADD COLUMN IF NOT EXISTS `ac_type`        ENUM('SPLIT','CASSETTE','WINDOW','CENTRAL','DUCTABLE') NOT NULL DEFAULT 'SPLIT' AFTER `ac_tonnage`,
  ADD COLUMN IF NOT EXISTS `ac_count`       TINYINT(3)    NOT NULL DEFAULT 1       AFTER `ac_type`,
  ADD COLUMN IF NOT EXISTS `scheduled_date` DATE          NULL                     AFTER `ac_count`,
  ADD COLUMN IF NOT EXISTS `scheduled_time` TIME          NULL                     AFTER `scheduled_date`,
  ADD COLUMN IF NOT EXISTS `estimated_cost` DECIMAL(10,2) NOT NULL DEFAULT 0       AFTER `scheduled_time`,
  ADD COLUMN IF NOT EXISTS `actual_cost`    DECIMAL(10,2) NOT NULL DEFAULT 0       AFTER `estimated_cost`,
  ADD COLUMN IF NOT EXISTS `payment_status` ENUM('UNPAID','PARTIAL','PAID')        NOT NULL DEFAULT 'UNPAID' AFTER `actual_cost`,
  ADD COLUMN IF NOT EXISTS `payment_mode`   VARCHAR(50)   NOT NULL DEFAULT ''      AFTER `payment_status`,
  ADD COLUMN IF NOT EXISTS `notes`          TEXT          NOT NULL                 AFTER `payment_mode`,
  ADD COLUMN IF NOT EXISTS `updated_by`     INT(11)       NOT NULL DEFAULT 0       AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `updated_date`   DATETIME      NULL                     AFTER `updated_by`;

-- 14-jun-2026 Job Closure tables
-- Master closure checklist (admin-configurable, applies to all job types)
CREATE TABLE IF NOT EXISTS `job_closure_checklist` (
  `cc_id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `service_type` VARCHAR(50)  NOT NULL DEFAULT 'ALL' COMMENT 'ALL = applies to every job type',
  `category`     VARCHAR(100) NOT NULL DEFAULT '',
  `item_name`    VARCHAR(250) NOT NULL,
  `item_code`    VARCHAR(50)  NOT NULL DEFAULT '',
  `is_mandatory` TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`   INT(11)      NOT NULL DEFAULT 0,
  `status`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_by`   INT(11)      NOT NULL DEFAULT 0,
  `created_date` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cc_id`),
  KEY `idx_service_type` (`service_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Default closure checklist items
INSERT INTO `job_closure_checklist`
  (`service_type`,`category`,         `item_name`,                              `item_code`,             `is_mandatory`,`sort_order`) VALUES
  ('ALL',          'Site',            'Work area cleaned and cleared',          'SITE_CLEAN',            1,             10),
  ('ALL',          'Site',            'Tools and equipment removed from site',  'TOOLS_REMOVED',         1,             20),
  ('ALL',          'Documentation',   'Customer informed about work done',      'CUSTOMER_INFORMED',     1,             30),
  ('ALL',          'Documentation',   'Job completion form signed by customer', 'CUSTOMER_SIGNED',       1,             40),
  ('ALL',          'Documentation',   'Invoice / receipt given to customer',    'INVOICE_GIVEN',         0,             50),
  ('ALL',          'Documentation',   'Warranty card issued (if applicable)',   'WARRANTY_CARD',         0,             60),
  ('ALL',          'Quality',         'Work quality verified by technician',    'QUALITY_VERIFIED',      1,             70),
  ('ALL',          'Quality',         'Customer satisfaction confirmed',        'CUST_SATISFIED',        1,             80),
  ('SERVICE',      'AC Unit',         'AC cooling tested after service',        'COOLING_TESTED',        1,             90),
  ('SERVICE',      'AC Unit',         'Remote and controls working properly',   'REMOTE_WORKING',        1,             100),
  ('INSTALLATION', 'AC Unit',         'AC installation tested and running',     'INSTALL_TESTED',        1,             90),
  ('INSTALLATION', 'AC Unit',         'Electrical connections verified safe',   'ELEC_VERIFIED',         1,             100),
  ('INSTALLATION', 'AC Unit',         'Demo given to customer',                 'DEMO_GIVEN',            1,             110),
  ('REPAIR',       'AC Unit',         'Repaired unit tested under load',        'REPAIR_TESTED',         1,             90),
  ('GAS_REFILL',   'Refrigerant',     'Gas pressure checked post-refill',       'GAS_PRESSURE_OK',       1,             90),
  ('GAS_REFILL',   'Refrigerant',     'No leakage after refill',                'NO_LEAKAGE',            1,             100);

-- Job closure header (one per job)
CREATE TABLE IF NOT EXISTS `job_closure` (
  `closure_id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `job_id`            BIGINT(20)   NOT NULL,
  `activity_id`       BIGINT(20)   NULL,
  `closed_by`         INT(11)      NOT NULL,
  `work_summary`      TEXT         NOT NULL,
  `customer_feedback` TEXT         NOT NULL,
  `customer_rating`   TINYINT(1)   NULL COMMENT '1-5 star rating',
  `customer_sign`     VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'base64 or file path',
  `actual_cost`       DECIMAL(10,2) NOT NULL DEFAULT 0,
  `payment_status`    ENUM('UNPAID','PARTIAL','PAID') NOT NULL DEFAULT 'UNPAID',
  `payment_mode`      VARCHAR(50)  NOT NULL DEFAULT '',
  `amount_received`   DECIMAL(10,2) NOT NULL DEFAULT 0,
  `balance_amount`    DECIMAL(10,2) NOT NULL DEFAULT 0,
  `closed_at`         DATETIME     NOT NULL,
  `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`closure_id`),
  UNIQUE KEY `uk_job_id` (`job_id`),
  KEY `idx_closed_by` (`closed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Closure checklist answers
CREATE TABLE IF NOT EXISTS `job_closure_answers` (
  `ca_id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `closure_id`   INT(11)      NOT NULL,
  `cc_id`        INT(11)      NOT NULL,
  `item_code`    VARCHAR(50)  NOT NULL DEFAULT '',
  `is_done`      TINYINT(1)   NOT NULL DEFAULT 1,
  `remark`       VARCHAR(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`ca_id`),
  KEY `idx_closure_id` (`closure_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Job completion photos (multiple, typed)
CREATE TABLE IF NOT EXISTS `job_completion_photos` (
  `jp_id`       INT(11)      NOT NULL AUTO_INCREMENT,
  `job_id`      BIGINT(20)   NOT NULL,
  `closure_id`  INT(11)      NULL,
  `activity_id` BIGINT(20)   NULL,
  `photo_type`  ENUM('BEFORE','AFTER','WORK_DONE','MATERIAL_USED','ISSUE','CUSTOMER_SIGN','OTHER') NOT NULL DEFAULT 'WORK_DONE',
  `file_path`   VARCHAR(500) NOT NULL,
  `caption`     VARCHAR(250) NOT NULL DEFAULT '',
  `uploaded_by` INT(11)      NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`jp_id`),
  KEY `idx_job_id`     (`job_id`),
  KEY `idx_closure_id` (`closure_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 14-jun-2026 Job Inspection tables
-- Master checklist template (admin-configurable per service_type)
CREATE TABLE IF NOT EXISTS `job_inspection_checklist` (
  `ic_id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `service_type` VARCHAR(50)  NOT NULL DEFAULT 'INSPECTION' COMMENT 'matches job_header.service_type',
  `category`     VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'e.g. Electrical, Mechanical, Refrigerant',
  `item_name`    VARCHAR(250) NOT NULL,
  `item_code`    VARCHAR(50)  NOT NULL DEFAULT '',
  `answer_type`  ENUM('PASS_FAIL','YES_NO','RATING_1_5','TEXT','NUMERIC') NOT NULL DEFAULT 'PASS_FAIL',
  `is_mandatory` TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`   INT(11)      NOT NULL DEFAULT 0,
  `status`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_by`   INT(11)      NOT NULL DEFAULT 0,
  `created_date` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ic_id`),
  KEY `idx_service_type` (`service_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Default checklist items for AC inspection
INSERT INTO `job_inspection_checklist`
  (`service_type`, `category`,       `item_name`,                          `item_code`,            `answer_type`, `is_mandatory`, `sort_order`) VALUES
  ('INSPECTION',   'General',        'AC Unit Running Condition',          'RUNNING_CONDITION',    'PASS_FAIL',   1,              10),
  ('INSPECTION',   'General',        'Remote Control Working',             'REMOTE_WORKING',       'YES_NO',      1,              20),
  ('INSPECTION',   'Electrical',     'Power Supply Voltage (Volts)',       'POWER_VOLTAGE',        'NUMERIC',     1,              30),
  ('INSPECTION',   'Electrical',     'Running Current (Amps)',             'RUNNING_CURRENT',      'NUMERIC',     1,              40),
  ('INSPECTION',   'Electrical',     'Capacitor Condition',                'CAPACITOR_CONDITION',  'PASS_FAIL',   1,              50),
  ('INSPECTION',   'Electrical',     'Wiring & Connections',               'WIRING',               'PASS_FAIL',   1,              60),
  ('INSPECTION',   'Mechanical',     'Indoor Coil Condition',              'INDOOR_COIL',          'PASS_FAIL',   1,              70),
  ('INSPECTION',   'Mechanical',     'Outdoor Coil Condition',             'OUTDOOR_COIL',         'PASS_FAIL',   1,              80),
  ('INSPECTION',   'Mechanical',     'Blower / Fan Motor Condition',       'FAN_MOTOR',            'PASS_FAIL',   1,              90),
  ('INSPECTION',   'Mechanical',     'Drain Pipe Clear',                   'DRAIN_PIPE',           'YES_NO',      1,              100),
  ('INSPECTION',   'Mechanical',     'Air Filter Condition',               'AIR_FILTER',           'PASS_FAIL',   1,              110),
  ('INSPECTION',   'Refrigerant',    'Gas Pressure (PSI)',                 'GAS_PRESSURE',         'NUMERIC',     1,              120),
  ('INSPECTION',   'Refrigerant',    'Gas Leakage',                        'GAS_LEAKAGE',          'YES_NO',      1,              130),
  ('INSPECTION',   'Refrigerant',    'Cooling Efficiency',                 'COOLING_EFFICIENCY',   'RATING_1_5',  1,              140),
  ('INSPECTION',   'Performance',    'Thermostat Function',                'THERMOSTAT',           'PASS_FAIL',   0,              150),
  ('INSPECTION',   'Performance',    'Noise Level',                        'NOISE_LEVEL',          'RATING_1_5',  0,              160),
  ('INSPECTION',   'Performance',    'Vibration',                          'VIBRATION',            'PASS_FAIL',   0,              170),
  ('INSPECTION',   'General',        'Technician Remarks',                 'TECH_REMARKS',         'TEXT',        0,              180);

-- Per-job inspection result header
CREATE TABLE IF NOT EXISTS `job_inspection` (
  `insp_id`       INT(11)      NOT NULL AUTO_INCREMENT,
  `job_id`        BIGINT(20)   NOT NULL,
  `activity_id`   BIGINT(20)   NULL,
  `employee_id`   INT(11)      NOT NULL,
  `ac_unit_no`    TINYINT(3)   NOT NULL DEFAULT 1 COMMENT 'Which AC unit (1..ac_count)',
  `ac_serial_no`  VARCHAR(100) NOT NULL DEFAULT '',
  `overall_result`ENUM('PASS','FAIL','NEEDS_SERVICE','NEEDS_GAS','PENDING') NOT NULL DEFAULT 'PENDING',
  `gas_pressure`  DECIMAL(6,2) NULL,
  `running_current` DECIMAL(5,2) NULL,
  `power_voltage` DECIMAL(6,2) NULL,
  `technician_remarks` TEXT    NOT NULL,
  `customer_sign` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'base64 or file path of customer signature',
  `inspected_at`  DATETIME     NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`insp_id`),
  KEY `idx_job_id`  (`job_id`),
  KEY `idx_emp_id`  (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Per-checklist-item answers for a job inspection
CREATE TABLE IF NOT EXISTS `job_inspection_answers` (
  `ia_id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `insp_id`       INT(11)      NOT NULL,
  `ic_id`         INT(11)      NOT NULL COMMENT 'FK to job_inspection_checklist',
  `item_code`     VARCHAR(50)  NOT NULL DEFAULT '',
  `answer_value`  VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'PASS/FAIL/YES/NO/numeric/text',
  `is_ok`         TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1=ok/pass, 0=issue found',
  `remark`        VARCHAR(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`ia_id`),
  KEY `idx_insp_id` (`insp_id`),
  KEY `idx_ic_id`   (`ic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Photos captured during inspection
CREATE TABLE IF NOT EXISTS `job_inspection_photos` (
  `ip_id`       INT(11)      NOT NULL AUTO_INCREMENT,
  `insp_id`     INT(11)      NOT NULL,
  `job_id`      BIGINT(20)   NOT NULL,
  `photo_type`  ENUM('BEFORE','AFTER','ISSUE','UNIT','GENERAL') NOT NULL DEFAULT 'GENERAL',
  `file_path`   VARCHAR(500) NOT NULL,
  `caption`     VARCHAR(250) NOT NULL DEFAULT '',
  `created_by`  INT(11)      NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ip_id`),
  KEY `idx_insp_id` (`insp_id`),
  KEY `idx_job_id`  (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 23-may-2026 Login history (location + device info)
CREATE TABLE IF NOT EXISTS `login_history` (
  `lh_id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11)      NULL,
  `device_id`    INT(11)      NULL,
  `device_uuid`  VARCHAR(100) NOT NULL DEFAULT '',
  `login_type`   ENUM('password','barcode') NOT NULL DEFAULT 'password',
  `lat`          DECIMAL(10,8) NULL,
  `lng`          DECIMAL(11,8) NULL,
  `ip_address`   VARCHAR(45)  NOT NULL DEFAULT '',
  `device_info`  VARCHAR(500) NOT NULL DEFAULT '',
  `status`       ENUM('success','failure') NOT NULL DEFAULT 'success',
  `fail_reason`  VARCHAR(250) NOT NULL DEFAULT '',
  `login_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`lh_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_login_at` (`login_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




--
-- Table structure for table `job_activity`
--

CREATE TABLE `job_activity` (
  `activity_id` bigint(20) NOT NULL,
  `job_id` bigint(20) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `is_auto_closed` tinyint(1) DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_activity`
--
ALTER TABLE `job_activity`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_emp_date` (`employee_id`,`start_time`),
  ADD KEY `job_id` (`job_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `job_activity`
--
ALTER TABLE `job_activity`
  MODIFY `activity_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `job_activity`
--
ALTER TABLE `job_activity`
  ADD CONSTRAINT `job_activity_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_header` (`job_id`);
COMMIT;


--
-- Table structure for table `job_employee`
--

CREATE TABLE `job_employee` (
  `job_emp_id` bigint(20) NOT NULL,
  `job_id` bigint(20) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `role` enum('PRIMARY','HELPER','SUPERVISOR','DEALER_EXEC','OWNER') DEFAULT NULL,
  `assigned_from` datetime DEFAULT NULL,
  `assigned_to` datetime DEFAULT NULL,
  `status` enum('ACTIVE','REMOVED') DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_employee`
--
ALTER TABLE `job_employee`
  ADD PRIMARY KEY (`job_emp_id`),
  ADD KEY `job_id` (`job_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `job_employee`
--
ALTER TABLE `job_employee`
  MODIFY `job_emp_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `job_employee`
--
ALTER TABLE `job_employee`
  ADD CONSTRAINT `job_employee_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_header` (`job_id`);
COMMIT;


--
-- Table structure for table `job_material_used`
--

CREATE TABLE `job_material_used` (
  `jm_id` bigint(20) NOT NULL,
  `job_id` bigint(20) DEFAULT NULL,
  `activity_id` bigint(20) DEFAULT NULL,
  `p_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `issued_from` enum('BRANCH','TECHNICIAN','BOUGHT') DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_material_used`
--
ALTER TABLE `job_material_used`
  ADD PRIMARY KEY (`jm_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `p_id` (`p_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `job_material_used`
--
ALTER TABLE `job_material_used`
  MODIFY `jm_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `job_material_used`
--
ALTER TABLE `job_material_used`
  ADD CONSTRAINT `job_material_used_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_header` (`job_id`),
  ADD CONSTRAINT `job_material_used_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `job_activity` (`activity_id`),
  ADD CONSTRAINT `job_material_used_ibfk_3` FOREIGN KEY (`p_id`) REFERENCES `products` (`p_id`);
COMMIT;

--
-- Table structure for table `job_serial_items`
--

CREATE TABLE `job_serial_items` (
  `js_id` bigint(20) NOT NULL,
  `job_id` bigint(20) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `installed_at` datetime DEFAULT NULL,
  `removed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_serial_items`
--
ALTER TABLE `job_serial_items`
  ADD PRIMARY KEY (`js_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `job_serial_items`
--
ALTER TABLE `job_serial_items`
  MODIFY `js_id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;


--
-- Table structure for table `job_type_weight`
--

CREATE TABLE `job_type_weight` (
  `job_type` varchar(30) NOT NULL,
  `weight` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_type_weight`
--

INSERT INTO `job_type_weight` (`job_type`, `weight`) VALUES
('AMC', 10.00),
('INSPECTION', 15.00),
('INSTALLATION', 50.00),
('REPAIR', 20.00),
('REWORK', 10.00),
('SERVICE', 25.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_type_weight`
--
ALTER TABLE `job_type_weight`
  ADD PRIMARY KEY (`job_type`);
COMMIT;

--
-- Table structure for table `job_work_done`
--

CREATE TABLE `job_work_done` (
  `work_id` bigint(20) NOT NULL,
  `job_id` bigint(20) DEFAULT NULL,
  `activity_id` bigint(20) DEFAULT NULL,
  `work_type` enum('INSTALLATION','SERVICE','REPAIR','GAS_REFILL','INSPECTION','DEALER_VISIT') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `spare_used` text DEFAULT NULL,
  `work_status` enum('DONE','PARTIAL') DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_work_done`
--
ALTER TABLE `job_work_done`
  ADD PRIMARY KEY (`work_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `job_work_done`
--
ALTER TABLE `job_work_done`
  MODIFY `work_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `job_work_done`
--
ALTER TABLE `job_work_done`
  ADD CONSTRAINT `job_work_done_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_header` (`job_id`),
  ADD CONSTRAINT `job_work_done_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `job_activity` (`activity_id`);
COMMIT;