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

    public function getReflectionRecord(string $studentId): array {
        return $this->model->getReflection($studentId);
    }

    public function saveReflection(array $post): array {
        try {
            $studentId = trim((string)($_SESSION['f_stafID'] ?? ''));
            
            if (empty($studentId)) {
                return ['status' => 'error', 'message' => 'Sesi telah tamat. Sila log masuk semula.'];
            }
            
            $data = [
                'student_id' => $studentId,
                'q1_answer'  => $post['q1_answer'] ?? '',
                'q2_answer'  => $post['q2_answer'] ?? '',
                'q3_answer'  => $post['q3_answer'] ?? '',
                'q4_answer'  => $post['q4_answer'] ?? ''
            ];

            $success = $this->model->saveReflection($data);

            if ($success) {
                return ['status' => 'success', 'message' => ''];
            } else {
                return ['status' => 'error', 'message' => ''];
            }
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()];
        }
    }
}