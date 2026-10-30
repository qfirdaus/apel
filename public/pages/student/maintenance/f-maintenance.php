<?php
$currentDate = date('d-M-Y');
$studentName = $_SESSION['f_nama'] ?? $_SESSION['nama'] ?? 'Pelajar';
?>

<!-- 1. KOTAK AMARAN STATUS SISTEM -->
<div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 p-3 rounded-3" role="alert">
    <div class="fs-2 text-warning me-3">
        <i class="ri-error-warning-fill"></i>
    </div>
    <div>
        <h6 class="fw-bold mb-1 text-dark">Penyelenggaraan & Penambahbaikan Modul</h6>
        <p class="mb-0 text-secondary small">
            Fungsi bagi menu ini sedang dinaik taraf oleh pihak <strong>Pusat APEL.Q UPNM</strong> bagi memastikan ketepatan integrasi rekod pelajar.
        </p>
    </div>
</div>

<!-- 2. KAD UTAMA "SEDANG DALAM KEMASKINI" -->
<div class="card border border-dark-subtle rounded-4 mb-4 shadow-sm bg-white overflow-hidden text-center py-5 px-3">
    <div class="card-body py-4">
        
        <!-- Ikon Penyelenggaraan Bernyawa -->
        <div class="avatar-xl bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center shadow-sm mx-auto mb-4" 
             style="width: 105px; height: 105px;">
            <i class="ri-tools-fill" style="font-size: 50px;"></i>
        </div>

        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold mb-3" style="font-size: 0.8rem;">
            <i class="ri-time-line me-1"></i> Sedang Dalam Proses Kemaskini
        </span>

        <h3 class="fw-bold text-dark mb-2 font-heading">Modul Sedang Dinaik Taraf</h3>
        <p class="text-secondary fs-6 mb-4 px-md-5 mx-auto" style="max-width: 620px; line-height: 1.6;">
            Harap maaf, halaman dan borang permohonan bagi bahagian ini belum dibuka buat sementara waktu. Pasukan teknikal kami sedang menjalankan penentukuran konfigurasi borang bagi sesi akademik semasa.
        </p>

        <!-- Kotak Butiran Ringkas Pelajar -->
        <div class="p-3 bg-light rounded-3 border border-light-subtle d-inline-flex flex-wrap align-items-center justify-content-center gap-4 text-start mb-4 shadow-xs">
            <div>
                <span class="text-muted d-block small text-uppercase fw-bold tracking-wider">Akaun Pelajar</span>
                <strong class="text-dark small text-uppercase"><?= htmlspecialchars((string)$studentName) ?></strong>
            </div>
            <div class="border-start ps-4 d-none d-sm-block">
                <span class="text-muted d-block small text-uppercase fw-bold tracking-wider">Tarikh Semakan</span>
                <strong class="text-dark small"><?= $currentDate ?></strong>
            </div>
            <div class="border-start ps-4 d-none d-sm-block">
                <span class="text-muted d-block small text-uppercase fw-bold tracking-wider">Status Akses</span>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill">Dihadkan</span>
            </div>
        </div>

        <!-- Butang Tindakan -->
        <div class="d-flex justify-content-center align-items-center gap-3 pt-2">
            <a href="javascript:history.back()" class="btn btn-light border rounded-pill px-4 py-2 fw-medium text-dark shadow-xs">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
            <a href="../../dashboard.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                <i class="ri-home-4-line me-1"></i> Papan Pemuka Utama
            </a>
        </div>

    </div>
</div>

<!-- 3. KAD PANDUAN / NOTIS MAKLUMAT -->
<div class="card border border-dark-subtle rounded-3 mb-4 shadow-none">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h6 class="card-title text-dark fw-bold mb-0 d-inline-flex align-items-center">
            <i class="ri-information-line text-primary me-2 fs-5"></i> Panduan Tindakan Pelajar
        </h6>
    </div>
    <div class="card-body p-4 pt-1">
        <ul class="text-secondary small mb-0 ps-3" style="line-height: 1.8;">
            <li>Anda masih boleh melengkapkan maklumat di bahagian menu lain yang telah dibuka seperti <strong>Maklumat Peribadi</strong> dan <strong>Keluarga</strong>.</li>
            <li>Sebarang pengumuman pembukaan rasmi modul ini akan dimaklumkan melalui notifikasi papan pemuka portal atau emel rasmi Al-Fateh anda.</li>
            <li>Sekiranya anda mempunyai sebarang pertanyaan segera, sila hubungi Penyelaras APEL.Q di Pusat Pembelajaran Sepanjang Hayat.</li>
        </ul>
    </div>
</div>