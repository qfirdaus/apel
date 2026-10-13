<style>
    /* Jadual Work Experience: muat dalam container, tiada scroll */
    #tblWork { width: 100% !important; table-layout: fixed; }
    #tblWork th, #tblWork td { overflow-wrap: anywhere; }
    #tblWork td.col-action { white-space: nowrap; }

    /* Ruang di bawah jadual (Showing... & Previous/Next) */
    #tblWork_wrapper .dataTables_info,
    #tblWork_wrapper .dt-info,
    #tblWork_wrapper .dataTables_paginate,
    #tblWork_wrapper .dt-paging { padding: .5rem 1.25rem 1.25rem !important; }
</style>

<div class="border border-light-subtle rounded-3 p-3 mt-3 bg-white shadow-sm">
    
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalWorkExp">
            <i class="ri-add-line me-1"></i> <?= h(__('work_btn_add')) ?>
        </button>
    </div>

    <div class="work-table-wrap">
        <table class="table table-bordered table-hover align-middle mb-0" id="tblWork">
            <thead class="table-light">
                <tr>
                    <th width="5%" class="text-center"><?= h(__('work_th_no')) ?></th>
                    <th width="18%"><?= h(__('work_th_employer')) ?></th>
                    <th width="15%"><?= h(__('work_th_duration')) ?></th>
                    <th width="17%"><?= h(__('work_th_position')) ?></th>
                    <th width="23%"><?= h(__('work_th_learned')) ?></th>
                    <th width="10%" class="text-center"><?= h(__('work_th_evidence')) ?></th>
                    <th width="12%" class="text-center"><?= h(__('work_th_action')) ?></th>
                </tr>
            </thead>
            
            <tbody>
                <?php
                $informalController = new InformalLearningController();
                $studentId = $_SESSION['student_id'] ?? '';
                $workRecords = $informalController->getWorkExperience($studentId);
                
                if (!empty($workRecords)): 
                    $bil = 1;
                    foreach ($workRecords as $row): 
                        $recordId = (int)($row['id'] ?? 0); 
                        $evidenceFiles = $informalController->getEvidenceFiles('WORK', $recordId);
                ?>
                    <tr>
                        <td class="text-center"><?= $bil++ ?></td>
                        <td><?= htmlspecialchars($row['employer_name']) ?></td>

                        <td>
                            <?= date('d/m/Y', strtotime($row['start_date'])) ?> - 
                            <?= date('d/m/Y', strtotime($row['end_date'])) ?>
                        </td>

                        <td><?= htmlspecialchars($row['position_held']) ?></td>
                        <td><?= htmlspecialchars($row['what_learned']) ?></td>

                        <td class="text-center align-middle">
                            <?php if (!empty($evidenceFiles)): ?>
                                <?php 
                                    $fileData = [];
                                    foreach ($evidenceFiles as $file) {
                                        $fileData[] = [
                                            'id'   => $file['id'],
                                            'name' => htmlspecialchars($file['file_name']),
                                            'url'  => base_url('pages/portfolio/informal/' . $file['file_path'])
                                        ];
                                    }
                                    $jsonFiles = htmlspecialchars(json_encode($fileData), ENT_QUOTES, 'UTF-8');
                                ?>
                                
                                <button type="button" 
                                        class="btn btn-sm btn-info text-white position-relative btn-view-files" 
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
                            <button type="button" class="btn btn-sm btn-warning text-white btn-edit-work" 
                                data-id="<?= $recordId ?>"
                                data-employer="<?= htmlspecialchars($row['employer_name']) ?>"
                                data-address="<?= htmlspecialchars($row['contact_address'] ?? '') ?>" 
                                data-start="<?= date('d/m/Y', strtotime($row['start_date'])) ?>"
                                data-end="<?= date('d/m/Y', strtotime($row['end_date'])) ?>"
                                data-position="<?= htmlspecialchars($row['position_held']) ?>"
                                data-learned="<?= htmlspecialchars($row['what_learned']) ?>"
                                data-files="<?= $jsonFiles ?>" 
                                title="<?= h(__('work_tip_edit')) ?>">
                                <i class="ri-pencil-line"></i>
                            </button>
                            
                            <button type="button" class="btn btn-sm btn-danger btn-delete-work" data-id="<?= $recordId ?>" title="<?= h(__('work_tip_delete')) ?>">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                            </div>
                        </td>
                    </tr>
                <?php 
                    endforeach; 
                endif; 
                ?>
            </tbody>
        </table>
    </div>

</div>

<?php 
    include __DIR__ . '/modal-work.php'; 
    include __DIR__ . '/modal-view-files.php'; 
    include __DIR__ . '/modal-update-work.php';
?>