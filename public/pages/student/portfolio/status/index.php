<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../../includes/functions-page.php';

$PAGE_TITLE = __('status_title', 'Portfolio Status');
$pageHeading = __('status_title', 'Portfolio Status');
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = true;
$NEED_SELECT2    = false;

include __DIR__ . '/../../../../includes/header.php';
require_once __DIR__ . '/../../../../controllers/ApplicationController.php';

$studentId = (string)($_SESSION['f_stafID'] ?? '');
$appController = new ApplicationController();
$allApps = $appController->getAllStudentApplications($studentId);

// Permohonan Aktif (Terkini)
$currentApp = $allApps[0] ?? null;
$statusId = (int)($currentApp['portfolio_status_id'] ?? 0);
$statusText = $currentApp['status_student_display'] ?? 'Draf';
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?= h($csrfToken) ?>">

                    <?php if (!$currentApp): ?>
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <div class="card border-0 shadow-sm rounded-4 py-5">
                                    <div class="card-body">
                                        <div class="avatar-xl bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto mb-4" style="width: 100px; height: 100px;">
                                            <i class="ri-lock-2-fill" style="font-size: 50px;"></i>
                                        </div>
                                        <h2 class="fw-bold text-dark mb-3"><?= h(__('app_access_denied')) ?></h2>
                                        <a href="../registration/index.php" class="btn btn-primary rounded-pill px-4"><?= h(__('app_status_check_registration')) ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="row mb-3 mt-3">
                            <div class="col-12">
                                <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="page-title-main">
                                        <h4 class="page-title"><i class="ri-time-line me-1"></i> <?= h($pageHeading) ?></h4>
                                        <p class="page-title-subtitle"><?= h(__('status_subtitle', 'Jejak kemajuan semakan permohonan baru dan rayuan anda.')) ?></p>
                                    </div>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="ri-home-4-line me-1"></i> <?= __('breadcrumb_home') ?></a></li>
                                            <li class="breadcrumb-item active"><?= h($pageHeading) ?></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MENGGUNAKAN GRID ROW UNTUK PAPARAN BERSEBELAHAN -->
                        <div class="row g-4">
                            
                            <!-- KIRI: TIMELINE TRACKER (Lebar 4/12) -->
                            <div class="col-xl-4 col-lg-5">
                                <div class="card border-0 shadow-sm profile-card mb-4 border-top border-primary border-3 h-100">
                                    <div class="card-header bg-light border-bottom py-3">
                                        <h5 class="card-title text-dark fw-bold mb-2">
                                            <i class="ri-focus-3-line text-primary me-1"></i> Status Terkini (#<?= h((string)$currentApp['portfolio_attempt_count']) ?>)
                                        </h5>
                                        <div>
                                            <span class="badge <?= $statusId === 2 ? 'bg-warning-subtle text-dark' : 'bg-primary' ?> fs-6 px-3 py-2 rounded-pill fw-bold shadow-sm">
                                                <?= h($statusText) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body p-4 bg-white">
                                        <div class="timeline-stepper">
                                            <!-- Step 1: Dihantar -->
                                            <div class="timeline-step-item">
                                                <div class="timeline-step-icon <?= $statusId >= 1 ? 'completed shadow-sm' : 'active' ?>">
                                                    <?= $statusId >= 1 ? '<i class="ri-check-line"></i>' : '1' ?>
                                                </div>
                                                <div class="ps-3">
                                                    <h6 class="fw-bold mb-1 <?= $statusId >= 1 ? 'text-dark' : 'text-primary' ?> fs-6">Portfolio Dihantar</h6>
                                                    <p class="text-muted small mb-0">Pelajar melengkapkan dan menghantar portfolio.</p>
                                                    <?php if (!empty($currentApp['portfolio_submitted_at'])): ?>
                                                        <div class="mt-2">
                                                            <span class="badge bg-light text-secondary border"><i class="ri-calendar-line me-1"></i> <?= date('d-M-Y h:i A', strtotime($currentApp['portfolio_submitted_at'])) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Step 2: Semakan Penasihat -->
                                            <div class="timeline-step-item">
                                                <div class="timeline-step-icon <?= ($statusId > 1 && $statusId !== 2) ? 'completed shadow-sm' : ($statusId === 1 ? 'active' : ($statusId === 2 ? 'rework bg-warning text-white border-warning shadow-sm' : 'pending')) ?>">
                                                    <?= ($statusId > 1 && $statusId !== 2) ? '<i class="ri-check-line"></i>' : ($statusId === 2 ? '<i class="ri-error-warning-line"></i>' : '2') ?>
                                                </div>
                                                <div class="ps-3">
                                                    <h6 class="fw-bold mb-1 <?= $statusId === 1 ? 'text-primary' : ($statusId === 2 ? 'text-warning-emphasis' : ($statusId > 1 ? 'text-dark' : 'text-muted')) ?> fs-6">Semakan Penasihat</h6>
                                                    <p class="text-muted small mb-0">Penasihat menyemak kelayakan dokumen.</p>
                                                    
                                                    <?php if ($statusId === 2): ?>
                                                        <!-- Kotak catatan diberi warna latar bg-warning-subtle supaya tak tenggelam -->
                                                        <div class="alert alert-warning mt-3 mb-0 p-3 border-0 border-start border-warning border-4 rounded-3 shadow-sm bg-warning-subtle">
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-shrink-0 me-2">
                                                                    <i class="ri-feedback-fill text-warning fs-5"></i>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.8rem;">Catatan Penasihat:</h6>
                                                                    <div class="text-dark mb-0 lh-sm" style="font-size: 0.8rem;">
                                                                        <?= !empty($currentApp['advisor_remarks']) ? nl2br(h($currentApp['advisor_remarks'])) : '<span class="text-muted fst-italic">Tiada catatan diberikan.</span>' ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Step 3 & 4 (Kekal sama) -->
                                            <div class="timeline-step-item">
                                                <div class="timeline-step-icon <?= $statusId >= 4 ? 'completed shadow-sm' : ($statusId === 3 ? 'active' : 'pending') ?>">
                                                    <?= $statusId >= 4 ? '<i class="ri-check-line"></i>' : '3' ?>
                                                </div>
                                                <div class="ps-3">
                                                    <h6 class="fw-bold mb-1 <?= $statusId === 3 ? 'text-primary' : ($statusId >= 4 ? 'text-dark' : 'text-muted') ?> fs-6">Penilaian Panel</h6>
                                                    <p class="text-muted small mb-0">Penilaian pengalaman kerja dan pembelajaran.</p>
                                                </div>
                                            </div>

                                            <div class="timeline-step-item">
                                                <div class="timeline-step-icon <?= $statusId === 9 ? 'completed shadow-sm' : ($statusId === 8 ? 'rework' : 'pending') ?>">
                                                    <?= $statusId === 9 ? '<i class="ri-check-line"></i>' : ($statusId === 8 ? 'X' : '4') ?>
                                                </div>
                                                <div class="ps-3">
                                                    <h6 class="fw-bold mb-1 <?= $statusId === 9 ? 'text-success' : ($statusId === 8 ? 'text-danger' : 'text-muted') ?> fs-6">Keputusan Senat</h6>
                                                    <p class="text-muted small mb-0">Pengesahan kelulusan pengiktirafan.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KANAN: JADUAL SEJARAH (Lebar 8/12) -->
                            <div class="col-xl-8 col-lg-7">
                                <div class="card border-0 shadow-sm profile-card mb-4 h-100">
                                    <div class="card-header bg-white border-bottom py-3">
                                        <h5 class="card-title text-dark fw-bold mb-2">
                                            <i class="ri-history-line text-primary me-1"></i> Sejarah Permohonan
                                        </h5>                                        
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="dt-standard">
                                            <table class="table table-hover table-bordered align-middle w-100 text-center mb-0" id="tblPortfolioStatus">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="5%" class="text-center small">NO</th>
                                                        <th width="25%" class="text-start small">KATEGORI PERMOHONAN</th>
                                                        <th width="20%" class="text-center small">TARIKH DIHANTAR</th>
                                                        <th width="20%" class="text-center small">PORTFOLIO</th>
                                                        <th width="20%" class="text-center small">STATUS</th>
                                                        <th width="10%" class="text-center small">TINDAKAN</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $no = 1;
                                                    foreach ($allApps as $app): 
                                                        $sId = (int)($app['portfolio_status_id'] ?? 0);
                                                        $sDisplay = h($app['status_student_display']);
                                                        $attemptNo = (int)($app['portfolio_attempt_count'] ?? 1);
                                                        $appId = (int)($app['id'] ?? 0);
                                                    ?>
                                                        <tr>
                                                            <td class="text-center fw-medium small"><?= $no++ ?></td>
                                                            <td class="text-start fw-medium small">
                                                                <?= $attemptNo == 1 ? 'Permohonan Pertama' : 'Rayuan (Ke-' . $attemptNo . ')' ?>
                                                            </td>
                                                            <td class="text-center small">
                                                                <?= !empty($app['portfolio_submitted_at']) ? date('d-M-Y', strtotime($app['portfolio_submitted_at'])) : '-' ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($sId >= 1): ?>
                                                                    <button type="button" 
                                                                            class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 fw-bold btn-view-pdf" 
                                                                            data-app-id="<?= $appId ?>" 
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#modalPreviewPdf"
                                                                            title="Lihat Portfolio" >
                                                                        <img src="../../../../assets/images/pdf-icon.png" alt="PDF" style="width: 16px; height: 16px;">
                                                                    </button>
                                                                <?php else: ?>
                                                                    <span class="text-muted small">Belum Dihantar</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?php if ($sId === 2): ?>
                                                                    <?php 
                                                                        $tooltipText = !empty($app['advisor_remarks']) ? h($app['advisor_remarks']) : 'Tiada catatan diberikan.';
                                                                    ?>
                                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-bold" 
                                                                        data-bs-toggle="tooltip" 
                                                                        data-bs-placement="top" 
                                                                        title="<?= $tooltipText ?>" 
                                                                        style="cursor: help;">
                                                                        <?= $sDisplay ?> <i class="ri-feedback-fill text-warning ms-1"></i>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-bold">
                                                                        <?= $sDisplay ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center col-action">
                                                                <?php if ($sId === 2): ?>
                                                                    <a href="../additional-document/index.php" class="btn btn-sm btn-outline-warning btn-edit-work"
                                                                    title="<?= h(__('work_tip_edit')) ?>" >
                                                                        <i class="ri-pencil-line"></i>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted small">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endif; ?>
                </div>
            </div>

            <!-- MODAL CETAK / PREVIEW PORTFOLIO PDF -->
            <div class="modal fade" id="modalPreviewPdf" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-bold text-dark"><i class="ri-file-pdf-fill text-danger me-2"></i> Dokumen Ringkasan Portfolio APEL Q</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4" id="pdfPreviewContent">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Memuatkan maklumat portfolio...</p>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary px-4 fw-bold" target="_blank" id="btnPrintPdf">
                                <i class="ri-printer-line me-1"></i> Cetak / Simpan PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php include __DIR__ . '/../../../../includes/footer.php'; ?>
    </div>
</div>

    <?php 
      include __DIR__ . '/../../../../includes/script.php';  
      include __DIR__ . '/../translate-js.php'; 
    ?>
    <script src="<?= h(base_url('assets/js/helpers/datatables-standard.js')) ?>?v=<?= h((string)time()) ?>" defer></script>
    <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
    <script src="<?= h(base_url('assets/js/pages/portfolio-status.js')) ?>?v=<?= h((string)time()) ?>" defer></script>

    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= h((string)time()) ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= h(base_url('assets/css/pages/portfolio.css')) ?>?v=<?= h((string)time()) ?>">
    
    <script>
        $(document).ready(function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>