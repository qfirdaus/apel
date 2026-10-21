<?php
    $controller = new RefereesController();
    $studentId = (string)($_SESSION['f_stafID'] ?? '');

    $records = $controller->getRefereesRecord($studentId, $applicationId);
?>
<form id="formReferees" method="post">
    <input type="hidden" name="action" value="save_referees">
    <input type="hidden" name="application_id" value="<?= $applicationId ?>">
    
    <div class="row g-4 mb-4">
        <!-- Referee 1 -->
        <div class="col-xl-6 col-lg-6">
            <div class="referee-card h-100">
                <div class="referee-header">
                    <div class="referee-icon-box">
                        <i class="ri-user-star-line"></i>
                    </div>
                    <h5 class="mb-0 text-dark fw-bold"><?= h(__('referees_info')) ?> 1</h5>
                </div>
                <div class="card-body p-4">
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-user-line"></i> <?= h(__('referees_name')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[1][name]" class="form-control form-control-custom uppercase" value="<?= h($records[1]['name'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-briefcase-line"></i> <?= h(__('referees_position')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[1][position_held]" class="form-control form-control-custom uppercase" value="<?= h($records[1]['position_held'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-building-4-line"></i> <?= h(__('referees_organization')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[1][organization]" class="form-control form-control-custom uppercase" value="<?= h($records[1]['organization'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-phone-line"></i> <?= h(__('referees_office_phone')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[1][office_phone]" class="form-control form-control-custom" value="<?= h($records[1]['office_phone'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-smartphone-line"></i> <?= h(__('referees_mobile_phone')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[1][mobile_phone]" class="form-control form-control-custom" value="<?= h($records[1]['mobile_phone'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-mail-line"></i> <?= h(__('referees_email')) ?></label>
                        <div class="col-sm-8">
                            <input type="email" name="referee[1][email]" class="form-control form-control-custom" value="<?= h($records[1]['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-2 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-group-line"></i> <?= h(__('referees_relationship')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[1][relationship]" class="form-control form-control-custom uppercase" value="<?= h($records[1]['relationship'] ?? '') ?>" required>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Referee 2 -->
        <div class="col-xl-6 col-lg-6">
            <div class="referee-card h-100">
                <div class="referee-header">
                    <div class="referee-icon-box">
                        <i class="ri-user-add-line"></i>
                    </div>
                    <h5 class="mb-0 text-dark fw-bold"><?= h(__('referees_info')) ?> 2</h5>
                </div>
                <div class="card-body p-4">
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-user-line"></i> <?= h(__('referees_name')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[2][name]" class="form-control form-control-custom uppercase" value="<?= h($records[2]['name'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-briefcase-line"></i> <?= h(__('referees_position')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[2][position_held]" class="form-control form-control-custom uppercase" value="<?= h($records[2]['position_held'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-building-4-line"></i> <?= h(__('referees_organization')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[2][organization]" class="form-control form-control-custom uppercase" value="<?= h($records[2]['organization'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-phone-line"></i> <?= h(__('referees_office_phone')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[2][office_phone]" class="form-control form-control-custom" value="<?= h($records[2]['office_phone'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-smartphone-line"></i> <?= h(__('referees_mobile_phone')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[2][mobile_phone]" class="form-control form-control-custom" value="<?= h($records[2]['mobile_phone'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-mail-line"></i> <?= h(__('referees_email')) ?></label>
                        <div class="col-sm-8">
                            <input type="email" name="referee[2][email]" class="form-control form-control-custom" value="<?= h($records[2]['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-2 align-items-center">
                        <label class="col-sm-4 col-form-label"><i class="ri-group-line"></i> <?= h(__('referees_relationship')) ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="referee[2][relationship]" class="form-control form-control-custom uppercase" value="<?= h($records[2]['relationship'] ?? '') ?>" required>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4 border-top pt-3">
        <button type="submit" class="btn btn-primary px-5 py-2">
            <i class="ri-save-3-line me-1"></i> <?= h(__('referees_btn_save')) ?>
        </button>
    </div>
</form>