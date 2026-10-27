$(function () {
    'use strict';

    if ($.fn.modal && $.fn.modal.Constructor) {
        $.fn.modal.Constructor.prototype._enforceFocus = function () {};
    }

    if ($.fn.DataTable && $('#tblFacultyCoordinators').length) {
        $('#tblFacultyCoordinators').DataTable({
            pageLength: 10,
            autoWidth: false
        });
    }

    if ($.fn.select2) {
        // Dropdown Fakulti
        $('#select_faculty_code').select2({
            dropdownParent: $('#modalAddFacultyCoordinator'),
            width: '100%',
            placeholder: '-- Sila Pilih Fakulti --'
        });

        // Dropdown Berbilang Penyelaras
        $('#select_coord_staf_id').select2({
            dropdownParent: $('#modalAddFacultyCoordinator'),
            width: '100%',
            placeholder: 'Taip nama atau ID staf untuk cari dan pilih...',
            closeOnSelect: false,    
            allowClear: true
        });
    }

    // Pastikan nama fakulti diambil dengan betul
    function syncFacultyName() {
        var opt = $('#select_faculty_code').find('option:selected');
        var name = opt.data('name') || '';
        $('#hidden_faculty_name').val(name);
    }

    $('#select_faculty_code').on('change select2:select', function () {
        syncFacultyName();
    });

    // 1. Paparkan kad maklumat bagi semua staf yang dipilih (Guna jQuery .val() bukan selectedOptions)
    function renderSelectedCoordCards() {
        var $select    = $('#select_coord_staf_id');
        var $container = $('#container_coord_cards');
        var $list      = $('#list_coord_cards');

        // Dapatkan senarai ID staf terpilih dalam bentuk Array
        var selectedVals = $select.val() || [];

        $list.empty();

        if (!Array.isArray(selectedVals) || selectedVals.length === 0) {
            $container.addClass('d-none');
            return;
        }

        selectedVals.forEach(function (stafId) {
            // Cari option sebenar dalam select asal
            var $opt = $select.find('option[value="' + CSS.escape(String(stafId)) + '"]');

            if ($opt.length) {
                var rawText = $.trim($opt.text());
                var dept    = $.trim($opt.attr('data-dept') || '-');
                var pos     = $.trim($opt.attr('data-pos') || '-');

                var cardHtml = 
                    '<div class="coord-info-card p-2 rounded-3 border bg-light mb-1" style="border-left: 4px solid #8b1538 !important;">' +
                        '<div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">' +
                            '<i class="ri-user-star-line text-danger me-1"></i> ' + rawText +
                        '</div>' +
                        '<div class="d-flex flex-wrap gap-3" style="font-size: 0.78rem;">' +
                            '<div>' +
                                '<span class="text-muted fw-bold"><i class="ri-building-line text-success me-1"></i>DEPARTMENT:</span> ' +
                                '<span class="text-secondary fw-semibold">' + dept + '</span>' +
                            '</div>' +
                            '<div>' +
                                '<span class="text-muted fw-bold"><i class="ri-briefcase-line text-primary me-1"></i>POSITION:</span> ' +
                                '<span class="text-secondary fw-semibold">' + pos + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                $list.append(cardHtml);
            }
        });

        // Tunjukkan bekas kad
        $container.removeClass('d-none');
    }

    // Dengar semua event Select2 dan Native Select
    $(document).on('change select2:select select2:unselect select2:clear', '#select_coord_staf_id', function () {
        renderSelectedCoordCards();
    });

    // Semakan berterusan (Fallback) jika select2 event lambat trigger
    var lastSelectedJSON = '';
    setInterval(function () {
        var el = document.getElementById('select_coord_staf_id');
        if (!el) return;

        var currentVal = $('#select_coord_staf_id').val() || [];
        var currentJSON = JSON.stringify(currentVal);

        if (lastSelectedJSON !== currentJSON) {
            lastSelectedJSON = currentJSON;
            renderSelectedCoordCards();
        }
    }, 250);

    // Reset apabila modal ditutup
    $('#modalAddFacultyCoordinator').on('hidden.bs.modal', function () {
        $('#formAddFacultyCoordinator')[0].reset();
        $('#select_faculty_code').val('').trigger('change');
        $('#select_coord_staf_id').val(null).trigger('change');
        $('#container_coord_cards').addClass('d-none');
        $('#list_coord_cards').empty();
        $('#hidden_faculty_name').val('');
        lastSelectedJSON = '';
    });

    // Penghantaran borang pelantikan
    $('#formAddFacultyCoordinator').on('submit', function (e) {
        e.preventDefault();

        syncFacultyName();

        var formData = new FormData(this);
        var csrfToken = $('#csrfToken').val();
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }

        Swal.fire({
            title: 'Pengesahan',
            text: 'Lantik staf yang dipilih sebagai Penyelaras bagi fakulti ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lantik',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });

                $.ajax({
                    url: 'ajax_assign_coordinator.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire('Berjaya', res.message, 'success');
                            setTimeout(function () { window.location.reload(); }, 1200);
                        } else {
                            Swal.fire('Ralat', res.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Ralat', 'Gagal berhubung dengan pelayan.', 'error');
                    }
                });
            }
        });
    });

    // Tindakan gugurkan lantikan
    $(document).on('click', '.btn-remove-coord', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var faculty = $(this).data('faculty');
        var csrfToken = $('#csrfToken').val();

        Swal.fire({
            title: 'Gugurkan Penyelaras?',
            text: 'Adakah anda pasti untuk menggugurkan ' + name + ' sebagai Penyelaras bagi ' + faculty + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Gugurkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then(function (res) {
            if (res.isConfirmed) {
                $.ajax({
                    url: 'ajax_assign_coordinator.php',
                    type: 'POST',
                    data: { action: 'remove_faculty_coordinator', id: id, csrf_token: csrfToken },
                    dataType: 'json',
                    success: function (r) {
                        if (r.status === 'success') {
                            Swal.fire('Berjaya', r.message, 'success');
                            setTimeout(function () { window.location.reload(); }, 1200);
                        } else {
                            Swal.fire('Ralat', r.message, 'error');
                        }
                    }
                });
            }
        });
    });
});