<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../includes/functions-page.php';

$PAGE_TITLE = __('advisor_title');
$pageHeading = __('advisor_title');
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');
$advisorStafId = (string)($_SESSION['f_stafID'] ?? '');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = true; 
$NEED_SELECT2    = false; 

include __DIR__ . '/../../../includes/header.php';
include __DIR__ . '/../../../controllers/AdvisorController.php';

$advisorController = new AdvisorController();
$pendingList = $advisorController->getPendingReviews($advisorStafId);
$roleInfo = $advisorController->getActiveRoleInfo();
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?= h($csrfToken) ?>">

                    <div class="row mb-3 mt-3">
                        <div class="col-12">
                            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                                <div class="page-title-main">
                                    <h4 class="page-title"><i class="ri-user-search-line me-1"></i> <?= h($pageHeading) ?></h4>
                                    <p class="page-title-subtitle"><?= h(__('advisor_subtitle', 'Semak portfolio dan tentukan sokongan permohonan APEL.Q pelajar.')) ?></p>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="ri-home-4-line me-1"></i> <?= __('breadcrumb_home', 'Utama') ?></a></li>
                                        <li class="breadcrumb-item active"><?= h($pageHeading) ?></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm profile-card">
                        <div class="card-body p-4">
                            <?php include __DIR__ . '/f-pending-reviews.php'; ?>
                        </div>
                    </div>

                </div>
            </div>
            <?php include __DIR__ . '/../../../includes/footer.php'; ?>
        </div>
    </div>

    <!-- MODAL PREVIEW PORTFOLIO PDF -->
    <div class="modal fade" id="modalPreviewPdf" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark"><i class="ri-file-pdf-fill text-danger me-2"></i> Ringkasan Portfolio APEL Q</h5>
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
                    <button type="button" class="btn btn-primary px-4 fw-bold" target="_blank" id="btnPrintPdfPreview">
                        <i class="ri-printer-line me-1"></i> Cetak / Simpan PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php 
      include __DIR__ . '/modal-assessment.php'; 
      include __DIR__ . '/../../../includes/script.php';  
      include __DIR__ . '/../translate-js.php'; 
    ?>

    <script src="<?= base_url('assets/js/helpers/datatables-standard.js') ?>" defer></script>
    <script src="<?= base_url('assets/js/pages/pages-main.js') ?>"></script> 
    <script src="<?= base_url('assets/js/pages/advisor-review.js') ?>" defer></script>

    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= h((string)time()) ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= h(base_url('assets/css/pages/portfolio.css')) ?>?v=<?= h((string)time()) ?>">
</body>
</html>