<!-- Modal Edit Other Learning Activity -->
<div class="modal fade modal-add-accent" id="modalEditOtherAct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formEditOtherAct" enctype="multipart/form-data">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-pencil-line"></i>
                        <span><?= h(__('other_modal_edit_title')) ?></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('work_btn_close')) ?>"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body px-4 py-4">
                    <input type="hidden" name="action" value="update_other">
                    <input type="hidden" name="record_id" id="edit_other_record_id">

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('other_activity')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="activity_name" id="edit_other_activity_name" class="form-control" placeholder="<?= h(__('other_placeholder_activity')) ?>" maxlength="255" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('other_start_date')) ?></label>
                            <input type="text" name="start_date" id="edit_other_start_date" class="form-control datepicker" placeholder="dd/mm/yyyy" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('other_end_date')) ?></label>
                            <input type="text" name="end_date" id="edit_other_end_date" class="form-control datepicker" placeholder="dd/mm/yyyy" autocomplete="off">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('other_organizer')) ?></label>
                            <input type="text" name="organizer" id="edit_other_organizer" class="form-control" placeholder="<?= h(__('other_placeholder_organizer')) ?>" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('other_skill_level')) ?></label>
                            <select name="skill_level" id="edit_other_skill_level" class="form-select">
                                <option value=""><?= h(__('other_skill_choose')) ?></option>
                                <?php foreach ($skillOptions as $opt): ?>
                                <option value="<?= h($opt['id']) ?>"><?= h($opt['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('other_learned')) ?></label>
                            <textarea name="what_learned" id="edit_other_what_learned" class="form-control" rows="4" placeholder="<?= h(__('work_placeholder_learned')) ?>"></textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <!-- Senarai Fail Sedia Ada -->
                            <label class="form-label fw-bold text-primary mb-2">
                                <i class="ri-attachment-line me-1"></i> <?= h(__('work_existing_evidence')) ?>
                            </label>
                            <div id="edit_other_existing_files" class="list-group mb-4 shadow-sm border rounded-3">
                                <!-- Fail lama (JS) -->
                            </div>

                            <!-- Tambah Fail Baharu -->
                            <div class="p-4 bg-light border border-primary border-opacity-25 rounded-3">
                                <label class="form-label fw-bold text-primary mb-3">
                                    <i class="ri-upload-cloud-2-line me-1"></i> <?= h(__('work_evidence')) ?> <span class="text-muted fw-normal fs-6 ms-1"><?= h(__('work_optional_new_file')) ?></span>
                                </label>

                                <div class="alert alert-primary bg-primary bg-opacity-10 text-primary py-2 px-3 mb-3 border-0 d-flex align-items-start" role="alert">
                                    <i class="ri-information-fill me-2 mt-1 fs-5"></i>
                                    <div>
                                        <!-- Jangan guna h() di sini kerana ada tag HTML -->
                                        <?= __('work_upload_info_1') ?>
                                        <br><span class="small text-muted"><?= __('work_upload_info_2') ?></span>
                                    </div>
                                </div>

                                <input type="file" name="evidence_files[]" id="evidenceFilesEditOther" class="form-control border-primary" accept=".pdf,.jpg,.jpeg" multiple>
                                <p class="text-danger small mt-2 mb-0 fw-medium"><?= h(__('work_upload_limit')) ?></p>

                                <div id="fileListEditOther" class="file-list-display mt-3 text-primary small fw-semibold"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"><?= h(__('work_btn_close')) ?></button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ri-save-3-line me-1"></i> <?= h(__('work_btn_save')) ?>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>