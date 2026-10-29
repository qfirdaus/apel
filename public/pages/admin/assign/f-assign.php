<!-- ROLE INDICATOR -->
<div class="role-indicator-wrapper">
    <span class="role-indicator-label">
        <i class="ri-shield-user-line"></i> <?= h(__('admin_assign_role_mode')) ?>
    </span>
    <span class="role-indicator-name">
        <span class="role-dot"></span>
        <?= h($roleInfo['name'] ?? __('admin_assign_role_default')) ?>
    </span>
</div>

<div class="dt-standard">
    <table class="table table-hover table-bordered align-middle w-100 text-center shadow-sm" id="tblAdminAssign">
        <thead class="table-light text-uppercase text-secondary" style="font-size: 0.78rem; letter-spacing: 0.5px;">
            <tr>
                <th width="5%"><?= h(__('th_no', 'BIL')) ?></th>
                <th width="25%" class="text-start"><?= h(__('th_student', 'PELAJAR')) ?></th>
                <th width="15%"><?= h(__('th_status_portfolio', 'STATUS PORTFOLIO')) ?></th>
                <th width="22%" class="text-start"><?= h(__('th_advisor', 'PENASIHAT')) ?></th>
                <th width="23%" class="text-start"><?= h(__('th_coordinator', 'PENYELARAS FAKULTI')) ?></th>
                <th width="10%"><?= h(__('th_action', 'TINDAKAN')) ?></th>
            </tr>
        </thead>
        <tbody style="font-size: 0.85rem;">
            <?php if (!empty($applications)): $bil = 1; foreach ($applications as $row): 
                $advId    = trim((string)($row['advisor_id'] ?? ''));
                $advName  = trim((string)($row['advisor_name'] ?? ''));

                $coorRawIds   = trim((string)($row['coordinator_id'] ?? ''));
                $coorRawNames = trim((string)($row['coordinator_name'] ?? ''));

                $studentId   = trim((string)($row['student_id'] ?? ''));
                $studentName = trim((string)($row['student_name'] ?? ''));
                $statusId    = (int)($row['portfolio_status_id'] ?? 0);
                $statusText  = (string)($row['status_student'] ?? '');

                $hasAdvisor     = ($advId !== '');
                $hasCoordinator = ($coorRawIds !== '');

                // Asingkan jika ada ramai penyelaras (array)
                $coorList = [];
                if ($hasCoordinator) {
                    $arrNames = array_map('trim', explode(',', $coorRawNames));
                    $arrIds   = array_map('trim', explode(',', $coorRawIds));
                    foreach ($arrNames as $idx => $name) {
                        $id = $arrIds[$idx] ?? '';
                        $coorList[] = ['name' => $name, 'id' => $id];
                    }
                }
            ?>
                <tr>
                    <td class="text-muted fw-bold"><?= $bil++ ?></td>
                    
                    <!-- MAKLUMAT PELAJAR -->
                    <td class="text-start">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2 text-primary fw-bold" style="width: 36px; height: 36px; min-width: 36px;">
                                <i class="ri-user-line"></i>
                            </div>
                            <div>
                                <strong class="text-dark d-block text-truncate" style="max-width: 260px;" title="<?= h($studentName) ?>">
                                    <?= h($studentName !== '' ? $studentName : $studentId) ?>
                                </strong>
                                <small class="text-muted">
                                    <i class="ri-barcode-line me-1"></i><?= h($studentId) ?> 
                                    <span class="badge bg-light text-secondary border ms-1">#<?= (int)$row['application_id'] ?></span>
                                </small>
                            </div>
                        </div>
                    </td>

                    <!-- STATUS PORTFOLIO -->
                    <td>
                        <?php if ($statusId === 0): ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-medium">
                                <i class="ri-draft-line me-1"></i> <?= h(__('admin_status_draft')) ?>
                            </span>
                        <?php elseif ($statusId === 1): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-medium">
                                <i class="ri-send-plane-line me-1"></i> <?= h(__('admin_status_submitted')) ?>
                            </span>
                        <?php elseif ($statusId === 2): ?>
                            <span class="badge bg-warning-subtle text-danger border border-warning-subtle px-2 py-1 rounded-pill fw-medium">
                                <i class="ri-arrow-go-back-line me-1"></i> <?= h(__('admin_status_amend')) ?>
                            </span>
                        <?php elseif ($statusId === 3): ?>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-medium">
                                <i class="ri-check-line me-1"></i> <?= h(__('admin_status_supported')) ?>
                            </span>
                        <?php elseif ($statusId === 4): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-medium">
                                <i class="ri-shield-check-line me-1"></i> <?= h(__('admin_status_endorsed')) ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill fw-medium">
                                <?= h($statusText ?: __('admin_status_review')) ?>
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- MAKLUMAT PENASIHAT -->
                    <td class="text-start">
                        <?php if ($hasAdvisor): ?>
                            <div class="d-flex align-items-center">
                                <div class="text-success me-2 fs-5"><i class="ri-user-star-fill"></i></div>
                                <div>
                                    <strong class="text-dark d-block"><?= h($advName !== '' ? $advName : $advId) ?></strong>
                                    <small class="text-muted"><i class="ri-id-card-line me-1"></i><?= h($advId) ?></small>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-bold">
                                <i class="ri-close-circle-line me-1"></i> <?= h(__('admin_not_appointed')) ?>
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- MAKLUMAT PENYELARAS FAKULTI (AUTO-MATCH) -->
                    <td class="text-start">
                        <?php if ($hasCoordinator): ?>
                            <div class="d-flex flex-column gap-1">
                                <?php foreach ($coorList as $coor): ?>
                                    <div class="p-1 px-2 rounded-2 border bg-light-subtle d-inline-block">
                                        <div class="fw-bold text-dark" style="font-size: 0.8rem;">
                                            <i class="ri-shield-user-line text-primary me-1"></i><?= h($coor['name'] ?: $coor['id']) ?>
                                        </div>
                                        <?php if (!empty($coor['id'])): ?>
                                            <small class="text-muted" style="font-size: 0.72rem;">ID: <?= h($coor['id']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-bold">
                                <i class="ri-close-circle-line me-1"></i> <?= h(__('admin_not_appointed')) ?>
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- TINDAKAN -->
                    <td>
                        <button type="button" 
                                class="btn btn-sm <?= $hasAdvisor ? 'btn-outline-primary' : 'btn-outline-warning' ?> rounded-pill px-3 shadow-sm btn-open-assign d-inline-flex align-items-center"
                                data-app-id="<?= (int)$row['application_id'] ?>"
                                data-student-id="<?= h($studentId) ?>"
                                data-advisor-id="<?= h($advId) ?>"
                                data-coordinator-name="<?= h($coorRawNames !== '' ? $coorRawNames : __('admin_coordinator_undetermined')) ?>"
                                title="<?= h($hasAdvisor ? __('admin_tip_update_advisor') : __('admin_tip_assign_advisor')) ?>">
                            <i class="<?= $hasAdvisor ? 'ri-edit-line' : 'ri-user-add-line' ?> me-1"></i> 
                        </button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>