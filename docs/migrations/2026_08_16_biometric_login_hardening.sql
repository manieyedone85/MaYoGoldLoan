-- Hardens BRD §15 "optional biometric" login (docs/BRD_COVERAGE_AUDIT.md),
-- found during code review to be exploitable as originally implemented:
-- `template_ref` was matched by plain equality with no minimum length/entropy
-- enforced at enrollment, and was not bound to the specific device that
-- enrolled it -- any device already in user_device_bindings (bindings are
-- never deactivated) could replay a leaked/guessed template_ref. Scoping the
-- credential to its enrolling device closes the cross-device replay path;
-- app-level rate limiting on login attempts closes the brute-force path
-- (application/controllers/api/v1/Auth.php::biometric_login()).

ALTER TABLE `user_biometric_ref`
  ADD COLUMN `device_id` VARCHAR(191) DEFAULT NULL AFTER `user_id`;
