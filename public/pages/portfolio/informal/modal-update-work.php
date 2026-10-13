<!-- Modal Edit Work Experience -->
<div class="modal fade modal-add-accent" id="modalEditWorkExp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formEditWorkExp" enctype="multipart/form-data">
            <div class="modal-content">
                
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-pencil-line"></i>
                        <span><?= h(__('work_modal_edit_title')) ?></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('work_btn_close')) ?>"></button>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body px-4 py-4">
                    <input type="hidden" name="action" value="update_work">
                    <input type="hidden" name="record_id" id="edit_record_id">
                    <input type="hidden" name="student_id" value="<?= $_SESSION['student_id'] ?? '' ?>">
                    
                    <div class="row g-4">
                        <!-- Name of Employer -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('work_employer')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="employer_name" id="edit_employer_name" class="form-control" placeholder="<?= h(__('work_placeholder_employer')) ?>" required>
                        </div>
                        
                        <!-- Position Held -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('work_position')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="position_held" id="edit_position_held" class="form-control" placeholder="<?= h(__('work_placeholder_position')) ?>" required>
                        </div>
                        
                        <!-- Contact Address -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('work_address')) ?></label>
                            <textarea name="contact_address" id="edit_contact_address" class="form-control" rows="2" placeholder="<?= h(__('work_placeholder_address')) ?>"></textarea>
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('work_start_date')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="start_date" id="edit_start_date" class="form-control datepicker" placeholder="dd/mm/yyyy" autocomplete="off" required>
                        </div>
                        
                        <!-- End Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('work_end_date')) ?> <span class="text-danger">*</span></label>
                            <input type="text" name="end_date" id="edit_end_date" class="form-control datepicker" placeholder="dd/mm/yyyy" autocomplete="off" required>
                        </div>

                        <!-- What I Have Learned -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('work_learned')) ?> <span class="text-danger">*</span></label>
                            <textarea name="what_learned" id="edit_what_learned" class="form-control" rows="4" placeholder="<?= h(__('work_placeholder_learned')) ?>" required></textarea>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <!-- Senarai Fail Sedia Ada -->
                            <label class="form-label fw-bold text-primary mb-2">
                                <i class="ri-attachment-line me-1"></i> <?= h(__('work_existing_evidence')) ?>
                            </label>
                            <div id="edit_existing_files" class="list-group mb-4 shadow-sm border rounded-3">
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
                                
                                <input type="file" name="evidence_files[]" id="evidenceFilesEditWork" class="form-control border-primary" accept=".pdf,.jpg,.jpeg" multiple>
                                <p class="text-danger small mt-2 mb-0 fw-medium"><?= h(__('work_upload_limit')) ?></p>
                                
                                <div id="fileListEditWork" class="file-list-display mt-3 text-primary small fw-semibold"></div>
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