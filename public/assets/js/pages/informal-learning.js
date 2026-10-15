$(function () {
    'use strict';

    // terjemahan global (pages-main.js)
    var t = window.t;

    function hideModal(modalId) {
        var modalElement = document.getElementById(modalId);
        if (!modalElement || !window.bootstrap || !window.bootstrap.Modal) return;
        window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
    }

    // Initialize DataTable
    function initializeDataTable(selector) {
        if ($.fn.DataTable && $(selector).length) {
            var options = window.DataTableStandard ? window.DataTableStandard.options({
                pageLength: 10,
                order: [],
                autoWidth: false
            }) : { pageLength: 10 };

            $(selector).DataTable(options);
            
            if (window.DataTableStandard) {
                window.DataTableStandard.decorate(selector, {
                    searchPlaceholder: t('js_search_placeholder')
                });
            }
        }
    }

    initializeDataTable('#tblWork');
    initializeDataTable('#tblOther');

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

    setupMultipleFilePreview('#evidenceFilesWork', '#fileListWork');
    setupMultipleFilePreview('#evidenceFilesOther', '#fileListOther');
    setupMultipleFilePreview('#evidenceFilesEditWork', '#fileListEditWork');
    setupMultipleFilePreview('#evidenceFilesEditOther', '#fileListEditOther');

    //  Submit Borang (Add & Edit)
    function submitForm(formSelector, modalId, endpoint) {
        $(formSelector).on('submit', function (event) {
            event.preventDefault();

            var form = this;
            var formData = new FormData(form);
            var csrfElement = document.getElementById('csrfToken');
            if (csrfElement) formData.set('csrf_token', csrfElement.value);

            Swal.fire({
                title: t('js_save_confirm_title'),
                text: t('js_save_confirm_text'),
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
                        text: t('js_saving_text'),
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: endpoint,
                        type: 'POST',
                        data: formData,
                        contentType: false, processData: false, dataType: 'json',
                        success: function (response) {
                            if (response && response.status === 'success') {
                                Swal.fire({ icon: 'success', title: t('js_success'), text: response.message || t('js_save_success'), timer: 2000, showConfirmButton: false });
                                hideModal(modalId);
                                setTimeout(function() { window.location.reload(); }, 2000);
                            } else {
                                Swal.fire(t('js_error'), response.message || t('js_request_failed'), 'error');
                            }
                        },
                        error: function (xhr) {
                            var responseMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : t('js_connection_error');
                            Swal.fire(t('js_system_error'), responseMessage, 'error');
                        }
                    });
                }
            });
        });

        $('#' + modalId).on('hidden.bs.modal', function () {
            $(formSelector)[0].reset();
            $(formSelector).find('.datepicker').val('');
            $(formSelector).find('.file-list-display').html('');
        });
    }

    submitForm('#formWorkExp', 'modalWorkExp', 'ajax_process.php');
    submitForm('#formEditWorkExp', 'modalEditWorkExp', 'ajax_process.php');
    submitForm('#formOtherAct', 'modalAddOtherAct', 'ajax_process.php');
    submitForm('#formEditOtherAct', 'modalEditOtherAct', 'ajax_process.php');

    // 'No Other Activities' Toggler + Autosave AJAX
    $('#chkNoOther').on('change', function (event) { 
        var checkbox = $(this);
        var isChecked = checkbox.is(':checked');
        var flagValue = isChecked ? 0 : 1; 
        
        $('#otherWrap').toggle(!isChecked);

        if(event && event.originalEvent) { 
            $.ajax({
                url: 'ajax_process.php', type: 'POST', data: { action: 'update_other_flag', flag: flagValue }, dataType: 'json',
                success: function(response) {
                    if(response.status !== 'success') {
                        checkbox.prop('checked', false); $('#otherWrap').show();
                        Swal.fire(t('js_error'), response.message, 'error');
                    }
                },
                error: function() {
                    checkbox.prop('checked', !isChecked); $('#otherWrap').toggle(isChecked);
                    Swal.fire(t('js_system_error'), 'Gagal berhubung dengan pelayan.', 'error');
                }
            });
        }
    }).trigger('change');

    // Simpan Tab Aktif
    document.querySelectorAll('#v-pills-tab [data-bs-toggle="pill"], .profile-tabs [data-bs-toggle="tab"], .nav-tabs [data-bs-toggle="tab"]').forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function (event) {
            var targetId = event.target.getAttribute('data-bs-target') || event.target.getAttribute('href');
            if (targetId) sessionStorage.setItem('activeInformalTab', targetId);
            
            var selector = (targetId && targetId.includes('other')) ? '#tblOther' : '#tblWork';
            if ($.fn.DataTable.isDataTable(selector)) {
                $(selector).DataTable().columns.adjust().draw(false);
            }
        });
    });

    var activeTab = sessionStorage.getItem('activeInformalTab');
    if (activeTab) {
        var triggerEl = document.querySelector('[data-bs-target="' + activeTab + '"]') || document.querySelector('[href="' + activeTab + '"]');
        if (triggerEl) {
            if (window.bootstrap && window.bootstrap.Tab) window.bootstrap.Tab.getOrCreateInstance(triggerEl).show();
            else $(triggerEl).tab('show'); 
        }
    }

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
    
    $(document).on('click', '.btn-delete-work, .btn-delete-other', function () {
        var recordId = $(this).data('id');
        var deleteAction = $(this).hasClass('btn-delete-other') ? 'delete_other' : 'delete_work';

        Swal.fire({
            title: t('js_delete_confirm_title'), text: t('js_delete_record_text'), icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> ' + t('js_delete_btn'), cancelButtonText: t('js_cancel'), reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: t('js_deleting'), allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: 'ajax_process.php', type: 'POST', data: { action: deleteAction, id: recordId }, dataType: 'json',
                    success: function (response) {
                        if (response && response.status === 'success') {
                            Swal.fire({ icon: 'success', title: t('js_success'), text: response.message, timer: 2000, showConfirmButton: false });
                            setTimeout(function() { window.location.reload(); }, 2000);
                        } else {
                            Swal.fire(t('js_error'), response.message || t('js_delete_record_failed'), 'error');
                        }
                    },
                    error: function () { Swal.fire(t('js_system_error'), t('js_server_unreachable'), 'error'); }
                });
            }
        });
    });  
    
    $(document).on('click', '.btn-edit-work', function() {
        var btn = $(this);
        $('#edit_record_id').val(btn.data('id'));
        $('#edit_employer_name').val(btn.data('employer'));        
        $('#edit_contact_address').val(btn.data('address'));
        $('#edit_start_date').val(btn.data('start'));
        $('#edit_end_date').val(btn.data('end'));
        $('#edit_position_held').val(btn.data('position'));
        $('#edit_what_learned').val(btn.data('learned'));

        var files = btn.data('files');
        var fileContainer = $('#edit_existing_files');
        fileContainer.empty();

        if (files && files.length > 0) {
            $.each(files, function(index, file) {
                var fileHtml = '<div class="list-group-item d-flex justify-content-between align-items-center p-2 bg-light border-0 mb-1">' +
                               '<div><i class="ri-file-text-line me-2 text-primary"></i><a href="'+file.url+'" target="_blank" class="text-dark">'+file.name+'</a></div>' +
                               '<button type="button" class="btn btn-sm btn-danger btn-delete-single-file" data-id="'+file.id+'" title="' + t('js_delete_this_file') + '"><i class="ri-close-line"></i></button></div>';
                fileContainer.append(fileHtml);
            });
        } else {
            fileContainer.append('<div class="text-muted small p-2">' + t('js_no_attachments') + '</div>');
        }
        new bootstrap.Modal(document.getElementById('modalEditWorkExp')).show();
    });

    $(document).on('click', '.btn-edit-other', function() {
        var btn = $(this);
        $('#edit_other_record_id').val(btn.data('id'));
        $('#edit_other_activity_name').val(btn.data('activity'));
        $('#edit_other_start_date').val(btn.data('start'));
        $('#edit_other_end_date').val(btn.data('end'));
        $('#edit_other_organizer').val(btn.data('organizer'));
        $('#edit_other_skill_level').val(btn.data('skill') || '');
        $('#edit_other_what_learned').val(btn.data('learned'));

        var files = btn.data('files');
        var fileContainer = $('#edit_other_existing_files');
        fileContainer.empty();

        if (files && files.length > 0) {
            $.each(files, function(index, file) {
                var fileHtml = '<div class="list-group-item d-flex justify-content-between align-items-center p-2 bg-light border-0 mb-1">' +
                               '<div><i class="ri-file-text-line me-2 text-primary"></i><a href="'+file.url+'" target="_blank" class="text-dark">'+file.name+'</a></div>' +
                               '<button type="button" class="btn btn-sm btn-danger btn-delete-single-file" data-id="'+file.id+'" title="' + t('js_delete_this_file') + '"><i class="ri-close-line"></i></button></div>';
                fileContainer.append(fileHtml);
            });
        } else {
            fileContainer.append('<div class="text-muted small p-2">' + t('js_no_attachments') + '</div>');
        }
        new bootstrap.Modal(document.getElementById('modalEditOtherAct')).show();
    });

    $(document).on('click', '.btn-delete-single-file', function() {
        var btnDelete = $(this);
        var fileId = btnDelete.data('id');
        var fileBox = btnDelete.closest('.list-group'); 

        Swal.fire({
            title: t('js_delete_confirm_title'), text: t('js_delete_file_text'), icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> ' + t('js_delete_btn'), cancelButtonText: t('js_cancel'), reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: t('js_deleting'), allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: 'ajax_process.php', type: 'POST', data: { action: 'delete_file', id: fileId }, dataType: 'json',
                    success: function(response) {
                        if(response && response.status === 'success') {
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
});