<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../includes/init.php';
require_login();
require_once __DIR__ . '/../../../../includes/functions-page.php';
require_once __DIR__ . '/../../../../controllers/ApplicationController.php';
require_once __DIR__ . '/../../../../controllers/FeeController.php'; // [+] TAMBAH INI

$PAGE_TITLE = 'Portfolio Registration';
$pageHeading = 'Portfolio Registration';
$csrfToken = (string)($_SESSION['csrf_token'] ?? '');

$NEED_DATERANGE  = false; 
$NEED_VECTORMAP  = false;
$NEED_DATATABLES = false; 
$NEED_SELECT2    = false; 

$appController = new ApplicationController();
$feeController = new FeeController(); // [+] INISIALISASI
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
                            <p class="page-title-subtitle">Pendaftaran instrumen penilaian portfolio APEL Q</p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                        <!-- LAJUR KIRI: KAD PENDAFTARAN UTAMA -->
                        <div class="col-xl-8 col-lg-7 mb-4">
                            
                            <?php if (!$gk1['is_allowed']): ?>
                                <!-- GATEKEEPER 1: YURAN PERKHIDMATAN BELUM SELESAI -->
                                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center p-4">
                                    <i class="ri-error-warning-fill fs-1 me-4"></i>
                                    <div>
                                        <h4 class="alert-heading fw-bold mb-2">Akses Disekat: Yuran Perkhidmatan</h4>
                                        <?php if ($gk1['status'] === 'processing'): ?>
                                            <p class="mb-0">Resit Yuran Perkhidmatan anda sedang disemak oleh Admin. Sila tunggu pengesahan sebelum mendaftar portfolio.</p>
                                        <?php else: ?>
                                            <p class="mb-0">Terdapat tunggakan Yuran Perkhidmatan (RM <?= number_format($serviceFee, 2) ?>) bagi semester <?= h($activeSemester) ?>. Sila jelaskan baki tersebut di menu My Account sebelum mendaftar.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            <?php elseif ($gk2['status'] === 'processing' && !$activeApp): ?>
                                <!-- GATEKEEPER 2: RESIT PORTFOLIO SEDANG DISEMAK (MENUNGGU ADMIN) -->
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-body p-5 text-center d-flex flex-column justify-content-center">
                                        <div class="mx-auto mb-4">
                                            <div class="avatar-lg bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto" style="width: 80px; height: 80px;">
                                                <i class="ri-time-line" style="font-size: 40px;"></i>
                                            </div>
                                        </div>
                                        <h3 class="fw-bolder text-dark mb-2">Resit Sedang Disemak</h3>
                                        <p class="text-muted fs-5 mb-0 px-md-4">
                                            Resit bayaran pendaftaran Portfolio anda telah diterima dan sedang menunggu pengesahan Admin. Modul penyediaan akan dibuka secara automatik selepas diluluskan.
                                        </p>
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
                                                <h3 class="mb-1 fw-bold text-dark">Penilaian Portfolio</h3>
                                                <p class="text-muted mb-0 fs-5">Sesi: <?= h($activeSemester) ?></p>
                                            </div>
                                        </div>
                                        <hr class="my-4 border-light">
                                        <div class="row align-items-center">
                                            <div class="col-md-7">
                                                <h6 class="text-uppercase text-muted fw-bold tracking-wider mb-2">Yuran Pendaftaran</h6>
                                                <!-- Harga ditarik terus dari DB -->
                                                <h1 class="display-5 fw-bolder text-dark mb-3">RM <?= number_format($portfolioFee, 2) ?></h1>
                                                <p class="text-muted fs-6 mb-0">Yuran ini membolehkan anda mengakses sistem penyediaan portfolio dan proses penilaian rasmi.</p>
                                            </div>
                                            <div class="col-md-5 text-md-end mt-4 mt-md-0">
                                                <button type="button" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm fw-bold w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#modalPaymentMethod">
                                                    Daftar & Bayar <i class="ri-arrow-right-line ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php else: ?>
                                <!-- KAD STATUS BERJAYA (Pendaftaran Selesai) -->
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-body p-0 d-flex flex-column h-100">
                                        <div class="p-4 p-md-5 bg-white flex-grow-1 d-flex flex-column justify-content-center text-center">
                                            <div class="mx-auto mb-4">
                                                <div class="avatar-lg bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto" style="width: 80px; height: 80px;">
                                                    <i class="ri-check-double-line" style="font-size: 40px;"></i>
                                                </div>
                                            </div>
                                            <h3 class="fw-bolder text-dark mb-2">Pendaftaran Selesai!</h3>
                                            <p class="text-muted fs-5 mb-0 px-md-4">
                                                Permohonan Penilaian Portfolio anda sedang berjalan. Sila ke halaman penyediaan untuk melengkapkan maklumat dan memuat naik dokumen bukti.
                                            </p>
                                        </div>

                                        <div class="p-4 p-md-5 border-top border-light-subtle d-flex flex-column flex-md-row align-items-md-center justify-content-between bg-primary-subtle" >
                                            <div class="d-flex align-items-center mb-4 mb-md-0">
                                                <div class="border-end border-2 pe-4 me-4">
                                                    <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Status Semasa</span>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-2 rounded-pill fw-bold">
                                                        <?= h($activeApp['status_student'] ?? 'Draf') ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Permohonan</span>
                                                    <h5 class="fw-bolder text-dark mb-0">
                                                        <?php echo ($activeApp['portfolio_attempt_count'] == 1) ? 'Kali Pertama' : 'Kali ke-' . h((string)($activeApp['portfolio_attempt_count'])); ?>                                                        
                                                    </h5>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="../informal/index.php" class="btn btn-primary btn-md rounded-pill px-5 py-3 shadow-sm fw-bold w-100 w-md-auto transition-all hover-elevate">
                                                    Ke Halaman Penyediaan <i class="ri-arrow-right-line ms-2"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>                            
                        </div>

                        <!-- LAJUR KANAN: GARIS PANDUAN (Kekal sama) -->
                        <!-- ... (Kod Lajur Kanan anda yang sedia ada) ... -->
                        <div class="col-xl-4 col-lg-5 mb-4">
                            <!-- Letakkan kod kad Proses Penilaian di sini seperti biasa -->
                            <div class="card border-0 shadow-sm h-100 bg-white">
                                <div class="card-body p-4">
                                    <h6 class="text-uppercase text-muted fw-bold mb-4 tracking-wider"><i class="ri-route-line me-2 text-primary"></i> Proses Penilaian</h6>
                                    
                                    <div class="d-flex mb-4">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs text-center bg-primary text-white rounded-circle fw-bold shadow-sm" style="width: 28px; height: 28px; line-height: 28px;">1</div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 fw-bold text-dark">Pendaftaran & Bayaran</h6>
                                            <p class="text-muted small mb-0">Daftar instrumen dan buat pembayaran.</p>
                                        </div>
                                    </div>
                                    <div class="d-flex mb-4">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs text-center bg-light text-secondary rounded-circle fw-bold" style="width: 28px; height: 28px; line-height: 28px;">2</div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 text-dark fw-semibold">Penyediaan Portfolio</h6>
                                            <p class="text-muted small mb-0">Isi maklumat & muat naik dokumen bukti.</p>
                                        </div>
                                    </div>
                                    <div class="d-flex mb-4">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs text-center bg-light text-secondary rounded-circle fw-bold" style="width: 28px; height: 28px; line-height: 28px;">3</div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 text-dark fw-semibold">Penghantaran</h6>
                                            <p class="text-muted small mb-0">Hantar portfolio untuk dinilai.</p>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs text-center bg-light text-secondary rounded-circle fw-bold" style="width: 28px; height: 28px; line-height: 28px;">4</div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 text-dark fw-semibold">Penilaian Akademik</h6>
                                            <p class="text-muted small mb-0">Disemak oleh Penasihat dan Senat.</p>
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