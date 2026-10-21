<!-- Modal Keputusan Penasihat -->
<div class="modal fade modal-add-accent" id="modalAdvisorReview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-briefcase-4-line"></i>
                    <span><?= h(__('adv_modal_title')) ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('work_btn_close')) ?>"></button>
            </div>

            <form id="formAdvisorReview">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="submit_advisor_decision">
                    <input type="hidden" name="app_id" id="review_app_id">
                    <input type="hidden" name="student_id" id="review_student_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Keputusan Penilaian <span class="text-danger">*</span></label>
                        <select name="decision" id="review_decision" class="form-select" required>
                            <option value="">- SILA PILIH -</option>
                            <option value="recommend"><?= h(__('adv_decision_recommend')) ?></option>
                            <option value="rework"><?= h(__('adv_decision_rework')) ?></option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= h(__('adv_remarks_label')) ?></label>
                        <textarea name="remarks" id="review_remarks" class="form-control" rows="4" placeholder="<?= h(__('adv_remarks_placeholder')) ?>"></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"><?= h(__('js_cancel')) ?></button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ri-save-3-line me-1"></i> <?= h(__('adv_btn_submit')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

