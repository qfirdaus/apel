<?php
// Resolusi ID dan Gambar Pelajar
$studentIdVal = trim((string)($student_profile['student_id'] ?? $_SESSION['f_stafID'] ?? ''));
$studentPhotoUrl = "https://kemasukan.upnm.edu.my/tawaran/pelajar/student_image/" . rawurlencode($studentIdVal) . ".jpg";

// Data Header
$dispName        = !empty($student_profile['full_name']) ? $student_profile['full_name'] : '-';
$dispIc          = !empty($student_profile['ic_no']) ? $student_profile['ic_no'] : '-';
$dispMatrik      = $studentIdVal ?: '-';
$dispEmelAlFateh = !empty($student_profile['official_email']) ? $student_profile['official_email'] : ($studentIdVal ? "{$studentIdVal}@alfateh.upnm.edu.my" : '-');

// Data Akademik (100% Paparan Sahaja / Read-Only)
$dispFakulti     = !empty($student_profile['faculty_name']) ? $student_profile['faculty_name'] : '';
$dispPeringkat   = !empty($student_profile['study_level']) ? $student_profile['study_level'] : '';
$dispProgram     = !empty($student_profile['program_name']) ? $student_profile['program_name'] : '';
$dispKodProgram  = !empty($student_profile['program_code']) ? $student_profile['program_code'] : '';
$dispTempoh      = !empty($student_profile['study_duration']) ? $student_profile['study_duration'] : '';
$dispMod         = !empty($student_profile['study_mode']) ? $student_profile['study_mode'] : '';
$dispLaluan      = !empty($student_profile['study_track']) ? $student_profile['study_track'] : '';

$dispSesiMasuk   = !empty($student_profile['intake_session']) ? "Sesi {$student_profile['intake_session']}" : '';
$dispSaluran     = !empty($student_profile['intake_type']) ? $student_profile['intake_type'] : '';
$dispSemTerkini  = !empty($student_profile['current_semester']) ? $student_profile['current_semester'] : '';
$dispSesiTamat   = !empty($student_profile['end_session']) ? $student_profile['end_session'] : '-';
$dispStatusDaftar= !empty($student_profile['registration_status']) ? $student_profile['registration_status'] : '';
$dispTarikhDaftar= !empty($student_profile['registration_date']) ? $student_profile['registration_date'] : '';

$dispPNGS        = !empty($student_profile['gpa']) ? number_format((float)$student_profile['gpa'], 2) : '';
$dispPNGK        = !empty($student_profile['cgpa']) ? number_format((float)$student_profile['cgpa'], 2) : '';
$dispKredit      = !empty($student_profile['credit_hours']) ? "{$student_profile['credit_hours']} Jam Kredit" : '';
$dispPenasihat   = !empty($student_profile['academic_advisor']) ? $student_profile['academic_advisor'] : '';
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
                            <i class="ri-checkbox-circle-fill me-1"></i> Pendaftaran Aktif
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
                            <i class="ri-graduation-cap-line text-primary me-1.5 fs-5"></i>
                            <span class="text-secondary me-1">Program:</span>
                            <span class="fw-semibold text-dark font-monospace"><?= htmlspecialchars($dispKodProgram) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. KAD MAKLUMAT PROGRAM & PENGAJIAN (2 LAJUR READ-ONLY) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white section-panel-card">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark fs-6 d-inline-flex align-items-center">
                <span class="icon-chip me-2 text-primary bg-primary-subtle"><i class="ri-building-line"></i></span>
                Maklumat Program & Fakulti
            </h5>
            <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                <i class="ri-lock-line me-1 text-muted"></i> Data Tidak Boleh Dipinda
            </span>
        </div>

        <div class="card-body p-4 p-md-4">
            <div class="row g-4">
                <!-- LAJUR KIRI -->
                <div class="col-lg-6">
                    <div class="d-flex flex-column gap-3">
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Fakulti</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box fw-bold text-dark text-truncate" title="<?= htmlspecialchars($dispFakulti) ?>">
                                    <?= htmlspecialchars($dispFakulti) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Peringkat Pengajian</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= htmlspecialchars($dispPeringkat) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Program Pengajian</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box fw-semibold text-truncate" title="<?= htmlspecialchars($dispProgram) ?>">
                                    <?= htmlspecialchars($dispProgram) ?>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Kod Program</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box font-monospace fw-bold text-primary">
                                    <?= htmlspecialchars($dispKodProgram) ?>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>

                <!-- LAJUR KANAN -->
                <div class="col-lg-6">
                    <div class="d-flex flex-column gap-3">
                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Tempoh Pengajian</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= htmlspecialchars($dispTempoh) ?>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Mod Pembelajaran</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= htmlspecialchars($dispMod) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Laluan Kemasukan</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box font-monospace">
                                    <?= htmlspecialchars($dispLaluan) ?>
                                </div>
                            </div>
                        </div> -->

                        <!-- <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Penasihat Akademik</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box text-truncate" title="<?= htmlspecialchars($dispPenasihat) ?>">
                                    <i class="ri-user-star-line text-primary me-2"></i><?= htmlspecialchars($dispPenasihat) ?>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. KAD STATUS PENDAFTARAN & PRESTASI (2 LAJUR READ-ONLY) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white section-panel-card">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark fs-6 d-inline-flex align-items-center">
                <span class="icon-chip me-2 text-success bg-success-subtle"><i class="ri-medal-line"></i></span>
                Status Pendaftaran & Prestasi Semasa
            </h5>
            <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                <i class="ri-lock-line me-1 text-muted"></i> Data Tidak Boleh Dipinda
            </span>
        </div>

        <div class="card-body p-4 p-md-4">
            <div class="row g-4">
                <!-- LAJUR KIRI: STATUS PENDAFTARAN -->
                <div class="col-lg-6">
                    <div class="d-flex flex-column gap-3">
                        <div class="section-column-title mb-2">
                            <i class="ri-calendar-event-line me-1 text-primary"></i> Rekod Sesi & Kemasukan
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Sesi Kemasukan</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <?= htmlspecialchars($dispSesiMasuk) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Saluran Kemasukan</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box font-monospace">
                                    <?= htmlspecialchars($dispSaluran) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Semester Semasa</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box fw-semibold">
                                    <?= htmlspecialchars($dispSemTerkini) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Status Pengajian</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold">
                                        <i class="ri-check-line me-1"></i><?= htmlspecialchars($dispStatusDaftar) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LAJUR KANAN: PRESTASI & KREDIT 
                <div class="col-lg-6">
                    <div class="d-flex flex-column gap-3">
                        <div class="section-column-title mb-2">
                            <i class="ri-pie-chart-line me-1 text-primary"></i> Prestasi Akademik
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">PNGS (GPA)</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box font-monospace fw-bold text-dark fs-6">
                                    <?= htmlspecialchars($dispPNGS) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">PNGK (CGPA)</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box font-monospace fw-bold text-primary fs-6">
                                    <?= htmlspecialchars($dispPNGK) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Kredit Terkumpul</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box font-monospace">
                                    <?= htmlspecialchars($dispKredit) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center g-2">
                            <label class="col-sm-4 col-12 form-label-horizontal">Tarikh Lapor Diri</label>
                            <div class="col-sm-8 col-12">
                                <div class="corp-display-box">
                                    <i class="ri-time-line text-muted me-1.5"></i><?= htmlspecialchars($dispTarikhDaftar) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>

            <div class="alert alert-light border border-light-subtle rounded-3 p-3 mt-4 text-muted small d-flex align-items-start gap-2">
                <i class="ri-information-line text-primary fs-5 mt-0.5"></i>
                <div>
                    Sebarang pindaan pada maklumat program pengajian, status semester, atau pendaftaran kursus tertakluk kepada kelulusan Senat Universiti dan Bahagian Pengurusan Akademik (BPA).
                </div>
            </div>
        </div>
    </div>
</div>