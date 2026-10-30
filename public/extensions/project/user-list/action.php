<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

try {
    ob_start();
    require_once __DIR__ . '/../../../includes/init.php';
    ob_end_clean();
    require_once __DIR__ . '/../../../ajax/_helpers.php';
    require_once __DIR__ . '/../../../classes/Database.php';
    require_once __DIR__ . '/../../../classes/User.php';
    require_once dirname(__DIR__) . '/apel-user-ownership.php';

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        jsonErrorResponse('Kaedah permintaan tidak dibenarkan.', 405);
    }
    if (empty($_SESSION['f_stafID'])) {
        jsonErrorResponse('Sila log masuk terlebih dahulu.', 401);
    }

    $pdo = Database::getInstance('mysql')->getConnection();
    ensureAjaxGroupManagePermission($pdo);
    if (!userListCanAddUsers($pdo)) {
        jsonErrorResponse('Anda tidak mempunyai kebenaran mengurus pengguna.', 403);
    }
    if (!apel_user_ownership_table_exists($pdo)) {
        jsonErrorResponse('Struktur pengguna APEL belum tersedia.', 503);
    }
    if (!checkRateLimit('apel_user_action', 30, 60)) {
        jsonErrorResponse('Terlalu banyak permintaan. Cuba sebentar lagi.', 429);
    }

    $payload = json_decode((string)file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        jsonErrorResponse('Data permintaan tidak sah.', 400);
    }
    $csrf = (string)($payload['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if (!isValidCsrfToken($csrf)) {
        jsonErrorResponse('Sesi keselamatan telah tamat. Muat semula halaman.', 400);
    }

    $action = strtolower(trim((string)($payload['action'] ?? '')));
    $userID = (int)($payload['userID'] ?? 0);
    $actor = trim((string)($_SESSION['f_stafID'] ?? '')) ?: 'system';
    $success = static function (array $data): never {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    };

    $syncLogFile = dirname(__DIR__, 4) . '/storage/logs/apel-student-sync.log';
    $readSyncRuns = static function (string $path, int $limit = 250): array {
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }
        $size = (int)(filesize($path) ?: 0);
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }
        $maxBytes = 1024 * 1024;
        if ($size > $maxBytes) {
            fseek($handle, -$maxBytes, SEEK_END);
            fgets($handle);
        }
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        $runs = [];
        $current = [];
        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            if (!preg_match('/^APEL_STUDENT_SYNC_([A-Z_]+)=(.*)$/', trim($line), $match)) {
                continue;
            }
            $key = strtolower($match[1]);
            if ($key === 'run_at' && $current !== []) {
                $runs[] = $current;
                $current = [];
            }
            $current[$key] = trim($match[2]);
        }
        if ($current !== [] && isset($current['run_at'])) {
            $runs[] = $current;
        }
        $runs = array_values(array_filter($runs, static fn(array $run): bool => isset($run['run_at'])));
        usort($runs, static fn(array $a, array $b): int => strcmp((string)($b['run_at'] ?? ''), (string)($a['run_at'] ?? '')));
        return array_slice($runs, 0, max(1, $limit));
    };

    $writeAudit = static function (
        string $eventType,
        string $outcome,
        int $targetUserID,
        string $targetLabel,
        array $meta = []
    ): void {
        if (!function_exists('audit_event')) {
            return;
        }

        $actorName = $_SESSION['user']['f_nama'] ?? $_SESSION['f_nama'] ?? null;
        $actorLogin = $_SESSION['f_loginID'] ?? $_SESSION['user']['f_loginID'] ?? ($_SESSION['f_stafID'] ?? null);
        $actorLabel = function_exists('audit_format_actor_label')
            ? audit_format_actor_label($actorName, $actorLogin)
            : trim((string)($actorName ?: $actorLogin));

        $eventID = audit_event([
            'event_type' => $eventType,
            'severity' => 'INFO',
            'outcome' => $outcome,
            'target_type' => 'apel_user',
            'target_id' => (string)$targetUserID,
            'target_label' => $targetLabel,
            'message' => function_exists('audit_format_message')
                ? audit_format_message('APEL user record changed', $actorLabel)
                : 'APEL user record changed',
            'request_id' => $GLOBALS['__AUDIT_REQUEST_ID'] ?? null,
            'session_id' => session_id() ?: null,
            'user_id' => !empty($_SESSION['f_userID']) && is_numeric($_SESSION['f_userID'])
                ? (int)$_SESSION['f_userID']
                : null,
            'actor_label' => $actorLabel,
            'meta' => array_merge([
                'source' => 'apel_user_extension',
                'project_owned' => true,
            ], $meta),
        ]);

        if (!$eventID) {
            throw new RuntimeException('Rekod audit APEL gagal disimpan.');
        }
    };

    $resolveGroup = static function (PDO $pdo, int $groupID): array {
        $stmt = $pdo->prepare(
            "SELECT f_groupID, f_groupKod, f_groupName
               FROM tbl_m_group
              WHERE f_groupID = :id
                AND UPPER(TRIM(COALESCE(f_categoryUser, ''))) = 'PELAJAR'
              LIMIT 1"
        );
        $stmt->execute([':id' => $groupID]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$group) {
            jsonErrorResponse('Kumpulan Pelajar yang dipilih tidak sah.', 400);
        }
        return $group;
    };

    $ownedUser = static function (PDO $pdo, int $id, bool $lock = false): array {
        $sql = "SELECT u.f_userID, u.f_loginID, u.f_stafID, u.f_nama
                  FROM tbl_apel_user au
                  JOIN tbl_m_user u ON u.f_userID = au.f_userID
                 WHERE au.f_userID = :id AND au.f_active = 1
                 LIMIT 1" . ($lock ? ' FOR UPDATE' : '');
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            jsonErrorResponse('Rekod pengguna APEL tidak ditemui.', 404);
        }
        return $row;
    };

    if ($action === 'sync_logs') {
        $success(['error' => false, 'runs' => $readSyncRuns($syncLogFile)]);
    }

    if ($action === 'manual_sync') {
        if (!checkRateLimit('apel_user_manual_sync', 3, 300)) {
            jsonErrorResponse('Too many manual sync requests. Please try again later.', 429);
        }
        if (!function_exists('proc_open')) {
            jsonErrorResponse('Manual sync is unavailable on this server.', 503);
        }
        $root = dirname(__DIR__, 4);
        $commandFile = $root . '/tools/cron/sync-apel-students.php';
        $phpBinary = is_executable('/usr/bin/php8.4') ? '/usr/bin/php8.4' : PHP_BINARY;
        if (!is_file($commandFile) || !is_executable($phpBinary)) {
            jsonErrorResponse('APEL sync command is unavailable.', 503);
        }
        $pipes = [];
        $process = proc_open(
            [$phpBinary, $commandFile],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $root,
            ['APEL_SYNC_TRIGGER' => 'manual', 'APEL_SYNC_ACTOR' => $actor]
        );
        if (!is_resource($process)) {
            jsonErrorResponse('Manual sync could not be started.', 500);
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        $logPayload = rtrim($stdout . ($stderr !== '' ? "\n" . $stderr : '')) . "\n\n";
        if (@file_put_contents($syncLogFile, $logPayload, FILE_APPEND | LOCK_EX) === false) {
            error_log('[apel-user-sync] Unable to append synchronization log.');
        }
        if ($exitCode !== 0) {
            jsonErrorResponse('APEL user synchronization failed. Review the cronjob log.', 500);
        }
        $runs = $readSyncRuns($syncLogFile, 1);
        $success([
            'error' => false,
            'message' => 'APEL user synchronization completed.',
            'run' => $runs[0] ?? [],
        ]);
    }

    if ($action === 'delete') {
        if ($userID <= 0) jsonErrorResponse('ID pengguna tidak sah.', 400);
        $pdo->beginTransaction();
        try {
            $target = $ownedUser($pdo, $userID, true);
            $stmt = $pdo->prepare('UPDATE tbl_apel_user SET f_active = 0, f_updatedAt = NOW() WHERE f_userID = :id');
            $stmt->execute([':id' => $userID]);
            $stmt = $pdo->prepare("UPDATE tbl_m_user SET f_flag = 0, f_statusID = 9, f_status = 'ARKIB APEL', f_updatedt = NOW(), f_updateby = :actor WHERE f_userID = :id");
            $stmt->execute([':actor' => $actor, ':id' => $userID]);
            $writeAudit('DELETE', 'SUCCESS', $userID, 'APEL User: ' . (string)$target['f_nama'], [
                'identifier' => (string)($target['f_loginID'] ?: $target['f_stafID']),
                'operation' => 'archive',
            ]);
            $pdo->commit();
            $success(['error' => false, 'message' => 'Pengguna APEL telah diarkibkan dengan selamat.', 'user' => $target]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    if (!in_array($action, ['create', 'update'], true)) {
        jsonErrorResponse('Tindakan tidak dikenali.', 400);
    }

    $identifier = mb_strtoupper(trim((string)($payload['identifier'] ?? '')), 'UTF-8');
    $name = trim((string)($payload['name'] ?? ''));
    $email = mb_strtolower(trim((string)($payload['email'] ?? '')), 'UTF-8');
    $unit = trim((string)($payload['unit'] ?? ''));
    $groupID = (int)($payload['groupID'] ?? 0);
    $flag = (int)($payload['flag'] ?? 1) === 1 ? 1 : 0;
    if ($identifier === '' || mb_strlen($identifier) > 10 || $name === '' || mb_strlen($name) > 255 || $groupID <= 0) {
        jsonErrorResponse('ID APEL, nama dan kumpulan wajib diisi dengan nilai yang sah.', 400);
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonErrorResponse('Alamat e-mel tidak sah.', 400);
    }
    $group = $resolveGroup($pdo, $groupID);

    $pdo->beginTransaction();
    try {
        if ($action === 'create') {
            $duplicate = $pdo->prepare("SELECT f_userID FROM tbl_m_user WHERE UPPER(TRIM(COALESCE(f_loginID, ''))) = :id OR UPPER(TRIM(COALESCE(f_stafID, ''))) = :id LIMIT 1 FOR UPDATE");
            $duplicate->execute([':id' => $identifier]);
            if ($duplicate->fetchColumn()) {
                jsonErrorResponse('ID ini telah digunakan oleh rekod pengguna lain.', 409);
            }

            $userSchema = new User($pdo);
            $values = [
                'f_loginID' => $identifier, 'f_stafID' => $identifier,
                'f_categoryUser' => 'PELAJAR', 'f_nama' => $name,
                'f_nickname' => $name, 'f_email' => $email !== '' ? $email : null,
                'f_namajabatan' => $unit !== '' ? $unit : null,
                'f_password' => password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT),
                'f_must_change_password' => 1, 'f_groupID' => $groupID,
                'f_groupKod' => (string)$group['f_groupKod'], 'f_flag' => $flag,
                'f_status' => 'APEL', 'f_statusID' => null,
                'f_updateby' => $actor, 'f_remarks' => 'Created by APEL user extension',
            ];
            $columns = []; $holders = []; $params = [];
            foreach ($values as $column => $value) {
                if (!$userSchema->authTableHasColumn($column)) continue;
                $columns[] = $column; $holders[] = ':' . $column; $params[':' . $column] = $value;
            }
            foreach (['f_insertdt', 'f_updatedt'] as $column) {
                if ($userSchema->authTableHasColumn($column)) { $columns[] = $column; $holders[] = 'NOW()'; }
            }
            $stmt = $pdo->prepare('INSERT INTO tbl_m_user (' . implode(',', $columns) . ') VALUES (' . implode(',', $holders) . ')');
            $stmt->execute($params);
            $userID = (int)$pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO tbl_apel_user (f_userID, f_student_identifier, f_active, f_source, f_createdBy) VALUES (:user, :identifier, 1, 'APEL_MANUAL', :actor)");
            $stmt->execute([':user' => $userID, ':identifier' => $identifier, ':actor' => $actor]);
            $writeAudit('CREATE', 'SUCCESS', $userID, 'APEL User: ' . $name, [
                'identifier' => $identifier,
                'group_id' => $groupID,
                'group_code' => (string)$group['f_groupKod'],
                'access_enabled' => $flag === 1,
            ]);
            $message = 'Pengguna APEL baharu berjaya ditambah.';
        } else {
            if ($userID <= 0) jsonErrorResponse('ID pengguna tidak sah.', 400);
            $ownedUser($pdo, $userID, true);
            $stmt = $pdo->prepare(
                "UPDATE tbl_m_user
                    SET f_nama = :name, f_nickname = :name, f_email = :email,
                        f_namajabatan = :unit, f_groupID = :groupID,
                        f_groupKod = :groupKod, f_flag = :flag,
                        f_updatedt = NOW(), f_updateby = :actor
                  WHERE f_userID = :id"
            );
            $stmt->execute([':name' => $name, ':email' => $email !== '' ? $email : null, ':unit' => $unit !== '' ? $unit : null, ':groupID' => $groupID, ':groupKod' => $group['f_groupKod'], ':flag' => $flag, ':actor' => $actor, ':id' => $userID]);
            $writeAudit('UPDATE', 'SUCCESS', $userID, 'APEL User: ' . $name, [
                'identifier' => $identifier,
                'group_id' => $groupID,
                'group_code' => (string)$group['f_groupKod'],
                'access_enabled' => $flag === 1,
            ]);
            $message = 'Maklumat pengguna APEL berjaya dikemas kini.';
        }
        $pdo->commit();
        $success(['error' => false, 'message' => $message, 'userID' => $userID]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
} catch (PDOException $e) {
    error_log('[apel-user-action] database: ' . $e->getMessage());
    jsonErrorResponse('Operasi pangkalan data gagal.', 500);
} catch (Throwable $e) {
    error_log('[apel-user-action] ' . $e->getMessage());
    jsonErrorResponse('Operasi pengguna APEL gagal.', 500);
}
