<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold">Other Learning Activities</h5>
</div>

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="noOtherActivity">
    <label class="form-check-label text-success fw-bold" for="noOtherActivity">
        No Other Learning Activities
    </label>
</div>

<button class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalAddOtherAct">
    <i class="ri-add-line"></i> Add Record
</button>

<!-- Jadual Other Activities -->
<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="5%">No</th>
                <th width="25%">Other activities</th>
                <th width="20%">Skill Level</th>
                <th width="30%">What I have learnt/acquired</th>
                <th width="10%" class="text-center">Evidence</th>
                <th width="10%" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>BENGKEL PEMBINAAN ITEM REKA BENTUK DAN TEKNOLOGI</td>
                <td>Mahir</td>
                <td>Membina soalan untuk PT3</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-info"><i class="ri-search-line"></i> (1)</button>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary"><i class="ri-edit-line"></i></button>
                    <button class="btn btn-sm btn-danger"><i class="ri-delete-bin-line"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal Add Other Activities -->
<div class="modal fade" id="modalAddOtherAct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formOtherAct" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add Record (Other Learning Activities)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Other activities</label>
                        <input type="text" name="activity_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Organizer</label>
                        <input type="text" name="organizer" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Skill Level</label>
                        <select name="skill_level" class="form-select">
                            <option value="" disabled selected>- PLEASE CHOOSE -</option>
                            <option value="Asas">Asas (Basic)</option>
                            <option value="Pertengahan">Pertengahan (Intermediate)</option>
                            <option value="Mahir">Mahir (Advanced)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">What I Have Learned/Acquired</label>
                        <textarea name="what_learned" class="form-control" rows="4"></textarea>
                    </div>
                    
                    <!-- Input Multiple Files -->
                    <div class="mb-3 p-3 bg-light border rounded">
                        <label class="form-label fw-bold">Evidence Of Learning</label>
                        <p class="text-danger small mb-1">** Only PDF, JPEG and JPG files are allowed. You can select multiple files.</p>
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