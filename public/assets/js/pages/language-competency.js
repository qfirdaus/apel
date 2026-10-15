$(function () {
    'use strict';

    function hideModal(modalId) {
        var modalElement = document.getElementById(modalId);
        if (modalElement && window.bootstrap) window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
    }

    // DataTable
    if ($.fn.DataTable && $('#tblLanguage').length) {
        $('#tblLanguage').DataTable({ pageLength: 10, autoWidth: false });
    }

    // Submit Form (Add & Edit)
    function submitForm(formSelector, modalId) {
        $(formSelector).on('submit', function (event) {
            event.preventDefault();
            var formData = new FormData(this);
            
            Swal.fire({
                title: t('js_save_confirm_title'), text: t('js_save_confirm_text'), icon: 'question',
                showCancelButton: true, confirmButtonText: t('js_save_btn'), cancelButtonText: t('js_cancel'), reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: t('js_saving_title'), allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: 'ajax_process.php', type: 'POST', data: formData, contentType: false, processData: false, dataType: 'json',
                        success: function (res) {
                            if (res.status === 'success') {
                                Swal.fire({ icon: 'success', title: t('js_success'), text: t('js_save_success'), timer: 2000, showConfirmButton: false });
                                hideModal(modalId); setTimeout(function() { window.location.reload(); }, 2000);
                            } else { Swal.fire(t('js_error'), res.message, 'error'); }
                        }
                    });
                }
            });
        });
    }

    submitForm('#formAddLanguage', 'modalAddLanguage');
    submitForm('#formEditLanguage', 'modalEditLanguage');

    $(document).on('click', '.btn-edit-language', function() {
        var btn = $(this);
        $('#edit_lang_record_id').val(btn.data('id'));
        $('#edit_lang_id').val(btn.data('lang')).trigger('change');        
        $('#edit_read_id').val(btn.data('read')).trigger('change');
        $('#edit_speak_id').val(btn.data('speak')).trigger('change');
        $('#edit_write_id').val(btn.data('write')).trigger('change');
        
        new bootstrap.Modal(document.getElementById('modalEditLanguage')).show();
    });

    // Delete Record
    $(document).on('click', '.btn-delete-language', function () {
        var recordId = $(this).data('id');
        Swal.fire({
            title: t('js_delete_confirm_title'), text: t('js_delete_record_text'), icon: 'warning', 
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: t('js_delete_btn'), cancelButtonText: t('js_cancel'), reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: t('js_deleting'), didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: 'ajax_process.php', type: 'POST', data: { action: 'delete_language', id: recordId }, dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire({ icon: 'success', title: t('js_success'), text: t('js_delete_record_success'), timer: 2000, showConfirmButton: false });
                            setTimeout(function() { window.location.reload(); }, 2000);
                        } else { Swal.fire(t('js_error'), res.message, 'error'); }
                    }
                });
            }
        });
    });

    // Reset Nilai & Paparan Select2 Apabila Borang Ditutup
    $('#modalAddLanguage').on('hidden.bs.modal', function () {
        $('#formAddLanguage')[0].reset();
        $('#formAddLanguage').find('.select2').val('').trigger('change');
    });

    $('#modalEditLanguage').on('hidden.bs.modal', function () {
        $('#formEditLanguage')[0].reset();
        $('#formEditLanguage').find('.select2').val('').trigger('change');
    });
});