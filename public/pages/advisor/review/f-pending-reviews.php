<div class="dt-standard">
    <table class="table table-hover table-bordered align-middle w-100 text-center shadow-sm" id="tblAdvisorPending">
        <thead class="table-light text-uppercase text-secondary" style="font-size: 0.78rem; letter-spacing: 0.5px;">
            <tr>
                <th width="5%"><?= h(__('adv_th_no', 'BIL')) ?></th>
                <th width="35%" class="text-start"><?= h(__('adv_th_student', 'MAKLUMAT PELAJAR & PROGRAM')) ?></th>
                <th width="20%"><?= h(__('adv_th_submitted_date', 'TARIKH HANTAR')) ?></th>
                <th width="20%"><?= h(__('adv_th_status', 'STATUS')) ?></th>
                <th width="20%"><?= h(__('adv_th_action', 'TINDAKAN')) ?></th>
            </tr>
        </thead>
        <tbody style="font-size: 0.85rem;">
            <?php if (!empty($pendingList)): $bil = 1; foreach ($pendingList as $row): 
                $studentId   = trim((string)($row['student_id'] ?? ''));
                $studentName = trim((string)($row['student_name'] ?? ''));
                $programName = trim((string)($row['program_name'] ?? 'Sarjana Muda Pengurusan Sumber Manusia (Kepujian)'));
                $facultyName = trim((string)($row['faculty_name'] ?? 'Fakulti Pengurusan & Teknologi Pertahanan'));
                $attempt     = (int)($row['portfolio_attempt_count'] ?? 1);
            ?>
                <tr>
                    <td class="text-muted fw-bold"><?= $bil++ ?></td>
                    
                    <!-- MAKLUMAT PELAJAR, PROGRAM & FAKULTI -->
                    <td class="text-start">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary-subtle text-primary border d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 40px; height: 40px; min-width: 40px;">
                                <i class="ri-user-3-line fs-5"></i>
                            </div>
                            <div>
                                <strong class="text-dark fs-6 d-block">
                                    <?= h($studentName !== '' ? $studentName : $studentId) ?>
                                </strong>
                                <div class="text-muted small">
                                    <span class="me-2"><i class="ri-barcode-line me-1"></i><?= h($studentId) ?></span>
                                    <span class="badge bg-light text-secondary border">Cubaan #<?= $attempt ?></span>
                                </div>
                                <div class="mt-1" style="font-size: 0.75rem;">
                                    <span class="text-primary fw-semibold"><i class="ri-book-open-line me-1"></i><?= h($programName) ?></span><br>
                                    <span class="text-muted"><i class="ri-building-line me-1"></i><?= h($facultyName) ?></span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- TARIKH PENGHANTARAN -->
                    <td>
                        <div class="fw-bold text-dark">
                            <i class="ri-calendar-event-line text-muted me-1"></i>
                            <?= !empty($row['portfolio_submitted_at']) ? date('d-M-Y', strtotime($row['portfolio_submitted_at'])) : '-' ?>
                        </div>
                        <small class="text-muted">
                            <?= !empty($row['portfolio_submitted_at']) ? date('h:i A', strtotime($row['portfolio_submitted_at'])) : '' ?>
                        </small>
                    </td>

                    <!-- STATUS -->
                    <td>
                        <span class="badge bg-warning-subtle text-danger border border-warning-subtle px-3 py-1 rounded-pill fw-bold">
                            <i class="ri-time-line me-1"></i> Semakan Penasihat
                        </span>
                    </td>

                    <!-- TINDAKAN -->
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <!-- Butang Lihat PDF Portfolio -->
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 fw-bold btn-view-pdf" 
                                    data-app-id="<?= (int)$row['id'] ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalPreviewPdf"
                                    title="Lihat Portfolio" >
                                <img src="../../../assets/images/pdf-icon.png" alt="PDF" style="width: 16px; height: 16px;">
                            </button>                            

                            <!-- Butang Buka Modal Keputusan -->
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold btn-action-review d-inline-flex align-items-center shadow-sm" 
                                    data-app-id="<?= (int)$row['id'] ?>" 
                                    data-student-id="<?= h($row['student_id']) ?>"
                                    title="Semak Portfolio">
                                <i class="ri-file-edit-line"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="ri-inbox-line fs-2 d-block mb-1 text-secondary"></i>
                        Tiada permohonan yang menunggu semakan buat masa ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
    include __DIR__ . '/modal-assessment.php'; 
?>