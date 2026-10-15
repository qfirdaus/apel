<!-- Modal Add -->
<div class="modal fade modal-add-accent" id="modalAddLanguage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formAddLanguage">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-global-line me-1"></i> <span><?= h(__('work_modal_add_title')) ?></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body px-4 py-4">
                    <input type="hidden" name="action" value="add_language">
                    
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('lang_label_language')) ?> <span class="text-danger">*</span></label>
                            <select name="language_id" id="add_lang_id" class="form-select select2" required>
                                <option value=""><?= h(__('other_skill_choose')) ?></option>
                                <?php foreach ($listLang as $l): ?>
                                    <option value="<?= h((string)$l['id']) ?>"><?= h($l['languageDesc']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('lang_label_reading')) ?> <span class="text-danger">*</span></label>
                            <select name="reading_level" class="form-select" required>
                                <option value=""><?= h(__('other_skill_choose')) ?></option>
                                <?php 
                                $isEnglish = (__('lang_code') === 'en');
                                foreach ($listSkill as $s): 
                                    $skillName = ($isEnglish && !empty($s['levelDesc_EN'])) ? $s['levelDesc_EN'] : $s['levelDesc'];
                                ?>
                                    <option value="<?= h((string)$s['id']) ?>"><?= h($skillName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('lang_label_speaking')) ?> <span class="text-danger">*</span></label>
                            <select name="speaking_level" class="form-select" required>
                                <option value=""><?= h(__('other_skill_choose')) ?></option>
                                <?php foreach ($listSkill as $s): 
                                    $skillName = ($isEnglish && !empty($s['levelDesc_EN'])) ? $s['levelDesc_EN'] : $s['levelDesc'];
                                ?>
                                    <option value="<?= h((string)$s['id']) ?>"><?= h($skillName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('lang_label_writing')) ?> <span class="text-danger">*</span></label>
                            <select name="writing_level" class="form-select" required>
                                <option value=""><?= h(__('other_skill_choose')) ?></option>
                                <?php foreach ($listSkill as $s): 
                                    $skillName = ($isEnglish && !empty($s['levelDesc_EN'])) ? $s['levelDesc_EN'] : $s['levelDesc'];
                                ?>
                                    <option value="<?= h((string)$s['id']) ?>"><?= h($skillName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"><?= h(__('work_btn_close')) ?></button>
                    <button type="submit" class="btn btn-primary px-4"><?= h(__('lang_btn_save')) ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade modal-add-accent" id="modalEditLanguage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formEditLanguage">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-edit-line me-1"></i> <span><?= h(__('lang_modal_edit_title')) ?></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body px-4 py-4">
                    <input type="hidden" name="action" value="update_language">
                    <input type="hidden" name="record_id" id="edit_lang_record_id">
                    
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('lang_label_language')) ?> <span class="text-danger">*</span></label>
                            <select name="language_id" id="edit_lang_id" class="form-select" required>
                                <option value=""><?= h(__('other_skill_choose')) ?></option>
                                <?php foreach ($listLang as $l): ?>
                                    <option value="<?= h((string)$l['id']) ?>"><?= h($l['languageDesc']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('lang_label_reading')) ?> <span class="text-danger">*</span></label>
                            <select name="reading_level" id="edit_read_id" class="form-select" required>
                                <option value=""><?= h(__('other_skill_choose')) ?></option>
                                <?php foreach ($listSkill as $s): 
                                    $skillName = ($isEnglish && !empty($s['levelDesc_EN'])) ? $s['levelDesc_EN'] : $s['levelDesc'];
                                ?>
                                    <option value="<?= h((string)$s['id']) ?>"><?= h($skillName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('lang_label_speaking')) ?> <span class="text-danger">*</span></label>
                            <select name="speaking_level" id="edit_speak_id" class="form-select" required>
                                <option value=""><?= h(__('other_skill_choose')) ?></option>
                                <?php foreach ($listSkill as $s): 
                                    $skillName = ($isEnglish && !empty($s['levelDesc_EN'])) ? $s['levelDesc_EN'] : $s['levelDesc'];
                                ?>
                                    <option value="<?= h((string)$s['id']) ?>"><?= h($skillName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary"><?= h(__('lang_label_writing')) ?> <span class="text-danger">*</span></label>
                            <select name="writing_level" id="edit_write_id" class="form-select" required>
                                <option value=""><?= h(__('other_skill_choose')) ?></option>
                                <?php foreach ($listSkill as $s): 
                                    $skillName = ($isEnglish && !empty($s['levelDesc_EN'])) ? $s['levelDesc_EN'] : $s['levelDesc'];
                                ?>
                                    <option value="<?= h((string)$s['id']) ?>"><?= h($skillName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"><?= h(__('work_btn_close')) ?></button>
                    <button type="submit" class="btn btn-primary px-4"><?= h(__('work_btn_save')) ?></button>
                </div>
            </div>
        </form>
    </div>
</div>