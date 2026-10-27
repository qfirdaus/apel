<div class="dt-standard">
    <table class="table table-striped table-bordered align-middle w-100 text-center" id="tblAdminAssign">
        <thead class="bg-light">
            <tr>
                <th width="5%"><?= h(__('adv_th_no', 'BIL')) ?></th>
                <th width="20%" class="text-start"><?= h(__('adv_th_student', 'PELAJAR')) ?></th>
                <th width="15%"><?= h(__('th_status_portfolio', 'STATUS PORTFOLIO')) ?></th>
                <th width="22%" class="text-start"><?= h(__('th_advisor', 'PENASIHAT')) ?></th>
                <th width="23%" class="text-start"><?= h(__('th_coordinator', 'PENYELARAS FAKULTI')) ?></th>
                <th width="15%"><?= h(__('adv_th_action', 'TINDAKAN')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($applications)): $bil = 1; foreach ($applications as $row): 
                $advId    = trim((string)($row['advisor_id'] ?? ''));
                $advName  = trim((string)($row['advisor_name'] ?? ''));

                $coorId   = trim((string)($row['coordinator_id'] ?? ''));
                $coorName = trim((string)($row['coordinator_name'] ?? ''));

                $studentId   = trim((string)($row['student_id'] ?? ''));
                $studentName = trim((string)($row['student_name'] ?? ''));

                $hasAdvisor     = ($advId !== '');
                $hasCoordinator = ($coorId !== '');
                $isFullyAssigned = ($hasAdvisor && $hasCoordinator);
            ?>
                <tr>
                    <td><?= $bil++ ?></td>
                    <td class="text-start">
                        <?php if ($studentName !== ''): ?>
                            <strong class="text-dark"><?= h($studentName) ?></strong><br>
                            <small class="text-muted"><?= h($studentId) ?> (ID: #<?= (int)$row['application_id'] ?>)</small>
                        <?php else: ?>
                            <strong class="text-dark"><?= h($studentId) ?></strong><br>
                            <small class="text-muted">ID Permohonan: #<?= (int)$row['application_id'] ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int)$row['portfolio_status_id'] === 0): ?>
                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">Draf Penyediaan</span>
                        <?php else: ?>
                            <span class="badge bg-info-subtle text-info border px-2 py-1 rounded-pill"><?= h($row['status_student'] ?? 'Dihantar') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-start">
                        <?php if ($hasAdvisor): ?>
                            <span class="text-dark fw-medium"><?= h($advName !== '' ? $advName : $advId) ?></span><br>
                            <small class="text-muted"><?= h($advId) ?></small>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">Belum Dilantik</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-start">
                        <?php if ($hasCoordinator): ?>
                            <span class="text-dark fw-medium"><?= h($coorName !== '' ? $coorName : $coorId) ?></span><br>
                            <small class="text-muted"><?= h($coorId) ?></small>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">Belum Dilantik</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" 
                                class="btn btn-sm <?= $hasAdvisor ? 'btn-outline-primary' : 'btn-outline-warning' ?> rounded-pill px-3 btn-open-assign"
                                data-app-id="<?= (int)$row['application_id'] ?>"
                                data-student-id="<?= h($studentId) ?>"
                                data-advisor-id="<?= h($advId) ?>"
                                data-coordinator-name="<?= h($coorName !== '' ? $coorName : 'Belum Ditentukan') ?>"
                                title="<?= $hasAdvisor ? 'Kemaskini' : 'Lantik Penasihat' ?>">
                            <i class="ri-user-shared-line me-1"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>