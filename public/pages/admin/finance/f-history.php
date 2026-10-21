<?php
    $controller = new AdminFinanceController();
    $historyList = $controller->getHistoryList();
?>

<div class="border border-light-subtle rounded-3 p-3 bg-white shadow-sm">
    <div class="table-responsive">

        <table id="tblHistory" class="table table-bordered table-striped table-hover align-middle mb-0 w-100">
            <thead class="table-light">
                <tr>
                    <th class="text-center" width="5%"><?= h(__('fin_th_no')) ?></th>
                    <th width="15%"><?= h(__('hist_th_student_id')) ?></th>
                    <th width="15%"><?= h(__('fin_th_category')) ?></th>
                    <th class="text-center" width="10%"><?= h(__('hist_th_verified_date')) ?></th>
                    <th class="text-center" width="15%"><?= h(__('hist_th_verified_by')) ?></th>
                    <th class="text-center" width="10%"><?= h(__('hist_th_status')) ?></th>
                    <th class="text-center" width="15%"><?= h(__('hist_th_remarks')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($historyList)): ?>
                    <?php $bil = 1; foreach ($historyList as $row): ?>
                        <tr>
                            <td class="text-center"><?= $bil++ ?></td>
                            <td><strong><?= htmlspecialchars($row['student_id']) ?></strong></td>
                            <td>
                                <?php if($row['payment_category'] === 'portfolio_fee'): ?>
                                    <?= h(__('fin_category_portfolio')) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($row['payment_category']) ?>
                                <?php endif; ?>
                                <br><small class="text-muted">RM <?= number_format((float)$row['amount'], 2) ?></small>
                            </td>
                            <td class="text-center">
                                <?= !empty($row['verified_at']) ? date('d/m/Y', strtotime($row['verified_at'])) : '-' ?>
                            </td>
                            <td><?= htmlspecialchars($row['verified_by_name'] ?? '-') ?></td>
                            <td class="text-center">
                                <?php if($row['payment_status'] === 'success'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill"><i class="ri-check-line me-1"></i><?= h(__('hist_status_approved')) ?></span>
                                <?php elseif($row['payment_status'] === 'failed'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill"><i class="ri-close-line me-1"></i><?= h(__('hist_status_rejected')) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($row['payment_status'] === 'failed' && !empty($row['remarks'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger me-1 btn-remarks" data-remarks="<?= htmlspecialchars($row['remarks']) ?>" title="<?= h(__('hist_tip_view_reason')) ?>">
                                        <i class="ri-message-2-line"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <a href="../../student/portfolio/registration/uploads/<?= htmlspecialchars($row['receipt_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="<?= h(__('fin_tip_view_receipt')) ?>">
                                    <i class="ri-file-search-line"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4"><?= h(__('hist_empty')) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>