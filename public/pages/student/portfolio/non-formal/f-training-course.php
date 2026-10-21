<?php
$nonFormalController ??= new NonFormalController();
$studentId = (string)($_SESSION['f_stafID'] ?? '');

$nfRecords = $nonFormalController->getTrainingCourses($studentId, $applicationId);
$durationTypes = $nonFormalController->getDurationTypes(); 
$durationLabels = [];

foreach ($durationTypes as $dt) {
    $durationLabels[(string)$dt['id']] = $dt['label_name'];
}
?>

<section class="card shadow-sm rounded-3 mb-3">
    <div class="card-body">
        
        <div id="nonFormalWrap">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" id="btnAddNonFormal" data-bs-toggle="modal" data-bs-target="#modalAddNonFormal">
                    <i class="ri-add-line me-1"></i> <?= h(__('work_btn_add')) ?>
                </button>
            </div>

            <div class="dt-standard">
                <table class="table table-striped table-bordered align-middle w-100" id="tblNonFormal">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center"><?= h(__('other_th_no')) ?></th>
                            <th width="25%"><?= h(__('nf_th_title')) ?></th>
                            <th width="20%"><?= h(__('nf_th_organizer')) ?></th>
                            <th width="10%"><?= h(__('nf_th_date')) ?></th>
                            <th width="10%"><?= h(__('nf_th_duration')) ?></th>
                            <th width="20%"><?= h(__('nf_th_learned')) ?></th>
                            <th width="5%" class="text-center"><?= h(__('nf_th_evidence')) ?></th>
                            <th width="5%" class="text-center"><?= h(__('nf_th_action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($nfRecords)): ?>
                            <?php
                            $bil = 1;
                            foreach ($nfRecords as $row):
                                $recordId = (int)($row['id'] ?? 0);
                                $evidenceFiles = $nonFormalController->getEvidenceFiles('TRAINING', $recordId);

                                $jsonFiles = '[]';
                                if (!empty($evidenceFiles)) {
                                    $fileData = [];
                                    foreach ($evidenceFiles as $file) {
                                        $fileData[] = [
                                            'id'   => $file['id'],
                                            'name' => htmlspecialchars($file['file_name']),
                                            'url'  => base_url('pages/student/portfolio/non-formal/' . $file['file_path'])
                                        ];
                                    }
                                    $jsonFiles = htmlspecialchars(json_encode($fileData), ENT_QUOTES, 'UTF-8');
                                }

                                $courseDate = !empty($row['course_date']) ? date('d/m/Y', strtotime($row['course_date'])) : '';
                                
                                $durTypeRaw = (string)($row['duration_type_id'] ?? '');
                                $durTypeTxt = $durationLabels[$durTypeRaw] ?? $durTypeRaw;
                                $durationFull = htmlspecialchars((string)$row['duration'] . ' ' . $durTypeTxt);

                                // Format skills dari JSON array kepada list
                                $skillsArray = json_decode($row['skills_learned'] ?? '[]', true);
                                $skillsHtml = '';
                                if (is_array($skillsArray)) {
                                    $skillsHtml = '<ol class="ps-3 mb-0 small">';
                                    foreach ($skillsArray as $skill) {
                                        $skillsHtml .= '<li>' . htmlspecialchars($skill) . '</li>';
                                    }
                                    $skillsHtml .= '</ol>';
                                }
                            ?>
                            <tr>
                                <td class="text-center"><?= $bil++ ?></td>
                                <td><?= htmlspecialchars((string)$row['course_name']) ?></td>
                                <td><?= htmlspecialchars((string)$row['organizer']) ?></td>
                                <td><?= $courseDate ?></td>
                                <td><?= $durationFull ?></td>
                                <td><?= $skillsHtml ?></td>

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
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-edit-nonformal"
                                            data-id="<?= $recordId ?>"
                                            data-course="<?= htmlspecialchars((string)$row['course_name']) ?>"
                                            data-organizer="<?= htmlspecialchars((string)$row['organizer']) ?>"
                                            data-date="<?= $courseDate ?>"
                                            data-duration="<?= htmlspecialchars((string)$row['duration']) ?>"
                                            data-duration-type="<?= htmlspecialchars($durTypeRaw) ?>"
                                            data-knowledge="<?= htmlspecialchars((string)$row['knowledge_desc']) ?>"
                                            data-skills="<?= htmlspecialchars(json_encode($skillsArray), ENT_QUOTES, 'UTF-8') ?>"
                                            data-files="<?= $jsonFiles ?>"
                                            title="<?= h(__('work_tip_edit')) ?>">
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-nonformal" data-id="<?= $recordId ?>" title="<?= h(__('work_tip_delete')) ?>">
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
    include __DIR__ . '/modal-training-course.php';
    include __DIR__ . '/modal-update-training-course.php';
?>