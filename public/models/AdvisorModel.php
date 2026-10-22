<?php
declare(strict_types=1);

class AdvisorModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // FUNGSI TRIGGER AUDIT DB
    private function setAuditUser(?string $userId = null): void {
        if (empty($userId)) {
            $userId = (string)($_SESSION['f_stafID'] ?? 'Sistem');
        }
        $stmt = $this->db->prepare("SET @app_user = ?");
        $stmt->execute([$userId]);
    }
        
    /** senarai permohonan untuk semakan penasihat (Status 1: Submitted) */
    public function getPendingReviews(): array {
        $sql = "SELECT a.*, 
                       s.status_student, s.status_student_EN
                FROM apelq_applications a
                LEFT JOIN lp_status s ON s.module_type = 'portfolio' AND s.status_code = a.portfolio_status_id
                WHERE a.portfolio_status_id = 1
                ORDER BY a.portfolio_submitted_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Simpan keputusan penilaian dan kemaskini status permohonan*/
    public function saveReviewDecision(int $appId, string $studentId, string $advisorId, string $decision, string $remarks): bool {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            $this->setAuditUser($advisorId);

            // status (1 -> 3 jika Recommend, 1 -> 2 jika Rework)
            $newStatusId = ($decision === 'recommend') ? 3 : 2;

            $sqlRev = "INSERT INTO apelq_advisor_reviews (application_id, student_id, advisor_id, decision, remarks)
                       VALUES (?, ?, ?, ?, ?)";
            $stmtRev = $this->db->prepare($sqlRev);
            $stmtRev->execute([$appId, $studentId, $advisorId, $decision, $remarks]);

            $sqlApp = "UPDATE apelq_applications SET portfolio_status_id = ? WHERE id = ?";
            $stmtApp = $this->db->prepare($sqlApp);
            $stmtApp->execute([$newStatusId, $appId]);

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("AdvisorModel saveReviewDecision Error: " . $e->getMessage());
            return false;
        }
    }

    /** history */
    public function getReviewHistory(string $advisorId): array {
        $sql = "SELECT r.*, 
                       a.id AS app_id,
                       COALESCE(r.student_id, a.student_id) AS student_id,
                       a.portfolio_attempt_count, 
                       a.portfolio_submitted_at
                FROM apelq_advisor_reviews r
                JOIN apelq_applications a ON r.application_id = a.id
                WHERE r.advisor_id = ?
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$advisorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } 
}