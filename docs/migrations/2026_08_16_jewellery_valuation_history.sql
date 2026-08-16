-- §8 Jewellery & Gold Valuation fix from docs/BRD_COVERAGE_AUDIT.md:
-- "Valuation history retained" was Missing -- jewellery_items only ever held
-- the current applied_rate/eligible_amount, with no table to keep prior
-- valuations once an item is re-evaluated at a later gold rate.
--
-- One row per valuation event (both the initial evaluate() and every
-- subsequent re_evaluate()), so jewellery_items keeps holding "current" and
-- this table holds "how we got here". Log-style table (created_at only, no
-- updated_at), same shape as the existing gold_rate_approval_log.

CREATE TABLE `jewellery_valuation_history` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `jewellery_item_id` bigint(20) UNSIGNED NOT NULL,
  `gold_rate_id` bigint(20) UNSIGNED NOT NULL,
  `gross_weight` decimal(8,3) NOT NULL,
  `stone_weight` decimal(8,3) NOT NULL DEFAULT 0.000,
  `applied_rate` decimal(10,2) NOT NULL,
  `eligible_percentage` decimal(5,2) NOT NULL,
  `eligible_amount` decimal(12,2) NOT NULL,
  `evaluated_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_jvh_item` (`jewellery_item_id`),
  KEY `fk_jvh_rate` (`gold_rate_id`),
  KEY `fk_jvh_evaluator` (`evaluated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `jewellery_valuation_history`
  ADD CONSTRAINT `fk_jvh_item` FOREIGN KEY (`jewellery_item_id`) REFERENCES `jewellery_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jvh_rate` FOREIGN KEY (`gold_rate_id`) REFERENCES `gold_rates` (`id`),
  ADD CONSTRAINT `fk_jvh_evaluator` FOREIGN KEY (`evaluated_by`) REFERENCES `user_master` (`id`);
