<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$registrationFile = $root . '/public/extensions/project/user-list.php';
$panelFile = $root . '/public/extensions/project/user-list/panel.php';
$assetsFile = $root . '/public/extensions/project/user-list/assets.php';
$actionFile = $root . '/public/extensions/project/user-list/action.php';

foreach ([$registrationFile, $panelFile, $assetsFile, $actionFile] as $file) {
    if (!is_file($file)) {
        throw new RuntimeException('Missing APEL user-list component: ' . $file);
    }
}

$registration = file_get_contents($registrationFile) ?: '';
$panel = file_get_contents($panelFile) ?: '';
$assets = file_get_contents($assetsFile) ?: '';
$action = file_get_contents($actionFile) ?: '';

foreach (['nav.after_student', 'panel.after_student', 'assets.footer'] as $slot) {
    if (!str_contains($registration, "'slot' => '" . $slot . "'")) {
        throw new RuntimeException('Missing extension slot: ' . $slot);
    }
}

if (!str_contains($panel, 'tbl_apel_user') || !str_contains($action, 'tbl_apel_user')) {
    throw new RuntimeException('APEL UI must be bounded by tbl_apel_user ownership.');
}

$combined = $panel . "\n" . $assets . "\n" . $action;
foreach (['user-sync-student.php', 'user-add-student.php', 'studentSearch', 'v210'] as $forbidden) {
    if (str_contains($combined, $forbidden)) {
        throw new RuntimeException('Core Student dependency detected: ' . $forbidden);
    }
}

if (!str_contains($assets, 'pageLength:10')) {
    throw new RuntimeException('APEL table must default to 10 rows per page.');
}

foreach (['CREATE', 'UPDATE', 'DELETE'] as $auditType) {
    if (!str_contains($action, "$auditType', 'SUCCESS'")) {
        throw new RuntimeException('Missing APEL audit event: ' . $auditType);
    }
}

if (!str_contains($action, "'target_type' => 'apel_user'")) {
    throw new RuntimeException('APEL changes must use a dedicated audit target.');
}

if (!str_contains($panel, 'apel-view-as-user') || !str_contains($assets, 'impersonation-start.php')) {
    throw new RuntimeException('APEL users must retain the protected generic View As workflow.');
}

echo "APEL user-list UI regression PASS\n";
