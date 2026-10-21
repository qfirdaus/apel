<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../includes/functions-page.php';
require_once __DIR__ . '/../../../controllers/AdminFinanceController.php';

$PAGE_TITLE = __('history_title');
$pageHeading = __('history_page_heading');
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = true; // [KEMASKINI: Wajib true]
$NEED_SELECT2    = false; 

include __DIR__ . '/../../../includes/header.php';
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?= h($csrfToken) ?>">

                    <div class="row mb-3">
                      <div class="col-12">
                        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                          <div class="page-title-main">
                            <h4 class="page-title"><i class="ri-history-line me-1"></i> <?= h($pageHeading) ?></h4>
                            <p class="page-title-subtitle"><?= h(__('history_subtitle')) ?></p>
                          </div>
                          <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                              <li class="breadcrumb-item">
                                <a href="dashboard.php">
                                  <i class="ri-home-4-line align-middle me-1"></i> <?= __('breadcrumb_home') ?>
                                </a>
                              </li>
                              <li class="breadcrumb-item active"><?= h($PAGE_TITLE) ?></li>
                            </ol>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="card border-0 shadow-sm profile-card">
                        <ul class="nav nav-tabs profile-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-history" data-bs-toggle="tab" href="#content-history" role="tab" aria-controls="content-history" aria-selected="true">
                                    <i class="ri-file-list-3-line me-1"></i> <?= h(__('history_section_title')) ?>
                                </a>
                            </li>
                        </ul>
                        
                        <div class="tab-content p-4">
                            <!-- Tab 1: History -->
                            <div class="tab-pane fade show active" id="content-history" role="tabpanel" aria-labelledby="tab-history">
                                <?php include __DIR__ . '/f-history.php'; ?>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
            <?php include __DIR__ . '/../../../includes/footer.php'; ?>
        </div>
    </div>

    <?php 
      include __DIR__ . '/../../../includes/script.php';  
      include __DIR__ . '/../translate-js.php'; 
    ?>

    <script src="<?= h(base_url('assets/js/helpers/datatables-standard.js')) ?>?v=<?= h((string)time()) ?>" defer></script>
    <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
    <script src="<?= base_url('assets/js/pages/admin-finance.js?v=' . time()) ?>" defer></script>

    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= h((string)time()) ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= h(base_url('assets/css/pages/portfolio.css')) ?>?v=<?= h((string)time()) ?>">
</body>
</html>