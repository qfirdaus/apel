<div class="modal fade modal-add-accent" id="modalAddFacultyCoordinator" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-community-line me-1"></i> <span><?= h(__('coord_modal_title')) ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('work_btn_close')) ?>"></button>
            </div>

            <form id="formAddFacultyCoordinator">
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="action" value="assign_faculty_coordinator">
                    <input type="hidden" name="faculty_name" id="hidden_faculty_name">

                    <!-- PILIH FAKULTI -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            <?= h(__('coord_label_faculty')) ?> <span class="text-danger">*</span>
                        </label>
                        <select name="faculty_code" id="select_faculty_code" class="form-select select2" required style="width: 100%;">
                            <option value=""><?= h(__('coord_choose_faculty')) ?></option>
                            <?php foreach ($faculties as $fac): ?>
                                <option value="<?= h($fac['faculty_code']) ?>" data-name="<?= h($fac['faculty_name']) ?>">
                                    <?= h($fac['faculty_name']) ?> 
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- PILIH RAMAI PEGAWAI PENYELARAS (MULTIPLE SELECT) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            <?= h(__('coord_label_officers')) ?> <span class="text-danger">*</span>
                        </label>
                        <select name="staf_ids[]" id="select_coord_staf_id" class="form-select select2" multiple="multiple" required style="width: 100%;">
                            <?php foreach ($academicStaff as $staf): 
                                $dept = !empty($staf['f_namajabatan']) ? trim((string)$staf['f_namajabatan']) : (!empty($staf['jabatansemasa']) ? trim((string)$staf['jabatansemasa']) : '-');
                                $pos  = !empty($staf['f_jawatan']) ? trim((string)$staf['f_jawatan']) : (!empty($staf['jawatansemasa']) ? trim((string)$staf['jawatansemasa']) : '-');
                                $nama = !empty($staf['f_nama']) ? trim((string)$staf['f_nama']) : trim((string)$staf['f_stafID']);
                            ?>
                                <option value="<?= h($staf['f_stafID']) ?>"
                                        data-dept="<?= h($dept) ?>"
                                        data-pos="<?= h($pos) ?>">
                                    <?= h($nama) ?> (<?= h($staf['f_stafID']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div id="container_coord_cards" class="mt-3 d-none">
                            <small class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <i class="ri-id-card-line me-1"></i> <?= h(__('coord_selected_info')) ?>
                            </small>
                            <div id="list_coord_cards" class="d-flex flex-column gap-2" style="max-height: 220px; overflow-y: auto;"></div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small border-0 mb-0">
                        <i class="ri-information-line me-1"></i> 
                        <?= __('coord_alert_info') ?>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4 fw-medium" data-bs-dismiss="modal"><?= h(__('js_cancel')) ?></button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnSaveCoord"><?= h(__('coord_btn_save')) ?></button>
                </div>
            </form>

        </div>
    </div>
</div>