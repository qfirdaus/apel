<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/AdvisorModel.php';

class AdvisorController {
    private AdvisorModel $model;
    private PDO $pdoApel;

    public function __construct() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $this->pdoApel = Database::pdoMysql();
        $this->pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new AdvisorModel($this->pdoApel);
    }

    public function getPendingReviews(): array {
        return $this->model->getPendingReviews();
    }

    public function getReviewHistory(string $advisorId): array {
        return $this->model->getReviewHistory($advisorId);
    }

    public function processDecision(array $postData, string $advisorId): array {
        $appId = (int)($postData['app_id'] ?? 0);
        $studentId = trim((string)($postData['student_id'] ?? ''));
        $decision = trim((string)($postData['decision'] ?? ''));
        $remarks = trim((string)($postData['remarks'] ?? ''));

        if ($appId <= 0 || empty($studentId) || !in_array($decision, ['recommend', 'rework'], true)) {
            return ['status' => 'error', 'message' => __('js_request_failed')];
        }

        if ($decision === 'rework' && empty($remarks)) {
            return ['status' => 'error', 'message' => __('js_fin_reject_required')];
        }

        $success = $this->model->saveReviewDecision($appId, $studentId, $advisorId, $decision, $remarks);

        if ($success) {
            return ['status' => 'success', 'message' => __('js_save_success')];
        }

        return ['status' => 'error', 'message' => __('js_system_error')];
    }
}