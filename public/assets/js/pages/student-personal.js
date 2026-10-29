$(function () {
    'use strict';

    var TEKS = window.TEKS_BAHASA || {};
    function t(key, fallback) {
        return Object.prototype.hasOwnProperty.call(TEKS, key) ? TEKS[key] : fallback;
    }

    if ($.fn.select2) {
        $('.select2-field').select2({
            width: '100%',
            allowClear: true,
            placeholder: function () {
                return $(this).data('placeholder') || '- Sila Pilih -';
            }
        });
    }

    $('#formStudentPersonal').on('submit', function (e) {
        e.preventDefault();

        var form = this;
        var formData = $(form).serialize();
        var $btn = $('#btnSavePersonal');

        Swal.fire({
            title: t('confirm_save_title', 'Simpan Maklumat?'),
            text: t('confirm_save_text', 'Adakah anda pasti mahu menyimpan perubahan profil peribadi ini?'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: t('btn_confirm', 'Ya, Simpan'),
            cancelButtonText: t('btn_cancel', 'Batal'),
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line spinner me-1"></i> Menyimpan...');

                $.ajax({
                    url: 'ajax_personal.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: t('title_success', 'Berjaya'),
                                text: res.message || 'Maklumat peribadi berjaya dikemaskini.',
                                timer: 1800,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: t('title_error', 'Ralat'),
                                text: (res ? res.message : 'Gagal menyimpan maklumat.')
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: t('title_sys_error', 'Ralat Sistem'),
                            text: 'Gagal berhubung dengan pelayan.'
                        });
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="ri-save-3-line me-2"></i> Simpan Maklumat Peribadi');
                    }
                });
            }
        });
    });
});