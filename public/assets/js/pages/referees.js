$(function () {
    'use strict';
    
    // terjemahan global (pages-main.js)
    var t = window.t;

    // Submit Referees
    $('#formReferees').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        
        var csrfElement = document.getElementById('csrfToken');
        if (csrfElement) {
            formData.set('csrf_token', csrfElement.value);
        }

        Swal.fire({
            title: t('js_referees_save_title'),
            text: t('js_referees_save_text'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ri-save-3-line me-1"></i> ' + t('js_save_btn'),
            cancelButtonText: t('js_cancel'),
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                
                Swal.fire({
                    title: t('js_saving_title'),
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'ajax_process.php', 
                    type: 'POST',
                    data: formData,
                    contentType: false, 
                    processData: false, 
                    dataType: 'json',
                    success: function (response) {
                        if (response && response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: t('js_success'),
                                text: response.message || t('js_referees_save_success'),
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                            
                        } else {
                            Swal.fire(t('js_error'), (response && response.message) || t('js_request_failed'), 'error');
                        }
                    },
                    error: function (xhr) {
                        var responseMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : t('js_server_unreachable');
                        Swal.fire(t('js_system_error'), responseMessage, 'error');
                    }
                });
            }
        });
    });
});