<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$panel = file_get_contents($root . '/public/extensions/project/user-list/panel.php') ?: '';
$assets = file_get_contents($root . '/public/extensions/project/user-list/assets.php') ?: '';
$action = file_get_contents($root . '/public/extensions/project/user-list/action.php') ?: '';

$contracts = [
    [$assets, 'apelManualSyncBtn', 'Manual Sync button is missing.'],
    [$assets, 'apelSyncLogBtn', 'Cronjob Log button is missing.'],
    [$assets, 'apelAddUserBtn', 'Add APEL User button is missing.'],
    [$panel, 'apelSyncLogModal', 'Synchronization log modal is missing.'],
    [$panel, 'apelSyncLogTable', 'Synchronization log DataTable is missing.'],
    [$assets, "order:[[0,'desc']]", 'Synchronization log must show the latest run first.'],
    [$action, "\$action === 'manual_sync'", 'Manual synchronization endpoint is missing.'],
    [$action, "\$action === 'sync_logs'", 'Synchronization log endpoint is missing.'],
    [$action, 'apel_user_manual_sync', 'Manual synchronization must be rate limited.'],
    [$action, 'proc_open', 'Manual synchronization must use the audited CLI command.'],
];

foreach ($contracts as [$source, $needle, $message]) {
    if (!str_contains($source, $needle)) {
        throw new RuntimeException($message);
    }
}

$manualPosition = strpos($assets, 'apelManualSyncBtn');
$logPosition = strpos($assets, 'apelSyncLogBtn');
$addPosition = strpos($assets, 'apelAddUserBtn');
if ($manualPosition === false || $logPosition === false || $addPosition === false
    || !($manualPosition < $logPosition && $logPosition < $addPosition)) {
    throw new RuntimeException('Manual Sync and Cronjob Log must be positioned before Add APEL User.');
}

echo "APEL student sync admin UI regression PASS\n";
