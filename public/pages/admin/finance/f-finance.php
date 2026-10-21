<?php
    $controller = new AdminFinanceController();
    $pendingReceipts = $controller->getPendingList(); 
?>
<div class="border border-light-subtle rounded-3 p-3 mt-3 bg-white shadow-sm">
    
    <div class="table-responsive">
        <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">            

        <table id="tblPendingReceipts" class="table table-bordered table-striped table-hover align-middle mb-0 w-100">
            <thead class="table-light">
                <tr>
                    <th class="text-center" width="5%"><?= h(__('fin_th_no')) ?></th>
                    <th width="15%"><?= h(__('fin_th_upload_date')) ?></th>
                    <th width="20%"><?= h(__('fin_th_student_id')) ?></th>
                    <th width="15%"><?= h(__('fin_th_category')) ?></th>
                    <th width="15%"><?= h(__('fin_th_amount')) ?></th>
                    <th class="text-center" width="15%"><?= h(__('fin_th_receipt')) ?></th>
                    <th class="text-center" width="15%"><?= h(__('fin_th_action')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pendingReceipts)): ?>
                    <?php $bil = 1; foreach ($pendingReceipts as $row): ?>
                        <tr>
                            <td class="text-center"><?= $bil++ ?></td>
                            <td><?= date('d/m/Y h:i A', strtotime($row['created_at'])) ?></td>
                            <td><strong><?= htmlspecialchars($row['student_id']) ?></strong></td>
                            <td>
                                <?php if($row['payment_category'] === 'portfolio_fee'): ?>
                                    <span class="badge bg-info"><?= h(__('fin_category_portfolio')) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($row['payment_category']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>RM <?= number_format((float)$row['amount'], 2) ?></td>
                            <td class="text-center">
                                <a href="../../student/portfolio/registration/uploads/<?= htmlspecialchars($row['receipt_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="<?= h(__('fin_tip_view_receipt')) ?>">
                                    <i class="ri-search-eye-line me-1"></i> <?= h(__('fin_btn_view')) ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-success btn-approve" data-id="<?= $row['id'] ?>" title="<?= h(__('fin_tip_approve')) ?>">
                                    <i class="ri-check-line"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-reject" data-id="<?= $row['id'] ?>" title="<?= h(__('fin_tip_reject')) ?>">
                                    <i class="ri-close-line"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4"><?= h(__('fin_empty_pending')) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>