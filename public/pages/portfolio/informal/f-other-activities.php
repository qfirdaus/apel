<section class="card shadow-sm rounded-3 mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Other Learning Activities</h5>
    </div>
    <div class="card-body">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="chkNoOther">
            <label class="form-check-label text-success fw-bold" for="chkNoOther">
                No Other Learning Activities
            </label>
        </div>

        <div id="otherWrap">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" id="btnAddOther" data-bs-toggle="modal" data-bs-target="#modalOther">
                    <i class="ri-add-line me-1"></i> Add Record
                </button>
            </div>

            <div class="table-responsive dt-standard">
                <table class="table table-striped table-bordered align-middle w-100" id="tblOther">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Other activities</th>
                            <th>Skill Level</th>
                            <th>What I have learnt/acquired</th>
                            <th class="text-center">Evidence</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>BENGKEL PEMBINAAN ITEM REKA BENTUK DAN TEKNOLOGI</td>
                            <td>Mahir</td>
                            <td>Membina soalan untuk PT3</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-info">
                                    <i class="ri-search-line"></i> (1)
                                </button>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary" aria-label="Edit">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" aria-label="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/modal-other.php'; ?>
