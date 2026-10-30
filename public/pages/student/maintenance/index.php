<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../includes/functions-page.php';

$PAGE_TITLE  = __('maintenance_title', 'Modul Dalam Kemaskini');
$pageHeading = __('maintenance_title', 'Modul Dalam Kemaskini');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = false;
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

                    <!-- TAJUK & BREADCRUMB -->
                    <div class="row mb-3 mt-3">
                        <div class="col-12">
                            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                                <div class="page-title-main">
                                    <h4 class="page-title">
                                        <i class="ri-tools-line me-2 text-warning"></i> <?= h($pageHeading) ?>
                                    </h4>
                                    <p class="page-title-subtitle text-muted mb-0">Status ketersediaan dan penambahbaikan sistem portal pelajar.</p>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="../../dashboard.php"><i class="ri-home-4-line me-1"></i> Utama</a></li>
                                        <li class="breadcrumb-item">Portal Pelajar</li>
                                        <li class="breadcrumb-item active">Kemaskini</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KANDUNGAN PAPARAN KEMASKINI -->
                    <div class="row mt-4">
                        <div class="col-lg-9 col-xl-8 mx-auto">
                            <?php include __DIR__ . '/f-maintenance.php'; ?>
                        </div>
                    </div>

                </div>
            </div>
            <?php include __DIR__ . '/../../../includes/footer.php'; ?>
        </div>
    </div>

    <?php 
      include __DIR__ . '/../../../includes/script.php';  
    ?>
    <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script>
    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= time() ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= base_url('assets/css/pages/portfolio.css') ?>?v=<?= time() ?>">
</body>
</html>