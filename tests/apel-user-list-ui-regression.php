<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$registrationFile = $root . '/public/extensions/project/user-list.php';
$panelFile = $root . '/public/extensions/project/user-list/panel.php';
$navFile = $root . '/public/extensions/project/user-list/nav.php';
$assetsFile = $root . '/public/extensions/project/user-list/assets.php';
$actionFile = $root . '/public/extensions/project/user-list/action.php';

foreach ([$registrationFile, $panelFile, $navFile, $assetsFile, $actionFile] as $file) {
    if (!is_file($file)) {
        throw new RuntimeException('Missing APEL user-list component: ' . $file);
    }
}

$registration = file_get_contents($registrationFile) ?: '';
$panel = file_get_contents($panelFile) ?: '';
$nav = file_get_contents($navFile) ?: '';
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
if (!str_contains($nav, "__('apelUser_tab_access')")) {
    throw new RuntimeException('APEL tab label must use the bilingual project translation key.');
}
if (!str_contains($assets, '#tab-apel-access-tab.active')
    || !str_contains($assets, '--apel-tab-rgb:245,158,11')
    || !str_contains($assets, 'color-mix(in srgb,rgb(var(--apel-tab-rgb)) 22%,var(--bs-body-bg))')
    || !str_contains($assets, 'background-color:rgb(var(--apel-tab-rgb))!important')
    || !str_contains($assets, 'ri-graduation-cap-fill::before')
) {
    throw new RuntimeException('APEL Access tab must use its distinct soft amber colour treatment.');
}
if (!str_contains($panel, 'user-access-table user-access-table--sixcol')
    || substr_count($panel, 'class="col-') < 12
) {
    throw new RuntimeException('APEL table must follow the Student Access six-column layout contract.');
}
if (!str_contains($assets, "document.addEventListener('DOMContentLoaded',initApelUserList")
    || !str_contains($assets, 'bootstrap.Modal.getOrCreateInstance')
) {
    throw new RuntimeException('APEL actions must initialise after deferred framework assets.');
}
if (!str_contains($assets, "}}):{isConfirmed:false}")) {
    throw new RuntimeException('APEL View As confirmation must close Swal.fire before its fallback branch.');
}
if (!str_contains($assets, '#userDTApel .group-chip')
    || !str_contains($assets, 'color:#334155')
) {
    throw new RuntimeException('APEL group chips must provide an explicit readable colour treatment.');
}
if (!str_contains($assets, "customClass:'apel-user-tooltip'")
    || !str_contains($assets, 'white-space:nowrap!important')
    || !str_contains($assets, 'width:max-content')
    || !str_contains($assets, 'getInstance(el)?.dispose()')
) {
    throw new RuntimeException('APEL table tooltips must remain on one line.');
}
foreach (['au-modal-tabs', 'form-section-title', 'modal-footer', 'is-edit-mode'] as $modalContract) {
    if (!str_contains($panel . $assets, $modalContract)) {
        throw new RuntimeException('APEL add/edit modal must follow Student Access layout: ' . $modalContract);
    }
}
foreach (['width:680', 'reverseButtons:true', 'apelImpersonationMode'] as $confirmationContract) {
    if (!str_contains($assets, $confirmationContract)) {
        throw new RuntimeException('APEL confirmations must follow Student Access interaction layout: ' . $confirmationContract);
    }
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
