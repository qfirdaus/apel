<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../../includes/functions-page.php';
require_once __DIR__ . '/../../../../controllers/ApplicationController.php';
require_once __DIR__ . '/../../../../controllers/FeeController.php'; 

$PAGE_TITLE = __('reg_title');
$pageHeading = __('reg_title');
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = false; 
$NEED_SELECT2    = false; 

$appController = new ApplicationController();
$feeController = new FeeController(); 
$studentId = (string)($_SESSION['f_stafID'] ?? '');

// Tetapan Harga & Semester
$settings = $feeController->getSettings();
$activeSemester = $settings['active_semester'];
$portfolioFee = (float)$settings['portfolio_fee'];
$serviceFee = (float)$settings['service_fee'];

// Semak Hutang
$gk1 = $feeController->checkGatekeeperServiceFee($studentId, $activeSemester);
$gk2 = $feeController->checkInstrumentPayment($studentId, $activeSemester, 'portfolio_fee');
$activeApp = $appController->getStudentApplication($studentId);

include __DIR__ . '/../../../../includes/header.php';
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading profile-page">
    <div class="wrapper">
        <?php include __DIR__ . '/../../../../includes/topbar.php'; ?>
        <?php include __DIR__ . '/../../../../includes/sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?= h($csrfToken) ?>">
                    <!-- Hantar semester aktif ke JS untuk AJAX -->
                    <input type="hidden" id="activeSemester" name="active_semester" value="<?= h($activeSemester) ?>">

                    <div class="row mb-3 mt-3">
                      <div class="col-12">
                        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                          <div class="page-title-main">
                            <h4 class="page-title"><i class="ri-file-add-line me-1"></i> <?= h($pageHeading) ?></h4>
                            <p class="page-title-subtitle"><?= h(__('reg_subtitle')) ?></p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-8 col-lg-7 mb-4">
                            
                            <?php if (!$gk1['is_allowed']): ?>
                                <!-- YURAN PERKHIDMATAN BELUM SELESAI -->
                                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center p-4">
                                    <i class="ri-error-warning-fill fs-1 me-4"></i>
                                    <div>
                                        <h4 class="alert-heading fw-bold mb-2"><?= h(__('reg_blocked_service_title')) ?></h4>
                                        <?php if ($gk1['status'] === 'processing'): ?>
                                            <p class="mb-0"><?= h(__('reg_service_processing')) ?></p>
                                        <?php else: ?>
                                            <p class="mb-0"><?= str_replace(['{amount}', '{semester}'], [number_format($serviceFee, 2), h($activeSemester)], h(__('reg_service_outstanding'))) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            <?php elseif ($gk2['status'] === 'processing' && !$activeApp): ?>
                                <!-- RESIT PORTFOLIO SEDANG DISEMAK (MENUNGGU ADMIN) -->
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-body p-5 text-center d-flex flex-column justify-content-center">
                                        <div class="mx-auto mb-4">
                                            <div class="avatar-lg bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto" style="width: 80px; height: 80px;">
                                                <i class="ri-time-line" style="font-size: 40px;"></i>
                                            </div>
                                        </div>
                                        <h3 class="fw-bolder text-dark mb-2"><?= h(__('reg_receipt_review_title')) ?></h3>
                                        <p class="text-muted fs-5 mb-0 px-md-4">
                                            <?= h(__('reg_receipt_review_text')) ?>
                                        </p>
                                    </div>
                                </div>

                            <?php elseif ($gk2['status'] === 'failed' && !$activeApp): ?>
                                <!-- RESIT PORTFOLIO DITOLAK (REJECTED) -->
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-4 p-md-5">
                                        
                                        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center p-4 mb-4 rounded-4 bg-danger-subtle text-danger">
                                            <i class="ri-error-warning-fill fs-1 me-4"></i>
                                            <div>
                                                <h4 class="alert-heading fw-bold mb-2 text-danger"><?= h(__('reg_receipt_failed_title')) ?></h4>
                                                <p class="mb-0 text-dark"><?= h(__('reg_receipt_rejected_text')) ?> <br>
                                                <strong><?= h(__('reg_rejection_reason')) ?></strong> <?= h($gk2['data']['remarks']) ?></p>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-lg bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center shadow-sm">
                                                    <i class="ri-briefcase-4-line fs-1"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-4">
                                                <h3 class="mb-1 fw-bold text-dark"><?= h(__('reg_portfolio_assessment')) ?></h3>
                                                <p class="text-muted mb-0 fs-5"><?= h(__('reg_session_label')) ?> <?= h($activeSemester) ?></p>
                                            </div>
                                        </div>
                                        <hr class="my-4 border-light">
                                        <div class="row align-items-center">
                                            <div class="col-md-7">
                                                <h6 class="text-uppercase text-muted fw-bold tracking-wider mb-2"><?= h(__('reg_registration_fee')) ?></h6>
                                                <h1 class="display-5 fw-bolder text-dark mb-3">RM <?= number_format($portfolioFee, 2) ?></h1>
                                                <p class="text-muted fs-6 mb-0"><?= h(__('reg_reupload_hint')) ?></p>
                                            </div>
                                            <div class="col-md-5 text-md-end mt-4 mt-md-0">
                                                <button type="button" class="btn btn-danger btn-lg rounded-pill px-4 shadow-sm fw-bold w-100 w-md-auto transition-all hover-elevate" data-bs-toggle="modal" data-bs-target="#modalPaymentMethod">
                                                    <?= h(__('reg_btn_reupload')) ?> <i class="ri-upload-2-line ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php elseif (!$activeApp): ?>
                                <!-- KAD DAFTAR (BELUM DAFTAR / FPX GAGAL) -->
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-4 p-md-5">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-lg bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center shadow-sm">
                                                    <i class="ri-briefcase-4-line fs-1"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-4">
                                                <h3 class="mb-1 fw-bold text-dark"><?= h(__('reg_portfolio_assessment')) ?></h3>
                                                <p class="text-muted mb-0 fs-5"><?= h(__('reg_session_label')) ?> <?= h($activeSemester) ?></p>
                                            </div>
                                        </div>
                                        <hr class="my-4 border-light">
                                        <div class="row align-items-center">
                                            <div class="col-md-7">
                                                <h6 class="text-uppercase text-muted fw-bold tracking-wider mb-2"><?= h(__('reg_registration_fee')) ?></h6>
                                                <!-- Harga ditarik terus dari DB -->
                                                <h1 class="display-5 fw-bolder text-dark mb-3">RM <?= number_format($portfolioFee, 2) ?></h1>
                                                <p class="text-muted fs-6 mb-0"><?= h(__('reg_fee_desc')) ?></p>
                                            </div>
                                            <div class="col-md-5 text-md-end mt-4 mt-md-0">
                                                <button type="button" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm fw-bold w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#modalPaymentMethod">
                                                    <?= h(__('reg_btn_register_pay')) ?> <i class="ri-arrow-right-line ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php elseif (isset($activeApp['portfolio_status_id']) && $activeApp['portfolio_status_id'] == 0): ?>
                                <!-- KAD STATUS BERJAYA (Pendaftaran Selesai - Sedang Disediakan) -->
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-body p-0 d-flex flex-column h-100">
                                        <div class="p-4 p-md-5 bg-white flex-grow-1 d-flex flex-column justify-content-center text-center">
                                            <div class="mx-auto mb-4">
                                                <div class="avatar-lg bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto" style="width: 80px; height: 80px;">
                                                    <i class="ri-check-double-line" style="font-size: 40px;"></i>
                                                </div>
                                            </div>
                                            <h3 class="fw-bolder text-dark mb-2">Registration Complete!</h3>
                                            <p class="text-muted fs-5 mb-0 px-md-4">
                                                Your Portfolio Assessment application is in progress. Please go to the preparation page to complete your information and upload supporting documents.
                                            </p>
                                        </div>

                                        <div class="p-4 p-md-5 border-top border-light-subtle d-flex flex-column flex-md-row align-items-md-center justify-content-between bg-primary-subtle" >
                                            <div class="d-flex align-items-center mb-4 mb-md-0">
                                                <div class="border-end border-2 pe-4 me-4">
                                                    <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">CURRENT STATUS</span>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-6 px-3 py-2 rounded-pill fw-bold">
                                                        <?= h($activeApp['status_student_display'] ?? 'Sedang Disediakan') ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;"><?= h(__('reg_application_label')) ?></span>
                                                    <h5 class="fw-bolder text-dark mb-0">
                                                        <?php echo ($activeApp['portfolio_attempt_count'] == 1) ? h(__('reg_attempt_first')) : str_replace('{n}', h((string)($activeApp['portfolio_attempt_count'])), h(__('reg_attempt_nth'))); ?>                                                        
                                                    </h5>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="../informal/index.php" class="btn btn-primary btn-md rounded-pill px-4 py-3 shadow-sm fw-bold w-100 w-md-auto transition-all hover-elevate">
                                                    Go to Preparation Page <i class="ri-arrow-right-line ms-2"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php else: ?>
                                <!-- KAD STATUS DIHANTAR (Portfolio Telah Dihantar) -->
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-body p-0 d-flex flex-column h-100">
                                        <div class="p-4 p-md-5 bg-white flex-grow-1 d-flex flex-column justify-content-center text-center">
                                            <div class="mx-auto mb-4">
                                                <div class="avatar-lg bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto" style="width: 80px; height: 80px;">
                                                    <i class="ri-send-plane-fill" style="font-size: 40px;"></i>
                                                </div>
                                            </div>
                                            <h3 class="fw-bolder text-dark mb-2">Portfolio Submitted!</h3>
                                            <p class="text-muted fs-5 mb-0 px-md-4">
                                                Your Portfolio has been successfully submitted and is currently under academic assessment.
                                            </p>
                                        </div>

                                        <div class="p-4 p-md-5 border-top border-light-subtle d-flex flex-column flex-md-row align-items-md-center justify-content-between bg-light" >
                                            <div class="d-flex align-items-center mb-4 mb-md-0">
                                                <div class="border-end border-2 pe-4 me-4">
                                                    <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">CURRENT STATUS</span>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 px-3 py-2 rounded-pill fw-bold">
                                                        <?= h($activeApp['status_student_display'] ?? 'Dihantar (Dalam Semakan)') ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;"><?= h(__('reg_application_label')) ?></span>
                                                    <h5 class="fw-bolder text-dark mb-0">
                                                        <?php echo ($activeApp['portfolio_attempt_count'] == 1) ? h(__('reg_attempt_first')) : str_replace('{n}', h((string)($activeApp['portfolio_attempt_count'])), h(__('reg_attempt_nth'))); ?>                                                        
                                                    </h5>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="../informal/index.php" class="btn btn-outline-primary bg-white btn-md rounded-pill px-4 py-3 shadow-sm fw-bold w-100 w-md-auto transition-all hover-elevate">
                                                    <i class="ri-eye-line me-1"></i> View Portfolio
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>                        
                        </div>

                        <!-- PROSES PENILAIAN (DINAMIK) -->
                        <div class="col-xl-4 col-lg-5 mb-4">
                            <?php
                                $currentStep = 1; // Default: Langkah 1
                                
                                if ($activeApp) {
                                    $statusId = (int)($activeApp['portfolio_status_id'] ?? 0);
                                    if ($statusId === 0) {
                                        $currentStep = 2; // Sedang sedia portfolio
                                    } elseif ($statusId >= 1) {
                                        $currentStep = 4; // Terus ke tahap 4 (Penilaian) kerana submit selesai
                                    }
                                }

                                // Bulatan Nombor (Badge)
                                function getStepBadge($step, $current) {
                                    if ($step < $current) {
                                        // Selesai (Hijau + Tanda Rait)
                                        return '<div class="avatar-xs text-center bg-success text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;"><i class="ri-check-line fw-bold fs-6"></i></div>';
                                    } elseif ($step === $current) {
                                        // Sedang Aktif (Biru)
                                        return '<div class="avatar-xs text-center bg-primary text-white rounded-circle fw-bold shadow-sm" style="width: 28px; height: 28px; line-height: 28px;">' . $step . '</div>';
                                    } else {
                                        // Belum Tiba (Kelabu)
                                        return '<div class="avatar-xs text-center bg-light text-secondary rounded-circle fw-bold" style="width: 28px; height: 28px; line-height: 28px;">' . $step . '</div>';
                                    }
                                }
                                
                                // warna Teks Tajuk
                                function getStepTitleClass($step, $current) {
                                    if ($step < $current) return 'text-dark fw-bold';
                                    if ($step === $current) return 'text-primary fw-bold';
                                    return 'text-secondary fw-semibold';
                                }
                            ?>

                            <div class="card border-0 shadow-sm h-100 bg-white rounded-4">
                                <div class="card-body p-4 p-md-5">
                                    <h6 class="text-uppercase text-muted fw-bold mb-4 tracking-wider" style="font-size: 0.75rem;">
                                        <i class="ri-route-line me-2 text-primary"></i> <?= h(__('reg_process_title')) ?>
                                    </h6>
                                    
                                    <!-- Langkah 1 -->
                                    <div class="d-flex mb-4">
                                        <div class="flex-shrink-0">
                                            <?= getStepBadge(1, $currentStep) ?>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 <?= getStepTitleClass(1, $currentStep) ?>"><?= h(__('reg_step1_title')) ?></h6>
                                            <p class="text-muted small mb-0"><?= h(__('reg_step1_desc')) ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Langkah 2 -->
                                    <div class="d-flex mb-4">
                                        <div class="flex-shrink-0">
                                            <?= getStepBadge(2, $currentStep) ?>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 <?= getStepTitleClass(2, $currentStep) ?>"><?= h(__('reg_step2_title')) ?></h6>
                                            <p class="text-muted small mb-0"><?= h(__('reg_step2_desc')) ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Langkah 3 (Transisi / Submit) -->
                                    <div class="d-flex mb-4">
                                        <div class="flex-shrink-0">
                                            <?= getStepBadge(3, $currentStep) ?>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 <?= getStepTitleClass(3, $currentStep) ?>"><?= h(__('reg_step3_title')) ?></h6>
                                            <p class="text-muted small mb-0"><?= h(__('reg_step3_desc')) ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Langkah 4 -->
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <?= getStepBadge(4, $currentStep) ?>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 <?= getStepTitleClass(4, $currentStep) ?>"><?= h(__('reg_step4_title')) ?></h6>
                                            <p class="text-muted small mb-0"><?= h(__('reg_step4_desc')) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php include __DIR__ . '/../../../../includes/footer.php'; ?>
        </div>
    </div>

    <?php 
      include __DIR__ . '/modal-payment.php'; 
      include __DIR__ . '/../../../../includes/script.php'; 
      include __DIR__ . '/../translate-js.php';          
    ?>

    <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
    <script src="<?= base_url('assets/js/pages/portfolio-registration.js?v=' . time()) ?>"></script>

    <link href="<?= base_url('assets/css/pages/profile.css') ?>?v=<?= h((string)time()) ?>" rel="stylesheet">    
    <link rel="stylesheet" href="<?= h(base_url('assets/css/pages/portfolio.css')) ?>?v=<?= h((string)time()) ?>">
</body>
</html>