<?php
declare(strict_types=1);

class CoordinatorModel 
{
    private PDO $db;

    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }

    /**
     * Ambil tugasan menunggu pengesahan Penyelaras:
     * 1. Status portfolio = 3 (Telah disokong Penasihat)
     * 2. Fakulti pelajar sama dengan fakulti yang diselaraskan oleh staf ini
     */
    public function getPendingReviews(string $coordinatorStafId): array 
    {
        $sql = "SELECT a.*, 
                       u_stu.f_nama AS student_name,
                       u_stu.f_namajabatan AS faculty_name,
                       s.status_student, s.status_student_EN,
                       ass.advisor_id,
                       u_adv.f_nama AS advisor_name,
                       adv_rev.decision AS advisor_decision,
                       adv_rev.remarks AS advisor_remarks,
                       adv_rev.created_at AS advisor_reviewed_at
                FROM apelq_applications a
                -- Dapatkan profil pelajar untuk padanan kod fakulti
                INNER JOIN tbl_m_user u_stu 
                        ON a.student_id COLLATE utf8mb4_unicode_ci = u_stu.f_loginID
                -- Padankan dengan jadual Penyelaras Fakulti yang aktif
                INNER JOIN apelq_faculty_coordinators fc 
                        ON u_stu.f_jabatanKod = fc.faculty_code AND fc.status = 1
                LEFT JOIN lp_status s 
                        ON s.module_type = 'portfolio' AND s.status_code = a.portfolio_status_id
                LEFT JOIN apelq_application_assignments ass 
                        ON a.id = ass.application_id
                LEFT JOIN tbl_m_user u_adv 
                        ON ass.advisor_id COLLATE utf8mb4_unicode_ci = u_adv.f_stafID
                -- Ambil keputusan sokongan penasihat
                LEFT JOIN apelq_advisor_reviews adv_rev 
                        ON a.id = adv_rev.application_id
                WHERE fc.coordinator_id = :coord_id
                  AND a.portfolio_status_id = 3 -- Status: Disokong Penasihat
                ORDER BY a.portfolio_submitted_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':coord_id' => $coordinatorStafId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveReviewDecision(int $appId, string $studentId, string $coordinatorId, string $decision, string $remarks): bool 
    {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            // Simpan audit log
            $stmtAudit = $this->db->prepare("SET @app_user = ?");
            $stmtAudit->execute([$coordinatorId]);

            // recommend -> Status 4 (Lulus ke Penilaian/Senat), rework -> Status 2
            $newStatusId = ($decision === 'recommend') ? 4 : 2;

            $sqlRev = "INSERT INTO apelq_coordinator_reviews (application_id, student_id, coordinator_id, decision, remarks)
                       VALUES (?, ?, ?, ?, ?)";
            $stmtRev = $this->db->prepare($sqlRev);
            $stmtRev->execute([$appId, $studentId, $coordinatorId, $decision, $remarks]);

            $sqlApp = "UPDATE apelq_applications SET portfolio_status_id = ? WHERE id = ?";
            $stmtApp = $this->db->prepare($sqlApp);
            $stmtApp->execute([$newStatusId, $appId]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Coordinator saveReviewDecision Error: " . $e->getMessage());
            return false;
        }
    }

    public function getReviewHistory(string $coordinatorId): array 
    {
        $sql = "SELECT r.*, 
                       a.id AS app_id,
                       u_stu.f_nama AS student_name,
                       a.portfolio_attempt_count, 
                       a.portfolio_submitted_at
                FROM apelq_coordinator_reviews r
                JOIN apelq_applications a ON r.application_id = a.id
                LEFT JOIN tbl_m_user u_stu ON a.student_id COLLATE utf8mb4_unicode_ci = u_stu.f_loginID
                WHERE r.coordinator_id = ?
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$coordinatorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}