<!-- ROLE INDICATOR -->
<div class="role-indicator-wrapper">
    <span class="role-indicator-label">
        <i class="ri-shield-user-line"></i> Mod Peranan
    </span>
    <span class="role-indicator-name">
        <span class="role-dot"></span>
        <?= h($roleInfo['name'] ?? 'Penyelaras') ?>
    </span>
</div>

<div class="dt-standard">
    <table class="table table-hover table-bordered align-middle w-100 text-center shadow-sm" id="tblCoordinatorPending">
        <thead class="table-light text-uppercase text-secondary" style="font-size: 0.78rem; letter-spacing: 0.5px;">
            <tr>
                <th width="2%"><?= h(__('coord_th_no', 'BIL')) ?></th>
                <th width="32%" class="text-start"><?= h(__('coord_th_student', 'MAKLUMAT PELAJAR & PROGRAM')) ?></th>
                <th width="10%"><?= h(__('coord_th_submitted_date', 'TARIKH HANTAR')) ?></th>
                <th width="30%" class="text-start"><?= h(__('coord_th_advisor_status', 'SEMAKAN PENASIHAT')) ?></th>
                <th width="12%"><?= h(__('coord_th_status', 'STATUS')) ?></th>
                <th width="14%"><?= h(__('coord_th_action', 'TINDAKAN')) ?></th>
            </tr>
        </thead>
        <tbody style="font-size: 0.85rem;">
            <?php if (!empty($pendingList)): $bil = 1; foreach ($pendingList as $row): 
                $studentId   = trim((string)($row['student_id'] ?? ''));
                $studentName = trim((string)($row['student_name'] ?? ''));
                $programName = trim((string)($row['program_name'] ?? 'Sarjana Muda Pengurusan Sumber Manusia (Kepujian)'));
                $facultyName = trim((string)($row['faculty_name'] ?? 'Fakulti Pengurusan & Teknologi Pertahanan'));
                $attempt     = (int)($row['portfolio_attempt_count'] ?? 1);
                $advisorName = trim((string)($row['advisor_name'] ?? $row['advisor_id'] ?? '-'));
            ?>
                <tr>
                    <td class="text-muted fw-bold"><?= $bil++ ?></td>
                    <td class="text-start">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary-subtle text-primary border d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 38px; height: 38px; min-width: 38px;">
                                <i class="ri-user-3-line fs-5"></i>
                            </div>
                            <div>
                                <strong class="text-dark fs-6 d-block"><?= h($studentName !== '' ? $studentName : $studentId) ?></strong>
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
                    <td>
                        <div class="fw-bold text-dark">
                            <i class="ri-calendar-line text-success me-1"></i>
                            <?= !empty($row['portfolio_submitted_at']) ? date('d-M-Y', strtotime($row['portfolio_submitted_at'])) : '-' ?>
                        </div>
                    </td>
                    <td class="text-start">
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                <i class="ri-checkbox-circle-line me-1"></i> Disokong
                            </span>
                            <small class="text-info fw-bold ms-2"><i class="ri-user-follow-line me-1"></i><?= h($advisorName) ?></small>
                        </div>
                        <?php if (!empty($row['advisor_remarks'])): ?>
                            <div class="p-2 bg-light rounded border text-muted small mt-1" style="line-height: 1.35; font-size: 0.76rem;">
                                "<?= nl2br(h($row['advisor_remarks'])) ?>"
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-warning-subtle text-danger border border-warning-subtle px-3 py-1 rounded-pill fw-bold">
                            Menunggu Perakuan
                        </span>
                    </td>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <!-- Butang Lihat PDF Portfolio -->
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger btn-view-pdf" 
                                    data-app-id="<?= (int)$row['id'] ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalPreviewPdf"
                                    title="Lihat Portfolio" >
                                <img src="../../../assets/images/pdf-icon.png" alt="PDF" style="width: 16px; height: 16px;">
                            </button>     
                                         
                            <!-- Butang Buka Modal Keputusan -->
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary btn-action-coord-review d-inline-flex align-items-center shadow-sm" 
                                    data-app-id="<?= (int)$row['id'] ?>" 
                                    data-student-id="<?= h($row['student_id']) ?>"
                                    title="Perakuan Penyelaras">
                                <i class="ri-file-edit-line"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>