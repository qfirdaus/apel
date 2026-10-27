<!-- Modal Keputusan Penyelaras Fakulti -->
<div class="modal fade modal-add-accent" id="modalCoordinatorReview" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-shield-check-line me-1"></i>
                    <span><?= h(__('coord_modal_title', 'Perakuan Penyelaras Fakulti')) ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <form id="formCoordinatorReview">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="submit_coordinator_decision">
                    <input type="hidden" name="app_id" id="coord_review_app_id">
                    <input type="hidden" name="student_id" id="coord_review_student_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Keputusan Perakuan <span class="text-danger">*</span></label>
                        <select name="decision" id="coord_review_decision" class="form-select" required>
                            <option value="">- SILA PILIH -</option>
                            <option value="recommend">Peraku & Majukan ke Penilaian/Senat</option>
                            <option value="rework">Kembalikan untuk Pindaan Pelajar (Rework)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ulasan / Catatan Penyelaras</label>
                        <textarea name="remarks" id="coord_review_remarks" class="form-control" rows="4" placeholder="Masukkan justifikasi atau catatan perakuan..."></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"><?= h(__('js_cancel', 'Batal')) ?></button>
                    <button type="submit" id="btnSubmitCoordDecision" class="btn btn-primary px-4">
                        <i class="ri-save-3-line me-1"></i> <?= h(__('coord_btn_submit', 'Simpan Keputusan')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>