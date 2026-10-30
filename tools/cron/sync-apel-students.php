<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

$options = getopt('', ['dry-run', 'force-deactivate', 'max-deactivate-percent::']);
$dryRun = array_key_exists('dry-run', $options);
$forceDeactivate = array_key_exists('force-deactivate', $options);
$maxDeactivatePercent = max(0.0, min(100.0, (float)($options['max-deactivate-percent'] ?? 20)));

$root = dirname(__DIR__, 2);
$lockDir = $root . '/storage/locks';
$logDir = $root . '/storage/logs';
foreach ([$lockDir, $logDir] as $directory) {
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        fwrite(STDERR, "Unable to create runtime directory: {$directory}\n");
        exit(1);
    }
}

$lockHandle = fopen($lockDir . '/apel-student-sync.internal.lock', 'c');
if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDOUT, "APEL_STUDENT_SYNC_SKIPPED=already_running\n");
    exit(0);
}

define('ALLOW_ANON_AJAX', true);
$_SERVER['SCRIPT_NAME'] = '/tools/cron/sync-apel-students.php';
$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
require_once $root . '/public/includes/init.php';
require_once $root . '/public/extensions/project/apel-user-ownership.php';

$stats = [
    'source' => 0,
    'created' => 0,
    'updated' => 0,
    'unchanged' => 0,
    'ownership_created' => 0,
    'ownership_updated' => 0,
    'reactivated' => 0,
    'deactivated' => 0,
    'errors' => 0,
];
$errors = [];
$startedAt = microtime(true);
$actor = 'cron:apel-student-sync';

/** @return string */
$clean = static fn(mixed $value): string => trim((string)$value);

$audit = static function (string $eventType, string $outcome, string $targetId, string $targetLabel, array $meta): void {
    if (!function_exists('audit_event')) {
        return;
    }
    audit_event([
        'event_type' => $eventType,
        'severity' => $outcome === 'SUCCESS' ? 'INFO' : 'ERROR',
        'outcome' => $outcome,
        'target_type' => 'apel_student_sync',
        'target_id' => $targetId,
        'target_label' => $targetLabel,
        'message' => 'APEL student profile synchronization',
        'actor_label' => 'APEL Student Sync',
        'login_id' => 'SYSTEM',
        'meta' => array_merge(['source' => 'apelq_student_profiles'], $meta),
    ]);
};

try {
    $pdo = Database::getInstance('mysql')->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (!apel_user_ownership_table_exists($pdo)) {
        throw new RuntimeException('tbl_apel_user is not installed.');
    }

    $groupStmt = $pdo->prepare(
        "SELECT f_groupID, f_groupKod, f_groupName
           FROM tbl_m_group
          WHERE UPPER(TRIM(COALESCE(f_groupKod, ''))) = 'ADM-STUDENT'
            AND UPPER(TRIM(COALESCE(f_categoryUser, ''))) = 'PELAJAR'
          LIMIT 1"
    );
    $groupStmt->execute();
    $group = $groupStmt->fetch(PDO::FETCH_ASSOC);
    if (!$group) {
        throw new RuntimeException('Student group ADM-STUDENT is unavailable.');
    }

    $sourceStmt = $pdo->query(
        "SELECT student_id, full_name, ic_no, official_email, personal_email,
                phone_no, program_code, program_name, faculty_code, faculty_name,
                student_status, updated_at
           FROM apelq_student_profiles
          WHERE TRIM(student_status) = '02'
       ORDER BY student_id"
    );
    $profiles = $sourceStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $stats['source'] = count($profiles);

    $findUser = $pdo->prepare(
        "SELECT * FROM tbl_m_user
          WHERE UPPER(TRIM(COALESCE(f_loginID, ''))) = :login_identifier
             OR UPPER(TRIM(COALESCE(f_stafID, ''))) = :staff_identifier
       ORDER BY f_userID
          LIMIT 2
          FOR UPDATE"
    );
    $insertUser = $pdo->prepare(
        "INSERT INTO tbl_m_user
            (f_loginID, f_stafID, f_categoryUser, f_groupID, f_groupKod,
             f_nama, f_nickname, f_nokp, f_password, f_must_change_password,
             f_email, f_handphone, f_jabatanKod, f_namajabatan,
             f_statusID, f_status, f_flag, f_insertdt, f_updatedt, f_updateby,
             f_remarks, f_isAutoProvisioned, f_identitySource)
         VALUES
            (:login_identifier, :staff_identifier, 'PELAJAR', :group_id, :group_code,
             :full_name, :nickname, :ic, :password, 0,
             :email, :phone, :faculty_code, :faculty_name,
             NULL, 'APEL', 1, NOW(), NOW(), :actor,
             :remarks, 1, 'ONEID')"
    );
    $findOwner = $pdo->prepare('SELECT * FROM tbl_apel_user WHERE f_userID = :user_id OR UPPER(TRIM(f_student_identifier)) = :identifier LIMIT 1 FOR UPDATE');
    $insertOwner = $pdo->prepare(
        "INSERT INTO tbl_apel_user
            (f_userID, f_student_identifier, f_active, f_source, f_createdBy)
         VALUES (:user_id, :identifier, 1, 'APEL_PROFILE_SYNC', :actor)"
    );
    $updateOwner = $pdo->prepare(
        "UPDATE tbl_apel_user
            SET f_student_identifier = :identifier, f_active = 1,
                f_source = 'APEL_PROFILE_SYNC', f_updatedAt = NOW()
          WHERE f_apelUserID = :owner_id"
    );

    foreach ($profiles as $profile) {
        $identifier = mb_strtoupper($clean($profile['student_id'] ?? ''), 'UTF-8');
        $name = $clean($profile['full_name'] ?? '');
        try {
            if ($identifier === '' || mb_strlen($identifier) > 10 || $name === '') {
                throw new RuntimeException('Invalid student identifier or name.');
            }
            $email = mb_strtolower($clean($profile['official_email'] ?? '') ?: $clean($profile['personal_email'] ?? ''), 'UTF-8');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Invalid student email.');
            }
            $remarks = mb_substr(trim(sprintf(
                'APEL programme: %s%s; synced from apelq_student_profiles',
                $clean($profile['program_code'] ?? ''),
                $clean($profile['program_name'] ?? '') !== '' ? ' - ' . $clean($profile['program_name']) : ''
            )), 0, 255);

            $pdo->beginTransaction();
            $findUser->execute([
                ':login_identifier' => $identifier,
                ':staff_identifier' => $identifier,
            ]);
            $matches = $findUser->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (count($matches) > 1) {
                throw new RuntimeException('Identifier matches multiple authentication records.');
            }

            $reactivated = false;
            $recordChanged = false;
            $operation = 'UNCHANGED';
            if ($matches === []) {
                if (!$dryRun) {
                    $insertUser->execute([
                        ':login_identifier' => $identifier,
                        ':staff_identifier' => $identifier,
                        ':group_id' => (int)$group['f_groupID'],
                        ':group_code' => (string)$group['f_groupKod'],
                        ':full_name' => $name,
                        ':nickname' => $name,
                        ':ic' => $clean($profile['ic_no'] ?? '') ?: null,
                        ':password' => password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT),
                        ':email' => $email !== '' ? $email : null,
                        ':phone' => $clean($profile['phone_no'] ?? '') ?: null,
                        ':faculty_code' => $clean($profile['faculty_code'] ?? '') ?: null,
                        ':faculty_name' => $clean($profile['faculty_name'] ?? '') ?: null,
                        ':actor' => $actor,
                        ':remarks' => $remarks,
                    ]);
                    $userID = (int)$pdo->lastInsertId();
                } else {
                    $userID = -1;
                }
                $stats['created']++;
                $recordChanged = true;
                $operation = 'CREATE';
            } else {
                $user = $matches[0];
                $userID = (int)$user['f_userID'];
                $wasSyncInactive = strtoupper($clean($user['f_status'] ?? '')) === 'APEL INACTIVE';
                $desired = [
                    'f_loginID' => $identifier,
                    'f_stafID' => $identifier,
                    'f_categoryUser' => 'PELAJAR',
                    'f_groupID' => (int)$group['f_groupID'],
                    'f_groupKod' => (string)$group['f_groupKod'],
                    'f_nama' => $name,
                    'f_nickname' => $name,
                    'f_nokp' => $clean($profile['ic_no'] ?? '') ?: null,
                    'f_email' => $email !== '' ? $email : null,
                    'f_handphone' => $clean($profile['phone_no'] ?? '') ?: null,
                    'f_jabatanKod' => $clean($profile['faculty_code'] ?? '') ?: null,
                    'f_namajabatan' => $clean($profile['faculty_name'] ?? '') ?: null,
                    'f_must_change_password' => 0,
                    'f_isAutoProvisioned' => 1,
                    'f_identitySource' => 'ONEID',
                    'f_statusID' => null,
                    'f_status' => 'APEL',
                    'f_remarks' => $remarks,
                ];
                if ($wasSyncInactive) {
                    $desired['f_flag'] = 1;
                    $reactivated = true;
                }
                $sets = [];
                $params = [':user_id' => $userID, ':actor' => $actor];
                foreach ($desired as $column => $value) {
                    $current = $user[$column] ?? null;
                    if ((string)($current ?? '') === (string)($value ?? '')) {
                        continue;
                    }
                    $sets[] = "{$column} = :{$column}";
                    $params[':' . $column] = $value;
                }
                if ($sets !== []) {
                    if (!$dryRun) {
                        $sql = 'UPDATE tbl_m_user SET ' . implode(', ', $sets) . ', f_updatedt = NOW(), f_updateby = :actor WHERE f_userID = :user_id';
                        $pdo->prepare($sql)->execute($params);
                    }
                    $stats['updated']++;
                    $recordChanged = true;
                    $operation = 'UPDATE';
                    if ($reactivated) {
                        $stats['reactivated']++;
                    }
                } else {
                    $stats['unchanged']++;
                }
            }

            if ($userID > 0) {
                $findOwner->execute([':user_id' => $userID, ':identifier' => $identifier]);
                $owner = $findOwner->fetch(PDO::FETCH_ASSOC);
                if (!$owner) {
                    if (!$dryRun) {
                        $insertOwner->execute([':user_id' => $userID, ':identifier' => $identifier, ':actor' => $actor]);
                    }
                    $stats['ownership_created']++;
                    $recordChanged = true;
                } elseif ((int)$owner['f_active'] !== 1
                    || strtoupper($clean($owner['f_student_identifier'])) !== $identifier
                    || $clean($owner['f_source']) !== 'APEL_PROFILE_SYNC'
                ) {
                    if (!$dryRun) {
                        $updateOwner->execute([':identifier' => $identifier, ':owner_id' => (int)$owner['f_apelUserID']]);
                    }
                    $stats['ownership_updated']++;
                    $recordChanged = true;
                }
            } elseif ($dryRun) {
                $stats['ownership_created']++;
                $recordChanged = true;
            }

            if (!$dryRun && $recordChanged) {
                $audit($operation === 'CREATE' ? 'CREATE' : 'UPDATE', 'SUCCESS', (string)$userID, 'APEL Student Sync', [
                    'identifier_hash' => hash('sha256', $identifier),
                    'reactivated' => $reactivated,
                ]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $stats['errors']++;
            $errors[] = ['identifier_hash' => hash('sha256', $identifier), 'error' => $e->getMessage()];
        }
    }

    // Reconcile only records previously adopted by this sync. Manual APEL users
    // remain untouched until they appear as an eligible source profile.
    $deactivateStmt = $pdo->query(
        "SELECT au.f_userID, au.f_student_identifier
           FROM tbl_apel_user au
           JOIN tbl_m_user u ON u.f_userID = au.f_userID
      LEFT JOIN apelq_student_profiles p
             ON UPPER(TRIM(p.student_id)) = UPPER(TRIM(au.f_student_identifier))
            AND TRIM(p.student_status) = '02'
          WHERE au.f_active = 1
            AND au.f_source = 'APEL_PROFILE_SYNC'
            AND p.id IS NULL
            AND UPPER(TRIM(COALESCE(u.f_status, ''))) <> 'APEL INACTIVE'"
    );
    $toDeactivate = $deactivateStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $managed = (int)$pdo->query("SELECT COUNT(*) FROM tbl_apel_user WHERE f_active = 1 AND f_source = 'APEL_PROFILE_SYNC'")->fetchColumn();
    $deactivatePercent = $managed > 0 ? (count($toDeactivate) / $managed) * 100 : 0.0;
    $allowDeactivate = $stats['source'] > 0
        && ($forceDeactivate || count($toDeactivate) <= 1 || $deactivatePercent <= $maxDeactivatePercent);

    if ($toDeactivate !== [] && !$allowDeactivate) {
        $errors[] = [
            'identifier_hash' => null,
            'error' => sprintf('Safety stop: %d of %d managed accounts would be deactivated (%.2f%%).', count($toDeactivate), $managed, $deactivatePercent),
        ];
        $stats['errors']++;
    } elseif ($toDeactivate !== []) {
        $disable = $pdo->prepare(
            "UPDATE tbl_m_user
                SET f_flag = 0, f_statusID = 9, f_status = 'APEL INACTIVE',
                    f_updatedt = NOW(), f_updateby = :actor
              WHERE f_userID = :user_id"
        );
        foreach ($toDeactivate as $row) {
            if (!$dryRun) {
                $disable->execute([':actor' => $actor, ':user_id' => (int)$row['f_userID']]);
                $audit('DEACTIVATE', 'SUCCESS', (string)$row['f_userID'], 'APEL Student Sync', [
                    'identifier_hash' => hash('sha256', (string)$row['f_student_identifier']),
                    'reason' => 'student_status_not_02_or_source_missing',
                ]);
            }
            $stats['deactivated']++;
        }
    }

    $durationMs = (int)round((microtime(true) - $startedAt) * 1000);
    if (!$dryRun) {
        $audit('SYNC', $stats['errors'] === 0 ? 'SUCCESS' : 'PARTIAL', date('YmdHis'), 'APEL Student Profile Sync', [
            'stats' => $stats,
            'duration_ms' => $durationMs,
            'dry_run' => false,
        ]);
    }
    echo 'APEL_STUDENT_SYNC_MODE=' . ($dryRun ? 'dry-run' : 'apply') . "\n";
    foreach ($stats as $name => $value) {
        echo 'APEL_STUDENT_SYNC_' . strtoupper($name) . '=' . $value . "\n";
    }
    echo "APEL_STUDENT_SYNC_DURATION_MS={$durationMs}\n";
    foreach ($errors as $error) {
        fwrite(STDERR, 'APEL_STUDENT_SYNC_ERROR=' . json_encode($error, JSON_UNESCAPED_SLASHES) . "\n");
    }
    exit($stats['errors'] === 0 ? 0 : 2);
} catch (Throwable $e) {
    $durationMs = (int)round((microtime(true) - $startedAt) * 1000);
    if (!$dryRun) {
        $audit('SYNC', 'FAILED', date('YmdHis'), 'APEL Student Profile Sync', [
            'error' => $e->getMessage(),
            'duration_ms' => $durationMs,
        ]);
    }
    fwrite(STDERR, 'APEL_STUDENT_SYNC_FATAL=' . $e->getMessage() . "\n");
    exit(1);
}
