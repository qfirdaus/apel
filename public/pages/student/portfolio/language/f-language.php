<?php
    $languageController ??= new LanguageController();
    $studentId = (string)($_SESSION['f_stafID'] ?? '');
    $langRecords = $languageController->getLanguageRecords($studentId, $applicationId);

    $listLang = $languageController->getLanguageList();
    $listSkill = $languageController->getSkillLevels();

    $isEnglish = (__('lang_code') === 'en');

    $langLabels = []; 
    $skillLabels = [];

    foreach ($listLang as $l) {
        $langLabels[(string)$l['id']] = $l['languageDesc'];
    }
    foreach ($listSkill as $s) {
        $skillLabels[(string)$s['id']] = ($isEnglish && !empty($s['levelDesc_EN'])) ? $s['levelDesc_EN'] : $s['levelDesc'];
    }
?>

<section class="card shadow-sm rounded-3 mb-3">
    <div class="card-body">
        
        <div id="languageWrap">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" id="btnAddLanguage" data-bs-toggle="modal" data-bs-target="#modalAddLanguage">
                    <i class="ri-add-line me-1"></i> <?= h(__('work_btn_add')) ?>
                </button>
            </div>

            <div class="dt-standard">
                <table class="table table-striped table-bordered align-middle w-100 text-center" id="tblLanguage">
                    <thead class="bg-light">
                        <tr>
                            <th class="align-middle text-center" width="5%"><?= h(__('other_th_no')) ?></th>
                            <th class="align-middle text-start" width="25%"><?= h(__('lang_label_language')) ?></th>
                            <th class="align-middle text-center" width="20%"><?= h(__('lang_label_reading')) ?></th>
                            <th class="align-middle text-center" width="20%"><?= h(__('lang_label_speaking')) ?></th>
                            <th class="align-middle text-center" width="20%"><?= h(__('lang_label_writing')) ?></th>
                            <th class="align-middle text-center" width="10%"><?= h(__('other_th_action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($langRecords)): ?>
                            <?php
                            $bil = 1;
                            foreach ($langRecords as $row):
                                $recordId = (int)($row['id'] ?? 0);
                                
                                $langIdRaw = (string)($row['language_id'] ?? '');
                                $langTxt = $langLabels[$langIdRaw] ?? $langIdRaw;

                                $readIdRaw = (string)($row['reading_level'] ?? '');
                                $readTxt = $skillLabels[$readIdRaw] ?? $readIdRaw;

                                $speakIdRaw = (string)($row['speaking_level'] ?? '');
                                $speakTxt = $skillLabels[$speakIdRaw] ?? $speakIdRaw;

                                $writeIdRaw = (string)($row['writing_level'] ?? '');
                                $writeTxt = $skillLabels[$writeIdRaw] ?? $writeIdRaw;
                            ?>
                            <tr>
                                <td class="text-center"><?= $bil++ ?></td>
                                <td class="text-start"><?= htmlspecialchars($langTxt) ?></td>
                                <td><?= htmlspecialchars($readTxt) ?></td>
                                <td><?= htmlspecialchars($speakTxt) ?></td>
                                <td><?= htmlspecialchars($writeTxt) ?></td>

                                <td class="text-center col-action">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-edit-language"
                                            data-id="<?= $recordId ?>"
                                            data-lang="<?= htmlspecialchars($langIdRaw) ?>"
                                            data-read="<?= htmlspecialchars($readIdRaw) ?>"
                                            data-speak="<?= htmlspecialchars($speakIdRaw) ?>"
                                            data-write="<?= htmlspecialchars($writeIdRaw) ?>"
                                            title="<?= h(__('work_tip_edit')) ?>">
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-language" data-id="<?= $recordId ?>" title="<?= h(__('work_tip_delete')) ?>">
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
    include __DIR__ . '/modal-language.php';
?>