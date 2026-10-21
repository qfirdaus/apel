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

    // Initialize DataTable
    function initializeDataTable(selector) {
        if ($.fn.DataTable && $(selector).length) {
            var options = window.DataTableStandard ? window.DataTableStandard.options({
                pageLength: 10,
                order: [],
                autoWidth: false
            }) : { pageLength: 10 };

            $(selector).DataTable(options);
            
            if (window.DataTableStandard) {
                window.DataTableStandard.decorate(selector, {
                    searchPlaceholder: t('js_search_placeholder')
                });
            }
        }
    }

    initializeDataTable('#tblPendingReceipts');
    initializeDataTable('#tblHistory');

    var csrfToken = $('#csrfToken').val();

    // LULUSKAN RESIT
    $(document).on('click', '.btn-approve', function () {
        var paymentId = $(this).data('id');

        Swal.fire({
            title: t('js_fin_approve_title'),
            text: t('js_fin_approve_text'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ri-check-line me-1"></i> ' + t('js_fin_approve_btn'),
            cancelButtonText: t('js_cancel'),
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                processApproval(paymentId, 'approve', '');
            }
        });
    });

    // TOLAK RESIT
    $(document).on('click', '.btn-reject', function () {
        var paymentId = $(this).data('id');

        Swal.fire({
            title: t('js_fin_reject_title'),
            text: t('js_fin_reject_text'),
            input: 'textarea',
            inputPlaceholder: t('js_fin_reject_placeholder'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ri-close-line me-1"></i> ' + t('js_fin_reject_btn'),
            cancelButtonText: t('js_cancel'),
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return t('js_fin_reject_required');
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                processApproval(paymentId, 'reject', result.value);
            }
        });
    });

    $(document).on('click', '.btn-remarks', function() {
        var remarks = $(this).data('remarks');
        Swal.fire({
            title: t('js_fin_reason_title'),
            text: remarks,
            icon: 'info',
            confirmButtonColor: '#3085d6',
            confirmButtonText: t('js_close')
        });
    });    

    function processApproval(paymentId, actionType, remarksData) {
        Swal.fire({
            title: t('js_processing'),
            text: t('js_please_wait'),
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: 'ajax_admin.php',
            type: 'POST',
            data: {
                action: 'admin_process_receipt',
                payment_id: paymentId,
                approval_action: actionType,
                remarks: remarksData,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function (res) {
                if (res && res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: t('js_success'),
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload(); // Refresh skrin selepas berjaya
                    });
                } else {
                    var errorMsg = res ? res.message : t('js_fin_process_failed');
                    Swal.fire(t('js_error'), errorMsg, 'error');
                }
            },
            error: function () {
                Swal.fire(t('js_system_error'), t('js_server_unreachable_retry'), 'error');
            }
        });
    }
});