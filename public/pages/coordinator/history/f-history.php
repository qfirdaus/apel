<div class="dt-standard">
    <table class="table table-striped table-bordered align-middle w-100 text-center" id="tblCoordinatorHistory">
        <thead class="bg-light">
            <tr>
                <th width="5%"><?= h(__('coord_th_no', 'BIL')) ?></th>
                <th width="25%" class="text-start"><?= h(__('coord_th_student', 'PELAJAR')) ?></th>
                <th width="15%"><?= h(__('coord_th_submitted_date', 'TARIKH DIHANTAR')) ?></th>
                <th width="15%"><?= h(__('hist_th_verified_date', 'TARIKH PERAKUAN')) ?></th>
                <th width="15%"><?= h(__('hist_th_status', 'KEPUTUSAN')) ?></th>
                <th width="25%"><?= h(__('hist_th_remarks', 'ULASAN')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($historyList)): $bil = 1; foreach ($historyList as $row): ?>
                <tr>
                    <td><?= $bil++ ?></td>
                    <td class="text-start">
                        <strong><?= h($row['student_name'] ?? $row['student_id']) ?></strong><br>
                        <small class="text-muted"><?= h($row['student_id']) ?> | Percubaan #<?= h((string)($row['portfolio_attempt_count'] ?? 1)) ?></small>
                    </td>
                    <td><?= !empty($row['portfolio_submitted_at']) ? date('d-M-Y h:i A', strtotime($row['portfolio_submitted_at'])) : '-' ?></td>
                    <td><?= !empty($row['created_at']) ? date('d-M-Y h:i A', strtotime($row['created_at'])) : '-' ?></td>
                    <td>
                        <?php if ($row['decision'] === 'recommend'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold">
                                <i class="ri-check-line me-1"></i> Diperaku
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-bold">
                                <i class="ri-arrow-go-back-line me-1"></i> Pindaan (Rework)
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-start">
                        <small class="text-muted"><?= !empty($row['remarks']) ? nl2br(h($row['remarks'])) : '-' ?></small>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>