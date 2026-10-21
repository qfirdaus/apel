$(function () {
    'use strict';

    // 1. Enable/Disable Submit Button based on Checkbox
    $('#chkDeclaration').on('change', function() {
        $('#btnSubmitPortfolio').prop('disabled', !$(this).is(':checked'));
    });

    // 2. Submit Action
    $('#formSubmitPortfolio').on('submit', function (event) {
        event.preventDefault();
        
        Swal.fire({
            title: 'Sahkan Penghantaran?',
            text: "Adakah anda pasti? Portfolio tidak boleh diubah selepas dihantar.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="ri-send-plane-fill me-1"></i> Ya, Hantar!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                
                Swal.fire({
                    title: 'Menyemak Data...',
                    text: 'Sistem sedang membuat validasi 7 jadual portfolio anda.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'ajax_process.php',
                    type: 'POST',
                    data: { action: 'submit_portfolio' },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berjaya!',
                                text: res.message,
                                confirmButtonText: 'Selesai'
                            }).then(() => {
                                // Reload page untuk papar skrin hijau (Locked)
                                window.location.reload();
                            });
                        } else {
                            // Ralat validasi (Jika ada jadual yang belum diisi)
                            Swal.fire({
                                icon: 'error',
                                title: 'Penghantaran Ditolak',
                                html: res.message,
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire('Ralat Sistem', 'Gagal berhubung dengan pelayan.', 'error');
                    }
                });
            }
        });
    });
});