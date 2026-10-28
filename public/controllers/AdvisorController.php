<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Mailer.php';
require_once __DIR__ . '/../models/AdvisorModel.php';

class AdvisorController {
    private AdvisorModel $model;
    private PDO $pdoApel;

    public function __construct() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->pdoApel = Database::pdoMysql();
        $this->pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new AdvisorModel($this->pdoApel);
    }

    public function getActiveRoleInfo(): array {
        return $this->model->getActiveRoleInfo();
    }    

    /** Senarai permohonan yang menunggu semakan penasihat (Status 1: Submitted) */
    public function getPendingReviews(string $advisorId): array {
        if (empty($advisorId)) {
            return [];
        }
        return $this->model->getPendingReviews($advisorId);
    }

    /** Sejarah semakan penasihat */
    public function getReviewHistory(string $advisorId): array {
        if (empty($advisorId)) {
            return [];
        }
        return $this->model->getReviewHistory($advisorId);
    }

    /** Keputusan penilaian (Recommend -> Status 3 / Rework -> Status 2) */
    public function processDecision(array $postData, string $advisorId): array {
        $appId     = (int)($postData['app_id'] ?? 0);
        $studentId = trim((string)($postData['student_id'] ?? ''));
        $decision  = trim((string)($postData['decision'] ?? ''));
        $remarks   = trim((string)($postData['remarks'] ?? ''));

        if ($appId <= 0 || empty($studentId) || !in_array($decision, ['recommend', 'rework'], true)) {
            return [
                'status'  => 'error', 
                'message' => 'Maklumat tidak lengkap atau keputusan tidak sah.'
            ];
        }

        if ($decision === 'rework' && empty($remarks)) {
            return [
                'status'  => 'error', 
                'message' => 'Sila masukkan ulasan/catatan jika keputusan adalah Rework.'
            ];
        }

        $success = $this->model->saveReviewDecision($appId, $studentId, $advisorId, $decision, $remarks);

        if ($success) {
            // 1. JIKA RECOMMEND: Hantar notifikasi emel kepada Penyelaras Fakulti
            if ($decision === 'recommend') {
                try {
                    $this->sendNotificationToCoordinators($appId, $studentId, $advisorId, $remarks);
                } catch (\Throwable $mailErr) {
                    error_log("Gagal hantar emel Penyelaras Fakulti: " . $mailErr->getMessage());
                }
            }

            // 2. JIKA REWORK: Hantar notifikasi emel pindaan kepada Pelajar
            if ($decision === 'rework') {
                try {
                    $this->sendNotificationToStudent($appId, $studentId, $advisorId, $remarks);
                } catch (\Throwable $mailErr) {
                    error_log("Gagal hantar emel Rework Pelajar: " . $mailErr->getMessage());
                }
            }

            return [
                'status'  => 'success', 
                'message' => 'Keputusan penilaian berjaya disimpan dan notifikasi telah dihantar.'
            ];
        }

        return [
            'status'  => 'error', 
            'message' => 'Gagal menyimpan keputusan ke dalam pangkalan data. Sila cuba lagi.'
        ];
    }

    /** Hantar notifikasi emel pindaan (Rework) kepada Pelajar */
    private function sendNotificationToStudent(int $appId, string $studentId, string $advisorId, string $advisorRemarks): void {
        $student = $this->model->getStudentProfile($studentId);
        if (!$student) {
            error_log("sendNotificationToStudent: Profil pelajar #{$studentId} tidak dijumpai.");
            return;
        }

        // Ambil nama Penasihat
        $stmtAdv = $this->pdoApel->prepare("SELECT f_nama FROM tbl_m_user WHERE TRIM(f_stafID) = :sid LIMIT 1");
        $stmtAdv->execute([':sid' => trim($advisorId)]);
        $advisorName = (string)($stmtAdv->fetchColumn() ?: $advisorId);

        $stmtTpl = $this->pdoApel->prepare("SELECT f_subjectTemplate, f_bodyHtml, f_bodyText FROM tbl_m_email_template WHERE f_templateCode = 'APELQ_ADVISOR_REWORK_STUDENT' AND (f_status = 'ACTIVE' OR f_status = 'PUBLISHED') LIMIT 1");
        $stmtTpl->execute();
        $tpl = $stmtTpl->fetch(PDO::FETCH_ASSOC);

        $actionLink = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $targetEmail = 'zaila@upnm.edu.my'; // Mode ujian: gantikan kepada (!empty($student['student_email']) ? $student['student_email'] : 'zaila@upnm.edu.my') untuk live

        $placeholders = [
            'student_name'    => $student['student_name'] ?? $studentId,
            'student_id'      => $studentId,
            'application_id'  => $appId,
            'advisor_name'    => $advisorName,
            'faculty_name'    => $student['faculty_name'] ?? '-',
            'advisor_remarks' => !empty($advisorRemarks) ? nl2br(htmlspecialchars($advisorRemarks)) : 'Sila kemaskini maklumat portfolio anda.',
            'review_date'     => date('d/m/Y H:i'),
            'action_link'     => $actionLink
        ];

        if ($tpl) {
            $subject  = $this->renderEmailTemplate((string)$tpl['f_subjectTemplate'], $placeholders);
            $htmlBody = $this->renderEmailTemplate((string)$tpl['f_bodyHtml'], $placeholders);
            $textBody = !empty($tpl['f_bodyText']) ? $this->renderEmailTemplate((string)$tpl['f_bodyText'], $placeholders) : null;
        } else {
            $subject = "[APEL.Q] Tindakan Pindaan Portfolio Diperlukan: {$student['student_name']} ({$studentId})";
            $htmlBody = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h3 style='color: #d97706;'>Pindaan Portfolio Diperlukan (Penasihat)</h3>
                    <p>Salam Sejahtera <strong>{$student['student_name']}</strong>,</p>
                    <p>Portfolio anda telah disemak oleh Penasihat Portfolio ({$advisorName}) dan dikembalikan untuk tindakan pindaan.</p>
                    <p><strong>Catatan Penasihat:</strong><br>" . nl2br(htmlspecialchars($advisorRemarks)) . "</p>
                    <p><a href='{$actionLink}' style='display:inline-block; padding:10px 18px; background-color:#d97706; color:#fff; text-decoration:none; border-radius:5px;'>Kemaskini Portfolio</a></p>
                </div>";
            $textBody = "Portfolio anda dikembalikan oleh Penasihat Portfolio untuk tindakan pindaan. Catatan: {$advisorRemarks}";
        }

        $opts = [
            'cc' => [
                'lavindastarz@gmail.com'
            ]
        ];

        Mailer::quickSend($this->pdoApel, $targetEmail, $subject, $htmlBody, $textBody, $opts);
    }

    /** Hantar notifikasi emel kepada Penyelaras Fakulti menggunakan templat dinamik */
    private function sendNotificationToCoordinators(int $appId, string $studentId, string $advisorId, string $advisorRemarks): void {
        $coordinators = $this->model->getCoordinatorsForApplication($appId);
        
        // Dapatkan nama penasihat
        $stmtAdv = $this->pdoApel->prepare("SELECT f_nama FROM tbl_m_user WHERE TRIM(f_stafID) = :sid LIMIT 1");
        $stmtAdv->execute([':sid' => trim($advisorId)]);
        $advisorName = (string)($stmtAdv->fetchColumn() ?: $advisorId);

        // Jika tiada rekod pemetaan fakulti dijumpai, hantar ke fallback admin/test
        if (empty($coordinators)) {
            error_log("sendNotificationToCoordinators: Tiada penyelaras fakulti aktif dijumpai untuk permohonan #{$appId}. Menghantar ke e-mel sandaran.");
            $coordinators = [[
                'coordinator_name'  => 'Penyelaras Fakulti',
                'coordinator_email' => 'zaila@upnm.edu.my',
                'student_name'      => $studentId,
                'faculty_name'      => 'Fakulti Berkaitan'
            ]];
        }

        $stmtTpl = $this->pdoApel->prepare("SELECT f_subjectTemplate, f_bodyHtml, f_bodyText FROM tbl_m_email_template WHERE f_templateCode = 'APELQ_ADVISOR_RECOMMENDED' AND (f_status = 'ACTIVE' OR f_status = 'PUBLISHED') LIMIT 1");
        $stmtTpl->execute();
        $tpl = $stmtTpl->fetch(PDO::FETCH_ASSOC);

        $actionLink = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $opts = [
            'cc' => [
                'lavindastarz@gmail.com'
            ]
        ];

        foreach ($coordinators as $row) {
            $coordinatorEmail = 'zaila@upnm.edu.my'; // Mode ujian: gantikan kepada (!empty($row['coordinator_email']) ? $row['coordinator_email'] : 'zaila@upnm.edu.my') untuk live
            $coordinatorName  = $row['coordinator_name'] ?? 'Penyelaras Fakulti';
            $studentName      = $row['student_name'] ?? $studentId;
            $facultyName      = $row['faculty_name'] ?? '-';

            $placeholders = [
                'recipient_name'  => $coordinatorName,
                'advisor_name'    => $advisorName,
                'student_name'    => $studentName,
                'student_id'      => $studentId,
                'application_id'  => $appId,
                'faculty_name'    => $facultyName,
                'advisor_remarks' => !empty($advisorRemarks) ? nl2br(htmlspecialchars($advisorRemarks)) : 'Tiada ulasan khusus.',
                'review_date'     => date('d/m/Y H:i'),
                'action_link'     => $actionLink
            ];

            if ($tpl) {
                $subject  = $this->renderEmailTemplate((string)$tpl['f_subjectTemplate'], $placeholders);
                $htmlBody = $this->renderEmailTemplate((string)$tpl['f_bodyHtml'], $placeholders);
                $textBody = !empty($tpl['f_bodyText']) ? $this->renderEmailTemplate((string)$tpl['f_bodyText'], $placeholders) : null;
            } else {
                $subject = "[APEL.Q] Portfolio Pelajar Disokong Penasihat: {$studentName} ({$studentId})";
                $htmlBody = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <h3 style='color: #8b1538;'>Perakuan Portfolio APEL.Q (Peringkat Fakulti)</h3>
                        <p>Salam Sejahtera <strong>{$coordinatorName}</strong>,</p>
                        <p>Portfolio bagi pelajar di bawah fakulti anda telah disemak dan <strong>DISOKONG</strong> oleh Penasihat Portfolio untuk tindakan perakuan Penyelaras Fakulti.</p>
                        <p><a href='{$actionLink}' style='display:inline-block; padding:10px 18px; background-color:#8b1538; color:#fff; text-decoration:none; border-radius:5px;'>Semak & Peraku Portfolio</a></p>
                    </div>";
                $textBody = "Portfolio pelajar {$studentName} ({$studentId}) telah disokong oleh Penasihat {$advisorName}.";
            }

            Mailer::quickSend($this->pdoApel, $coordinatorEmail, $subject, $htmlBody, $textBody, $opts);
        }
    }

    private function renderEmailTemplate(string $templateText, array $placeholders): string {
        foreach ($placeholders as $key => $value) {
            $templateText = str_replace(['{{' . $key . '}}', '{' . $key . '}'], (string)$value, $templateText);
        }
        return $templateText;
    }

    public function renderPortfolioPdfPreview(int $appId): array {
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