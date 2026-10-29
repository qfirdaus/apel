<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('ALLOW_ANON_AJAX', true);
require_once __DIR__ . '/../public/includes/init.php';

$migration = __DIR__ . '/../database/migrations/20261030_create_tbl_apel_user.sql';
$sql = file_get_contents($migration);
if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, "Migration file is unavailable.\n");
    exit(1);
}

$pdo = Database::getInstance('mysql')->getConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // PDO MySQL does not reliably execute a mixed multi-statement migration
    // with native prepares, so split only on statement terminators used by
    // this controlled file (it contains no stored routines).
    $statements = preg_split('/;\s*(?:\R|$)/', $sql) ?: [];
    foreach ($statements as $statement) {
        $statement = trim(preg_replace('/^\s*--.*$/m', '', $statement) ?? '');
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }

    $owned = (int)$pdo->query('SELECT COUNT(*) FROM tbl_apel_user WHERE f_active = 1')->fetchColumn();
    $orphans = (int)$pdo->query(
        'SELECT COUNT(*) FROM tbl_apel_user au
          LEFT JOIN tbl_m_user u ON u.f_userID = au.f_userID
         WHERE u.f_userID IS NULL'
    )->fetchColumn();
    if ($orphans !== 0) {
        throw new RuntimeException('Ownership verification found orphan records.');
    }

    echo "APEL_USER_OWNERSHIP_READY=yes\n";
    echo "ACTIVE_OWNERS={$owned}\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'APEL_USER_OWNERSHIP_FAILED=' . $e->getMessage() . "\n");
    exit(1);
}

