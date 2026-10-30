<?php
// Resolusi ID dan Gambar Pelajar
$studentIdVal = trim((string)($student_profile['student_id'] ?? $_SESSION['f_stafID'] ?? ''));
$studentPhotoUrl = "https://kemasukan.upnm.edu.my/tawaran/pelajar/student_image/" . rawurlencode($studentIdVal) . ".jpg";

$dispName        = !empty($student_profile['full_name']) ? $student_profile['full_name'] : '-';
$dispIc          = !empty($student_profile['ic_no']) ? $student_profile['ic_no'] : '-';
$dispMatrik      = $studentIdVal ?: '-';
$dispKahwin      = !empty($student_profile['marital_status']) ? $student_profile['marital_status'] : '-';

// Data Ibu Bapa (Read-Only)
$dispFatherName  = !empty($student_profile['father_name']) ? $student_profile['father_name'] : '-';
$dispFatherIc    = !empty($student_profile['father_ic']) ? $student_profile['father_ic'] : '-';
$dispMotherName  = !empty($student_profile['mother_name']) ? $student_profile['mother_name'] : '-';
$dispMotherIc    = !empty($student_profile['mother_ic']) ? $student_profile['mother_ic'] : '-';

$senaraiHubungan = ['Pasangan (Suami / Isteri)', 'Bapa Kandung', 'Ibu Kandung', 'Penjaga Sah', 'Adik-Beradik', 'Waris Terdekat'];
$senaraiGaji     = ['Tiada Pendapatan', 'Kurang RM 2,500', 'RM 2,501 - RM 4,850', 'RM 4,851 - RM 10,960', 'RM 10,961 dan ke atas'];
?>

<div class="corporate-profile-container">

    <!-- 1. KAD PROFIL HEADER EKSEKUTIF -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white hero-profile-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <div class="position-relative flex-shrink-0">
                    <div class="profile-photo-frame shadow-sm">
                        <img src="<?= htmlspecialchars($studentPhotoUrl) ?>" 
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
                        <?= htmlspecialchars($dispName) ?>
                    </h3>

                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-4 text-muted pt-1 info-subhead-list">
                        <div class="d-inline-flex align-items-center">
                            <i class="ri-barcode-line text-primary me-1.5 fs-5"></i>
                            <span class="text-secondary me-1">No. Matrik:</span>
                            <span class="fw-bold text-dark font-monospace"><?= htmlspecialchars($dispMatrik) ?></span>
                        </div>
                        <div class="d-inline-flex align-items-center">
                            <i class="ri-id-card-line text-primary me-1.5 fs-5"></i>
                            <span class="text-secondary me-1">No. KP:</span>
                            <span class="fw-bold text-dark font-monospace"><?= htmlspecialchars($dispIc) ?></span>
                        </div>
                        <div class="d-inline-flex align-items-center">
                            <i class="ri-heart-line text-primary me-1.5 fs-5"></i>
                            <span class="text-secondary me-1">Status Perkahwinan:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($dispKahwin) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. BORANG KEMASKINI MAKLUMAT KELUARGA & WARIS -->
    <form id="formStudentFamily">
        <input type="hidden" name="action" value="update_family_profile">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <!-- KAD 1: MAKLUMAT IBU BAPA / PENJAGA -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white section-panel-card">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-dark fs-6 d-inline-flex align-items-center">
                    <span class="icon-chip me-2 text-primary bg-primary-subtle"><i class="ri-parent-line"></i></span>
                    Maklumat Ibu Bapa / Penjaga
                </h5>
                <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                    <i class="ri-lock-line me-1 text-muted"></i> Nama & No. KP Kekal
                </span>
            </div>

            <div class="card-body p-4 p-md-4">
                <div class="row g-4">
                    <!-- LAJUR KIRI: MAKLUMAT BAPA / PENJAGA -->
                    <div class="col-lg-6">
                        <div class="d-flex flex-column gap-3">
                            <div class="section-column-title mb-2">
                                <i class="ri-user-line me-1 text-primary"></i> Maklumat Bapa / Penjaga
                            </div>

                            <!-- Nama Bapa (Read-Only) -->
                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Nama Bapa</label>
                                <div class="col-sm-8 col-12">
                                    <div class="corp-display-box fw-bold text-dark text-truncate" title="<?= htmlspecialchars($dispFatherName) ?>">
                                        <?= htmlspecialchars($dispFatherName) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- No. KP Bapa (Read-Only) -->
                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">No. Kad Pengenalan</label>
                                <div class="col-sm-8 col-12">
                                    <div class="corp-display-box font-monospace fw-semibold">
                                        <?= htmlspecialchars($dispFatherIc) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">No. Telefon</label>
                                <div class="col-sm-8 col-12">
                                    <div class="input-group">
                                        <span class="input-group-text corp-addon bg-light"><i class="ri-phone-line text-muted"></i></span>
                                        <input type="text" name="father_phone" class="form-control corp-input font-monospace border-start-0" 
                                               value="<?= htmlspecialchars($student_profile['father_phone'] ?? '') ?>" placeholder="0123456789">
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Pekerjaan</label>
                                <div class="col-sm-8 col-12">
                                    <input type="text" name="father_job" class="form-control corp-input" 
                                           value="<?= htmlspecialchars($student_profile['father_job'] ?? '') ?>" placeholder="Contoh: Pesara / Swasta">
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Pendapatan Bulanan</label>
                                <div class="col-sm-8 col-12">
                                    <select name="father_income" class="form-select select2-field" data-placeholder="- Sila Pilih Pendapatan -">
                                        <option value=""></option>
                                        <?php foreach ($senaraiGaji as $gaji): ?>
                                            <option value="<?= $gaji ?>" <?= (($student_profile['father_income'] ?? '') === $gaji) ? 'selected' : '' ?>><?= $gaji ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LAJUR KANAN: MAKLUMAT IBU -->
                    <div class="col-lg-6">
                        <div class="d-flex flex-column gap-3">
                            <div class="section-column-title mb-2">
                                <i class="ri-user-heart-line me-1 text-primary"></i> Maklumat Ibu
                            </div>

                            <!-- Nama Ibu (Read-Only) -->
                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Nama Ibu</label>
                                <div class="col-sm-8 col-12">
                                    <div class="corp-display-box fw-bold text-dark text-truncate" title="<?= htmlspecialchars($dispMotherName) ?>">
                                        <?= htmlspecialchars($dispMotherName) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- No. KP Ibu (Read-Only) -->
                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">No. Kad Pengenalan</label>
                                <div class="col-sm-8 col-12">
                                    <div class="corp-display-box font-monospace fw-semibold">
                                        <?= htmlspecialchars($dispMotherIc) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">No. Telefon</label>
                                <div class="col-sm-8 col-12">
                                    <div class="input-group">
                                        <span class="input-group-text corp-addon bg-light"><i class="ri-phone-line text-muted"></i></span>
                                        <input type="text" name="mother_phone" class="form-control corp-input font-monospace border-start-0" 
                                               value="<?= htmlspecialchars($student_profile['mother_phone'] ?? '') ?>" placeholder="0123456789">
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Pekerjaan</label>
                                <div class="col-sm-8 col-12">
                                    <input type="text" name="mother_job" class="form-control corp-input" 
                                           value="<?= htmlspecialchars($student_profile['mother_job'] ?? '') ?>" placeholder="Contoh: Suri Rumah / Kerajaan">
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Pendapatan Bulanan</label>
                                <div class="col-sm-8 col-12">
                                    <select name="mother_income" class="form-select select2-field" data-placeholder="- Sila Pilih Pendapatan -">
                                        <option value=""></option>
                                        <?php foreach ($senaraiGaji as $gaji): ?>
                                            <option value="<?= $gaji ?>" <?= (($student_profile['mother_income'] ?? '') === $gaji) ? 'selected' : '' ?>><?= $gaji ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KAD 2: MAKLUMAT WARIS & TANGGUNGAN (PERKATAAN 'KECEMASAN' DIBUANG) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white section-panel-card">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-dark fs-6 d-inline-flex align-items-center">
                    <span class="icon-chip me-2 text-success bg-success-subtle"><i class="ri-contacts-line"></i></span>
                    Maklumat Waris & Tanggungan
                </h5>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small">
                    <i class="ri-edit-2-line me-1"></i> Boleh Dikemaskini
                </span>
            </div>

            <div class="card-body p-4 p-md-4">
                <div class="row g-4">
                    <!-- LAJUR KIRI: WARIS TERDEKAT -->
                    <div class="col-lg-6">
                        <div class="d-flex flex-column gap-3">
                            <div class="section-column-title mb-2">
                                <i class="ri-shield-user-line me-1 text-primary"></i> Waris Terdekat
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Nama Waris <span class="text-danger">*</span></label>
                                <div class="col-sm-8 col-12">
                                    <input type="text" name="spouse_name" class="form-control corp-input" 
                                           value="<?= htmlspecialchars($student_profile['spouse_name'] ?? '') ?>" placeholder="Nama Pasangan atau Waris" required>
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Hubungan Waris <span class="text-danger">*</span></label>
                                <div class="col-sm-8 col-12">
                                    <select name="spouse_relationship" class="form-select select2-field" data-placeholder="- Sila Pilih Hubungan -" required>
                                        <option value=""></option>
                                        <?php foreach ($senaraiHubungan as $rel): ?>
                                            <option value="<?= $rel ?>" <?= (($student_profile['spouse_relationship'] ?? '') === $rel) ? 'selected' : '' ?>><?= $rel ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">No. Telefon Waris <span class="text-danger">*</span></label>
                                <div class="col-sm-8 col-12">
                                    <div class="input-group">
                                        <span class="input-group-text corp-addon bg-light"><i class="ri-phone-line text-muted"></i></span>
                                        <input type="text" name="spouse_phone" class="form-control corp-input font-monospace border-start-0" 
                                               value="<?= htmlspecialchars($student_profile['spouse_phone'] ?? '') ?>" placeholder="0123456789" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LAJUR KANAN: BILANGAN TANGGUNGAN & ADIK-BERADIK -->
                    <div class="col-lg-6">
                        <div class="d-flex flex-column gap-3">
                            <div class="section-column-title mb-2">
                                <i class="ri-team-line me-1 text-primary"></i> Statistik Tanggungan
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Bil. Adik-Beradik</label>
                                <div class="col-sm-8 col-12">
                                    <input type="number" name="siblings_count" class="form-control corp-input font-monospace" 
                                           min="0" max="30" value="<?= htmlspecialchars((string)($student_profile['siblings_count'] ?? '0')) ?>" placeholder="0">
                                </div>
                            </div>

                            <div class="row align-items-center g-2">
                                <label class="col-sm-4 col-12 form-label-horizontal">Bil. Tanggungan / Anak</label>
                                <div class="col-sm-8 col-12">
                                    <input type="number" name="dependents_count" class="form-control corp-input font-monospace" 
                                           min="0" max="30" value="<?= htmlspecialchars((string)($student_profile['dependents_count'] ?? '0')) ?>" placeholder="0">
                                </div>
                            </div>

                            <div class="alert alert-light border border-light-subtle rounded-3 p-3 mt-2 text-muted small d-flex align-items-start gap-2">
                                <i class="ri-information-line text-primary fs-5 mt-0.5"></i>
                                <div>
                                    Maklumat waris akan digunakan oleh pihak pengurusan universiti bagi sebarang urusan rasmi pelajar sepanjang tempoh pengajian.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Butang Simpan -->
                <div class="d-flex justify-content-end align-items-center pt-4 mt-3 border-top">
                    <button type="submit" id="btnSaveFamily" class="btn btn-primary px-4 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center">
                        <i class="ri-save-3-line me-2 fs-5"></i> Simpan Maklumat Keluarga
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>