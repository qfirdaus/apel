<?php
declare(strict_types=1);
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/SelfReflection.php';

class SelfReflectionController {
    private SelfReflection $model;

    public function __construct() {        
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $pdoApel = Database::pdoMysql();
        $pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new SelfReflection($pdoApel);   
    }

    public function getReflectionRecord(string $studentId, int $applicationId = 0): array {
        return $this->model->getReflection($studentId, $applicationId); 
    }

    public function saveReflection(array $post): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            $appId = (int)($post['application_id'] ?? 0); 
            
            if (empty($studentId)) {
                return ['status' => 'error', 'message' => 'Sesi telah tamat. Sila log masuk semula.'];
            }
            
            if ($appId === 0) {
                return ['status' => 'error', 'message' => 'ID Permohonan tidak sah (0). Sila "Refresh" (F5) halaman ini.'];
            }
            
            $data = [
                'student_id'     => $studentId,
                'application_id' => $appId, 
                'q1_answer'      => $post['q1_answer'] ?? '',
                'q2_answer'      => $post['q2_answer'] ?? '',
                'q3_answer'      => $post['q3_answer'] ?? '',
                'q4_answer'      => $post['q4_answer'] ?? ''
            ];

            $success = $this->model->saveReflection($data);

            if ($success) {
                return ['status' => 'success', 'message' => 'Refleksi berjaya disimpan.'];
            } else {
                return ['status' => 'error', 'message' => 'Gagal menyimpan rekod.'];
            }
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }
}