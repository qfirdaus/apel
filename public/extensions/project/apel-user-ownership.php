<?php
declare(strict_types=1);

/**
 * APEL-owned user boundary.
 *
 * This project file is intentionally outside the IQS Framework sync scope.
 */

function apel_user_ownership_table_exists(PDO $pdo): bool
{
    static $exists;
    if (is_bool($exists)) {
        return $exists;
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
           FROM INFORMATION_SCHEMA.TABLES
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table_name'
    );
    $stmt->execute([':table_name' => 'tbl_apel_user']);
    return $exists = ((int)$stmt->fetchColumn() === 1);
}

function apel_user_identifier(array $record, array $context = []): string
{
    $candidates = [
        $context['identifier'] ?? null,
        $record['matrik'] ?? null,
        $record['f_loginID'] ?? null,
        $record['f_stafID'] ?? null,
        $record['student_id'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        $value = trim((string)$candidate);
        if ($value !== '') {
            return mb_strtoupper($value, 'UTF-8');
        }
    }
    return '';
}

function apel_user_is_owned(PDO $pdo, array $record, array $context = []): bool
{
    if (!apel_user_ownership_table_exists($pdo)) {
        // Before the approved migration is installed, do not silently claim
        // users or alter the behaviour of the framework Student workflow.
        return false;
    }

    $identifier = apel_user_identifier($record, $context);
    if ($identifier === '') {
        return false;
    }

    $stmt = $pdo->prepare(
        'SELECT 1
           FROM tbl_apel_user au
           JOIN tbl_m_user u ON u.f_userID = au.f_userID
          WHERE au.f_active = 1
            AND (
              UPPER(TRIM(au.f_student_identifier)) = :identifier
              OR UPPER(TRIM(COALESCE(u.f_loginID, \'\'))) = :identifier
              OR UPPER(TRIM(COALESCE(u.f_stafID, \'\'))) = :identifier
            )
          LIMIT 1'
    );
    $stmt->execute([':identifier' => $identifier]);
    return (bool)$stmt->fetchColumn();
}

