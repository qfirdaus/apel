$(function () {
    'use strict';

    var TEKS = window.TEKS_BAHASA || {};
    function t(key, fallback) {
        return Object.prototype.hasOwnProperty.call(TEKS, key) ? TEKS[key] : fallback;
    }

    // Inisialisasi DataTable untuk senarai tugasan menunggu Penyelaras
    if ($.fn.DataTable && $('#tblCoordinatorPending').length) {
        $('#tblCoordinatorPending').DataTable({
            pageLength: 10,
            autoWidth: false,
            ordering: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: t('dt_search', 'Cari...')
            }
        });
    }

    // Inisialisasi DataTable untuk sejarah semakan Penyelaras
    if ($.fn.DataTable && $('#tblCoordinatorHistory').length) {
        $('#tblCoordinatorHistory').DataTable({
            pageLength: 10,
            autoWidth: false,
            ordering: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: t('dt_search', 'Cari...')
            }
        });
    }

    // 1. Buka Modal Keputusan Perakuan Penyelaras
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

    // 2. Butang Pratonton Portfolio PDF
    $(document).on('click', '.btn-view-pdf', function (e) {
        e.preventDefault();

        var appId = $(this).data('app-id');
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
                app_id: appId
            },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success' && res.html) {
                    $previewContainer.html(res.html);
                } else {
                    $previewContainer.html(
                        '<div class="alert alert-danger my-3 text-center">' +
                            (res.message || t('err_load_pdf', 'Gagal memuatkan dokumen portfolio.')) +
                        '</div>'
                    );
                }
            },
            error: function () {
                $previewContainer.html(
                    '<div class="alert alert-danger my-3 text-center">' +
                        t('err_network', 'Ralat pelayan semasa memuatkan ringkasan portfolio.') +
                    '</div>'
                );
            }
        });
    });

    // 3. Hantar Keputusan Perakuan (Submit Form)
    $('#formCoordinatorReview').on('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(this);
        var decision = $('#coord_review_decision').val();
        var remarks  = $.trim($('#coord_review_remarks').val());
        var csrf     = $('#csrfToken').val();

        if (csrf) {
            formData.append('csrf_token', csrf);
        }

        // Semakan: Rework wajib ada catatan
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
                        if (res.status === 'success') {
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
                            Swal.fire(t('title_error', 'Ralat'), res.message || t('err_default', 'Gagal memproses permohonan.'), 'error');
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