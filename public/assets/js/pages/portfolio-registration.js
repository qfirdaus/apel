$(function () {
    'use strict';

    var csrfToken = $('#csrfToken').val();
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

    function forceCleanupBackdrop() {
        $('.modal-backdrop').remove(); 
        $('body').removeClass('modal-open').css({
            'overflow': '',
            'padding-right': ''
        });
    }

    // -- KAEDAH 1: KLIK BUTANG FPX --
    $(document).on('click', '.btn-pay-fpx', function (e) {
        e.preventDefault();
        
        setTimeout(function() {
            forceCleanupBackdrop(); 

            Swal.fire({
                title: t('js_fpx_title'),
                text: t('js_fpx_text'),
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="ri-save-3-line me-1"></i> ' + t('js_continue'),
                cancelButtonText: t('js_cancel'),
                reverseButtons: true                
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: t('js_fpx_connecting'),
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.post('ajax_process.php', { 
                        action: 'register_portfolio', 
                        payment_method: 'fpx',
                        csrf_token: csrfToken 
                    }, function(res) {
                        if (res && res.status === 'success') {
                            Swal.fire({
                                icon: 'success', 
                                title: t('js_success'), 
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            var errorMsg = res ? res.message : t('js_unknown_error');
                            Swal.fire(t('js_error'), errorMsg, 'error');
                        }
                    }, 'json');
                }
            });
        }, 400); 
    });

    // -- KAEDAH 2: KLIK BUTANG MANUAL RESIT --
    $(document).on('click', '.btn-pay-manual', function (e) {
        e.preventDefault();
        
        setTimeout(function() {
            forceCleanupBackdrop(); 

            var modalUploadEl = document.getElementById('modalUploadReceipt');
            var modalUpload = bootstrap.Modal.getInstance(modalUploadEl) || new bootstrap.Modal(modalUploadEl);
            modalUpload.show();
        }, 400); 
    });

    // -- SUBMIT BORANG RESIT --
    $('#formUploadReceipt').on('submit', function (e) {
        e.preventDefault();
        
        var form = document.getElementById('formUploadReceipt');
        var formData = new FormData(form);
        formData.append('csrf_token', csrfToken);

        var modalUploadEl = document.getElementById('modalUploadReceipt');
        var modalUpload = bootstrap.Modal.getInstance(modalUploadEl);
        if (modalUpload) { modalUpload.hide(); }

        setTimeout(function() {
            forceCleanupBackdrop();

            Swal.fire({
                title: t('js_uploading_title'),
                text: t('js_please_wait'),
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: 'ajax_process.php', 
                type: 'POST',
                data: formData,
                contentType: false, 
                processData: false, 
                dataType: 'json',
                success: function (res) {
                    if (res && res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: t('js_success'),
                            text: res.message || t('js_receipt_uploaded'),
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        var errorMsg = res ? res.message : t('js_registration_failed');
                        Swal.fire(t('js_error'), errorMsg, 'error');
                    }
                },
                error: function () {
                    Swal.fire(t('js_system_error'), t('js_upload_doc_failed'), 'error');
                }
            });
        }, 400);
    });
});