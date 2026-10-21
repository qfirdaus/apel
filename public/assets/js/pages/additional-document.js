$(document).ready(function() {
    // DataTable
    if ($.fn.DataTable && $('#tblAdditionalDoc').length) {
        $('#tblAdditionalDoc').DataTable({ pageLength: 10, autoWidth: false });
    }

    // 1. Muat Naik Dokumen Tambahan (AJAX)
    $('#formAddDoc').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let csrfToken = $('#csrfToken').val();
        formData.append('csrf_token', csrfToken);

        $('#btnSaveDoc').prop('disabled', true).html('<i class="ri-loader-4-line spinner"></i> Memproses...');

        $.ajax({
            url: '../status/ajax_process.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire('Berjaya!', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Ralat!', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Ralat!', 'Gagal menghubungi pelayan.', 'error');
            },
            complete: function() {
                $('#btnSaveDoc').prop('disabled', false).html('Muat Naik');
            }
        });
    });

    // 2. Hantar Semula Rework (AJAX)
    $('#btnResubmitRework').on('click', function() {
        Swal.fire({
            title: 'Hantar Semula Portfolio?',
            text: "Portfolio anda akan dihantar semula untuk penilaian penasihat.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hantar Semula',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let csrfToken = $('#csrfToken').val();
                $.post('../status/ajax_process.php', {
                    action: 'resubmit_rework',
                    csrf_token: csrfToken
                }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Berjaya!', res.message, 'success').then(() => window.location.href = '../status/index.php');
                    } else {
                        Swal.fire('Ralat!', res.message, 'error');
                    }
                }, 'json');
            }
        });
    });
});