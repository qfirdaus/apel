<?php
declare(strict_types=1);

// Aktifkan sementara untuk kenal pasti jika ada ralat lain
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../includes/functions-page.php';
require_once __DIR__ . '/../../../controllers/ApelAdminOfficerController.php';

// Paparan Halaman (GET)
$PAGE_TITLE  = __('admin_assign_title');
$pageHeading = __('admin_assign_title');
$csrfToken   = (string)($_SESSION['csrf_token'] ?? '');

$NEED_DATERANGE  = false;
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = true;
$NEED_SELECT2    = true;

include __DIR__ . '/../../../includes/header.php';

$controller = new ApelAdminOfficerController();

try {
    $roleInfo = $controller->getActiveRoleInfo();
} catch (\Throwable $e) {
    error_log("Ralat roleInfo: " . $e->getMessage());
    $roleInfo = [];
}

try {
    $applications = $controller->getApplications();
} catch (\Throwable $e) {
    error_log("Ralat memuatkan Applications: " . $e->getMessage());
}

try {
    $advisors = $controller->getAdvisors();
} catch (\Throwable $e) {
    error_log("Ralat memuatkan Advisors (Sybase): " . $e->getMessage());
}

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
                                    <h4 class="page-title"><i class="ri-user-settings-line me-1"></i> <?= h($pageHeading) ?></h4>
                                    <p class="page-title-subtitle"><?= h(__('admin_assign_sub', 'Lantik Penasihat Pelajar dan semak padanan Penyelaras Fakulti.')) ?></p>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="../../dashboard.php"><i class="ri-home-4-line me-1"></i> <?= __('breadcrumb_home') ?></a></li>
                                        <li class="breadcrumb-item active"><?= h($pageHeading) ?></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm profile-card">
                        <div class="card-body p-4">
                            <?php include __DIR__ . '/f-assign.php'; ?>
                        </div>
                    </div>

                </div>
            </div>
            <?php include __DIR__ . '/../../../includes/footer.php'; ?>
        </div>
    </div>

    <?php 
      include __DIR__ . '/modal-assign.php';
      include __DIR__ . '/../../../includes/script.php';  
      if (file_exists(__DIR__ . '/../translate-js.php')) {
          include __DIR__ . '/../translate-js.php'; 
      }
    ?>

    <?php if ($NEED_SELECT2): ?>
      <script src="<?= base_url('assets/vendor/select2/js/select2.min.js') ?>?v=<?= time(); ?>"></script>
    <?php endif; ?>

    <script src="<?= h(base_url('assets/js/helpers/datatables-standard.js')) ?>?v=<?= h((string)time()) ?>" defer></script>
    <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
    <script src="<?= h(base_url('assets/js/pages/admin-assign.js')) ?>?v=<?= h((string)time()) ?>" defer></script>

    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= h((string)time()) ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= h(base_url('assets/css/pages/portfolio.css')) ?>?v=<?= h((string)time()) ?>">
</body>
</html>