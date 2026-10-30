<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/StudentFamilyModel.php';

class StudentFamilyController
{
    private StudentFamilyModel $model;
    private PDO $pdoMysql;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdoMysql = Database::pdoMysql();
        $this->pdoMysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new StudentFamilyModel($this->pdoMysql);
    }

    public function getFamily(string $studentId): ?array
    {
        if (empty($studentId)) {
            return null;
        }
        return $this->model->getStudentFamily($studentId);
    }

    public function saveFamily(array $postData, string $studentId): array
    {
        $studentId = trim($studentId);
        if ($studentId === '') {
            return ['status' => 'error', 'message' => 'Sesi pengguna tidak sah. Sila log masuk semula.'];
        }

        $postedCsrf  = (string)($postData['csrf_token'] ?? '');
        $sessionCsrf = (string)($_SESSION['csrf_token'] ?? '');
        if ($postedCsrf === '' || !hash_equals($sessionCsrf, $postedCsrf)) {
            return ['status' => 'error', 'message' => 'Token keselamatan tamat tempoh. Sila muat semula halaman.'];
        }

        $cleanData = [
            'father_name'         => trim((string)($postData['father_name'] ?? '')),
            'father_ic'           => trim((string)($postData['father_ic'] ?? '')),
            'father_phone'        => trim((string)($postData['father_phone'] ?? '')),
            'father_job'          => trim((string)($postData['father_job'] ?? '')),
            'father_income'       => trim((string)($postData['father_income'] ?? '')),
            'mother_name'         => trim((string)($postData['mother_name'] ?? '')),
            'mother_ic'           => trim((string)($postData['mother_ic'] ?? '')),
            'mother_phone'        => trim((string)($postData['mother_phone'] ?? '')),
            'mother_job'          => trim((string)($postData['mother_job'] ?? '')),
            'mother_income'       => trim((string)($postData['mother_income'] ?? '')),
            'spouse_name'         => trim((string)($postData['spouse_name'] ?? '')),
            'spouse_ic'           => trim((string)($postData['spouse_ic'] ?? '')),
            'spouse_phone'        => trim((string)($postData['spouse_phone'] ?? '')),
            'spouse_relationship' => trim((string)($postData['spouse_relationship'] ?? '')),
            'siblings_count'      => trim((string)($postData['siblings_count'] ?? '0')),
            'dependents_count'    => trim((string)($postData['dependents_count'] ?? '0'))
        ];

        $success = $this->model->updateStudentFamily($studentId, $cleanData);

        if ($success) {
            return [
                'status'  => 'success', 
                'message' => 'Maklumat keluarga & waris berjaya dikemas kini.'
            ];
        }

        return [
            'status'  => 'error', 
            'message' => 'Gagal mengemas kini maklumat keluarga. Sila cuba sebentar lagi.'
        ];
    }
}