$(function () {
    'use strict';

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

    // DataTable
    $('#tblAdminAssign').DataTable({
        pageLength: 10,
        autoWidth: false,
        language: {
            emptyTable: t('js_no_files_found'),
            zeroRecords: t('js_no_files_found')
        }
    });

    // Fungsi Papar / Sorok Kad Maklumat Staf
    function updateCardDisplay(selectId, cardId) {
        var $select = $(selectId);
        var $card   = $(cardId);
        var val     = $select.val();

        if (val === null || val === undefined || String(val) === '') {
            $card.addClass('d-none');
            return;
        }

        var $opt = $select.find('option').filter(function () {
            return this.value === String(val);
        }).first();

        if (!$opt.length) {
            $card.addClass('d-none');
            return;
        }

        var dept = $.trim($opt.attr('data-dept') || '');
        var pos  = $.trim($opt.attr('data-pos') || '');

        $card.find('.info-dept').text(dept !== '' ? dept : '-');
        $card.find('.info-pos').text(pos !== '' ? pos : '-');
        $card.removeClass('d-none');
    }

    var CARD_MAP = {
        'assign_advisor_id':     '#card_advisor_info',
        'assign_coordinator_id': '#card_coordinator_info'
    };

    function refreshCard(selectEl) {
        var cardSel = CARD_MAP[selectEl.id];
        if (cardSel) {
            updateCardDisplay('#' + selectEl.id, cardSel);
        }
    }

    $(document).on('change select2:select select2:unselect select2:clear select2:close',
        '#assign_advisor_id, #assign_coordinator_id',
        function () { refreshCard(this); });

    ['assign_advisor_id', 'assign_coordinator_id'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () { refreshCard(this); });
        }
    });

    var lastSelectValue = {};
    setInterval(function () {
        Object.keys(CARD_MAP).forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) { return; }
            if (lastSelectValue[id] !== el.value) {
                lastSelectValue[id] = el.value;
                refreshCard(el);
            }
        });
    }, 250);

    $(document).on('click', '.btn-open-assign', function (e) {
        e.preventDefault();

        var btn = $(this);
        var appId           = btn.data('app-id');
        var studentId       = btn.data('student-id');
        var advisorId       = btn.data('advisor-id');
        var coordinatorName = btn.data('coordinator-name');

        $('#assign_app_id').val(appId);
        $('#assign_student_id').val(studentId);
        $('#display_student_id').text(t('js_assign_student_display', { student: studentId, id: appId }));
        $('#display_coordinator_name').text(coordinatorName || t('js_assign_coordinator_unset'));

        var modalEl = document.getElementById('modalAssignOfficers');
        var modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalObj.show();

        $('#assign_advisor_id').val(advisorId ? String(advisorId) : '').trigger('change');

        setTimeout(function () {
            updateCardDisplay('#assign_advisor_id', '#card_advisor_info');
        }, 150);
    });

    $('#modalAssignOfficers').on('hidden.bs.modal', function () {
        $('#formAssignOfficers')[0].reset();
        $('#assign_advisor_id').val('').trigger('change');
        $('#assign_coordinator_id').val('').trigger('change');
        $('#card_advisor_info').addClass('d-none');
        $('#card_coordinator_info').addClass('d-none');
    });

    // Submit Form 
    $('#formAssignOfficers').on('submit', function (event) {
        event.preventDefault();

        var formData = new FormData(this);
        var csrfToken = $('#csrfToken').val();
        formData.append('csrf_token', csrfToken);

        Swal.fire({
            title: t('js_assign_confirm_title'),
            text: t('js_assign_confirm_text'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: t('js_save_btn'),
            cancelButtonText: t('js_cancel'),
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: t('js_saving_title'),
                    allowOutsideClick: false,
                    didOpen: function () { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'ajax_admin.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: t('js_success'),
                                text: res.message || t('js_assign_saved'),
                                timer: 2000,
                                showConfirmButton: false
                            });
                            hideModal('modalAssignOfficers');
                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                        } else {
                            Swal.fire(t('js_error'), res.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        Swal.fire(t('js_system_error'), t('js_server_unreachable'), 'error');
                    }
                });
            }
        });
    });
});