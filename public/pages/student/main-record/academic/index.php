<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../../includes/functions-page.php';
require_once __DIR__ . '/../../../../controllers/StudentAcademicController.php';

$PAGE_TITLE  = __('student_academic_title', 'Maklumat Akademik Pelajar');
$pageHeading = __('student_academic_title', 'Maklumat Akademik Pelajar');
$csrfToken   = (string)($_SESSION['csrf_token'] ?? '');

$studentId   = trim((string)($_SESSION['f_stafID'] ?? ''));

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = false; 
$NEED_SELECT2    = false; 

include __DIR__ . '/../../../../includes/header.php';

$controller      = new StudentAcademicController();
$student_profile = $controller->getAcademic($studentId);
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="row mb-3 mt-3">
                        <div class="col-12">
                            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                                <div class="page-title-main">
                                    <h4 class="page-title"><i class="ri-book-open-line me-2 text-primary"></i> <?= htmlspecialchars($pageHeading) ?></h4>
                                    <p class="page-title-subtitle text-muted mb-0">Paparan maklumat program pengajian, fakulti, sesi kemasukan dan status akademik.</p>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="../../dashboard.php"><i class="ri-home-4-line me-1"></i> Utama</a></li>
                                        <li class="breadcrumb-item">Akaun Saya</li>
                                        <li class="breadcrumb-item active">Akademik</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-card-container">
                        <?php include __DIR__ . '/f-academic.php'; ?>
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

    <script src="<?= base_url('assets/js/pages/pages-main.js') ?>"></script> 
    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= time() ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= base_url('assets/css/pages/portfolio.css') ?>?v=<?= time() ?>">
</body>
</html>