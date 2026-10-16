<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../../includes/functions-page.php';

$PAGE_TITLE = __('informal_title');
$pageHeading = __('informal_title');
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');

$NEED_DATERANGE  = true;
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = true;
$NEED_SELECT2    = true;

include __DIR__ . '/../../../../includes/header.php';
include __DIR__ . '/../../../../controllers/InformalLearningController.php';
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
                            <h4 class="page-title"><i class="ri-book-read-line me-1"></i> <?= h($pageHeading) ?></h4>
                            <p class="page-title-subtitle"><?= h(__('informal_subtitle')) ?> </p>
                          </div>
                          <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                              <li class="breadcrumb-item">
                                <a href="dashboard.php">
                                  <i class="ri-home-4-line align-middle me-1"></i> <?= __('breadcrumb_home') ?>
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
                                <a class="nav-link active" id="tab-work-exp" data-bs-toggle="tab" href="#content-work-exp" role="tab" aria-controls="content-work-exp" aria-selected="true">
                                    <i class="ri-briefcase-4-line me-1"></i> <?= h(__('work_title')) ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-other-act" data-bs-toggle="tab" href="#content-other-act" role="tab" aria-controls="content-other-act" aria-selected="false">
                                    <i class="ri-lightbulb-flash-line me-1"></i> <?= h(__('informal_tab_other')) ?>
                                </a>
                            </li>
                        </ul>
                        
                        <div class="tab-content p-4">
                            <!-- Tab 1: Work Experience -->
                            <div class="tab-pane fade show active" id="content-work-exp" role="tabpanel" aria-labelledby="tab-work-exp">
                                <div class="profile-section-heading">
                                    <div class="profile-section-icon"><i class="ri-briefcase-line"></i></div>
                                    <div>
                                        <h5><?= h(__('work_title')) ?></h5>
                                        <div class="text-dark fs-6"><?= __('informal_work_info') ?></div>
                                    </div>
                                </div>
                                <?php include __DIR__ . '/f-work-experience.php'; ?>
                            </div>

                            <!-- Tab 2: Other Learning Activities -->
                            <div class="tab-pane fade" id="content-other-act" role="tabpanel" aria-labelledby="tab-other-act">
                                <div class="profile-section-heading">
                                    <div class="profile-section-icon"><i class="ri-lightbulb-line"></i></div>
                                    <div>
                                        <h5><?= h(__('informal_tab_other')) ?></h5>
                                        <p><?= h(__('informal_other_desc')) ?></p>
                                    </div>
                                </div>
                                <?php include __DIR__ . '/f-other-activities.php'; ?>
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
      include __DIR__ . '/modal-view-files.php';       
      include __DIR__ . '/../translate-js.php'; 
    ?>

    <?php if ($NEED_SELECT2): ?>
      <script src="<?= base_url('assets/vendor/select2/js/select2.min.js') ?>?v=<?= time(); ?>"></script>
    <?php endif; ?>

    <script src="<?= h(base_url('assets/js/helpers/datatables-standard.js')) ?>?v=<?= h((string)time()) ?>" defer></script>
    <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
    <script src="<?= h(base_url('assets/js/pages/informal-learning.js')) ?>?v=<?= h((string)time()) ?>" defer></script>

    <!-- css refer profile dan custom -->
    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= h((string)time()) ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(base_url('assets/css/pages/portfolio.css')) ?>?v=<?= h((string)time()) ?>">
</body>
</html>