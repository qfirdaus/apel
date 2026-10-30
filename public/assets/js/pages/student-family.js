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

    $('#formStudentFamily').on('submit', function (e) {
        e.preventDefault();

        var form = this;
        var formData = $(form).serialize();
        var $btn = $('#btnSaveFamily');

        Swal.fire({
            title: t('confirm_save_family_title', 'Simpan Maklumat Keluarga?'),
            text: t('confirm_save_family_text', 'Adakah anda pasti mahu mengemas kini maklumat keluarga dan waris ini?'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: t('btn_confirm', 'Ya, Simpan'),
            cancelButtonText: t('btn_cancel', 'Batal'),
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line spinner me-1"></i> Menyimpan...');

                $.ajax({
                    url: 'ajax_family.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: t('title_success', 'Berjaya'),
                                text: res.message || 'Maklumat keluarga berjaya dikemas kini.',
                                timer: 1800,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: t('title_error', 'Ralat'),
                                text: (res ? res.message : 'Gagal menyimpan maklumat keluarga.')
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
                        $btn.prop('disabled', false).html('<i class="ri-save-3-line me-2 fs-5"></i> Simpan Maklumat Keluarga');
                    }
                });
            }
        });
    });
});