<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../../includes/functions-page.php';

$PAGE_TITLE = __('add_doc_title', 'Additional Document');
$pageHeading = __('add_doc_title', 'Additional Document');
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = true;
$NEED_SELECT2    = false;

include __DIR__ . '/../../../../includes/header.php';
require_once __DIR__ . '/../../../../controllers/ApplicationController.php';

$studentId = (string)($_SESSION['f_stafID'] ?? '');
$appController = new ApplicationController();
$activeApp = $appController->getStudentApplication($studentId);
$appId = (int)($activeApp['id'] ?? 0);
$statusId = (int)($activeApp['portfolio_status_id'] ?? 0);

$documents = $appId > 0 ? $appController->getAdditionalDocuments($appId) : [];
$isEditable = ($statusId === 2); // Kunci jika bukan status Rework (2)
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?= h($csrfToken) ?>">

                    <div class="row mb-3 mt-3">
                        <div class="col-12">
                            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                                <div class="page-title-main">
                                    <h4 class="page-title"><i class="ri-file-add-fill me-1"></i> <?= h($pageHeading) ?></h4>
                                    <p class="page-title-subtitle"><?= h(__('add_doc_subtitle', 'Muat naik dokumen sokongan tambahan seperti yang diminta oleh penilai.')) ?></p>
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

                    <?php if (!$isEditable): ?>
                        <div class="alert alert-warning border-0 shadow-sm rounded-3 p-3 mb-4 d-flex align-items-center text-dark">
                            <i class="ri-lock-2-fill fs-4 me-3 text-danger"></i>
                            <div>
                                <strong>Modul Dikunci:</strong> Muat naik dan kemaskini dokumen tambahan hanya dibenarkan apabila status permohonan berada di peringkat <strong>"Dikembalikan untuk Kemaskini (Rework)"</strong>.
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm profile-card">
                        <div class="card-body p-4">
                            <?php include __DIR__ . '/f-additional-document.php'; ?>
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
    <script src="<?= h(base_url('assets/js/pages/additional-document.js')) ?>?v=<?= h((string)time()) ?>" defer></script>

    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= h((string)time()) ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= h(base_url('assets/css/pages/portfolio.css')) ?>?v=<?= h((string)time()) ?>">
</body>
</html>