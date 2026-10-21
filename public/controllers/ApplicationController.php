<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/ApelqApplication.php';
require_once __DIR__ . '/../models/ApelqFee.php'; // Pastikan ini ada

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

    public function submitPortfolio(int $applicationId): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            if (empty($studentId) || $applicationId === 0) {
                return ['status' => 'error', 'message' => 'Sesi atau ID Permohonan tidak sah.'];
            }

            // COOLING PERIOD - Rayuan (Guna portfolio_attempt_count & portfolio_senate_date)
            $stmtApp = $this->pdoApel->prepare("SELECT portfolio_attempt_count, portfolio_senate_date FROM apelq_applications WHERE id = ?");
            $stmtApp->execute([$applicationId]);
            $appData = $stmtApp->fetch(PDO::FETCH_ASSOC);

            // Jika portfolio_attempt_count > 1 bermaksud ini adalah rayuan
            if ($appData && (int)$appData['portfolio_attempt_count'] > 1 && !empty($appData['portfolio_senate_date'])) {
                $senateDate = new DateTime($appData['portfolio_senate_date']);
                $today = new DateTime();
                $diff = $today->diff($senateDate)->days;

                if ($diff < 30) {
                    return ['status' => 'error', 'message' => 'Tempoh bertenang (Cooling Period) 30 hari belum tamat. Baki hari: ' . (30 - $diff) . ' hari.'];
                }
            }

            // CHECKLIST VALIDATIONS
            $errors = [];

            // Pengalaman Kerja
            $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_work_experience WHERE application_id = ?");
            $stmt->execute([$applicationId]);
            if ($stmt->fetchColumn() == 0) $errors[] = "Pengalaman Kerja belum diisi.";

            // Aktiviti Pembelajaran Lain (Sama ada ada rekod ATAU flag = 0)
            $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_other_activities WHERE application_id = ?");
            $stmt->execute([$applicationId]);
            $otherCount = $stmt->fetchColumn();
            
            $stmtFlag = $this->pdoApel->prepare("SELECT has_other_activities FROM apelq_other_activities_status WHERE application_id = ?");
            $stmtFlag->execute([$applicationId]);
            $otherFlag = $stmtFlag->fetchColumn();
            
            if ($otherCount == 0 && $otherFlag !== 0) {
                $errors[] = "Aktiviti Pembelajaran Lain belum diisi (Sila tanda 'Tiada' jika tiada).";
            }

            // Pembelajaran Non-Formal
            $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_training_course WHERE application_id = ?");
            $stmt->execute([$applicationId]);
            if ($stmt->fetchColumn() == 0) $errors[] = "Kursus Latihan (Non-Formal) belum diisi.";

            // Kemahiran Bahasa
            $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_language WHERE application_id = ?");
            $stmt->execute([$applicationId]);
            if ($stmt->fetchColumn() == 0) $errors[] = "Kemahiran Bahasa belum diisi.";

            // Refleksi Kendiri
            $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_self_reflection WHERE application_id = ?");
            $stmt->execute([$applicationId]);
            if ($stmt->fetchColumn() == 0) $errors[] = "Refleksi Kendiri belum diisi.";

            // Referees (Mesti 2 orang)
            $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_referees WHERE application_id = ?");
            $stmt->execute([$applicationId]);
            if ($stmt->fetchColumn() < 2) $errors[] = "Maklumat 2 orang Perujuk tidak lengkap.";

            // Dokumen Sokongan
            $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_student_documents WHERE application_id = ? AND file_path IS NOT NULL");
            $stmt->execute([$applicationId]);
            if ($stmt->fetchColumn() == 0) $errors[] = "Dokumen sokongan wajib belum dimuat naik.";

            // JIKA ADA ERROR PADA CHECKLIST
            if (!empty($errors)) {
                return [
                    'status' => 'error', 
                    'message' => 'Sila lengkapkan bahagian berikut sebelum menghantar:<br><ul class="text-start mt-2 mb-0"><li>' . implode('</li><li>', $errors) . '</li></ul>'
                ];
            }

            // Lengkap, STATUS 1 (DIHANTAR)
            // portfolio_status_id dan portfolio_submitted_at
            $updateSql = "UPDATE apelq_applications SET portfolio_status_id = 1, portfolio_submitted_at = NOW() WHERE id = ? AND student_id = ?";
            $updateStmt = $this->pdoApel->prepare($updateSql);
            $updateStmt->execute([$applicationId, $studentId]);

            return ['status' => 'success', 'message' => 'Portfolio anda telah berjaya dihantar! Modul kini dikunci untuk penilaian.'];

        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    } 
    
    public function getPortfolioChecklist(int $applicationId): array {
        $status = [
            'work' => false, 'other' => false, 'training' => false, 'language' => false, 
            'reflection' => false, 'referees' => false, 'documents' => false, 'all_complete' => false
        ];

        if ($applicationId <= 0) return $status;

        // 1. Work Experience
        $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_work_experience WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['work'] = ($stmt->fetchColumn() > 0);

        // 2. Other Activities (Ada data atau flag 'Tiada')
        $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_other_activities WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $otherCount = $stmt->fetchColumn();
        
        $stmtFlag = $this->pdoApel->prepare("SELECT has_other_activities FROM apelq_other_activities_status WHERE application_id = ?");
        $stmtFlag->execute([$applicationId]);
        $otherFlag = $stmtFlag->fetchColumn();
        $status['other'] = ($otherCount > 0 || $otherFlag === 0); 

        // 3. Training/Course
        $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_training_course WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['training'] = ($stmt->fetchColumn() > 0);

        // 4. Language Competency
        $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_language WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['language'] = ($stmt->fetchColumn() > 0);

        // 5. Self-Assessment/Reflection
        $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_self_reflection WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['reflection'] = ($stmt->fetchColumn() > 0);

        // 6. Referees (Mesti 2 orang)
        $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_referees WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['referees'] = ($stmt->fetchColumn() >= 2);

        // 7. Upload Other Documents
        $stmt = $this->pdoApel->prepare("SELECT COUNT(*) FROM apelq_student_documents WHERE application_id = ? AND file_path IS NOT NULL");
        $stmt->execute([$applicationId]);
        $status['documents'] = ($stmt->fetchColumn() > 0);

        $status['all_complete'] = ($status['work'] && $status['other'] && $status['training'] && $status['language'] && $status['reflection'] && $status['referees'] && $status['documents']);

        return $status;
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
}