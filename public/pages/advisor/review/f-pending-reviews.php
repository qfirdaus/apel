<div class="dt-standard">
    <table class="table table-striped table-bordered align-middle w-100 text-center" id="tblAdvisorPending">
        <thead class="bg-light">
            <tr>
                <th width="5%"><?= h(__('adv_th_no')) ?></th>
                <th width="25%" class="text-start"><?= h(__('adv_th_student')) ?></th>
                <th width="20%"><?= h(__('adv_th_submitted_date')) ?></th>
                <th width="20%"><?= h(__('adv_th_status')) ?></th>
                <th width="30%"><?= h(__('adv_th_action')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pendingList)): $bil = 1; foreach ($pendingList as $row): ?>
                <tr>
                    <td><?= $bil++ ?></td>
                    <td class="text-start">
                        <strong><?= h($row['student_id']) ?></strong><br>
                        <small class="text-muted">Attempt #<?= h((string)$row['portfolio_attempt_count']) ?></small>
                    </td>
                    <td><?= !empty($row['portfolio_submitted_at']) ? date('d-M-Y h:i A', strtotime($row['portfolio_submitted_at'])) : '-' ?></td>
                    <td>
                        <span class="badge bg-warning-subtle text-secondary border border-warning-subtle px-3 py-1 rounded-pill fw-bold">
                            Semakan Penasihat
                        </span>
                    </td>
                    <td>
                        <button type="button" 
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold btn-view-pdf" 
                            data-app-id="<?= $row['id'] ?>" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalPreviewPdf"
                            title="Lihat Portfolio PDF" >
                        <img src="../../../assets/images/pdf-icon.png" alt="PDF" style="width: 18px; height: 18px; vertical-align: middle;">
                        </button>

                        <button type="button" 
                                class="btn btn-sm btn-outline-warning rounded-pill px-3 btn-action-review" 
                                data-app-id="<?= $row['id'] ?>" 
                                data-student-id="<?= h($row['student_id']) ?>"
                                title="Penilain">
                            <i class="ri-todo-line me-1"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php 
    include __DIR__ . '/modal-assessment.php'; 
?>