<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
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

    /** senarai permohonan yang menunggu semakan penasihat (Status 1: Submitted)*/
    public function getPendingReviews(): array {
        return $this->model->getPendingReviews();
    }

    /** history */
    public function getReviewHistory(string $advisorId): array {
        if (empty($advisorId)) {
            return [];
        }
        return $this->model->getReviewHistory($advisorId);
    }

    /** keputusan penilaian (Recommend -> Status 3 / Rework -> Status 2)*/
    public function processDecision(array $postData, string $advisorId): array {
        $appId = (int)($postData['app_id'] ?? 0);
        $studentId = trim((string)($postData['student_id'] ?? ''));
        $decision = trim((string)($postData['decision'] ?? ''));
        $remarks = trim((string)($postData['remarks'] ?? ''));

        // Validasi input asas
        if ($appId <= 0 || empty($studentId) || !in_array($decision, ['recommend', 'rework'], true)) {
            return [
                'status' => 'error', 
                'message' => 'Maklumat tidak lengkap atau keputusan tidak sah.'
            ];
        }

        // Jika keputusan Rework, ulasan/catatan adalah WAJIB
        if ($decision === 'rework' && empty($remarks)) {
            return [
                'status' => 'error', 
                'message' => 'Sila masukkan ulasan/catatan jika keputusan adalah Rework.'
            ];
        }

        // Hantar ke Model untuk simpan ulasan & kemaskini status
        $success = $this->model->saveReviewDecision($appId, $studentId, $advisorId, $decision, $remarks);

        if ($success) {
            return [
                'status' => 'success', 
                'message' => 'Keputusan penilaian berjaya disimpan.'
            ];
        }

        return [
            'status' => 'error', 
            'message' => 'Gagal menyimpan keputusan ke dalam pangkalan data. Sila cuba lagi.'
        ];
    }
}