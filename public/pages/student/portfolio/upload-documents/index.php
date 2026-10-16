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

?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?= h($csrfToken) ?>">

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