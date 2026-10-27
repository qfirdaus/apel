$(function () {
    'use strict';

    var TEKS = window.TEKS_BAHASA || {};
    function t(key, fallback) { return TEKS[key] || fallback || key; }

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

    if ($.fn.DataTable && $('#tblAdvisorHistory').length) {
        if (!$.fn.DataTable.isDataTable('#tblAdvisorHistory')) {
            $('#tblAdvisorHistory').DataTable({
                pageLength: 10,
                responsive: true,
                autoWidth: false
            });
        }
    }

    // 1. PAPAR PORTFOLIO (AJAX + MODAL PDF)
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
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
                $('#pdfPreviewContent').html('<div class="alert alert-danger mb-0"><i class="ri-close-circle-line me-1"></i> Gagal berhubung dengan pelayan.</div>');
            }
        });
    });

    // 2. FUNGSI CETAK DOKUMEN BERSIH (MENGGUNAKAN IFRAME - BEBAS SEKATAN POPUP)
    $(document).on('click', '#btnPrintPdfPreview', function (e) {
        e.preventDefault();

        var $content = $('#pdfPreviewContent');
        if (!$content.length || $content.find('.spinner-border').length) {
            Swal.fire('Perhatian', 'Sila tunggu dokumen selesai dimuatkan.', 'warning');
            return;
        }

        var htmlData = $content.html();
        if (!htmlData || $.trim(htmlData) === '') {
            Swal.fire('Ralat', 'Tiada kandungan dokumen untuk dicetak.', 'error');
            return;
        }

        // Buang iframe lama jika ada
        $('#printFrame').remove();

        // Cipta iframe tersembunyi
        var $iframe = $('<iframe id="printFrame" style="position: absolute; top: -9999px; left: -9999px; width: 0; height: 0; border: 0;"></iframe>');
        $('body').append($iframe);

        var doc = $iframe[0].contentWindow.document;
        doc.open();
        doc.write('<!DOCTYPE html><html><head><title>Portfolio APEL Q</title>');
        doc.write('<link rel="stylesheet" href="../../../assets/css/bootstrap.min.css">');
        doc.write('<style>');
        doc.write('@page { size: A4 portrait; margin: 12mm; }');
        doc.write('body { font-family: "Segoe UI", Arial, sans-serif; color: #111; background: #fff !important; margin: 0; padding: 15px; }');
        doc.write('.apel-doc-container { padding: 0 !important; width: 100% !important; }');
        doc.write('table { width: 100% !important; border-collapse: collapse !important; }');
        doc.write('th, td { border: 1px solid #cbd5e1 !important; padding: 6px 8px !important; }');
        doc.write('.apel-section-header { background-color: #0f172a !important; color: #ffffff !important; padding: 6px 10px !important; margin-top: 15px !important; font-weight: bold !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }');
        doc.write('</style>');
        doc.write('</head><body>');
        doc.write(htmlData);
        doc.write('</body></html>');
        doc.close();

        // Jalankan cetakan melalui iframe
        setTimeout(function () {
            $iframe[0].contentWindow.focus();
            $iframe[0].contentWindow.print();
        }, 350);
    });

    // 3. PENILAIAN (MODAL KEPUTUSAN)
    $(document).on('click', '.btn-action-review', function (e) {
        e.preventDefault();
        
        var appId = $(this).attr('data-app-id') || $(this).data('app-id');
        var studentId = $(this).attr('data-student-id') || $(this).data('student-id');

        $('#review_app_id').val(appId);
        $('#review_student_id').val(studentId);
        $('#review_decision').val('');
        $('#review_remarks').val('');

        var modalEl = document.getElementById('modalAdvisorReview');
        if (modalEl) {
            var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            myModal.show();
        }
    });

    // 4. HANTAR KEPUTUSAN PENILAIAN (AJAX)
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
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#btnSubmitDecision').prop('disabled', true).html('<i class="ri-loader-4-line spinner"></i> Memproses...');

                $.post('ajax_advisor.php', formData, function (res) {
                    if (res && res.status === 'success') {
                        Swal.fire(t('js_success', 'Berjaya!'), res.message, 'success').then(function () {
                            location.reload();
                        });
                    } else {
                        var msg = res ? res.message : t('js_request_failed', 'Permintaan gagal.');
                        Swal.fire(t('js_error', 'Ralat'), msg, 'error');
                    }
                }, 'json').fail(function (xhr) {
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