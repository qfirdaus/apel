<?php
declare(strict_types=1);

require_once __DIR__ . '/apel-user-ownership.php';

return [
    'query_filters' => [
        'student.list' => static function (PDO $pdo, array $context): array {
            if (!apel_user_ownership_table_exists($pdo)) {
                return [];
            }

            $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($context['alias'] ?? 'u')) ?: 'u';
            return [
                'sql' => "NOT EXISTS (
                    SELECT 1
                      FROM tbl_apel_user apel_owner
                     WHERE apel_owner.f_userID = {$alias}.f_userID
                       AND apel_owner.f_active = :apel_owner_active
                )",
                'params' => [':apel_owner_active' => 1],
            ];
        },
    ],
    'sync_guards' => [
        'student.sync' => static function (PDO $pdo, array $record, array $context): bool {
            return !apel_user_is_owned($pdo, $record, $context);
        },
        'student.add' => static function (PDO $pdo, array $record, array $context): bool {
            return !apel_user_is_owned($pdo, $record, $context);
        },
    ],
];

