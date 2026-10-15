$(function () {
    'use strict';
    // Teks bahasa dihantar dari PHP (lihat translate-js.php -> window.TEKS_BAHASA)
    // t('kunci') = ambil teks ikut bahasa semasa; t('kunci', {n: 5}) untuk ganti {n}
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

    // Initialize TinyMCE
    tinymce.init({
        selector: '.tinymce-editor', 
        height: 250,
        menubar: false,
        branding: false,
        promotion: false,
        plugins: [
            'advlist', 'lists', 'image', 'table', 'charmap', 'wordcount'
        ],
        toolbar: 'blocks | bold italic underline | bullist numlist | alignleft aligncenter alignright alignjustify | image table charmap | undo redo',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 15px; }',
    });

    // Submit
    $('#formSelfReflection').on('submit', function (e) {
        e.preventDefault();
        var form = this;

        tinymce.triggerSave();

        // Semakan Jumlah Perkataan 
        var totalWordCount = 0;
        
        $('.tinymce-editor').each(function() {
            var editorId = $(this).attr('id');
            var editorInstance = tinymce.get(editorId);
            
            if (editorInstance && editorInstance.plugins.wordcount) {
                totalWordCount += editorInstance.plugins.wordcount.body.getWordCount();
            }
        });

        if (totalWordCount < 500) {
            Swal.fire({
                icon: 'warning',
                title: t('js_warning') || 'Perhatian',
                text: t('js_reflection_min_words', { n: totalWordCount })
            });
            return false; 
        }        

        var formData = new FormData(form);
        var csrfElement = document.getElementById('csrfToken');
        if (csrfElement) {
            formData.set('csrf_token', csrfElement.value);
        }

        // Simpan (AJAX)
        Swal.fire({
            title: t('js_reflection_save_title'),
            text: t('js_reflection_save_text'),
            icon: 'question',
            showCancelButton: true, confirmButtonColor: '#28a745', cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ri-save-3-line me-1"></i> ' + t('js_save_btn'),
            cancelButtonText: t('js_cancel'), reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                
                Swal.fire({ title: t('js_saving_title'), allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                $.ajax({
                    url: 'ajax_process.php', 
                    type: 'POST',
                    data: formData,
                    contentType: false, processData: false, dataType: 'json',
                    success: function (response) {
                        if (response && response.status === 'success') {
                            Swal.fire({ icon: 'success', title: t('js_success'), text: t('js_save_success'), timer: 2000, showConfirmButton: false });
                            setTimeout(function() { window.location.reload(); }, 2000);
                        } else {
                            Swal.fire(t('js_error'), (response && response.message) || t('js_request_failed'), 'error');
                        }
                    },
                    error: function (xhr) {
                        Swal.fire(t('js_system_error'), t('js_server_unreachable'), 'error');
                    }
                });
            }
        });
    });
});