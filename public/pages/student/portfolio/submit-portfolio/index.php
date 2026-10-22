<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../../includes/functions-page.php';

$PAGE_TITLE = __('submit_title');
$pageHeading = __('submit_title');
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = false;
$NEED_SELECT2    = false;

include __DIR__ . '/../../../../includes/header.php';
require_once __DIR__ . '/../../../../controllers/ApplicationController.php';

$studentId = (string)($_SESSION['f_stafID'] ?? '');
$appController = new ApplicationController();
$activeApp = $appController->getStudentApplication($studentId);
$appStatus = (int)($activeApp['portfolio_status_id'] ?? 0);
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?= h($csrfToken) ?>">

                    <?php if (!$activeApp): ?>            
                        <!-- TIADA PERMOHONAN -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <div class="card border-0 shadow-sm rounded-4 py-5">
                                    <div class="card-body">
                                        <div class="avatar-xl bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto mb-4" style="width: 100px; height: 100px;">
                                            <i class="ri-lock-2-fill" style="font-size: 50px;"></i>
                                        </div>
                                        <h2 class="fw-bold text-dark mb-3"><?= h(__('submit_denied_title')) ?></h2>
                                        <a href="../registration/index.php" class="btn btn-primary rounded-pill px-4"><?= h(__('submit_btn_check_registration')) ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($appStatus >= 1): ?>
                        <!-- SKRIN BERJAYA DIHANTAR -->
                        <div class="row mt-5">
                            <div class="col-md-8 mx-auto text-center">
                                <div class="card border-0 shadow-sm rounded-4 py-5" style="background: linear-gradient(to bottom, #ffffff, #f8f9fa);">
                                    <div class="card-body">
                                        <div class="avatar-xl bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto mb-4" style="width: 110px; height: 110px; background-color: #10b981 !important;">
                                            <i class="ri-check-double-line" style="font-size: 55px;"></i>
                                        </div>
                                        <h2 class="fw-bold text-dark mb-3"><?= h(__('submit_done_title')) ?></h2>
                                        <p class="text-secondary fs-5 mb-5 px-md-5">
                                            <?= __('submit_done_text') ?>
                                        </p>
                                        <div class="d-flex justify-content-center gap-3">
                                            <a href="../informal/index.php" class="btn btn-light border btn-lg rounded-pill px-4 fw-medium text-dark">
                                                <i class="ri-eye-line me-1"></i> <?= h(__('submit_btn_view_portfolio')) ?>
                                            </a>
                                            <a href="../status/index.php" class="btn btn-primary btn-lg rounded-pill px-4 fw-medium shadow-sm">
                                                <i class="ri-time-line me-1"></i> <?= h(__('submit_btn_check_status')) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>                    
                      <!-- SKRIN BORANG SUBMIT (DRAFT / REWORK) -->
                      <div class="row mb-3 mt-3">
                        <div class="col-12">
                            <div class="page-title-box">
                              <h4 class="page-title"><i class="ri-send-plane-fill me-1"></i> <?= h($pageHeading) ?></h4>
                            </div>
                        </div>
                      </div>
                      <div class="card border-0 shadow-sm profile-card">
                          <div class="tab-content p-4">
                              <?php include __DIR__ . '/f-submit.php'; ?>
                          </div>
                      </div> 
                    <?php endif;?>
                </div>
            </div>
            <?php include __DIR__ . '/../../../../includes/footer.php'; ?>
        </div>
    </div>

    <?php 
      include __DIR__ . '/../../../../includes/script.php';  
      include __DIR__ . '/../translate-js.php'; 
    ?>
    <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
    <script src="<?= h(base_url('assets/js/pages/submit-portfolio.js')) ?>?v=<?= h((string)time()) ?>" defer></script>
    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= h((string)time()) ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= h(base_url('assets/css/pages/portfolio.css')) ?>?v=<?= h((string)time()) ?>">    
</body>
</html>