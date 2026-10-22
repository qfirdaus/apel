$(function () {
    'use strict';

    var TEKS = window.TEKS_BAHASA || {};
    function t(key) { return TEKS[key] || key; }

    // Init DataTable Standard
    if ($.fn.DataTable && $('#tblAdvisorPending').length) {
        if (!$.fn.DataTable.isDataTable('#tblAdvisorPending')) {
            $('#tblAdvisorPending').DataTable({
                pageLength: 10,
                responsive: true,
                autoWidth: false
            });
        }
    }

    // Inisialisasi DataTable Sejarah
    if ($.fn.DataTable && $('#tblAdvisorHistory').length) {
        if (!$.fn.DataTable.isDataTable('#tblAdvisorHistory')) {
            $('#tblAdvisorHistory').DataTable({
                pageLength: 10,
                responsive: true,
                autoWidth: false
            });
        }
    }

    // PAPAR PORTFOLIO (AJAX + MODAL PDF)
    $(document).on('click', '.btn-view-pdf', function () {
        var appId = $(this).data('app-id');
        var csrfToken = $('#csrfToken').val();

        $('#pdfPreviewContent').html(
            '<div class="text-center py-5">' +
                '<div class="spinner-border text-primary" role="status"></div>' +
                '<p class="mt-2 text-muted">Memuatkan maklumat portfolio...</p>' +
            '</div>'
        );

        $.ajax({
            url: 'ajax_advisor.php',
            type: 'POST',
            data: {
                action: 'get_portfolio_pdf_details',
                app_id: appId,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function (res) {
                if (res && res.status === 'success') {
                    $('#pdfPreviewContent').html(res.html);
                } else {
                    var msg = res ? res.message : 'Gagal memuatkan data.';
                    $('#pdfPreviewContent').html('<div class="alert alert-danger mb-0"><i class="ri-error-warning-line me-1"></i> ' + msg + '</div>');
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                $('#pdfPreviewContent').html('<div class="alert alert-danger mb-0"><i class="ri-close-circle-line me-1"></i> Gagal berhubung dengan pelayan.</div>');
            }
        });
    });

    // PENILAIAN (MODAL KEPUTUSAN)
    $(document).on('click', '.btn-action-review', function (e) {
        e.preventDefault();
        
        var appId = $(this).attr('data-app-id') || $(this).data('app-id');
        var studentId = $(this).attr('data-student-id') || $(this).data('student-id');

        $('#review_app_id').val(appId);
        $('#review_student_id').val(studentId);
        $('#review_decision').val('');
        $('#review_remarks').val('');

        // Penggunaan Bootstrap 5 Native Modal API
        var modalEl = document.getElementById('modalAdvisorReview');
        if (modalEl) {
            var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            myModal.show();
        } else {
            console.error("Elemen modal #modalAdvisorReview tidak wujud dalam DOM.");
        }
    });

    // HANTAR KEPUTUSAN PENILAIAN (AJAX)
    $('#formAdvisorReview').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var csrfToken = $('#csrfToken').val();
        formData += '&csrf_token=' + encodeURIComponent(csrfToken);

        Swal.fire({
            title: t('js_adv_confirm_title', 'Sahkan Keputusan Penilaian?'),
            text: t('js_adv_confirm_text', 'Adakah anda pasti mahu menyimpan keputusan penilaian ini?'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: t('js_continue', 'Teruskan'),
            cancelButtonText: t('js_cancel', 'Batal')
        }).then((result) => {
            if (result.isConfirmed) {
                $('#btnSubmitDecision').prop('disabled', true).html('<i class="ri-loader-4-line spinner"></i> Memproses...');

                $.post('ajax_advisor.php', formData, function (res) {
                    if (res && res.status === 'success') {
                        Swal.fire(t('js_success', 'Berjaya!'), res.message, 'success').then(() => location.reload());
                    } else {
                        var msg = res ? res.message : t('js_request_failed', 'Permintaan gagal.');
                        Swal.fire(t('js_error', 'Ralat'), msg, 'error');
                    }
                }, 'json').fail(function (xhr, status, error) {
                    console.error("AJAX Error Response:", xhr.responseText);
                    
                    var errorMsg = t('js_connection_error', 'Gagal berhubung dengan pelayan.');
                    try {
                        var jsonErr = JSON.parse(xhr.responseText);
                        if (jsonErr && jsonErr.message) {
                            errorMsg = jsonErr.message;
                        }
                    } catch (e) {}

                    Swal.fire(t('js_system_error', 'Ralat Sistem'), errorMsg, 'error');
                }).always(function () {
                    $('#btnSubmitDecision').prop('disabled', false).html(t('adv_btn_submit', 'Hantar Keputusan'));
                });
            }
        });
    });
});