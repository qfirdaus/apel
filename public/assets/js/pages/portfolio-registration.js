$(function () {
    'use strict';

    var csrfToken = $('#csrfToken').val();
    // terjemahan global (pages-main.js)
    var t = window.t;

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
                title: 'Sistem Pembayaran FPX',
                text: 'Anda akan dibawa ke halaman perbankan FPX. Teruskan?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="ri-save-3-line me-1"></i> ' + 'Teruskan',
                cancelButtonText: t('js_cancel'),
                reverseButtons: true                
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyambung ke FPX...',
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
                                title: 'Berjaya', 
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            var errorMsg = res ? res.message : 'Ralat tidak diketahui.';
                            Swal.fire('Ralat', errorMsg, 'error');
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
                title: 'Memuat Naik...',
                text: 'Sila tunggu sebentar.',
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
                            title: 'Berjaya',
                            text: res.message || 'Resit berjaya dimuat naik.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        var errorMsg = res ? res.message : 'Gagal memproses pendaftaran.';
                        Swal.fire('Ralat', errorMsg, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Ralat Sistem', 'Gagal memuat naik dokumen.', 'error');
                }
            });
        }, 400);
    });
});