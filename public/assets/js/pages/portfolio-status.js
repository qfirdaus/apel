$(document).ready(function() {
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
});