<?php
    $appController ??= new ApplicationController();
    $studentId = (string)($_SESSION['f_stafID'] ?? '');
    $activeApp = $appController->getStudentApplication($studentId);
    $appId = (int)($activeApp['id'] ?? 0);
    $statusId = (int)($activeApp['portfolio_status_id'] ?? 0);

    $documents = $appId > 0 ? $appController->getAdditionalDocuments($appId) : [];
    $isEditable = ($statusId === 2);
?>

<div id="additionalDocWrap">
    <?php if ($isEditable): ?>
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-primary" id="btnAddDoc" data-bs-toggle="modal" data-bs-target="#modalAddDoc">
                <i class="ri-add-line me-1"></i> <?= h(__('work_btn_add', 'Add Record')) ?>
            </button>
        </div>
    <?php endif; ?>

    <div class="dt-standard">
        <table class="table table-striped table-bordered align-middle w-100 text-center" id="tblAdditionalDoc">
            <thead class="bg-light">
                <tr>
                    <th class="align-middle text-center" width="5%"><?= h(__('other_th_no', 'NO')) ?></th>
                    <th class="align-middle text-start" width="30%">TAJUK DOKUMEN</th>
                    <th class="align-middle text-start" width="35%">CATATAN / PENJELASAN</th>
                    <th class="align-middle text-center" width="20%">TARIKH MUAT NAIK</th>
                    <th class="align-middle text-center" width="10%"><?= h(__('other_th_action', 'ACTION')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($documents)): ?>
                    <?php $bil = 1; foreach ($documents as $doc): ?>
                        <tr>
                            <td class="text-center"><?= $bil++ ?></td>
                            <td class="text-start">
                                <strong class="text-dark d-block"><?= h($doc['document_title']) ?></strong>
                                <small class="text-muted"><?= h($doc['original_file_name']) ?></small>
                            </td>
                            <td class="text-start"><?= !empty($doc['document_remarks']) ? nl2br(h($doc['document_remarks'])) : '-' ?></td>
                            <td class="text-center"><?= date('d-M-Y', strtotime($doc['uploaded_at'])) ?></td>
                            <td class="text-center col-action">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="uploads/<?= h($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat Dokumen">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($isEditable): ?>
        <div class="pt-4 mt-3 border-top d-flex justify-content-end">
            <button type="button" id="btnResubmitRework" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                <i class="ri-send-plane-fill me-1"></i> Hantar Semula Portfolio untuk Semakan
            </button>
        </div>
    <?php endif; ?>
</div>

<?php
    if ($isEditable) {
        include __DIR__ . '/modal-additional-document.php';
    }
?>