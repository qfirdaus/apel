<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../../includes/functions-page.php';

$PAGE_TITLE = __('upload_doc_title') ?? 'Upload Other Documents';
$pageHeading = __('upload_doc_title') ?? 'Upload Other Documents';
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = false;
$NEED_SELECT2    = false;

include __DIR__ . '/../../../../includes/header.php';
include __DIR__ . '/../../../../controllers/UploadDocumentController.php';
require_once __DIR__ . '/../../../../controllers/ApplicationController.php';

$studentId = (string)($_SESSION['f_stafID'] ?? '');
$appController = new ApplicationController();
$activeApp = $appController->getStudentApplication($studentId);
$applicationId = (int)($activeApp['id'] ?? 0);
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?= h($csrfToken) ?>">

                    <!-- LOCK SKRIN READONLY AFTER SUBMIT -->
                    <?php 
                        $statusId = (int)($activeApp['portfolio_status_id'] ?? 0);
                        if ($statusId >= 1): 
                    ?>
                        <div class="alert alert-warning border-0 shadow-sm mt-2 mb-4 fw-medium text-dark" >
                            <i class="ri-lock-2-fill text-danger me-2 fs-5 align-middle"></i> 
                            <?= __('lock_screen_info') ?>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                document.body.classList.add('read-only-mode');
                            });
                        </script>
                    <?php endif; ?> 
                                        
                    <?php if (!$activeApp): ?>            
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <div class="card border-0 shadow-sm rounded-4 py-5">
                                    <div class="card-body">
                                        <div class="avatar-xl bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto mb-4" style="width: 100px; height: 100px;">
                                            <i class="ri-lock-2-fill" style="font-size: 50px;"></i>
                                        </div>
                                        <h2 class="fw-bold text-dark mb-3"><?= h(__('app_access_denied')) ?></h2>
                                        <p class="text-muted fs-5 mb-4 px-md-5">
                                            <?= __('app_status_info') ?>
                                        </p>
                                        <a href="../registration/index.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm fw-bold">
                                            <i class="ri-arrow-left-line me-2"></i> <?= h(__('app_status_check_registration')) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>                    
                      <div class="row mb-3">
                        <div class="col-12">
                          <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                            <div class="page-title-main">
                              <h4 class="page-title"><i class="ri-folder-upload-line me-1"></i> <?= h($pageHeading) ?></h4>
                            </div>
                            <div class="page-title-right">
                              <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                  <a href="dashboard.php">
                                    <i class="ri-home-4-line align-middle me-1"></i> <?= __('breadcrumb_home') ?? 'Home' ?>
                                  </a>
                                </li>
                                <li class="breadcrumb-item active"><?= h($pageHeading) ?></li>
                              </ol>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="card border-0 shadow-sm profile-card">

                          <ul class="nav nav-tabs profile-tabs" role="tablist">
                              <li class="nav-item">
                                  <a class="nav-link active" id="tab-upload" data-bs-toggle="tab" href="#content-upload" role="tab" aria-controls="content-upload" aria-selected="true">
                                      <i class="ri-file-list-3-line me-1"></i> <?= h(__('upload_supporting_docs')) ?>
                                  </a>
                              </li>
                          </ul>
                          
                          <div class="tab-content p-4">
                              <div class="tab-pane fade show active" id="content-upload" role="tabpanel" aria-labelledby="tab-upload">
                                  <div class="profile-section-heading mb-4">
                                      <div class="profile-section-icon"><i class="ri-upload-cloud-2-line"></i></div>
                                      <div>
                                          <h5><?= h(__('upload_required_docs')) ?></h5>
                                          <div class="text-dark small mt-1"><?= __('upload_doc_info') ?></div>
                                      </div>
                                  </div>
                                  
                                  <?php include __DIR__ . '/f-upload-documents.php'; ?>
                                  
                              </div>
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
    <script src="<?= h(base_url('assets/js/pages/upload-document.js')) ?>?v=<?= h((string)time()) ?>" defer></script>

    <!-- css refer profile dan custom -->
    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= h((string)time()) ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= h(base_url('assets/css/pages/portfolio.css')) ?>?v=<?= h((string)time()) ?>">
</body>
</html>