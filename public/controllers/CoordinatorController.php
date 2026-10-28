<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Mailer.php';
require_once __DIR__ . '/../models/CoordinatorModel.php';

class CoordinatorController 
{
    private CoordinatorModel $model;
    private PDO $pdoApel;

    public function __construct() 
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->pdoApel = Database::pdoMysql();
        $this->pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new CoordinatorModel($this->pdoApel);
    }

    public function getActiveRoleInfo(): array {
        return $this->model->getActiveRoleInfo();
    }       

    public function getPendingReviews(string $coordinatorId): array 
    {
        if (empty($coordinatorId)) return [];
        return $this->model->getPendingReviews($coordinatorId);
    }

    public function getReviewHistory(string $coordinatorId): array 
    {
        if (empty($coordinatorId)) return [];
        return $this->model->getReviewHistory($coordinatorId);
    }

    public function processDecision(array $postData, string $coordinatorId): array 
    {
        $appId     = (int)($postData['app_id'] ?? 0);
        $studentId = trim((string)($postData['student_id'] ?? ''));
        $decision  = trim((string)($postData['decision'] ?? ''));
        $remarks   = trim((string)($postData['remarks'] ?? ''));

        if ($appId <= 0 || empty($studentId) || !in_array($decision, ['recommend', 'rework'], true)) {
            return ['status' => 'error', 'message' => 'Maklumat tidak lengkap atau pilihan keputusan tidak sah.'];
        }

        // 1. SYARAT REWORK: Catatan ulasan adalah wajib
        if ($decision === 'rework' && empty($remarks)) {
            return ['status' => 'error', 'message' => 'Sila masukkan catatan ulasan untuk tindakan pindaan (Rework).'];
        }

        // 2. SYARAT RECOMMEND: Wajib ada panel penilai dilantik terlebih dahulu
        if ($decision === 'recommend') {
            $totalAssessors = $this->model->countActiveAssessors($appId);
            if ($totalAssessors === 0) {
                return [
                    'status'  => 'error',
                    'message' => 'Permohonan tidak boleh diperaku kerana Panel Penilai belum dilantik bagi pelajar ini. Sila hubungi Pentadbir/Lantik Panel Penilai terlebih dahulu, atau pilih keputusan Rework.'
                ];
            }
        }

        // Simpan keputusan ke pangkalan data
        $result = $this->model->saveReviewDecision($appId, $studentId, $coordinatorId, $decision, $remarks);

        if ($result['success'] ?? false) {
            // JIKA RECOMMEND: Hantar emel kepada semua Panel Penilai
            if ($decision === 'recommend') {
                try {
                    $this->sendNotificationToAssessors($appId, $studentId, $coordinatorId, $remarks);
                } catch (\Throwable $mailErr) {
                    error_log("Gagal hantar emel kepada Panel Penilai: " . $mailErr->getMessage());
                }
            }
            
            // JIKA REWORK: Hantar emel arahan pindaan kepada Pelajar
            if ($decision === 'rework') {
                try {
                    $this->sendNotificationToStudent($appId, $studentId, $coordinatorId, $remarks);
                } catch (\Throwable $mailErr) {
                    error_log("Gagal hantar emel Rework kepada Pelajar: " . $mailErr->getMessage());
                }
            }

            return ['status' => 'success', 'message' => 'Keputusan perakuan Penyelaras Fakulti berjaya disimpan dan notifikasi telah dihantar.'];
        }

        return [
            'status'  => 'error', 
            'message' => 'Gagal menyimpan keputusan: ' . ($result['error'] ?? 'Ralat pangkalan data.')
        ];
    }

    /** Hantar notifikasi emel kepada Pelajar apabila keputusan adalah REWORK */
    private function sendNotificationToStudent(int $appId, string $studentId, string $coordinatorId, string $coordinatorRemarks): void 
    {
        $student = $this->model->getStudentProfile($studentId);
        if (!$student) {
            error_log("sendNotificationToStudent: Profil pelajar #{$studentId} tidak dijumpai.");
            return;
        }

        // Ambil nama Penyelaras Fakulti
        $stmtCoord = $this->pdoApel->prepare("SELECT f_nama FROM tbl_m_user WHERE TRIM(f_stafID) = :sid LIMIT 1");
        $stmtCoord->execute([':sid' => trim($coordinatorId)]);
        $coordinatorName = (string)($stmtCoord->fetchColumn() ?: $coordinatorId);

        // Ambil templat emel APELQ_COORDINATOR_REWORK_STUDENT
        $stmtTpl = $this->pdoApel->prepare("SELECT f_subjectTemplate, f_bodyHtml, f_bodyText FROM tbl_m_email_template WHERE f_templateCode = 'APELQ_COORDINATOR_REWORK_STUDENT' AND (f_status = 'ACTIVE' OR f_status = 'PUBLISHED') LIMIT 1");
        $stmtTpl->execute();
        $tpl = $stmtTpl->fetch(PDO::FETCH_ASSOC);

        $actionLink = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $targetEmail = 'zaila@upnm.edu.my'; // Mode ujian: gantikan kepada (!empty($student['student_email']) ? $student['student_email'] : 'zaila@upnm.edu.my') untuk live

        $placeholders = [
            'student_name'        => $student['student_name'] ?? $studentId,
            'student_id'          => $studentId,
            'application_id'      => $appId,
            'coordinator_name'    => $coordinatorName,
            'faculty_name'        => $student['faculty_name'] ?? '-',
            'coordinator_remarks' => !empty($coordinatorRemarks) ? nl2br(htmlspecialchars($coordinatorRemarks)) : 'Sila semak dokumen portfolio anda.',
            'review_date'         => date('d/m/Y H:i'),
            'action_link'         => $actionLink
        ];

        if ($tpl) {
            $subject  = $this->renderEmailTemplate((string)$tpl['f_subjectTemplate'], $placeholders);
            $htmlBody = $this->renderEmailTemplate((string)$tpl['f_bodyHtml'], $placeholders);
            $textBody = !empty($tpl['f_bodyText']) ? $this->renderEmailTemplate((string)$tpl['f_bodyText'], $placeholders) : null;
        } else {
            $subject = "[APEL.Q] Tindakan Pindaan Portfolio Diperlukan: {$student['student_name']} ({$studentId})";
            $htmlBody = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h3 style='color: #dc2626;'>Pindaan Portfolio Diperlukan</h3>
                    <p>Salam Sejahtera <strong>{$student['student_name']}</strong>,</p>
                    <p>Portfolio anda telah disemak oleh Penyelaras Fakulti ({$coordinatorName}) dan dikembalikan untuk tindakan pindaan/pembetulan.</p>
                    <p><strong>Ulasan Pindaan:</strong><br>" . nl2br(htmlspecialchars($coordinatorRemarks)) . "</p>
                    <p><a href='{$actionLink}' style='display:inline-block; padding:10px 18px; background-color:#dc2626; color:#fff; text-decoration:none; border-radius:5px;'>Log Masuk Portal APEL.Q</a></p>
                </div>";
            $textBody = "Portfolio anda dikembalikan oleh Penyelaras Fakulti untuk pindaan. Ulasan: {$coordinatorRemarks}";
        }

        $opts = [
            'cc' => [
                'lavindastarz@gmail.com'
            ]
        ];

        Mailer::quickSend($this->pdoApel, $targetEmail, $subject, $htmlBody, $textBody, $opts);
    }

    /** Hantar notifikasi emel kepada semua Panel Penilai berdaftar bagi permohonan */
    private function sendNotificationToAssessors(int $appId, string $studentId, string $coordinatorId, string $coordinatorRemarks): void 
    {
        $assessors = $this->model->getAssessorsForApplication($appId);
        if (empty($assessors)) {
            error_log("sendNotificationToAssessors: Tiada panel penilai aktif untuk permohonan #{$appId}");
            return;
        }

        // Ambil nama Penyelaras Fakulti
        $stmtCoord = $this->pdoApel->prepare("SELECT f_nama FROM tbl_m_user WHERE TRIM(f_stafID) = :sid LIMIT 1");
        $stmtCoord->execute([':sid' => trim($coordinatorId)]);
        $coordinatorName = (string)($stmtCoord->fetchColumn() ?: $coordinatorId);

        // Ambil profil pelajar
        $stmtStu = $this->pdoApel->prepare("SELECT f_nama, f_namajabatan, f_jawatan FROM tbl_m_user WHERE TRIM(f_loginID) = :lid LIMIT 1");
        $stmtStu->execute([':lid' => trim($studentId)]);
        $student = $stmtStu->fetch(PDO::FETCH_ASSOC);

        $studentName = $student['f_nama'] ?? $studentId;
        $facultyName = $student['f_namajabatan'] ?? '-';
        $programName = $student['f_jawatan'] ?? '-';

        // Ambil templat emel APELQ_COORDINATOR_ENDORSED_ASSESSOR
        $stmtTpl = $this->pdoApel->prepare("SELECT f_subjectTemplate, f_bodyHtml, f_bodyText FROM tbl_m_email_template WHERE f_templateCode = 'APELQ_COORDINATOR_ENDORSED_ASSESSOR' AND (f_status = 'ACTIVE' OR f_status = 'PUBLISHED') LIMIT 1");
        $stmtTpl->execute();
        $tpl = $stmtTpl->fetch(PDO::FETCH_ASSOC);

        $actionLink = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $opts = [
            'cc' => [
                'lavindastarz@gmail.com'
            ]
        ];

        foreach ($assessors as $row) {
            $assessorEmail = ($row['assessor_type'] === 'INTERNAL' && !empty($row['internal_email'])) 
                ? (string)$row['internal_email'] 
                : (string)$row['assessor_email'];
            
            $assessorName = ($row['assessor_type'] === 'INTERNAL' && !empty($row['internal_name'])) 
                ? (string)$row['internal_name'] 
                : (string)$row['assessor_name'];

            $targetEmail = 'zaila@upnm.edu.my'; // Mode ujian: gantikan kepada $assessorEmail untuk live

            $placeholders = [
                'recipient_name'      => $assessorName,
                'assessor_role'       => $row['assessor_role'] ?? 'Panel Penilai',
                'coordinator_name'    => $coordinatorName,
                'student_name'        => $studentName,
                'student_id'          => $studentId,
                'application_id'      => $appId,
                'faculty_name'        => $facultyName,
                'program_name'        => $programName,
                'coordinator_remarks' => !empty($coordinatorRemarks) ? nl2br(htmlspecialchars($coordinatorRemarks)) : 'Tiada ulasan khusus.',
                'endorse_date'        => date('d/m/Y H:i'),
                'action_link'         => $actionLink
            ];

            if ($tpl) {
                $subject  = $this->renderEmailTemplate((string)$tpl['f_subjectTemplate'], $placeholders);
                $htmlBody = $this->renderEmailTemplate((string)$tpl['f_bodyHtml'], $placeholders);
                $textBody = !empty($tpl['f_bodyText']) ? $this->renderEmailTemplate((string)$tpl['f_bodyText'], $placeholders) : null;
            } else {
                $subject = "[APEL.Q] Portfolio Pelajar Sedia Untuk Penilaian: {$studentName} ({$studentId})";
                $htmlBody = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <h3 style='color: #8b1538;'>Penilaian Portfolio Pelajar APEL.Q</h3>
                        <p>Salam Sejahtera <strong>{$assessorName}</strong>,</p>
                        <p>Portfolio bagi permohonan pelajar berikut telah diperakukan oleh Penyelaras Fakulti dan kini sedia untuk penilaian panel.</p>
                        <p><a href='{$actionLink}' style='display:inline-block; padding:10px 18px; background-color:#8b1538; color:#fff; text-decoration:none; border-radius:5px;'>Log Masuk Portal</a></p>
                    </div>";
                $textBody = "Portfolio pelajar {$studentName} telah diperakukan oleh Penyelaras Fakulti.";
            }

            Mailer::quickSend($this->pdoApel, $targetEmail, $subject, $htmlBody, $textBody, $opts);
        }
    }

    private function renderEmailTemplate(string $templateText, array $placeholders): string 
    {
        foreach ($placeholders as $key => $value) {
            $templateText = str_replace(['{{' . $key . '}}', '{' . $key . '}'], (string)$value, $templateText);
        }
        return $templateText;
    }

    public function renderPortfolioPdfPreview(int $appId): array 
    {
        if ($appId <= 0) {
            return ['status' => 'error', 'message' => 'ID Permohonan tidak sah.'];
        }

        $studentId = $this->model->getStudentIdByAppId($appId);
        if (!$studentId) {
            return ['status' => 'error', 'message' => 'Permohonan tidak dijumpai.'];
        }

        require_once __DIR__ . '/ApplicationController.php';
        $appController = new ApplicationController();
        $pdfData = $appController->getPortfolioPdfData($appId);

        $templatePath = dirname(__DIR__) . '/pages/student/portfolio/status/view-pdf-template.php';
        if (!file_exists($templatePath)) {
            return ['status' => 'error', 'message' => 'Fail templat paparan PDF tidak ditemui.'];
        }

        ob_start();
        include $templatePath;
        $html = ob_get_clean();

        return ['status' => 'success', 'html' => $html];
    }
}