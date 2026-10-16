<!-- Modal Edit Non-Formal -->
<div class="modal fade modal-add-accent" id="modalEditTraining" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formEditTraining" enctype="multipart/form-data">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-edit-line"></i>
                        <span><?= h(__('nf_modal_edit_title')) ?></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('work_btn_close')) ?>"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body px-4 py-4">
                    <input type="hidden" name="action" value="update_training">
                    <input type="hidden" name="record_id" id="edit_record_id">

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('nf_label_title')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="course_name" id="edit_course_name" class="form-control" maxlength="255" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('nf_label_organizer')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="organizer" id="edit_organizer" class="form-control" maxlength="255" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('nf_label_date')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="course_date" id="edit_course_date" class="form-control datepicker" autocomplete="off" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('nf_label_duration')) ?> <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <input type="number" name="duration" id="edit_duration" class="form-control" min="1" required>
                                </div>
                                <div class="col-sm-6">
                                    <select name="duration_type" id="edit_duration_type" class="form-select" required>
                                        <option value="" selected><?= h(__('nf_unit_choose')) ?></option>
                                        <?php foreach ($durationTypes as $opt): ?>
                                        <option value="<?= h($opt['id']) ?>"><?= h($opt['label_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('nf_label_desc')) ?></label>
                            <textarea name="knowledge_desc" id="edit_knowledge_description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('nf_label_outcomes')) ?> <span class="text-danger">*</span></label><br>
                            <small class="text-muted fst-italic"><?= __('nf_outcomes_hint') ?></small>
                            
                            <div class="mt-2 ps-2">
                                <?php 
                                $outcomes = $nonFormalController->getOutcomes();
                                $isEnglish = (__('lang_code') === 'en');
                                
                                foreach($outcomes as $idx => $outcome): 
                                    $skillName = $isEnglish && !empty($outcome['outcomeDesc_EN']) ? $outcome['outcomeDesc_EN'] : $outcome['outcomeDesc'];
                                ?>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="skills[]" value="<?= h($skillName) ?>" id="edit_nf_skill_<?= $idx ?>">
                                    <label class="form-check-label" for="edit_nf_skill_<?= $idx ?>"><?= h($skillName) ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Bahagian Muat Naik Fail -->
                        <div class="col-12 mt-4">
                            <!-- Senarai Fail Sedia Ada -->
                            <div class="p-3 bg-light border border-secondary border-opacity-25 rounded-3 mb-3">
                                <label class="form-label fw-bold text-dark mb-2">
                                    <i class="ri-attachment-2 me-1"></i> <?= h(__('work_existing_evidence')) ?>
                                </label>
                                <div class="list-group list-group-flush" id="edit_existing_files">
                                    <!-- Senarai fail sedia ada & butang padam akan dijana oleh JavaScript -->
                                </div>
                            </div>

                            <!-- Tambah Fail Baharu -->
                            <div class="p-4 bg-light border border-primary border-opacity-25 rounded-3">
                                <label class="form-label fw-bold text-primary mb-3">
                                    <i class="ri-upload-cloud-2-line me-1"></i> <?= h(__('work_optional_new_file')) ?>
                                </label>

                                <div class="alert alert-primary bg-primary bg-opacity-10 text-primary py-2 px-3 mb-3 border-0 d-flex align-items-start" role="alert">
                                    <i class="ri-information-fill me-2 mt-1 fs-5"></i>
                                    <div>
                                        <?= __('work_upload_info_1') ?>
                                        <br><span class="small text-muted"><?= __('work_upload_info_2') ?></span>
                                    </div>
                                </div>

                                <input type="file" name="evidence_files[]" id="evidenceFilesEditNonFormal" class="form-control border-primary" accept=".pdf,.jpg,.jpeg" multiple>
                                <p class="text-danger small mt-2 mb-0 fw-medium"><?= h(__('work_upload_limit')) ?></p>

                                <div id="fileListEditNonFormal" class="file-list-display mt-3 text-primary small fw-semibold"></div>
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