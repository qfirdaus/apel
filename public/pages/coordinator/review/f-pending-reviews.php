<div class="dt-standard">
    <table class="table table-striped table-bordered align-middle w-100 text-center" id="tblCoordinatorPending">
        <thead class="bg-light">
            <tr>
                <th width="5%"><?= h(__('coord_th_no', 'BIL')) ?></th>
                <th width="25%" class="text-start"><?= h(__('coord_th_student', 'PELAJAR')) ?></th>
                <th width="15%"><?= h(__('coord_th_submitted_date', 'TARIKH DIHANTAR')) ?></th>
                <th width="20%"><?= h(__('coord_th_advisor_status', 'STATUS PENASIHAT')) ?></th>
                <th width="15%"><?= h(__('coord_th_status', 'STATUS')) ?></th>
                <th width="20%"><?= h(__('coord_th_action', 'TINDAKAN')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pendingList)): $bil = 1; foreach ($pendingList as $row): ?>
                <tr>
                    <td><?= $bil++ ?></td>
                    <td class="text-start">
                        <strong><?= h($row['student_name'] ?? $row['student_id']) ?></strong><br>
                        <small class="text-muted"><?= h($row['student_id']) ?> | Percubaan #<?= h((string)($row['portfolio_attempt_count'] ?? 1)) ?></small>
                    </td>
                    <td><?= !empty($row['portfolio_submitted_at']) ? date('d-M-Y h:i A', strtotime($row['portfolio_submitted_at'])) : '-' ?></td>
                    <td>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill">
                            <i class="ri-checkbox-circle-line me-1"></i> Disokong Penasihat
                        </span>
                        <?php if (!empty($row['advisor_remarks'])): ?>
                            <br><small class="text-muted italic">"<?= h($row['advisor_remarks']) ?>"</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-warning-subtle text-secondary border border-warning-subtle px-3 py-1 rounded-pill fw-bold">
                            Menunggu Perakuan
                        </span>
                    </td>
                    <td>
                        <button type="button" 
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold btn-view-pdf" 
                            data-app-id="<?= $row['id'] ?>" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalPreviewPdf"
                            title="Lihat Portfolio PDF">
                            <img src="../../../assets/images/pdf-icon.png" alt="PDF" style="width: 18px; height: 18px; vertical-align: middle;">
                        </button>

                        <button type="button" 
                                class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-action-coord-review" 
                                data-app-id="<?= $row['id'] ?>" 
                                data-student-id="<?= h($row['student_id']) ?>"
                                title="Perakuan Penyelaras">
                            <i class="ri-shield-check-line me-1"></i> Perakuan
                        </button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>