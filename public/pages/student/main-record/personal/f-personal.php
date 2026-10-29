<?php
// Resolusi ID dan Gambar Pelajar dari pelayan rasmi
$studentIdVal = trim((string)($student_profile['student_id'] ?? $_SESSION['f_stafID'] ?? ''));
$studentPhotoUrl = "https://kemasukan.upnm.edu.my/tawaran/pelajar/student_image/" . rawurlencode($studentIdVal) . ".jpg";

// Data Peribadi (Read-Only)
$dispName      = !empty($student_profile['full_name']) ? $student_profile['full_name'] : ($_SESSION['f_nama'] ?? '-');
$dispIc        = !empty($student_profile['ic_no']) ? $student_profile['ic_no'] : ($_SESSION['f_nokp'] ?? '-');
$dispMatrik    = $studentIdVal ?: '-';
$dispBirthDate = (!empty($student_profile['birth_date']) && $student_profile['birth_date'] !== '0000-00-00') 
                ? date('d/m/Y', strtotime($student_profile['birth_date'])) 
                : '-';

// Pengiraan umur dinamik
$dispUmur = '-';
if (!empty($student_profile['birth_date']) && $student_profile['birth_date'] !== '0000-00-00') {
    try {
        $dob  = new DateTime($student_profile['birth_date']);
        $now  = new DateTime();
        $diff = $now->diff($dob);
        $dispUmur = "{$diff->y} Tahun {$diff->m} Bulan {$diff->d} Hari";
    } catch (\Throwable $e) {
        $dispUmur = '-';
    }
}

$dispNegeriLahir   = !empty($student_profile['birth_state']) ? $student_profile['birth_state'] : '-';
$dispWarga         = !empty($student_profile['citizenship']) ? $student_profile['citizenship'] : 'Malaysia';
$dispJantina       = !empty($student_profile['gender']) ? $student_profile['gender'] : '-';
$dispBangsa        = !empty($student_profile['race']) ? $student_profile['race'] : '-';
$dispAgama         = !empty($student_profile['religion']) ? $student_profile['religion'] : '-';
$dispKahwin        = !empty($student_profile['marital_status']) ? $student_profile['marital_status'] : '-';
$dispEmelAlFateh   = !empty($student_profile['official_email']) ? $student_profile['official_email'] : ($studentIdVal ? "{$studentIdVal}@alfateh.upnm.edu.my" : '-');
$dispStatusPelajar = 'Awam';

$senaraiNegeri = [
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 
    'Pulau Pinang', 'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor', 
    'Terengganu', 'W.P. Kuala Lumpur', 'W.P. Labuan', 'W.P. Putrajaya'
];
?>

<div class="corporate-profile-container">

    <!-- 1. KAD PROFIL HEADER EKSEKUTIF -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white hero-profile-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <div class="position-relative flex-shrink-0">
                    <div class="profile-photo-frame shadow-sm">
                        <img src="<?= h($studentPhotoUrl) ?>" 
                             alt="Foto Profil Pelajar" 
                             class="profile-photo-img"
                             onerror="this.onerror=null; this.src='<?= base_url('assets/images/users/avatar-default.png') ?>';">
                    </div>
                </div>

                <div class="flex-grow-1 text-center text-md-start">
                    <div class="d-inline-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge badge-corp-primary px-3 py-1.5 rounded-pill">
                            <i class="ri-user-star-line me-1"></i> Pelajar APEL.Q
                        </span>
                        <span class="badge badge-corp-success px-3 py-1.5 rounded-pill">
                            <i class="ri-checkbox-circle-fill me-1"></i> Profil Disahkan
                        </span>
                    </div>

                    <h3 class="fw-bold text-dark mb-2 text-uppercase font-heading" style="letter-spacing: -0.3px; font-size: 1.35rem;">
                        <?= h($dispName) ?>
                    </h3>

                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-4 text-muted pt-1 info-subhead-list">
                        <div class="d-inline-flex align-items-center">
                            <i class="ri-barcode-line text-primary me-1.5 fs-5"></i>
                            <span class="text-secondary me-1">No. Matrik:</span>
                            <span class="fw-bold text-dark font-monospace"><?= h($dispMatrik) ?></span>
                        </div>
                        <div class="d-inline-flex align-items-center">
                            <i class="ri-id-card-line text-primary me-1.5 fs-5"></i>
                            <span class="text-secondary me-1">No. KP:</span>
                            <span class="fw-bold text-dark font-monospace"><?= h($dispIc) ?></span>
                        </div>
                        <div class="d-inline-flex align-items-center">
                            <i class="ri-mail-line text-primary me-1.5 fs-5"></i>
                            <span class="fw-semibold text-dark"><?= h($dispEmelAlFateh) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. KAD MAKLUMAT PERIBADI (2 LAJUR: LABEL & DATA SEBELAH-SEBELAH) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white section-panel-card">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark fs-6 d-inline-flex align-items-center">
                <span class="icon-chip me-2 text-primary bg-primary-subtle"><i class="ri-user-3-line"></i></span>
                Maklumat Peribadi
            </h5>
            <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                <i class="ri-lock-line me-1 text-muted"></i> Data Tidak Boleh Dipinda
            </span>
        </div>

        <div class="card-body p-4 p-md-4">
            <div class="row g-4">
                <!-- LAJUR KIRI MAKLUMAT PERIBADI (6 Item) -->
                <div class="col-lg-6">
                    <div class="d-flex flex-column gap-3">
                        <!-- Nama Penuh -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Nama Penuh</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box fw-bold text-dark text-truncate" title="<?= h($dispName) ?>">
                                    <?= h($dispName) ?>
                                </div>
                            </div>
                        </div>

                        <!-- No. Kad Pengenalan -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">No. Kad Pengenalan</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box font-monospace fw-semibold">
                                    <?= h($dispIc) ?>
                                </div>
                            </div>
                        </div>

                        <!-- No. Matrik Pelajar -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">No. Matrik Pelajar</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box font-monospace fw-semibold">
                                    <?= h($dispMatrik) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tarikh Lahir -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Tarikh Lahir</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= h($dispBirthDate) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Umur -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Umur</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= h($dispUmur) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Negeri Kelahiran -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Negeri Kelahiran</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= h($dispNegeriLahir) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LAJUR KANAN MAKLUMAT PERIBADI (6 Item) -->
                <div class="col-lg-6">
                    <div class="d-flex flex-column gap-3">
                        <!-- Jantina -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Jantina</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= h($dispJantina) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Bangsa -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Bangsa</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= h($dispBangsa) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Agama -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Agama</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= h($dispAgama) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Status Perkahwinan -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Status Perkahwinan</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= h($dispKahwin) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Kewarganegaraan -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Kewarganegaraan</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= h($dispWarga) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Status Pelajar -->
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Status Pelajar</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1 rounded-pill fw-medium"><?= h($dispStatusPelajar) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. KAD MAKLUMAT ALAMAT (KEKALKAN 2 LAJUR: ALAMAT DI KIRI, NEGERI & TELEFON DI KANAN) -->
    <form id="formStudentPersonal">
        <input type="hidden" name="action" value="update_personal_profile">
        <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">

        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white section-panel-card">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-dark fs-6 d-inline-flex align-items-center">
                    <span class="icon-chip me-2 text-success bg-success-subtle"><i class="ri-map-pin-2-line"></i></span>
                    Maklumat Alamat & No. Telefon
                </h5>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small">
                    <i class="ri-edit-2-line me-1"></i> Boleh Dikemaskini
                </span>
            </div>

            <div class="card-body p-4 p-md-4">
                <div class="row g-4">
                    <!-- LAJUR KIRI: ALAMAT KEDIAMAN -->
                    <div class="col-lg-6">
                        <div class="d-flex flex-column gap-3">
                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Alamat Baris 1 <span class="text-danger">*</span></label>
                                <div class="col-sm-8 col-12">
                                    <input type="text" name="address_1" class="form-control corp-input" 
                                           value="<?= h($student_profile['address_1'] ?? '') ?>" placeholder="No. Rumah, Nama Jalan, Blok" required>
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Alamat Baris 2</label>
                                <div class="col-sm-8 col-12">
                                    <input type="text" name="address_2" class="form-control corp-input" 
                                           value="<?= h($student_profile['address_2'] ?? '') ?>" placeholder="Taman Perumahan, Seksyen, Kampung">
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Poskod & Bandar</label>
                                <div class="col-sm-8 col-12">
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <input type="text" name="postcode" class="form-control corp-input font-monospace" 
                                                   maxlength="5" value="<?= h($student_profile['postcode'] ?? '') ?>" placeholder="70300">
                                        </div>
                                        <div class="col-7">
                                            <input type="text" name="city" class="form-control corp-input" 
                                                   value="<?= h($student_profile['city'] ?? '') ?>" placeholder="Bandar">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LAJUR KANAN: NEGERI & NO. TELEFON -->
                    <div class="col-lg-6">
                        <div class="d-flex flex-column gap-3">
                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Negeri <span class="text-danger">*</span></label>
                                <div class="col-sm-8 col-12">
                                    <select name="state" class="form-select select2-field" data-placeholder="- Sila Pilih Negeri -">
                                        <option value=""></option>
                                        <?php foreach ($senaraiNegeri as $neg): ?>
                                            <option value="<?= $neg ?>" <?= ($student_profile['state'] ?? '') === $neg ? 'selected' : '' ?>><?= $neg ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">No. Telefon <span class="text-danger">*</span></label>
                                <div class="col-sm-8 col-12">
                                    <div class="input-group">
                                        <span class="input-group-text corp-addon bg-light"><i class="ri-phone-line text-muted"></i></span>
                                        <input type="text" name="phone_no" class="form-control corp-input font-monospace border-start-0" 
                                               value="<?= h($student_profile['phone_no'] ?? '') ?>" placeholder="0123456789" required>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-light border border-light-subtle rounded-3 p-3 mt-1 text-muted small d-flex align-items-start gap-2">
                                <i class="ri-information-line text-primary fs-5 mt-0.5"></i>
                                <div>
                                    Pastikan alamat dan nombor telefon aktif untuk tujuan perhubungan portal.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Butang Simpan -->
                <div class="d-flex justify-content-end align-items-center pt-4 mt-3 border-top">
                    <button type="submit" id="btnSavePersonal" class="btn btn-primary px-4 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center">
                        <i class="ri-save-3-line me-2 fs-5"></i> Simpan Maklumat Alamat
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>