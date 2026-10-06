<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold">Work Experience</h5>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddWork">
        <i class="ri-add-line"></i> Add Record
    </button>
</div>

<!-- Jadual Work Experience -->
<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="5%">No</th>
                <th width="20%">Name of Employer/Self-Employed</th>
                <th width="25%">Position Held</th>
                <th width="30%">What I have learnt/acquired</th>
                <th width="10%" class="text-center">Evidence</th>
                <th width="10%" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dummy Data -->
            <tr>
                <td>1</td>
                <td>SEKOLAH MENENGAH AGAMA TINGGI AL-RAHMAN</td>
                <td>GURU AKADEMIK</td>
                <td>MELAKSANAKAN SESI PEMBELAJARAN...</td>
                <td class="text-center">
                    <!-- Ikon ini boleh diklik untuk lihat senarai fail -->
                    <button class="btn btn-sm btn-outline-info" title="View 3 Documents"><i class="ri-search-line"></i> (3)</button>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary"><i class="ri-edit-line"></i></button>
                    <button class="btn btn-sm btn-danger"><i class="ri-delete-bin-line"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal Add Work Experience -->
<div class="modal fade" id="modalAddWork" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formWorkExp" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add Record (Work Experience)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name of Employer/Self-Employed</label>
                        <input type="text" name="employer_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Address</label>
                        <textarea name="contact_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From (eg: mm/dd/yyyy)</label>
                            <input type="date" name="date_from" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">To (eg: mm/dd/yyyy)</label>
                            <input type="date" name="date_to" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position Held</label>
                        <input type="text" name="position_held" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">What I Have Learned/Acquired</label>
                        <textarea name="what_learned" class="form-control" rows="4"></textarea>
                    </div>
                    
                    <!-- Input Multiple Files -->
                    <div class="mb-3 p-3 bg-light border rounded">
                        <label class="form-label fw-bold">Evidence Of Learning</label>
                        <p class="text-danger small mb-1">** Only PDF, JPEG and JPG files with a maximum size of 5MB per file are allowed. You can select multiple files.</p>
                        <!-- Tambah [] pada name dan atribut "multiple" -->
                        <input type="file" name="evidence[]" class="form-control" accept=".pdf,.jpeg,.jpg" multiple>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>