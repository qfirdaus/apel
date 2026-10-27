<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../includes/functions-page.php';

$PAGE_TITLE  = __('coord_hist_title', 'Sejarah Perakuan Penyelaras');
$pageHeading = __('coord_hist_title', 'Sejarah Perakuan Penyelaras');
$csrfToken   = (string)($_SESSION['csrf_token'] ?? '');
$coordStafId = (string)($_SESSION['f_stafID'] ?? '');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = true; 
$NEED_SELECT2    = false; 

include __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../controllers/CoordinatorController.php';

$coordinatorController = new CoordinatorController();
$historyList = $coordinatorController->getReviewHistory($coordStafId);
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row mb-3 mt-3">
                        <div class="col-12">
                            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                                <div class="page-title-main">
                                    <h4 class="page-title"><i class="ri-history-line me-1"></i> <?= h($pageHeading) ?></h4>
                                    <p class="page-title-subtitle"><?= h(__('coord_hist_subtitle', 'Rekod keputusan dan catatan perakuan yang telah anda laksanakan.')) ?></p>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="../dashboard.php"><i class="ri-home-4-line me-1"></i> <?= __('breadcrumb_home', 'Utama') ?></a></li>
                                        <li class="breadcrumb-item active"><?= h($pageHeading) ?></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm profile-card">
                        <div class="card-body p-4">
                            <?php include __DIR__ . '/f-history.php'; ?>
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
    <script src="<?= base_url('assets/js/helpers/datatables-standard.js') ?>" defer></script>
</body>
</html>