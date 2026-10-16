<?php

$kunciUntukJs = [

    // Umum
    'js_cancel',               // Batal
    'js_success',              // Berjaya!
    'js_error',                // Ralat
    'js_system_error',         // Ralat Sistem
    'js_datepicker_clear',     // label butang kosongkan tarikh
    'js_search_placeholder',   // placeholder carian jadual

    // Simpan rekod
    'js_save_confirm_title',
    'js_save_confirm_text',
    'js_save_btn',
    'js_saving_title',
    'js_saving_text',
    'js_save_success',
    'js_request_failed',
    'js_connection_error',

    // Senarai fail
    'js_selected_files',
    'js_no_files_found',
    'js_no_attachments',
    'js_no_existing_attachments',
    'js_delete_this_file',

    // Padam rekod / fail
    'js_delete_confirm_title',
    'js_delete_record_text',
    'js_delete_file_text',
    'js_delete_btn',
    'js_deleting',
    'js_file_deleted',
    'js_delete_record_failed',
    'js_delete_file_failed',
    'js_server_unreachable',
    'js_server_unreachable_retry',
    'js_delete_record_success',
    
    // Kemahiran diperlukan
    'js_skill_required',

    // Self Reflection
    'js_warning',
    'js_reflection_min_words',
    'js_reflection_save_title',
    'js_reflection_save_text',

    // Referees
    'js_referees_save_title',
    'js_referees_save_text',
    'js_referees_save_success',

    // Upload Document
    'js_upload_too_big',
    'js_upload_bad_format',
    'js_uploading_title',
    'js_uploading_text',
    'js_upload_success',
    'js_upload_failed',
];

// Ambil teks sebenar (ikut bahasa semasa) bagi setiap kunci
$teksUntukJs = [];
foreach ($kunciUntukJs as $kunci) {
    $teksUntukJs[$kunci] = __($kunci);
}

// Tukar kepada format JSON supaya JS boleh baca.
$jsonTeks = json_encode(
    $teksUntukJs,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
<script>
    window.TEKS_BAHASA = <?= $jsonTeks ?>;
</script>