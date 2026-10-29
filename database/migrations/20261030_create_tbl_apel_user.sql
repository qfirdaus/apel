-- APEL project-owned student boundary for IQS Framework user-list extension.
-- This migration is idempotent and does not change tbl_m_user.

CREATE TABLE IF NOT EXISTS `tbl_apel_user` (
  `f_apelUserID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `f_userID` INT NOT NULL,
  `f_student_identifier` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `f_active` TINYINT(1) NOT NULL DEFAULT 1,
  `f_source` VARCHAR(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'APEL',
  `f_createdBy` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `f_createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `f_updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`f_apelUserID`),
  UNIQUE KEY `uq_tbl_apel_user_user` (`f_userID`),
  UNIQUE KEY `uq_tbl_apel_user_identifier` (`f_student_identifier`),
  KEY `idx_tbl_apel_user_active` (`f_active`),
  CONSTRAINT `fk_tbl_apel_user_user`
    FOREIGN KEY (`f_userID`) REFERENCES `tbl_m_user` (`f_userID`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adopt only existing PELAJAR records already referenced by APEL-owned data.
-- Core student-directory records with no APEL relationship remain untouched.
INSERT INTO `tbl_apel_user`
  (`f_userID`, `f_student_identifier`, `f_active`, `f_source`, `f_createdBy`)
SELECT DISTINCT
  u.`f_userID`,
  COALESCE(NULLIF(TRIM(u.`f_loginID`), ''), TRIM(u.`f_stafID`)),
  1,
  'APEL_EXISTING_DATA',
  'migration:20261030'
FROM `tbl_m_user` u
WHERE TRIM(COALESCE(u.`f_categoryUser`, '')) = 'PELAJAR'
  AND COALESCE(NULLIF(TRIM(u.`f_loginID`), ''), TRIM(u.`f_stafID`)) IS NOT NULL
  AND (
    EXISTS (
      SELECT 1 FROM `apelq_applications` a
       WHERE TRIM(a.`student_id`) COLLATE utf8mb4_unicode_ci
             IN (TRIM(u.`f_loginID`), TRIM(u.`f_stafID`))
    )
    OR EXISTS (
      SELECT 1 FROM `apelq_student_profiles` p
       WHERE TRIM(p.`student_id`) COLLATE utf8mb4_unicode_ci
             IN (TRIM(u.`f_loginID`), TRIM(u.`f_stafID`))
    )
  )
ON DUPLICATE KEY UPDATE
  `f_active` = VALUES(`f_active`),
  `f_updatedAt` = CURRENT_TIMESTAMP;

