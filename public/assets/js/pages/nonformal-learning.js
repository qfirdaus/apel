$(function () {
    'use strict';

    // Teks bahasa dihantar dari PHP (translate-js.php) 
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

    function hideModal(modalId) {
        var modalElement = document.getElementById(modalId);
        if (modalElement && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
        }
    }

    // DatePicker
    function initDatePickers() {
        if ($.fn.daterangepicker) {
            $('.datepicker').each(function () {
                if (!$(this).data('daterangepicker')) {
                    var parentModal = $(this).closest('.modal');
                    $(this).daterangepicker({
                        singleDatePicker: true, autoApply: true, showDropdowns: true, autoUpdateInput: false,
                        parentEl: parentModal.length ? parentModal : 'body',
                        locale: { format: 'DD/MM/YYYY', cancelLabel: t('js_datepicker_clear') }
                    }).on('apply.daterangepicker', function(ev, picker) {
                        $(this).val(picker.startDate.format('DD/MM/YYYY'));
                    }).on('cancel.daterangepicker', function(ev, picker) {
                        $(this).val('');
                    });
                }
            });
        }
    }
    
    initDatePickers();
    $('#modalAddNonFormal, #modalEditTraining').on('shown.bs.modal', function () { initDatePickers(); });

    // Initialize DataTable
    function initializeDataTable(selector) {
        if ($.fn.DataTable && $(selector).length) {
            var options = window.DataTableStandard ? window.DataTableStandard.options({
                pageLength: 10, order: [], autoWidth: false
            }) : { pageLength: 10 };

            $(selector).DataTable(options);
            
            if (window.DataTableStandard) {
                window.DataTableStandard.decorate(selector, {
                    searchPlaceholder: t('js_search_placeholder')
                });
            }
        }
    }
    initializeDataTable('#tblNonFormal');

    // Preview Multiple Files 
    function setupMultipleFilePreview(inputId, displayContainerId) {
        $(inputId).on('change', function () {
            var files = this.files;
            var html = '';
            if (files.length > 0) {
                html += '<p class="mb-1 text-dark">' + t('js_selected_files', { n: files.length }) + '</p><ul class="mb-0 ps-3">';
                for (var i = 0; i < files.length; i++) {
                    var sizeKB = (files[i].size / 1024).toFixed(2);
                    html += '<li><i class="ri-file-text-line"></i> ' + files[i].name + ' <span class="text-muted">(' + sizeKB + ' KB)</span></li>';
                }
                html += '</ul>';
            }
            $(displayContainerId).html(html);
        });
    }

    // Set preview untuk borang 
    setupMultipleFilePreview('#evidenceFilesNonFormal', '#fileListNonFormal');
    setupMultipleFilePreview('#evidenceFilesEditNonFormal', '#fileListEditNonFormal');

    // Submit Borang (Add / Edit)
    function submitNFForm(formSelector, modalId) {
        $(formSelector).on('submit', function (event) {
            event.preventDefault();
            var form = this;
            var formData = new FormData(form);
            
            Swal.fire({
                title: t('js_save_confirm_title'), 
                text: t('js_save_confirm_text'), 
                icon: 'question',
                showCancelButton: true, confirmButtonColor: '#28a745', cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="ri-save-3-line me-1"></i> ' + t('js_save_btn'), 
                cancelButtonText: t('js_cancel'), reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: t('js_saving_title'), text: t('js_saving_text'), allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: 'ajax_process.php', type: 'POST', data: formData,
                        contentType: false, processData: false, dataType: 'json',
                        success: function (res) {
                            if (res.status === 'success') {
                                Swal.fire({ 
                                    icon: 'success', 
                                    title: t('js_success'), 
                                    text: res.message || t('js_save_success'), 
                                    timer: 2000, 
                                    showConfirmButton: false 
                                });
                                hideModal(modalId);
                                setTimeout(function() { window.location.reload(); }, 2000);
                            } else {
                                Swal.fire(t('js_error'), res.message || t('js_request_failed'), 'error');
                            }
                        },
                        error: function () {
                            Swal.fire(t('js_system_error'), t('js_connection_error'), 'error');
                        }
                    });
                }
            });
        });
    }
    submitNFForm('#formAddNonFormal', 'modalAddNonFormal');
    submitNFForm('#formEditTraining', 'modalEditTraining');

    // Pop-up Edit Form Populate
    $(document).on('click', '.btn-edit-nonformal', function() {
        var btn = $(this);
        $('#edit_record_id').val(btn.data('id'));
        $('#edit_course_name').val(btn.data('course'));        
        $('#edit_organizer').val(btn.data('organizer'));
        $('#edit_course_date').val(btn.data('date'));
        $('#edit_duration').val(btn.data('duration'));
        $('#edit_duration_type').val(btn.data('duration-type')); 
        $('#edit_knowledge_description').val(btn.data('knowledge'));
        
        var skills = btn.data('skills');
        $('input[name="skills[]"]').prop('checked', false);
        if(skills && skills.length > 0) {
            $.each(skills, function(index, val) {
                $('input[name="skills[]"][value="'+val+'"]').prop('checked', true);
            });
        }
        
        var files = btn.data('files');
        var fileContainer = $('#edit_existing_files');
        fileContainer.empty();
        if (files && files.length > 0) {
            $.each(files, function(index, file) {
                var fileHtml = '<div class="list-group-item d-flex justify-content-between align-items-center p-2 bg-light border-0 mb-1">' +
                               '<div><i class="ri-file-text-line me-2 text-primary"></i><a href="'+file.url+'" target="_blank" class="text-dark">'+file.name+'</a></div>' +
                               '<button type="button" class="btn btn-sm btn-danger btn-delete-single-file" data-id="'+file.id+'" title="'+t('js_delete_this_file')+'"><i class="ri-close-line"></i></button>' +
                               '</div>';
                fileContainer.append(fileHtml);
            });
        } else {
            fileContainer.append('<div class="text-muted small p-2">' + t('js_no_attachments') + '</div>');
        }
        
        new bootstrap.Modal(document.getElementById('modalEditTraining')).show();
    });

    // Delete Keseluruhan Rekod
    $(document).on('click', '.btn-delete-nonformal', function () {
        var recordId = $(this).data('id');
        Swal.fire({
            title: t('js_delete_confirm_title'),
            text: t('js_delete_record_text'),
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> ' + t('js_delete_btn'),
            cancelButtonText: t('js_cancel'), reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: t('js_deleting'), allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: 'ajax_process.php', type: 'POST', data: { action: 'delete_training', id: recordId },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire({ 
                                icon: 'success', 
                                title: t('js_success'), 
                                text: res.message || t('js_delete_record_success'), 
                                timer: 2000, 
                                showConfirmButton: false 
                            });
                            setTimeout(function() { window.location.reload(); }, 2000);
                        } else {
                            Swal.fire(t('js_error'), res.message || t('js_delete_record_failed'), 'error');
                        }
                    },
                    error: function() {
                        Swal.fire(t('js_system_error'), t('js_server_unreachable'), 'error');
                    }
                });
            }
        });
    });

    // Delete Satu Fail Lampiran (Borang Edit)
    $(document).on('click', '.btn-delete-single-file', function() {
        var btnDelete = $(this);
        var fileId = btnDelete.data('id');
        var fileBox = btnDelete.closest('.list-group'); 

        Swal.fire({
            title: t('js_delete_confirm_title'),
            text: t('js_delete_file_text'),
            icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> ' + t('js_delete_btn'),
            cancelButtonText: t('js_cancel'), reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: t('js_deleting'), allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: 'ajax_process.php', type: 'POST', data: { action: 'delete_file', id: fileId },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            Swal.fire({ icon: 'success', title: t('js_success'), text: t('js_file_deleted'), timer: 1500, showConfirmButton: false });
                            btnDelete.closest('.list-group-item').fadeOut(300, function() { 
                                $(this).remove(); 
                                if (fileBox.find('.list-group-item').length === 0) {
                                    fileBox.html('<div class="text-muted small p-3 text-center">' + t('js_no_existing_attachments') + '</div>');
                                }
                            });
                        } else {
                            Swal.fire(t('js_error'), response.message || t('js_delete_file_failed'), 'error');
                        }
                    },
                    error: function() { Swal.fire(t('js_system_error'), t('js_server_unreachable_retry'), 'error'); }
                });
            }
        });
    });

    // View Files Modal
    $(document).on('click', '.btn-view-files', function() {
        var files = $(this).data('files');
        var listContainer = $('#fileListContainer');
        listContainer.empty();
        if (files && files.length > 0) {
            $.each(files, function(index, file) {
                var listItem = '<a href="' + file.url + '" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center border-0 mb-1 rounded bg-light">' +
                               '<i class="ri-file-text-line fs-4 me-3 text-primary"></i>' +
                               '<span class="text-truncate flex-grow-1">' + file.name + '</span>' +
                               '<i class="ri-external-link-line text-muted ms-2"></i></a>';
                listContainer.append(listItem);
            });
        } else {
            listContainer.append('<div class="text-center text-muted p-3">' + t('js_no_files_found') + '</div>');
        }
        new bootstrap.Modal(document.getElementById('modalViewFiles')).show();
    });
});