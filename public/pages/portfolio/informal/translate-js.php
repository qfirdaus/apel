<?php
// Hantar terjemahan ke informal-learning.js (SweetAlert dll.)
// Tambah kunci js_* baharu di sini dan dalam lang/en.php + lang/ms.php.
$jsI18nKeys = [
    'js_datepicker_clear',
    'js_search_placeholder',
    'js_cancel',
    'js_save_confirm_title',
    'js_save_confirm_text',
    'js_save_btn',
    'js_saving_title',
    'js_saving_text',
    'js_success',
    'js_save_success',
    'js_error',
    'js_system_error',
    'js_request_failed',
    'js_connection_error',
    'js_selected_files',
    'js_no_files_found',
    'js_delete_confirm_title',
    'js_delete_record_text',
    'js_delete_btn',
    'js_deleting',
    'js_delete_record_failed',
    'js_server_unreachable',
    'js_server_unreachable_retry',
    'js_delete_this_file',
    'js_no_attachments',
    'js_no_existing_attachments',
    'js_delete_file_text',
    'js_file_deleted',
    'js_delete_file_failed'
];

$jsI18n = [];
foreach ($jsI18nKeys as $k) {
    $jsI18n[$k] = __($k);
}
?>
<script>
    window.I18N_INFORMAL = <?= json_encode($jsI18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>