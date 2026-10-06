<?php
  declare(strict_types=1);
  require_once __DIR__ . '/../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../includes/functions-page.php'; 
  require_once __DIR__ . '/../../../controllers/RegisterController.php'; 

  $PAGE_TITLE = tr('reg_portfolio_title', 'Pendaftaran Portfolio');
  $pageHeading = tr('reg_portfolio_heading', 'Pendaftaran & Pembayaran Portfolio');
  
  $controller = new RegisterController();
  $data = $controller->getHalamanData();
  
  $NEED_SELECT2 = true;  
  include __DIR__ . '/../../../includes/header.php';
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading">
  <div class="wrapper">
    <?php include __DIR__ . '/../../../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">

          <div class="row mb-3">
            <div class="col-12">
              <div class="page-title-box d-flex justify-content-between align-items-center">
                <h4 class="page-title"><i class="ri-book-read-line me-1"></i> <?= h($pageHeading) ?></h4>
              </div>
            </div>
          </div>

          <div class="card shadow-sm rounded-3">
            <div class="card-body">
              <h5 class="text-primary mb-4">Lengkapkan Maklumat Pendaftaran</h5>

              <form id="formDaftarPortfolio" enctype="multipart/form-data">
                <!-- Pilihan Program -->
                <div class="mb-4 row align-items-center">
                    <label class="col-sm-3 col-form-label fw-semibold">Pilih Program Portfolio</label>
                    <div class="col-sm-6">
                        <select class="form-select select2" name="id_program" id="id_program" required>
                            <option value="" disabled selected>- Sila Pilih -</option>
                            <?php foreach ($data['senarai_program'] as $prog): ?>
                                <option value="<?= h($prog['id']) ?>" data-yuran="<?= h($prog['yuran']) ?>">
                                    <?= h($prog['nama']) ?> (RM <?= number_format($prog['yuran'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr class="text-muted mb-4">
                
                <h5 class="text-primary mb-3">Maklumat Pembayaran</h5>
                <div class="alert alert-info d-inline-block">
                    Jumlah Perlu Dibayar: <strong class="fs-4" id="paparan_yuran">RM 0.00</strong>
                </div>

                <!-- Kaedah Pembayaran -->
                <div class="mb-4 row">
                    <label class="col-sm-3 col-form-label fw-semibold">Kaedah Pembayaran</label>
                    <div class="col-sm-9">
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input payment-method" type="radio" name="kaedah_bayaran" id="payManual" value="manual" checked>
                            <label class="form-check-label" for="payManual">Upload Resit (Manual)</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input payment-method" type="radio" name="kaedah_bayaran" id="payGateway" value="gateway">
                            <label class="form-check-label" for="payGateway">Perbankan Online (FPX / Gateway)</label>
                        </div>
                    </div>
                </div>

                <!-- Seksyen Upload Resit (Kaedah 1) -->
                <div id="section_manual" class="bg-light p-3 rounded border mb-4">
                    <div class="mb-2">Sila buat pindahan bank ke akaun <strong>Maybank (1234567890)</strong> atas nama <strong>Universiti ABC</strong> dan muat naik bukti.</div>
                    <label class="form-label fw-semibold">Muat Naik Resit</label>
                    <input type="file" class="form-control w-50" name="resit_bayaran" id="resit_bayaran" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <!-- Butang Submit -->
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-primary" id="btnHantarPendaftaran">
                        <i class="ri-secure-payment-line me-1"></i> Teruskan Pendaftaran
                    </button>
                </div>
              </form>

              <!-- Form Tersembunyi (Kaedah 2 - Gateway Push Data) -->
              <form id="formKewanganGateway" method="POST" action="https://dummy-kewangan-gateway.com/pay" style="display: none;">
                  <input type="hidden" name="merchant_id" value="UM123">
                  <input type="hidden" name="order_id" id="gw_order_id" value="">
                  <input type="hidden" name="amount" id="gw_amount" value="">
                  <input type="hidden" name="return_url" value="<?= base_url('pages/portfolio/register/status.php') ?>">
              </form>

            </div>
          </div>    

        </div>
      </div>
      <?php include __DIR__ . '/../../../includes/footer.php'; ?>
    </div>
  </div>

  <?php include __DIR__ . '/../../../includes/script.php'; ?>
  <?php if ($NEED_SELECT2): ?>
    <script src="<?= base_url('assets/vendor/select2/js/select2.min.js') ?>"></script>
  <?php endif; ?>
  
  <script src="<?= base_url('assets/js/pages/portfolio-register.js?v=' . time()) ?>" defer></script> 
  <link rel="stylesheet" href="<?= base_url('assets/css/pages/portfolio-register.css') ?>">
</body>
</html>