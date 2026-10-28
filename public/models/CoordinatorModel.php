<?php
declare(strict_types=1);

class CoordinatorModel 
{
    private PDO $db;

    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }

    /** Audit user trigger */
    private function setAuditUser(string $userId): void 
    {
        $stmt = $this->db->prepare("SET @app_user = ?");
        $stmt->execute([$userId]);
    }

    /** get current role */
    public function getActiveRoleInfo(): array 
    {
        $groupId   = (int)($_SESSION['f_groupID'] ?? 0);
        $groupCode = trim((string)($_SESSION['f_groupKod'] ?? ''));

        try {
            $stmt = $this->db->prepare("SELECT f_groupID, f_groupKod, f_groupName, f_badge_class, f_color 
                                        FROM tbl_m_group 
                                        WHERE f_groupID = :gid OR f_groupKod = :gkod 
                                        LIMIT 1");
            $stmt->execute([
                ':gid'  => $groupId,
                ':gkod' => $groupCode
            ]);
            $group = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($group && !empty($group['f_groupName'])) {
                return [
                    'id'    => (int)$group['f_groupID'],
                    'code'  => (string)$group['f_groupKod'],
                    'name'  => (string)$group['f_groupName'],
                    'badge' => !empty($group['f_badge_class']) ? (string)$group['f_badge_class'] : 'bg-primary-subtle text-primary border border-primary-subtle',
                    'color' => !empty($group['f_color']) ? (string)$group['f_color'] : '#3b82f6'
                ];
            }
        } catch (\Throwable $e) {
            error_log("AdvisorModel::getActiveRoleInfo Error: " . $e->getMessage());
        }

        return [
            'id'    => $groupId,
            'code'  => $groupCode,
            'name'  => !empty($groupCode) ? $groupCode : 'Penasihat Portfolio',
            'badge' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'color' => '#3b82f6'
        ];
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
                       u_stu.f_jawatan AS program_name,
                       s.status_student, s.status_student_EN,
                       ass.advisor_id,
                       u_adv.f_nama AS advisor_name,
                       adv_rev.decision AS advisor_decision,
                       adv_rev.remarks AS advisor_remarks,
                       adv_rev.created_at AS advisor_reviewed_at
                FROM apelq_applications a
                INNER JOIN tbl_m_user u_stu 
                        ON a.student_id COLLATE utf8mb4_unicode_ci = u_stu.f_loginID
                INNER JOIN apelq_faculty_coordinators fc 
                        ON u_stu.f_jabatanKod = fc.faculty_code AND fc.status = 1
                LEFT JOIN lp_status s 
                        ON s.module_type = 'portfolio' AND s.status_code = a.portfolio_status_id
                LEFT JOIN apelq_application_advisors ass 
                        ON a.id = ass.application_id
                LEFT JOIN tbl_m_user u_adv 
                        ON ass.advisor_id COLLATE utf8mb4_unicode_ci = u_adv.f_stafID
                -- Ambil HANYA ulasan penasihat yang terkini bagi mengelak duplikasi baris
                LEFT JOIN (
                    SELECT r1.*
                    FROM apelq_advisor_reviews r1
                    INNER JOIN (
                        SELECT application_id, MAX(id) AS max_id
                        FROM apelq_advisor_reviews
                        GROUP BY application_id
                    ) r2 ON r1.id = r2.max_id
                ) adv_rev ON a.id = adv_rev.application_id
                WHERE fc.coordinator_id = :coord_id
                  AND a.portfolio_status_id = 3
                ORDER BY a.portfolio_submitted_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':coord_id' => $coordinatorStafId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStudentIdByAppId(int $appId): ?string 
    {
        $stmt = $this->db->prepare("SELECT student_id FROM apelq_applications WHERE id = ? LIMIT 1");
        $stmt->execute([$appId]);
        $res = $stmt->fetchColumn();
        return $res ? (string)$res : null;
    }

    public function saveReviewDecision(int $appId, string $studentId, string $coordinatorId, string $decision, string $remarks): array 
    {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            $stmtAudit = $this->db->prepare("SET @app_user = ?");
            $stmtAudit->execute([$coordinatorId]);

            $newStatusId = ($decision === 'recommend') ? 4 : 2;

            $sqlRev = "INSERT INTO apelq_coordinator_reviews (application_id, student_id, coordinator_id, decision, remarks)
                       VALUES (?, ?, ?, ?, ?)";
            $stmtRev = $this->db->prepare($sqlRev);
            $stmtRev->execute([$appId, $studentId, $coordinatorId, $decision, $remarks]);

            $sqlApp = "UPDATE apelq_applications SET portfolio_status_id = ? WHERE id = ?";
            $stmtApp = $this->db->prepare($sqlApp);
            $stmtApp->execute([$newStatusId, $appId]);

            $this->db->commit();
            return ['success' => true];

        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("CoordinatorModel saveReviewDecision Error: " . $e->getMessage());
            return [
                'success' => false, 
                'error'   => $e->getMessage()
            ];
        }
    }

    public function getReviewHistory(string $coordinatorId): array 
    {
        $sql = "SELECT r.*, 
                       a.id AS app_id,
                       u_stu.f_nama AS student_name,
                       u_stu.f_namajabatan AS faculty_name,
                       u_stu.f_jawatan AS program_name,
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

    /** count penilai aktif */
    public function countActiveAssessors(int $appId): int 
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM apelq_application_assessors WHERE application_id = ? AND status = 1");
        $stmt->execute([$appId]);
        return (int)$stmt->fetchColumn();
    }

    /** pelajar e-mel */
    public function getStudentProfile(string $studentId): ?array 
    {
        $stmt = $this->db->prepare("SELECT f_loginID AS student_id, f_nama AS student_name, f_email AS student_email, f_namajabatan AS faculty_name, f_jawatan AS program_name 
                                    FROM tbl_m_user 
                                    WHERE TRIM(f_loginID) = :sid LIMIT 1");
        $stmt->execute([':sid' => trim($studentId)]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }    
}