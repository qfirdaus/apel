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

    $('.btn-upload-trigger').on('click', function () {
        var docTypeId = $(this).data('doctype');
        var docName = $(this).data('docname');

        $('#hidden_doc_type_id').val(docTypeId);
        $('#hidden_doc_name').val(docName);
        $('#hidden_file_input').val('');
        $('#hidden_file_input').click();
    });

    $('#hidden_file_input').on('change', function () {
        var fileInput = this;
        
        if (fileInput.files && fileInput.files.length > 0) {
            var file = fileInput.files[0];

            var fileSize = file.size / 1024 / 1024; // Tukar ke MB
            if (fileSize > 5) {
                Swal.fire({
                    icon: 'warning',
                    title: t('js_warning'),
                    text: t('js_upload_too_big')
                });
                $(this).val(''); 
                return;
            }

            var fileName = file.name;
            var ext = fileName.split('.').pop().toLowerCase();
            if ($.inArray(ext, ['pdf', 'jpg', 'jpeg', 'png']) === -1) {
                Swal.fire({
                    icon: 'warning',
                    title: t('js_warning'),
                    text: t('js_upload_bad_format')
                });
                $(this).val(''); 
                return;
            }

            var form = document.getElementById('formUploadDocument');
            var formData = new FormData(form);

            var csrfElement = document.getElementById('csrfToken');
            if (csrfElement) {
                formData.set('csrf_token', csrfElement.value);
            }

            Swal.fire({
                title: t('js_uploading_title'),
                text: t('js_uploading_text'),
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
                            text: response.message || t('js_upload_success'),
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                        
                    } else {
                        Swal.fire(t('js_error'), (response && response.message) || t('js_upload_failed'), 'error');
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