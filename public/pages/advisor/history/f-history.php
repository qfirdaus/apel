<!-- ROLE INDICATOR -->
<div class="role-indicator-wrapper">
    <span class="role-indicator-label">
        <i class="ri-shield-user-line"></i> Mod Peranan
    </span>
    <span class="role-indicator-name">
        <span class="role-dot"></span>
        <?= h($roleInfo['name'] ?? 'Pentadbir') ?>
    </span>
</div>

<div class="dt-standard">
    <table class="table table-hover table-bordered align-middle w-100 text-center shadow-sm" id="tblAdvisorHistory">
        <!-- Bahagian Thead -->
        <thead class="table-light text-uppercase text-secondary" style="font-size: 0.78rem; letter-spacing: 0.5px;">
            <tr>
                <th width="5%"><?= h(__('adv_th_no', 'BIL')) ?></th>
                <th width="32%" class="text-start"><?= h(__('adv_th_student', 'MAKLUMAT PELAJAR & PROGRAM')) ?></th>
                <th width="18%"><?= h(__('hist_th_verified_date', 'TARIKH TINDAKAN')) ?></th>
                <th width="12%"><?= h(__('hist_th_status', 'KEPUTUSAN')) ?></th>
                <th width="25%" class="text-start"><?= h(__('hist_th_remarks', 'ULASAN / CATATAN')) ?></th>
                <th width="8%"><?= h(__('adv_th_action', 'TINDAKAN')) ?></th>
            </tr>
        </thead>

        <!-- Bahagian Tbody -->
        <tbody>
            <?php if (!empty($historyList)): $bil = 1; foreach ($historyList as $row): 
                $studentId   = trim((string)($row['student_id'] ?? ''));
                $studentName = trim((string)($row['student_name'] ?? ''));
                $programName = trim((string)($row['program_name'] ?? ''));
                $facultyName = trim((string)($row['faculty_name'] ?? ''));
                $attempt     = (int)($row['portfolio_attempt_count'] ?? 1);
                $appId       = (int)($row['application_id'] ?? $row['app_id'] ?? 0);
            ?>
                <tr>
                    <td class="text-muted fw-bold"><?= $bil++ ?></td>
                    
                    <!-- MAKLUMAT PELAJAR -->
                    <td class="text-start">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary-subtle text-primary border d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 38px; height: 38px; min-width: 38px;">
                                <i class="ri-user-3-line fs-5"></i>
                            </div>
                            <div>
                                <strong class="text-dark fs-6 d-block">
                                    <?= h($studentName !== '' ? $studentName : $studentId) ?>
                                </strong>
                                <div class="text-muted small">
                                    <span class="me-2"><i class="ri-barcode-line me-1"></i><?= h($studentId) ?></span>
                                    <span class="badge bg-light text-secondary border">
                                        <?= ($attempt == 1) ? 'Penghantaran Pertama' : 'Penghantaran Kali Ke-' . ($attempt - 1) ?>
                                    </span>
                                </div>
                                <div class="mt-1" style="font-size: 0.74rem;">
                                    <span class="text-primary fw-semibold"><i class="ri-book-open-line me-1"></i><?= h($programName) ?></span><br>
                                    <span class="text-muted"><i class="ri-building-line me-1"></i><?= h($facultyName) ?></span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- HANYA SATU LAJUR TARIKH TINDAKAN PENASIHAT -->
                    <td>
                        <div class="fw-bold text-dark">
                            <i class="ri-calendar-check-line text-success me-1"></i>
                            <?= !empty($row['created_at']) ? date('d-M-Y', strtotime($row['created_at'])) : '-' ?>
                        </div>
                        <!-- <small class="text-muted">
                            <?= !empty($row['created_at']) ? date('h:i A', strtotime($row['created_at'])) : '' ?>
                        </small> -->
                    </td>

                    <!-- KEPUTUSAN -->
                    <td>
                        <?php if ($row['decision'] === 'recommend'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold">
                                <i class="ri-check-line me-1"></i> Disokong
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-bold">
                                <i class="ri-arrow-go-back-line me-1"></i> Rework
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- ULASAN -->
                    <td class="text-start">
                        <div class="text-dark small" style="white-space: normal; word-break: break-word; line-height: 1.4;">
                            <?= !empty($row['remarks']) ? nl2br(h($row['remarks'])) : '<span class="text-muted fst-italic">Tiada catatan</span>' ?>
                        </div>
                    </td>

                    <!-- TINDAKAN -->
                    <td>
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger btn-view-pdf" 
                                data-app-id="<?= $appId ?>" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalPreviewPdf"
                                title="Lihat Portfolio PDF">
                            <img src="../../../assets/images/pdf-icon.png" alt="PDF" style="width: 16px; height: 16px;">
                        </button>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="ri-history-line fs-2 d-block mb-1 text-secondary"></i>
                        Tiada sejarah semakan dijumpai.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>