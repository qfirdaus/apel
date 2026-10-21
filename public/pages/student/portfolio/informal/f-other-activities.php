<?php
$informalController ??= new InformalLearningController();

$studentId = (string)($_SESSION['f_stafID'] ?? ''); 
$otherRecords = $informalController->getOtherActivities($studentId, $applicationId);

$hasOtherActFlag = $informalController->getOtherActivitiesFlag($studentId, $applicationId);
$hasOtherRecords = !empty($otherRecords);

$isEnglish   = (__('lang_code') === 'en');
$skillOptions = [];   
$skillLabels  = [];   
foreach ($informalController->getSkillLevels() as $lv) {
    $label = $isEnglish
        ? (($lv['levelDescEN'] ?? '') !== '' ? $lv['levelDescEN'] : (string)$lv['levelDesc'])
        : (($lv['levelDesc']   ?? '') !== '' ? $lv['levelDesc']   : (string)$lv['levelDescEN']);
    $skillOptions[] = ['id' => (string)$lv['levelID'], 'label' => $label];
    $skillLabels[(string)$lv['levelID']] = $label;
}
?>

<section class="card shadow-sm rounded-3 mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0"><?= h(__('informal_tab_other')) ?></h5>
    </div>
    <div class="card-body">
        <div class="form-check mb-3 p-3 bg-light border rounded">
            <input class="form-check-input ms-1" type="checkbox" id="chkNoOther" 
                   <?= (!$hasOtherRecords && $hasOtherActFlag === 0) ? 'checked' : '' ?>
                   <?= $hasOtherRecords ? 'disabled' : '' ?>>
                   
            <label class="form-check-label text-success fw-bold ms-2" for="chkNoOther">
                <?= h(__('other_checkbox_none')) ?>
            </label>
            
            <?php if ($hasOtherRecords): ?>
                <div class="text-danger small mt-2 ms-1">
                    <i class="ri-error-warning-line me-1"></i> 
                    <?= __('other_checkbox_none_info') ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="otherWrap">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" id="btnAddOther" data-bs-toggle="modal" data-bs-target="#modalAddOtherAct">
                    <i class="ri-add-line me-1"></i> <?= h(__('work_btn_add')) ?>
                </button>
            </div>

            <div class="dt-standard">
                <table class="table table-striped table-bordered align-middle w-100" id="tblOther">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center"><?= h(__('other_th_no')) ?></th>
                            <th width="27%"><?= h(__('other_th_activity')) ?></th>
                            <th width="13%"><?= h(__('other_th_skill')) ?></th>
                            <th width="33%"><?= h(__('other_th_learned')) ?></th>
                            <th width="10%" class="text-center"><?= h(__('other_th_evidence')) ?></th>
                            <th width="12%" class="text-center"><?= h(__('other_th_action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($otherRecords)): ?>
                            <?php
                            $bil = 1;
                            foreach ($otherRecords as $row):
                                $recordId = (int)($row['id'] ?? 0);
                                $evidenceFiles = $informalController->getEvidenceFiles('OTHER', $recordId);

                                $jsonFiles = '[]';
                                if (!empty($evidenceFiles)) {
                                    $fileData = [];
                                    foreach ($evidenceFiles as $file) {
                                        $fileData[] = [
                                            'id'   => $file['id'],
                                            'name' => htmlspecialchars($file['file_name']),
                                            'url'  => base_url('pages/student/portfolio/informal/' . $file['file_path'])
                                        ];
                                    }
                                    $jsonFiles = htmlspecialchars(json_encode($fileData), ENT_QUOTES, 'UTF-8');
                                }

                                $startDmy = !empty($row['start_date']) ? date('d/m/Y', strtotime($row['start_date'])) : '';
                                $endDmy   = !empty($row['end_date'])   ? date('d/m/Y', strtotime($row['end_date']))   : '';
                                $skillRaw = (string)($row['skill_level'] ?? '');
                                $skillTxt = $skillLabels[$skillRaw] ?? $skillRaw;
                            ?>
                            <tr>
                                <td class="text-center"><?= $bil++ ?></td>
                                <td><?= htmlspecialchars((string)$row['activity_name']) ?></td>
                                <td><?= htmlspecialchars($skillTxt) ?></td>
                                <td><?= htmlspecialchars((string)($row['what_learned'] ?? '')) ?></td>

                                <td class="text-center align-middle">
                                    <?php if (!empty($evidenceFiles)): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-info position-relative btn-view-files"
                                                data-files="<?= $jsonFiles ?>"
                                                title="<?= h(__('work_tip_view_files')) ?>">
                                            <i class="ri-attachment-line"></i>
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                <?= count($evidenceFiles) ?>
                                            </span>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small"><?= h(__('work_none')) ?></span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center col-action">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-edit-other"
                                            data-id="<?= $recordId ?>"
                                            data-activity="<?= htmlspecialchars((string)$row['activity_name']) ?>"
                                            data-start="<?= $startDmy ?>"
                                            data-end="<?= $endDmy ?>"
                                            data-organizer="<?= htmlspecialchars((string)($row['organizer'] ?? '')) ?>"
                                            data-skill="<?= htmlspecialchars($skillRaw) ?>"
                                            data-learned="<?= htmlspecialchars((string)($row['what_learned'] ?? '')) ?>"
                                            data-files="<?= $jsonFiles ?>"
                                            title="<?= h(__('work_tip_edit')) ?>">
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-other" data-id="<?= $recordId ?>" title="<?= h(__('work_tip_delete')) ?>">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
    include __DIR__ . '/modal-other.php';
    include __DIR__ . '/modal-update-other.php';
?>