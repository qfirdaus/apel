<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Mailer.php';
require_once __DIR__ . '/../classes/NotificationWorkflowService.php';
require_once __DIR__ . '/../models/ApelqApplication.php';
require_once __DIR__ . '/../models/ApelqFee.php'; 

class ApplicationController {
    private ApelqApplication $model;
    private PDO $pdoApel;

    public function __construct()
    {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $pdoStudent = Database::pdoSybaseStudent();
        if (!$pdoStudent instanceof PDO) {
            throw new RuntimeException('Sambungan Sybase Pelajar tidak tersedia.');
        }

        $this->pdoApel = Database::pdoMysql();
        $this->pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->model = new ApelqApplication($this->pdoApel);   
    }    

    public function getStudentApplication(string $studentId): ?array {
        $result = $this->model->getActiveApplication($studentId);
        
        if ($result) {
            $isEnglish = (($_SESSION['lang'] ?? 'ms') === 'en' || (function_exists('__') && __('lang_code') === 'en'));
            
            $result['status_student_display'] = $isEnglish && !empty($result['status_student_EN']) 
                ? $result['status_student_EN'] 
                : ($result['status_student'] ?? 'Sedang Disediakan');
        }

        return $result;
    }

    // --- KAEDAH A: FPX (Daftar HANYA JIKA Lulus) ---
    public function processRegistrationFPX(string $studentId): array {
        try {
            $feeModel = new ApelqFee($this->pdoApel);
            $settings = $feeModel->getActiveSettings();
            
            if (!$settings) {
                return ['status' => 'error', 'message' => 'Tetapan sistem belum dikonfigurasi.'];
            }
            
            $activeSemester = $settings['active_semester'];
            $amount = (float)$settings['portfolio_fee'];

            $existing = $this->model->getActiveApplication($studentId);
            if ($existing) {
                return ['status' => 'error', 'message' => 'Anda telah mendaftar Penilaian Portfolio.'];
            }

            // SIMULASI CALL API FPX: Di dunia nyata, ini bergantung kpd response bank.
            $isFpxSuccess = true; 
            
            if (!$isFpxSuccess) {
                $sql = "INSERT INTO apelq_payments (student_id, semester, payment_category, payment_method, amount, payment_status) 
                        VALUES (:student_id, :semester, 'portfolio_fee', 'fpx', :amount, 'failed')";
                
                $this->pdoApel->prepare($sql)->execute([
                    'student_id' => $studentId,
                    'semester' => $activeSemester,
                    'amount' => $amount
                ]);
                
                return ['status' => 'error', 'message' => 'Pembayaran FPX gagal atau dibatalkan. Pendaftaran tidak diteruskan.'];
            }

            if ($this->model->registerPortfolio($studentId)) {
                $appId = $this->pdoApel->lastInsertId(); 

                $sql = "INSERT INTO apelq_payments (application_id, student_id, semester, payment_category, payment_method, amount, payment_status) 
                        VALUES (:app_id, :student_id, :semester, 'portfolio_fee', 'fpx', :amount, 'success')";
                
                $this->pdoApel->prepare($sql)->execute([
                    'app_id' => $appId,
                    'student_id' => $studentId,
                    'semester' => $activeSemester,
                    'amount' => $amount
                ]);

                return ['status' => 'success', 'message' => 'Pembayaran berjaya! Pendaftaran disahkan.'];
            }
            
            return ['status' => 'error', 'message' => 'Ralat semasa menjana rekod pendaftaran.'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }

    // --- KAEDAH B: RESIT MANUAL (Daftar Bersyarat dengan Info Pengesahan) ---
    public function processRegistrationManual(string $studentId, array $file): array {
        try {
            $feeModel = new ApelqFee($this->pdoApel);
            $settings = $feeModel->getActiveSettings();
            
            if (!$settings) {
                return ['status' => 'error', 'message' => 'Tetapan sistem belum dikonfigurasi.'];
            }
            
            $activeSemester = $settings['active_semester'];
            $amount = (float)$settings['portfolio_fee'];

            $existing = $this->model->getActiveApplication($studentId);
            if ($existing) {
                return ['status' => 'error', 'message' => 'Anda telah mendaftar Penilaian Portfolio.'];
            }

            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                return ['status' => 'error', 'message' => 'Sila muat naik fail resit yang sah.'];
            }

            $uploadDir = __DIR__ . '/../pages/student/portfolio/registration/uploads/';
            if (!is_dir($uploadDir)) { 
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($file['name']));
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                return ['status' => 'error', 'message' => 'Gagal menyimpan resit ke pelayan. Pendaftaran dibatalkan.'];
            }

            $sql = "INSERT INTO apelq_payments (student_id, semester, payment_category, payment_method, amount, receipt_file, payment_status) 
                    VALUES (:student_id, :semester, 'portfolio_fee', 'manual', :amount, :receipt_file, 'processing')";
            
            $inserted = $this->pdoApel->prepare($sql)->execute([
                'student_id' => $studentId,
                'semester' => $activeSemester,
                'amount' => $amount,
                'receipt_file' => $fileName
            ]);

            if ($inserted) {
                return ['status' => 'success', 'message' => 'Resit berjaya dimuat naik dan sedang diproses.'];
            } else {
                if (file_exists($targetPath)) { unlink($targetPath); }
                return ['status' => 'error', 'message' => 'Ralat pangkalan data. Resit telah dipadam.'];
            }
            
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }

    public function getPortfolioChecklist(int $applicationId): array {
        return $this->model->getPortfolioChecklistStatus($applicationId);
    }

    public function submitPortfolio(int $applicationId): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            if (empty($studentId) || $applicationId <= 0) {
                return ['status' => 'error', 'message' => 'Sesi atau ID Permohonan tidak sah.'];
            }

            // COOLING PERIOD (RAYUAN)
            $activeApp = $this->model->getActiveApplication($studentId);
            if ($activeApp && (int)($activeApp['portfolio_attempt_count'] ?? 1) > 1 && !empty($activeApp['portfolio_senate_date'])) {
                $senateDate = new DateTime($activeApp['portfolio_senate_date']);
                $today = new DateTime();
                $diff = $today->diff($senateDate)->days;

                if ($diff < 30) {
                    return [
                        'status' => 'error', 
                        'message' => 'Tempoh bertenang (Cooling Period) 30 hari belum tamat. Baki hari: ' . (30 - $diff) . ' hari.'
                    ];
                }
            }

            // BLOCK LANTIKAN PEGAWAI (PENASIHAT)
            $assignees = $this->model->getApplicationAssignees($applicationId);
            $advisorId = trim((string)($assignees['advisor_id'] ?? ''));

            if (empty($advisorId)) {
                return [
                    'status' => 'error',
                    'message' => 'Portfolio tidak boleh dihantar kerana Penasihat belum dilantik untuk permohonan anda. Sila rujuk Pentadbir APEL.'
                ];
            }

            // CHECKLIST 7 MODUL
            $checklist = $this->model->getPortfolioChecklistStatus($applicationId);
            if (!$checklist['all_complete']) {
                $errors = [];
                if (!$checklist['work']) $errors[] = "Pengalaman Kerja belum diisi.";
                if (!$checklist['other']) $errors[] = "Aktiviti Pembelajaran Lain belum diisi (Sila tanda 'Tiada' jika tiada).";
                if (!$checklist['training']) $errors[] = "Kursus Latihan (Non-Formal) belum diisi.";
                if (!$checklist['language']) $errors[] = "Kemahiran Bahasa belum diisi.";
                if (!$checklist['reflection']) $errors[] = "Refleksi Kendiri belum diisi.";
                if (!$checklist['referees']) $errors[] = "Maklumat 2 orang Perujuk tidak lengkap.";
                if (!$checklist['documents']) $errors[] = "Dokumen sokongan wajib belum dimuat naik.";

                return [
                    'status' => 'error', 
                    'message' => 'Sila lengkapkan bahagian berikut sebelum menghantar:<br><ul class="text-start mt-2 mb-0"><li>' . implode('</li><li>', $errors) . '</li></ul>'
                ];
            }

            $updated = $this->model->submitPortfolioStatus($applicationId, $studentId);
            if ($updated) {
                // Hantar notifikasi emel kepada penasihat berserta CC
                try {
                    $this->sendPortfolioSubmittedEmailToAdvisor($advisorId, $studentId, $applicationId);
                } catch (\Throwable $mailErr) {
                    error_log("Emel notifikasi submit portfolio ke Penasihat gagal: " . $mailErr->getMessage());
                }

                return [
                    'status' => 'success', 
                    'message' => 'Portfolio anda telah berjaya dihantar! Modul kini dikunci untuk semakan Penasihat.'
                ];
            }

            return ['status' => 'error', 'message' => 'Gagal mengemaskini status permohonan.'];

        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }
    
    public function getAdditionalDocuments(int $applicationId): array {
        return $this->model->getAdditionalDocuments($applicationId);
    }

    /** Hantar (Status 2 -> Status 1) */
    public function resubmitReworkPortfolio(int $applicationId): array {
        try {
            $studentId = (string)($_SESSION['f_stafID'] ?? '');
            $app = $this->model->getActiveApplication($studentId);

            if (!$app || (int)$app['id'] !== $applicationId) {
                return ['status' => 'error', 'message' => 'Permohonan tidak sah.'];
            }

            if ((int)$app['portfolio_status_id'] !== 2) {
                return ['status' => 'error', 'message' => 'Permohonan ini bukan dalam status kemaskini/rework.'];
            }

            // status kembali ke 1 (Dihantar / Semakan Penasihat)
            $sql = "UPDATE apelq_applications 
                    SET portfolio_status_id = 1, portfolio_submitted_at = NOW() 
                    WHERE id = ? AND student_id = ?";
            $stmt = $this->pdoApel->prepare($sql);
            $stmt->execute([$applicationId, $studentId]);

            // Hantar emel semula kepada penasihat jika ia adalah hantaran pembetulan (rework)
            $assignees = $this->model->getApplicationAssignees($applicationId);
            $advisorId = trim((string)($assignees['advisor_id'] ?? ''));
            if (!empty($advisorId)) {
                try {
                    $this->sendPortfolioSubmittedEmailToAdvisor($advisorId, $studentId, $applicationId);
                } catch (\Throwable $mailErr) {
                    error_log("Emel resubmit portfolio ke Penasihat gagal: " . $mailErr->getMessage());
                }
            }

            return ['status' => 'success', 'message' => 'Portfolio yang dikemaskini telah berjaya dihantar semula!'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }
    
    public function getAllStudentApplications(string $studentId): array {
        return $this->model->getAllApplications($studentId);
    }

    public function uploadAdditionalDocument(int $applicationId, string $title, string $remarks, array $file): array {
        try {
            $studentId = (string)($_SESSION['f_stafID'] ?? '');
            if (empty($studentId) || $applicationId === 0) {
                return ['status' => 'error', 'message' => 'Sesi tidak sah.'];
            }

            // status 2 (Rework)
            $app = $this->model->getActiveApplication($studentId);
            if (!$app || (int)$app['id'] !== $applicationId) {
                return ['status' => 'error', 'message' => 'Permohonan tidak dijumpai.'];
            }

            if ((int)$app['portfolio_status_id'] !== 2) {
                return ['status' => 'error', 'message' => 'Muat naik dokumen hanya dibenarkan apabila permohonan berstatus "Dikembalikan untuk Kemaskini".'];
            }

            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                return ['status' => 'error', 'message' => 'Fail yang dimuat naik tidak sah.'];
            }

            $uploadDir = __DIR__ . '/../pages/student/portfolio/additional-document/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'ADD_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                return ['status' => 'error', 'message' => 'Gagal menyimpan fail ke pelayan.'];
            }

            $data = [
                'application_id'          => $applicationId,
                'student_id'              => $studentId,
                'portfolio_attempt_count' => (int)($app['portfolio_attempt_count'] ?? 1),
                'document_title'          => trim($title),
                'document_remarks'        => trim($remarks),
                'file_path'               => $fileName,
                'original_file_name'      => $file['name'],
                'file_size'               => (int)$file['size']
            ];

            if ($this->model->insertAdditionalDocument($data)) {
                return ['status' => 'success', 'message' => 'Dokumen tambahan berjaya dimuat naik!'];
            }

            if (file_exists($targetPath)) unlink($targetPath);
            return ['status' => 'error', 'message' => 'Gagal menyimpan rekod dokumen.'];

        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }    

    public function getPortfolioPdfData(int $applicationId): array {
        return $this->model->getFullPortfolioDetails($applicationId);
    }    

    // get advisor / coordinator
    public function getApplicationAssignees(int $applicationId): ?array {
        return $this->model->getApplicationAssignees($applicationId);
    }

    /* Hantar e-mel kepada Penasihat apabila pelajar menghantar portfolio semakan*/
    private function sendPortfolioSubmittedEmailToAdvisor(string $advisorStafId, string $studentId, int $applicationId): bool
    {
        try {
            // maklumat staf penasihat
            $stmtAdv = $this->pdoApel->prepare("SELECT f_nama, f_email FROM tbl_m_user WHERE TRIM(f_stafID) = :sid LIMIT 1");
            $stmtAdv->execute([':sid' => trim($advisorStafId)]);
            $advisor = $stmtAdv->fetch(PDO::FETCH_ASSOC);

            $namaPenasihat  = $advisor['f_nama'] ?? $advisorStafId;
            $emailPenasihat = 'zaila@upnm.edu.my'; // Mode test: ganti $advisor['f_email'] untuk live

            // maklumat pelajar
            $stmtStu = $this->pdoApel->prepare("SELECT f_nama, f_namajabatan FROM tbl_m_user WHERE TRIM(f_loginID) = :lid OR TRIM(f_stafID) = :sid LIMIT 1");
            $stmtStu->execute([':lid' => trim($studentId), ':sid' => trim($studentId)]);
            $student = $stmtStu->fetch(PDO::FETCH_ASSOC);

            $studentName = !empty($student['f_nama']) ? $student['f_nama'] : $studentId;
            $facultyName = $student['f_namajabatan'] ?? '-';

            // template emel dari pangkalan data
            $stmtTpl = $this->pdoApel->prepare("SELECT f_subjectTemplate, f_bodyHtml, f_bodyText FROM tbl_m_email_template WHERE f_templateCode = 'APELQ_PORTFOLIO_SUBMITTED' AND (f_status = 'ACTIVE' OR f_status = 'PUBLISHED') LIMIT 1");
            $stmtTpl->execute();
            $tpl = $stmtTpl->fetch(PDO::FETCH_ASSOC);

            if (!$tpl) {
                error_log("sendPortfolioSubmittedEmailToAdvisor: Template APELQ_PORTFOLIO_SUBMITTED tidak ditemui atau tidak aktif.");
                return false;
            }

            $actionLink = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $placeholders = [
                'recipient_name' => $namaPenasihat,
                'student_name'   => $studentName,
                'student_id'     => $studentId,
                'application_id' => $applicationId,
                'faculty_name'   => $facultyName,
                'submit_date'    => date('d/m/Y H:i'),
                'action_link'    => $actionLink
            ];

            $subject  = $this->renderEmailTemplate((string)$tpl['f_subjectTemplate'], $placeholders);
            $htmlBody = $this->renderEmailTemplate((string)$tpl['f_bodyHtml'], $placeholders);
            $textBody = !empty($tpl['f_bodyText']) ? $this->renderEmailTemplate((string)$tpl['f_bodyText'], $placeholders) : null;

            $opts = [
                'cc' => [
                    'lavindastarz@gmail.com'
                ]
            ];

            return Mailer::quickSend($this->pdoApel, $emailPenasihat, $subject, $htmlBody, $textBody, $opts);

        } catch (\Throwable $e) {
            error_log("sendPortfolioSubmittedEmailToAdvisor Error: " . $e->getMessage());
            return false;
        }
    }

    private function renderEmailTemplate(string $templateText, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $templateText = str_replace(['{{' . $key . '}}', '{' . $key . '}'], (string)$value, $templateText);
        }
        return $templateText;
    }
}