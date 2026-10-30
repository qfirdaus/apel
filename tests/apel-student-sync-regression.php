<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$syncFile = $root . '/tools/cron/sync-apel-students.php';
if (!is_file($syncFile)) {
    throw new RuntimeException('Missing APEL student sync command.');
}
$sync = file_get_contents($syncFile) ?: '';

$contracts = [
    "TRIM(student_status) = '02'" => 'Only source status 02 may be provisioned.',
    "'f_identitySource' => 'ONEID'" => 'Existing users must be marked as OneID identities.',
    "f_isAutoProvisioned" => 'Synced accounts must be marked as auto-provisioned.',
    "f_must_change_password" => 'OneID users must not be forced through local password change.',
    "APEL_PROFILE_SYNC" => 'Ownership must identify profile-sync records.',
    "APEL INACTIVE" => 'Ineligible synced users must be disabled without deleting ownership.',
    "'dry-run'" => 'A non-mutating dry-run must be supported.',
    "LOCK_EX | LOCK_NB" => 'Concurrent sync runs must be rejected.',
    "max-deactivate-percent" => 'Bulk deactivation must have a safety threshold.',
    "identifier_hash" => 'Operational output must avoid exposing student identifiers.',
    "APEL_STUDENT_SYNC_RUN_AT" => 'Each run must expose a timestamp for the administration log.',
    "APEL_STUDENT_SYNC_TRIGGER" => 'Each run must identify whether cron or an administrator triggered it.',
    "APEL_STUDENT_SYNC_SKIPPED=no_changes" => 'Scheduled sync must skip full processing output when no data changed.',
    "\$trigger === 'cron'" => 'No-change skipping must apply specifically to scheduled runs.',
];
foreach ($contracts as $needle => $message) {
    if (!str_contains($sync, $needle)) {
        throw new RuntimeException($message);
    }
}

if (str_contains($sync, 'DELETE FROM tbl_m_user') || str_contains($sync, 'DELETE FROM tbl_apel_user')) {
    throw new RuntimeException('APEL sync must never physically delete user or ownership records.');
}

echo "APEL student sync regression PASS\n";
