<?php
    $appId = (int)($activeApp['id'] ?? 0);
    $checklist = $appController->getPortfolioChecklist($appId);
    $assignment = $appController->getApplicationAssignees($appId);

    $isOfficerAssigned = (!empty($assignment['advisor_id']) && !empty($assignment['coordinator_id']));

    $studentName = $_SESSION['f_nama'] ?? $_SESSION['nama'] ?? ''; 
    $currentDate = date('d-M-Y');

    function getStatusIcon($isComplete) {
        if ($isComplete) {
            return '<span class="status-indicator complete shadow-sm"></span>';
        }
        return '<span class="status-indicator incomplete shadow-sm"></span>';
    }

    function getRowAction($isComplete, $url) {
        if (!$isComplete) {
            return ' onclick="window.location.href=\'' . $url . '\'" style="cursor: pointer;" title="' . h(__('submit_tip_complete')) . '"';
        }
        return ''; 
    }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="text-dark fw-bold mb-1"><?= h(__('submit_title')) ?></h4>
    </div>
</div>

<!-- KOTAK AMARAN APEL: STATUS LANTIKAN PENASIHAT & PENYELARAS -->
<?php if (!$isOfficerAssigned): ?>
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 p-3 rounded-3" role="alert">
        <div class="fs-2 text-warning me-3">
            <i class="ri-error-warning-fill"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-1 text-dark">Pelantikan Pegawai Sedang Diproses</h6>
            <p class="mb-0 text-secondary small">
                Permohonan anda masih belum mempunyai <strong>Penasihat</strong> atau <strong>Penyelaras Fakulti</strong> yang dilantik. 
                Sila rujuk <strong>Pentadbir APEL</strong>. Butang penghantaran hanya akan diaktifkan setelah lantikan selesai.
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4 p-3 rounded-3" role="alert">
        <div class="fs-2 text-success me-3">
            <i class="ri-checkbox-circle-fill"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-1 text-dark">Pegawai Penilai Telah Dilantik</h6>
            <p class="mb-0 text-secondary small">
                Penasihat: <strong><?= h($assignment['advisor_name'] ?? $assignment['advisor_id']) ?></strong> | 
                Penyelaras Fakulti: <strong><?= h($assignment['coordinator_name'] ?? $assignment['coordinator_id']) ?></strong>
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- Portfolio Checklist Card -->
<div class="card border border-dark-subtle rounded-3 mb-4 shadow-none">
    <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title text-dark fw-bold mb-0"><?= h(__('submit_checklist_title')) ?></h5>
        
        <div class="d-flex align-items-center gap-3">
            <span class="d-flex align-items-center gap-2 text-secondary fw-medium" style="font-size: 0.85rem;">
                <span class="status-indicator complete shadow-none" style="width:14px;height:14px;"></span> <?= h(__('submit_complete')) ?>
            </span>
            <span class="d-flex align-items-center gap-2 text-secondary fw-medium" style="font-size: 0.85rem;">
                <span class="status-indicator incomplete shadow-none" style="width:14px;height:14px;"></span> <?= h(__('submit_not_complete')) ?>
            </span>
        </div>
    </div>
    
    <div class="card-body p-4 bg-light bg-opacity-50">
        <div class="table-wrapper shadow-sm">
            <table class="table table-premium text-center w-100">
                <thead>
                    <tr>
                        <th width="10%"><?= h(__('submit_th_no')) ?></th>
                        <th width="70%" class="text-start"><?= h(__('submit_th_section')) ?></th>
                        <th width="20%"><?= h(__('submit_th_status')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr<?= getRowAction($checklist['work'], '../informal/index.php') ?>>
                        <td class="fw-medium">1</td>
                        <td class="text-start fw-medium"><?= h(__('work_title')) ?></td>
                        <td><?= getStatusIcon($checklist['work']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['other'], '../informal/index.php') ?>>
                        <td class="fw-medium">2</td>
                        <td class="text-start fw-medium"><?= h(__('informal_tab_other')) ?></td>
                        <td><?= getStatusIcon($checklist['other']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['training'], '../non-formal/index.php') ?>>
                        <td class="fw-medium">3</td>
                        <td class="text-start fw-medium"><?= h(__('nf_section_title')) ?></td>
                        <td><?= getStatusIcon($checklist['training']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['language'], '../language/index.php') ?>>
                        <td class="fw-medium">4</td>
                        <td class="text-start fw-medium"><?= h(__('lang_section_title')) ?></td>
                        <td><?= getStatusIcon($checklist['language']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['reflection'], '../reflection/index.php') ?>>
                        <td class="fw-medium">5</td>
                        <td class="text-start fw-medium"><?= h(__('reflection_title')) ?></td>
                        <td><?= getStatusIcon($checklist['reflection']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['referees'], '../referees/index.php') ?>>
                        <td class="fw-medium">6</td>
                        <td class="text-start fw-medium"><?= h(__('referees_title')) ?></td>
                        <td><?= getStatusIcon($checklist['referees']) ?></td>
                    </tr>
                    <tr<?= getRowAction($checklist['documents'], '../upload-documents/index.php') ?>>
                        <td class="fw-medium">7</td>
                        <td class="text-start fw-medium"><?= h(__('upload_doc_title')) ?></td>
                        <td><?= getStatusIcon($checklist['documents']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Self-Declaration Card -->
<div class="card border border-dark-subtle rounded-3 mb-4 shadow-none">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h5 class="card-title text-dark fw-bold mb-0"><?= h(__('submit_declaration_title')) ?></h5>
    </div>
    <div class="card-body p-4">
        <div class="declaration-box p-4 rounded-3 border border-light">
            <p class="text-dark mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                <?= h(__('submit_declaration_text')) ?>
            </p>
            
            <div class="d-flex flex-column flex-md-row gap-5 mt-4 pt-3 border-top border-light-subtle">
                <div>
                    <span class="text-muted d-block small text-uppercase fw-bold mb-1 tracking-wider"><?= h(__('submit_label_name')) ?></span>
                    <strong class="text-dark text-uppercase fs-6"><?= htmlspecialchars($studentName) ?></strong>
                </div>
                <div>
                    <span class="text-muted d-block small text-uppercase fw-bold mb-1 tracking-wider"><?= h(__('submit_label_date')) ?></span>
                    <strong class="text-dark fs-6"><?= $currentDate ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Form -->
<form id="formSubmitPortfolio">
    <input type="hidden" name="action" value="submit_portfolio">
    <div class="d-flex justify-content-end pt-3 mt-2">
        <button type="submit" 
                id="btnSubmitPortfolio" 
                class="btn btn-primary btn-lg px-5 shadow fw-bold rounded-pill" 
                <?= ($checklist['all_complete'] && $isOfficerAssigned) ? '' : 'disabled' ?>>
            <i class="ri-send-plane-fill me-2"></i> <?= h(__('submit_btn_submit')) ?>
        </button>
    </div>
</form>