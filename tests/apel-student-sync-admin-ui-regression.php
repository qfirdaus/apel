<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$panel = file_get_contents($root . '/public/extensions/project/user-list/panel.php') ?: '';
$assets = file_get_contents($root . '/public/extensions/project/user-list/assets.php') ?: '';
$action = file_get_contents($root . '/public/extensions/project/user-list/action.php') ?: '';

$contracts = [
    [$assets, 'apelManualSyncBtn', 'Manual Sync button is missing.'],
    [$assets, 'apelSyncLogBtn', 'Cronjob Log button is missing.'],
    [$assets, 'apelAddUserBtn', 'Add APEL Student button is missing.'],
    [$panel, 'apelSyncLogModal', 'Synchronization log modal is missing.'],
    [$panel, 'apelSyncLogTable', 'Synchronization log DataTable is missing.'],
    [$panel, 'apel-sync-log-dialog', 'Synchronization log modal must use its top-aligned dialog layout.'],
    [$assets, '#apelSyncLogModal .apel-sync-log-dialog{margin-top:1rem', 'Synchronization log modal must remain top-aligned.'],
    [$assets, 'ordering:false', 'Synchronization log sorting controls must be disabled.'],
    [$action, "\$action === 'manual_sync'", 'Manual synchronization endpoint is missing.'],
    [$action, "\$action === 'sync_logs'", 'Synchronization log endpoint is missing.'],
    [$action, 'apel_user_manual_sync', 'Manual synchronization must be rate limited.'],
    [$action, 'APEL_SYNC_INTERNAL_RUN', 'Manual synchronization must use the shared audited sync runner.'],
    [$action, "'ownership_created', 'ownership_updated', 'reactivated', 'deactivated'", 'No-change synchronization runs must be omitted from the administration log.'],
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
    throw new RuntimeException('Manual Sync and Cronjob Log must be positioned before Add APEL Student.');
}

echo "APEL student sync admin UI regression PASS\n";
