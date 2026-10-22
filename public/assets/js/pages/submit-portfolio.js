$(function () {
    'use strict';

    // Teks bahasa dihantar dari PHP (lihat translate-js.php -> window.TEKS_BAHASA)
    // t('kunci') = ambil teks ikut bahasa semasa
    var TEKS = window.TEKS_BAHASA || {};
    function t(key, params) {
        var text = Object.prototype.hasOwnProperty.call(TEKS, key) ? TEKS[key] : key;
        if (params) {
            Object.keys(params).forEach(function (p) {
                text = text.split('{' + p + '}').join(params[p]);
            });
        }
        return text;
    }

    // 1. Enable/Disable Submit Button based on Checkbox
    $('#chkDeclaration').on('change', function() {
        $('#btnSubmitPortfolio').prop('disabled', !$(this).is(':checked'));
    });

    // 2. Submit Action
    $('#formSubmitPortfolio').on('submit', function (event) {
        event.preventDefault();
        
        Swal.fire({
            title: t('js_submit_confirm_title'),
            text: t('js_submit_confirm_text'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="ri-send-plane-fill me-1"></i> ' + t('js_submit_confirm_btn'),
            cancelButtonText: t('js_cancel'),
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                
                Swal.fire({
                    title: t('js_submit_checking_title'),
                    text: t('js_submit_checking_text'),
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'ajax_process.php',
                    type: 'POST',
                    data: { action: 'submit_portfolio' },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: t('js_success'),
                                text: res.message,
                                confirmButtonText: t('js_submit_done_btn')
                            }).then(() => {
                                // Reload page untuk papar skrin hijau (Locked)
                                window.location.reload();
                            });
                        } else {
                            // Ralat validasi (Jika ada jadual yang belum diisi)
                            Swal.fire({
                                icon: 'error',
                                title: t('js_submit_rejected_title'),
                                html: res.message,
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire(t('js_system_error'), t('js_server_unreachable'), 'error');
                    }
                });
            }
        });
    });
});