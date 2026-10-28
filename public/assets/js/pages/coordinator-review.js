$(function () {
    'use strict';

    var TEKS = window.TEKS_BAHASA || {};
    function t(key, fallback) {
        return Object.prototype.hasOwnProperty.call(TEKS, key) ? TEKS[key] : fallback;
    }

    // Inisialisasi DataTable Penyelaras 
    if ($.fn.DataTable && $('#tblCoordinatorPending').length) {
        if (!$.fn.DataTable.isDataTable('#tblCoordinatorPending')) {
            $('#tblCoordinatorPending').DataTable({
                pageLength: 10,
                autoWidth: false,
                ordering: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: t('dt_search', 'Cari...'),
                    emptyTable: "Tiada portfolio pelajar yang menunggu perakuan buat masa ini.",
                    zeroRecords: "Tiada rekod padanan ditemui."
                }
            });
        }
    }

    // Inisialisasi DataTable Sejarah Penyelaras
    if ($.fn.DataTable && $('#tblCoordinatorHistory').length) {
        if (!$.fn.DataTable.isDataTable('#tblCoordinatorHistory')) {
            $('#tblCoordinatorHistory').DataTable({
                pageLength: 10,
                autoWidth: false,
                ordering: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: t('dt_search', 'Cari...'),
                    emptyTable: "Tiada sejarah perakuan portfolio dijumpai.",
                    zeroRecords: "Tiada rekod padanan ditemui."
                }
            });
        }
    }

    // Modal Keputusan Perakuan Penyelaras
    $(document).on('click', '.btn-action-coord-review', function (e) {
        e.preventDefault();

        var btn       = $(this);
        var appId     = btn.data('app-id');
        var studentId = btn.data('student-id');

        $('#coord_review_app_id').val(appId);
        $('#coord_review_student_id').val(studentId);
        $('#coord_review_decision').val('');
        $('#coord_review_remarks').val('');

        var modalEl = document.getElementById('modalCoordinatorReview');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });

    // Portfolio PDF (AJAX Modal)
    $(document).on('click', '.btn-view-pdf', function (e) {
        e.preventDefault();

        var appId = $(this).data('app-id');
        var csrfToken = $('#csrfToken').val();
        var $previewContainer = $('#pdfPreviewContent');

        $previewContainer.html(
            '<div class="text-center py-5">' +
                '<div class="spinner-border text-primary" role="status"></div>' +
                '<p class="mt-2 text-muted">' + t('loading_portfolio', 'Memuatkan maklumat portfolio...') + '</p>' +
            '</div>'
        );

        $.ajax({
            url: 'ajax_coordinator.php',
            type: 'POST',
            data: {
                action: 'get_portfolio_pdf_details',
                app_id: appId,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function (res) {
                if (res && res.status === 'success' && res.html) {
                    $previewContainer.html(res.html);
                } else {
                    $previewContainer.html(
                        '<div class="alert alert-danger my-3 text-center">' +
                            ((res && res.message) ? res.message : t('err_load_pdf', 'Gagal memuatkan dokumen portfolio.')) +
                        '</div>'
                    );
                }
            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
                $previewContainer.html(
                    '<div class="alert alert-danger my-3 text-center">' +
                        t('err_network', 'Ralat pelayan semasa memuatkan ringkasan portfolio.') +
                    '</div>'
                );
            }
        });
    });

    // Cetak Dokumen Bersih Penuh (Iframe)
    $(document).on('click', '#btnPrintPdfPreview', function (e) {
        e.preventDefault();

        var $content = $('#pdfPreviewContent');
        if (!$content.length || $content.find('.spinner-border').length) {
            Swal.fire({
                icon: 'warning',
                title: t('title_attention', 'Perhatian'),
                text: 'Sila tunggu dokumen selesai dimuatkan.'
            });
            return;
        }

        var htmlData = $content.html();
        if (!htmlData || $.trim(htmlData) === '') {
            Swal.fire({
                icon: 'error',
                title: t('title_error', 'Ralat'),
                text: 'Tiada kandungan dokumen untuk dicetak.'
            });
            return;
        }

        // Padam iframe cetakan lama jika wujud
        $('#printFrame').remove();

        // Bina iframe tersembunyi
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
        doc.write('table { width: 100% !important; border-collapse: collapse !important; margin-bottom: 15px !important; }');
        doc.write('th, td { border: 1px solid #cbd5e1 !important; padding: 6px 8px !important; }');
        doc.write('.apel-section-header { background-color: #0f172a !important; color: #ffffff !important; padding: 6px 10px !important; margin-top: 15px !important; font-weight: bold !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }');
        doc.write('</style>');
        doc.write('</head><body>');
        doc.write(htmlData);
        doc.write('</body></html>');
        doc.close();

        // Cetak dokumen dalam iframe
        setTimeout(function () {
            $iframe[0].contentWindow.focus();
            $iframe[0].contentWindow.print();
        }, 350);
    });

    // Hantar Keputusan Perakuan Penyelaras
    $('#formCoordinatorReview').on('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(this);
        var decision = $('#coord_review_decision').val();
        var remarks  = $.trim($('#coord_review_remarks').val());
        var csrf     = $('#csrfToken').val();

        if (csrf) {
            formData.append('csrf_token', csrf);
        }

        if (decision === 'rework' && remarks === '') {
            Swal.fire({
                icon: 'warning',
                title: t('title_attention', 'Perhatian'),
                text: t('req_remarks_rework', 'Sila masukkan ulasan / catatan jika memilih keputusan Pindaan (Rework).')
            });
            return;
        }

        var confirmTitle = (decision === 'recommend') 
            ? t('confirm_recommend_title', 'Peraku Permohonan?') 
            : t('confirm_rework_title', 'Kembalikan untuk Pindaan?');

        var confirmText = (decision === 'recommend')
            ? t('confirm_recommend_text', 'Permohonan ini akan diperakukan dan dimajukan ke fasa Penilaian Senat.')
            : t('confirm_rework_text', 'Permohonan akan dikembalikan kepada pelajar untuk tindakan pembetulan portfolio.');

        Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: t('btn_confirm', 'Ya, Teruskan'),
            cancelButtonText: t('btn_cancel', 'Batal'),
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: t('msg_saving', 'Menyimpan keputusan...'),
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'ajax_coordinator.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: t('title_success', 'Berjaya'),
                                text: res.message || t('msg_success_saved', 'Keputusan berjaya direkodkan.'),
                                timer: 1800,
                                showConfirmButton: false
                            });

                            var modalEl = document.getElementById('modalCoordinatorReview');
                            if (modalEl && window.bootstrap) {
                                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                            }

                            setTimeout(function () {
                                window.location.reload();
                            }, 1500);
                        } else {
                            Swal.fire(t('title_error', 'Ralat'), (res ? res.message : t('err_default', 'Gagal memproses permohonan.')), 'error');
                        }
                    },
                    error: function (xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        Swal.fire(t('title_sys_error', 'Ralat Sistem'), t('err_server', 'Gagal berhubung dengan pelayan.'), 'error');
                    }
                });
            }
        });
    });
});