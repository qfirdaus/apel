<?php
  declare(strict_types=1);
  require_once __DIR__ . '/../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../includes/functions-page.php'; 

  $PAGE_TITLE = "Informal Learning";
  $pageHeading = "Informal Learning (start with the most recent)";
  include __DIR__ . '/../../../includes/header.php';
?>

<body data-topbar-color="light" data-menu-color="dark" data-layout="vertical" class="loading">
  <div class="wrapper">
    <?php include __DIR__ . '/../../../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">

          <!-- Tajuk Halaman -->
          <div class="row mb-3 mt-3">
            <div class="col-12">
              <h4 class="page-title text-primary"><?= h($pageHeading) ?></h4>
              <p class="text-muted">Learning that takes place continuously through life and work experience.</p>
              <div class="alert alert-warning">
                 ** Please submit your portfolio before . <br>
                 Please click <strong>attachment</strong> for reference upload supporting document.
              </div>
            </div>
          </div>

          <!-- Layout Vertical Tabs -->
          <div class="row">
            <!-- Navigasi Tab Menegak -->
            <div class="col-md-3 col-xl-2 mb-3">
                <div class="nav flex-column nav-pills shadow-sm bg-white rounded p-2" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active text-start mb-1" id="v-work-tab" data-bs-toggle="pill" data-bs-target="#v-work" type="button" role="tab" aria-controls="v-work" aria-selected="true">
                        <i class="ri-briefcase-line me-2"></i> Work Experience
                    </button>
                    <button class="nav-link text-start" id="v-other-tab" data-bs-toggle="pill" data-bs-target="#v-other" type="button" role="tab" aria-controls="v-other" aria-selected="false">
                        <i class="ri-lightbulb-line me-2"></i> Other Learning Activities
                    </button>
                </div>
            </div>

            <!-- Kandungan Tab -->
            <div class="col-md-9 col-xl-10">
                <div class="tab-content card shadow-sm p-4 border-0" id="v-pills-tabContent">
                    
                    <!-- Tab 1: Work Experience -->
                    <div class="tab-pane fade show active" id="v-work" role="tabpanel" aria-labelledby="v-work-tab">
                        <?php include __DIR__ . '/tab-work-experience.php'; ?>
                    </div>
                    
                    <!-- Tab 2: Other Learning Activities -->
                    <div class="tab-pane fade" id="v-other" role="tabpanel" aria-labelledby="v-other-tab">
                        <?php include __DIR__ . '/tab-other-activities.php'; ?>
                    </div>

                </div>
            </div>
          </div>    

        </div>
      </div>
      <?php include __DIR__ . '/../../../includes/footer.php'; ?>
    </div>
  </div>

  <?php include __DIR__ . '/../../../includes/script.php'; ?>
  <script src="<?= base_url('assets/js/pages/portfolio-informal.js?v=' . time()) ?>" defer></script> 
</body>
</html>