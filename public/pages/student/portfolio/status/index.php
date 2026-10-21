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

                        <!-- SENARAI SEJARAH PERMOHONAN -->
                        <div class="card border-0 shadow-sm profile-card mb-4">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="card-title text-dark fw-bold mb-0">Sejarah Permohonan & Rayuan</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="dt-standard">
                                    <table class="table table-striped table-bordered align-middle w-100 text-center mb-0" id="tblPortfolioStatus">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="5%" class="text-center">NO</th>
                                                <th width="25%" class="text-start">KATEGORI PERMOHONAN</th>
                                                <th width="20%" class="text-center">TARIKH DIHANTAR</th>
                                                <th width="20%" class="text-center">PORTFOLIO (PDF)</th>
                                                <th width="18%" class="text-center">STATUS SEMASA</th>
                                                <th width="12%" class="text-center">TINDAKAN</th>
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
                                                    <td class="text-center fw-medium"><?= $no++ ?></td>
                                                    <td class="text-start fw-medium">
                                                        <?= $attemptNo == 1 ? 'Permohonan Pertama' : 'Rayuan (Percubaan Ke-' . $attemptNo . ')' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?= !empty($app['portfolio_submitted_at']) ? date('d-M-Y h:i A', strtotime($app['portfolio_submitted_at'])) : '-' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($sId >= 1): ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold btn-view-pdf" 
                                                                    data-app-id="<?= $appId ?>" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#modalPreviewPdf"
                                                                    title="Lihat Portfolio PDF" >
                                                                <img src="../../../../assets/images/pdf-icon.png" alt="PDF" style="width: 18px; height: 18px; vertical-align: middle;">
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Belum Dihantar</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
                                                            <?= $sDisplay ?>
                                                        </span>
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

                        <!-- TIMELINE TRACKER (PERMOHONAN AKTIF) -->
                        <div class="card border-0 shadow-sm profile-card mb-4">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h5 class="card-title text-dark fw-bold mb-0">
                                    Status Permohonan Aktif (#<?= h((string)$currentApp['portfolio_attempt_count']) ?>)
                                </h5>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-2 rounded-pill fw-bold">
                                    <?= h($statusText) ?>
                                </span>
                            </div>
                            <div class="card-body p-4 p-md-5">
                                <div class="timeline-stepper">
                                    <div class="timeline-step-item">
                                        <div class="timeline-step-icon <?= $statusId >= 1 ? 'completed' : 'active' ?>">
                                            <?= $statusId >= 1 ? '<i class="ri-check-line"></i>' : '1' ?>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="fw-bold mb-1 <?= $statusId >= 1 ? 'text-dark' : 'text-primary' ?>">Portfolio Dihantar</h6>
                                            <p class="text-muted small mb-0">Pelajar melengkapkan 7 modul dan menghantar portfolio untuk penilaian.</p>
                                            <?php if (!empty($currentApp['portfolio_submitted_at'])): ?>
                                                <span class="badge bg-light text-secondary mt-1 border"><i class="ri-calendar-line me-1"></i> <?= date('d-M-Y h:i A', strtotime($currentApp['portfolio_submitted_at'])) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="timeline-step-item">
                                        <div class="timeline-step-icon <?= $statusId > 1 ? 'completed' : ($statusId === 1 ? 'active' : ($statusId === 2 ? 'rework' : 'pending')) ?>">
                                            <?= $statusId > 1 ? '<i class="ri-check-line"></i>' : ($statusId === 2 ? '!' : '2') ?>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="fw-bold mb-1 <?= $statusId === 1 ? 'text-primary' : ($statusId === 2 ? 'text-warning' : ($statusId > 1 ? 'text-dark' : 'text-muted')) ?>">Semakan Penasihat Akademik</h6>
                                            <p class="text-muted small mb-0">Penasihat menyemak kelayakan dan kesempurnaan dokumen sokongan.</p>
                                        </div>
                                    </div>

                                    <div class="timeline-step-item">
                                        <div class="timeline-step-icon <?= $statusId >= 4 ? 'completed' : ($statusId === 3 ? 'active' : 'pending') ?>">
                                            <?= $statusId >= 4 ? '<i class="ri-check-line"></i>' : '3' ?>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="fw-bold mb-1 <?= $statusId === 3 ? 'text-primary' : ($statusId >= 4 ? 'text-dark' : 'text-muted') ?>">Penilaian Panel & Penyelaras</h6>
                                            <p class="text-muted small mb-0">Penilai akademik menilai pengalaman kerja dan bukti pembelajaran.</p>
                                        </div>
                                    </div>

                                    <div class="timeline-step-item">
                                        <div class="timeline-step-icon <?= $statusId === 9 ? 'completed' : ($statusId === 8 ? 'rework' : 'pending') ?>">
                                            <?= $statusId === 9 ? '<i class="ri-check-line"></i>' : ($statusId === 8 ? 'X' : '4') ?>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="fw-bold mb-1 <?= $statusId === 9 ? 'text-success' : ($statusId === 8 ? 'text-danger' : 'text-muted') ?>">Keputusan Jawatankuasa / Senat</h6>
                                            <p class="text-muted small mb-0">Pengesahan keputusan kelulusan pengiktirafan APEL Q.</p>
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
                <!-- Data portfolio akan dimuatkan secara dinamik di sini -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuatkan maklumat portfolio...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" onclick="printPdfModal()">
                    <i class="ri-printer-line me-1"></i> Cetak / Simpan PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function printPdfModal() {
    let printContents = document.getElementById('pdfPreviewContent').innerHTML;
    let originalContents = document.body.innerHTML;
    document.body.innerHTML = '<div style="padding: 30px; background: #fff;">' + printContents + '</div>';
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>

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
</body>
</html>