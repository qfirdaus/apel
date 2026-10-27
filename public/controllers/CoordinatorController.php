<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
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
        $appId = (int)($postData['app_id'] ?? 0);
        $studentId = trim((string)($postData['student_id'] ?? ''));
        $decision = trim((string)($postData['decision'] ?? ''));
        $remarks = trim((string)($postData['remarks'] ?? ''));

        if ($appId <= 0 || empty($studentId) || !in_array($decision, ['recommend', 'rework'], true)) {
            return ['status' => 'error', 'message' => 'Maklumat tidak lengkap atau pilihan keputusan tidak sah.'];
        }

        if ($decision === 'rework' && empty($remarks)) {
            return ['status' => 'error', 'message' => 'Sila masukkan catatan ulasan untuk pindaan (rework).'];
        }

        $success = $this->model->saveReviewDecision($appId, $studentId, $coordinatorId, $decision, $remarks);

        if ($success) {
            return ['status' => 'success', 'message' => 'Keputusan semakan Penyelaras Fakulti berjaya direkodkan.'];
        }

        return ['status' => 'error', 'message' => 'Gagal menyimpan keputusan ke dalam pangkalan data.'];
    }
}