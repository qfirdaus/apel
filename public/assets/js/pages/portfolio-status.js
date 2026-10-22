$(document).ready(function() {
    'use strict';

    // 1. Inisialisasi Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. Inisialisasi DataTable untuk Jadual Sejarah
    if ($.fn.DataTable && $('#tblPortfolioStatus').length) {
        if (!$.fn.DataTable.isDataTable('#tblPortfolioStatus')) {
            $('#tblPortfolioStatus').DataTable({
                pageLength: 5,
                lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "Semua"]],
                responsive: true,
                autoWidth: false,
                order: [[0, 'asc']]
            });
        }
    }

    // 3. Papar Portfolio PDF (AJAX)
    $(document).on('click', '.btn-view-pdf', function() {
        let appId = $(this).data('app-id');
        let csrfToken = $('#csrfToken').val();

        $('#pdfPreviewContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuatkan maklumat portfolio...</p></div>');

        $.ajax({
            url: 'ajax_process.php',
            type: 'POST',
            data: {
                action: 'get_portfolio_pdf_details',
                app_id: appId,
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#pdfPreviewContent').html(res.html);
                } else {
                    $('#pdfPreviewContent').html('<div class="alert alert-danger mb-0"><i class="ri-error-warning-line me-1"></i> ' + res.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                $('#pdfPreviewContent').html('<div class="alert alert-danger mb-0"><i class="ri-close-circle-line me-1"></i> Gagal memuatkan data portfolio. Ralat: ' + error + '</div>');
            }
        });
    }); 

    $(document).on('click', '#btnPrintPdf', function() {
        var printContents = document.getElementById('pdfPreviewContent').innerHTML;
        
        var printWindow = window.open('', '_blank');
        
        printWindow.document.write('<html><head><title>Cetak Portfolio APEL Q</title>');
        
        // Salin semua fail CSS (Style) dari halaman asal supaya format PDF kekal cantik
        var styles = document.querySelectorAll('link[rel="stylesheet"], style');
        styles.forEach(function(style) {
            printWindow.document.write(style.outerHTML);
        });
        
        printWindow.document.write('</head><body style="background: white; padding: 30px;">');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        
        printWindow.document.close();
        printWindow.focus();
        
        // Beri masa 500ms supaya CSS selesai dimuatkan sebelum arahan print dipanggil
        setTimeout(function() {
            printWindow.print();
            printWindow.close(); // Tutup tab secara automatik selepas cetak selesai
        }, 500);
    });

});