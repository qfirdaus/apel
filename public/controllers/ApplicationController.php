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
        return $this->model->getActiveApplication($studentId);
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
}