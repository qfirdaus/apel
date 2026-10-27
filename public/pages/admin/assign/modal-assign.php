<!-- Modal Penugasan Penasihat Pelajar -->
<div class="modal fade modal-add-accent" id="modalAssignOfficers" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-user-settings-line me-1"></i> <span>Penugasan Penasihat Pelajar APEL.Q</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <form id="formAssignOfficers">
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="action" value="assign_advisor">
                    <input type="hidden" name="application_id" id="assign_app_id">
                    <input type="hidden" name="student_id" id="assign_student_id">

                    <!-- Maklumat Pelajar & Fakulti -->
                    <div class="alert alert-light border mb-3 rounded-3">
                        <div class="row">
                            <div class="col-md-7">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Pelajar:</small>
                                <strong class="text-dark fs-6" id="display_student_id">-</strong>
                            </div>
                            <div class="col-md-5 border-start">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Penyelaras Fakulti (Automatik):</small>
                                <strong class="text-primary" id="display_coordinator_name">-</strong>
                            </div>
                        </div>
                    </div>

                    <!-- PILIH PENASIHAT -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="ri-user-star-line text-primary me-1"></i> Penasihat Pelajar <span class="text-danger">*</span>
                        </label>
                        <select name="advisor_id" id="assign_advisor_id" class="form-select select2" required style="width: 100%;">
                            <option value="">-- Sila Pilih Penasihat --</option>
                            <?php foreach ($advisors as $adv): ?>
                                <option value="<?= h($adv['f_stafID']) ?>"
                                        data-dept="<?= h(trim((string)($adv['f_namajabatan'] ?? '')) ?: '-') ?>"
                                        data-pos="<?= h(trim((string)($adv['f_jawatan'] ?? '')) ?: '-') ?>">
                                    <?= h(trim($adv['f_nama'])) ?> (<?= h($adv['f_stafID']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Kad Maklumat Penasihat -->
                        <div id="card_advisor_info" class="mt-2 p-3 bg-light rounded-3 border border-light-subtle d-none">
                            <div class="d-flex align-items-start mb-2">
                                <div class="text-success me-2 fs-5"><i class="ri-building-line"></i></div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Department</small>
                                    <div class="fw-bold text-dark info-dept">-</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="text-success me-2 fs-5"><i class="ri-briefcase-line"></i></div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Position</small>
                                    <div class="fw-bold text-dark info-pos">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4 fw-medium" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnSaveAssignment">
                        Simpan Penasihat
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>