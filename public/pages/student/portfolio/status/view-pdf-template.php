<?php
// view-pdf-template.php
declare(strict_types=1);

$work       = $pdfData['work'] ?? [];
$other      = $pdfData['other'] ?? [];
$training   = $pdfData['training'] ?? [];
$lang       = $pdfData['language'] ?? [];
$reflection = $pdfData['reflection'] ?? [];
$ref        = $pdfData['referees'] ?? [];
$docs       = $pdfData['documents'] ?? [];
?>

<style>
    .apel-doc-container {
        font-family: 'Segoe UI', Arial, sans-serif;
        color: #111827;
        background: #ffffff;
        padding: 25px;
        line-height: 1.5;
    }

    /* HEADER STYLING */
    .apel-header {
        border-bottom: 2px solid #0f172a;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }
    .apel-title-main {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }
    .apel-title-sub {
        font-size: 0.9rem;
        color: #475569;
        font-weight: 600;
    }

    /* SECTION HEADER STYLING */
    .apel-section-header {
        background-color: #0f172a;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 8px 14px;
        margin-top: 30px;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 2px;
    }
    .apel-subsection-title {
        font-weight: 700;
        font-size: 0.88rem;
        color: #1e293b;
        margin-top: 20px;
        margin-bottom: 10px;
        border-bottom: 1px solid #cbd5e1;
        padding-bottom: 4px;
    }

    /* TABLE STYLING */
    .apel-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        font-size: 0.85rem;
    }
    .apel-table th {
        background-color: #f1f5f9;
        color: #0f172a;
        font-weight: 700;
        border: 1px solid #94a3b8;
        padding: 8px 10px;
        text-align: left;
        vertical-align: middle;
    }
    .apel-table td {
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        vertical-align: top;
    }
    .apel-table-striped tbody tr:nth-of-type(odd) {
        background-color: #f8fafc;
    }

    /* DETAILS BOX / REFLECTION STYLING */
    .apel-box-text {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 12px 15px;
        font-size: 0.85rem;
        border-radius: 4px;
        white-space: pre-line;
        margin-bottom: 15px;
    }

    .q-title {
        font-weight: 700;
        color: #0f172a;
        margin-top: 12px;
        margin-bottom: 6px;
        font-size: 0.86rem;
    }

    /* PRINT RULES */
    @media print {
        .apel-doc-container { padding: 0; }
        .apel-section-header { background-color: #0f172a !important; color: #ffffff !important; -webkit-print-color-adjust: exact; }
        .apel-table th { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
    }
</style>

<div class="apel-doc-container">

    <!-- PENGEPALA UTAMA (PORTFOLIO APEL) -->
    <div class="apel-header d-flex justify-content-between align-items-center">
        <div>
            <div class="apel-title-main">Portfolio APEL Q</div>
            <div class="apel-title-sub">Pengakreditan Pembelajaran Berasaskan Pengalaman Terdahulu</div>
        </div>
        <div class="text-end">
            <span class="badge bg-dark px-3 py-2 fs-6">ID Permohonan: APELQ-<?= h((string)$appId) ?></span>
        </div>
    </div>

    <!-- BAHAGIAN 1: MAKLUMAT PERIBADI -->
    <div class="apel-section-header">Bahagian 1: Maklumat Peribadi</div>
    <table class="apel-table">
        <tr>
            <th width="25%">Nama Pelajar</th>
            <td width="75%"><strong><?= h($studentId) ?></strong></td>
        </tr>
        <tr>
            <th>Status Permohonan</th>
            <td><span class="badge bg-primary px-2 py-1">Aktif / Dihantar</span></td>
        </tr>
        <tr>
            <th>Tarikh Cetakan</th>
            <td><?= date('d-M-Y h:i A') ?></td>
        </tr>
    </table>

    <!-- BAHAGIAN 2: BUTIRAN PEMBELAJARAN YANG DIPEROLEH -->
    <div class="apel-section-header">Bahagian 2: Butiran Pembelajaran Yang Diperoleh</div>

    <!-- 2.i Pengalaman Bekerja (Tidak Formal) -->
    <div class="apel-subsection-title">a. Pengalaman Bekerja (Pembelajaran Tidak Formal)</div>
    <table class="apel-table apel-table-striped">
        <thead>
            <tr>
                <th width="5%" class="text-center">Bil.</th>
                <th width="35%">Nama & Alamat Majikan</th>
                <th width="25%" class="text-center">Tempoh Berkhidmat</th>
                <th width="35%">Jawatan & Tanggungjawab</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($work)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Tiada maklumat pengalaman kerja direkodkan.</td></tr>
            <?php else: ?>
                <?php $bil = 1; foreach ($work as $w): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $bil++ ?></td>
                        <td>
                            <strong><?= h($w['company_name'] ?? '-') ?></strong>
                        </td>
                        <td class="text-center">
                            <?= !empty($w['start_date']) ? date('d-M-Y', strtotime($w['start_date'])) : '-' ?><br>
                            <span class="text-muted small">hingga</span><br>
                            <?= !empty($w['is_current']) ? 'Kini' : (!empty($w['end_date']) ? date('d-M-Y', strtotime($w['end_date'])) : '-') ?>
                        </td>
                        <td>
                            <strong><?= h($w['position_title'] ?? '-') ?></strong><br>
                            <span class="text-muted small"><?= nl2br(h($w['job_description'] ?? '-')) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 2.ii Aktiviti Pembelajaran Lain -->
    <div class="apel-subsection-title">b. Aktiviti Pembelajaran Lain</div>
    <table class="apel-table apel-table-striped">
        <thead>
            <tr>
                <th width="5%" class="text-center">Bil.</th>
                <th width="35%">Tajuk Aktiviti</th>
                <th width="25%" class="text-center">Tahun / Tempoh</th>
                <th width="35%">Hasil Pembelajaran</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($other)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Tiada aktiviti pembelajaran lain direkodkan.</td></tr>
            <?php else: ?>
                <?php $bil = 1; foreach ($other as $o): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $bil++ ?></td>
                        <td><strong><?= h($o['activity_name'] ?? '-') ?></strong></td>
                        <td class="text-center"><?= h((string)($o['year_activity'] ?? $o['activity_year'] ?? '-')) ?></td>
                        <td><?= nl2br(h($o['learning_outcome'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 2.iii Latihan / Seminar / Bengkel (Bukan Formal) -->
    <div class="apel-subsection-title">c. Pembelajaran Bukan Formal / Latihan / Seminar / Bengkel</div>
    <table class="apel-table apel-table-striped">
        <thead>
            <tr>
                <th width="5%" class="text-center">Bil.</th>
                <th width="30%">Nama / Tajuk Latihan / Kursus</th>
                <th width="25%">Penganjur & Tarikh</th>
                <th width="40%">Pengetahuan & Hasil Pembelajaran</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($training)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Tiada rekod kursus/latihan direkodkan.</td></tr>
            <?php else: ?>
                <?php $bil = 1; foreach ($training as $t): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $bil++ ?></td>
                        <td><strong><?= h($t['course_name'] ?? '-') ?></strong></td>
                        <td>
                            <?= h($t['organizer'] ?? '-') ?><br>
                            <span class="text-muted small"><?= !empty($t['course_date']) ? date('d-M-Y', strtotime($t['course_date'])) : '-' ?></span>
                        </td>
                        <td><?= nl2br(h($t['knowledge_desc'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 2.iv Kemahiran Bahasa -->
    <div class="apel-subsection-title">d. Kemahiran Bahasa</div>
    <table class="apel-table text-center">
        <thead>
            <tr>
                <th width="5%">Bil.</th>
                <th width="35%" class="text-start">Bahasa</th>
                <th width="20%">Membaca</th>
                <th width="20%">Bertutur</th>
                <th width="20%">Menulis</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($lang)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">Tiada rekod kemahiran bahasa direkodkan.</td></tr>
            <?php else: ?>
                <?php $bil = 1; foreach ($lang as $l): ?>
                    <tr>
                        <td class="fw-bold"><?= $bil++ ?></td>
                        <td class="text-start"><strong><?= h($l['languageDesc'] ?? $l['language_id'] ?? '-') ?></strong></td>
                        <td><?= h($l['read_desc'] ?? $l['reading_level'] ?? '-') ?></td>
                        <td><?= h($l['speak_desc'] ?? $l['speaking_level'] ?? '-') ?></td>
                        <td><?= h($l['write_desc'] ?? $l['writing_level'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 2.v Penilaian / Refleksi Diri -->
    <div class="apel-subsection-title">e. Penilaian / Refleksi Diri</div>
    <?php if (empty($reflection)): ?>
        <p class="text-muted fst-italic">Tiada rekod refleksi kendiri disimpan.</p>
    <?php else: ?>
        <div class="apel-box-text">
            <?= nl2br(h($reflection['reflection_text'] ?? '-')) ?>
        </div>
    <?php endif; ?>

    <!-- BAHAGIAN 3: MAKLUMAT REFEREE -->
    <div class="apel-section-header">Bahagian 3: Referee</div>
    <?php if (empty($ref)): ?>
        <p class="text-muted fst-italic">Tiada maklumat perujuk direkodkan.</p>
    <?php else: ?>
        <?php $bilRef = 1; foreach ($ref as $r): ?>
            <div class="apel-subsection-title">Maklumat Referee #<?= $bilRef++ ?></div>
            <table class="apel-table mb-3">
                <tr>
                    <th width="25%">Nama</th>
                    <td width="75%"><strong><?= h($r['referee_name'] ?? '-') ?></strong></td>
                </tr>
                <tr>
                    <th>Jawatan</th>
                    <td><?= h($r['position'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Organisasi</th>
                    <td><?= h($r['organization'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>No. Telefon / Bimbit</th>
                    <td><?= h($r['phone_no'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Emel</th>
                    <td><?= h($r['email'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Hubungan</th>
                    <td><?= h($r['relationship'] ?? '-') ?></td>
                </tr>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- BAHAGIAN 4: PENGAKUAN DIRI & TERMA SYARAT -->
    <div class="apel-section-header">Bahagian 4: Pengakuan Diri</div>
    <div class="apel-box-text mb-3">
        Saya dengan ini mengesahkan bahawa semua maklumat/dokumen yang disertakan untuk menyokong permohonan ini adalah asli, benar dan tepat. Saya memahami sepenuhnya terma dan syarat permohonan dan bersetuju bahawa permohonan saya akan ditolak jika saya telah memalsukan mana-mana maklumat dalam apa jua cara.
    </div>

    <table class="apel-table mb-4">
        <tr>
            <th width="25%">Nama Pelajar</th>
            <td width="75%"><strong><?= h($studentId) ?></strong></td>
        </tr>
        <tr>
            <th>Tarikh Penyerahan</th>
            <td><?= date('d-M-Y') ?></td>
        </tr>
    </table>

    <div class="small text-muted border-top pt-3">
        <strong>Syarat & Terma:</strong>
        <ol class="mb-0 ps-3">
            <li>Universiti diberi kuasa oleh pemohon untuk menggunakan maklumat atau data dalam permohonan ini sekiranya perlu.</li>
            <li>Dokumen yang bukan di dalam Bahasa Inggeris atau Bahasa Melayu perlu disertakan dengan satu terjemahan penuh yang telah sah diperakui.</li>
            <li>Setelah penghantaran lengkap portfolio dibuat, pemohon adalah diminta untuk menghadiri satu sesi temuduga/penilaian yang akan dimaklumkan kemudian.</li>
            <li>Yuran permohonan tidak akan dikembalikan.</li>
        </ol>
    </div>

</div>