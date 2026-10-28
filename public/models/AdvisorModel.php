<?php
declare(strict_types=1);

class AdvisorModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    private function setAuditUser(?string $userId = null): void {
        if (empty($userId)) {
            $userId = (string)($_SESSION['f_stafID'] ?? 'Sistem');
        }
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
            
    /** Senarai permohonan untuk semakan penasihat (Status 1: Submitted) */
    public function getPendingReviews(string $advisorId): array {
        $sql = "SELECT a.*, 
                       u_stu.f_nama AS student_name,
                       u_stu.f_namajabatan AS faculty_name,
                       u_stu.f_jawatan AS program_name, 
                       s.status_student, 
                       s.status_student_EN
                FROM apelq_applications a
                INNER JOIN apelq_application_advisors ass ON a.id = ass.application_id
                LEFT JOIN tbl_m_user u_stu ON a.student_id COLLATE utf8mb4_unicode_ci = u_stu.f_loginID
                LEFT JOIN lp_status s ON s.module_type = 'portfolio' AND s.status_code = a.portfolio_status_id
                WHERE a.portfolio_status_id = 1
                  AND ass.advisor_id = :adv_id
                ORDER BY a.portfolio_submitted_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':adv_id' => $advisorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Simpan keputusan penilaian dan kemaskini status permohonan */
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

    /** Sejarah semakan penasihat */
    public function getReviewHistory(string $advisorId): array {
        $sql = "SELECT r.*, 
                       a.id AS app_id,
                       COALESCE(r.student_id, a.student_id) AS student_id,
                       a.portfolio_attempt_count, 
                       a.portfolio_submitted_at,
                       u_stu.f_nama AS student_name,
                       u_stu.f_namajabatan AS faculty_name,
                       u_stu.f_jawatan AS program_name
                FROM apelq_advisor_reviews r
                JOIN apelq_applications a ON r.application_id = a.id
                LEFT JOIN tbl_m_user u_stu ON COALESCE(r.student_id, a.student_id) COLLATE utf8mb4_unicode_ci = u_stu.f_loginID
                WHERE r.advisor_id = ?
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$advisorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getStudentIdByAppId(int $appId): ?string {
        $stmt = $this->db->prepare("SELECT student_id FROM apelq_applications WHERE id = ? LIMIT 1");
        $stmt->execute([$appId]);
        $res = $stmt->fetchColumn();
        return $res ? (string)$res : null;
    } 

    /** Dapatkan profil pelajar untuk penghantaran e-mel Rework */
    public function getStudentProfile(string $studentId): ?array {
        $stmt = $this->db->prepare("SELECT f_loginID AS student_id, f_nama AS student_name, f_email AS student_email, f_namajabatan AS faculty_name, f_jawatan AS program_name 
                                    FROM tbl_m_user 
                                    WHERE TRIM(f_loginID) = :sid LIMIT 1");
        $stmt->execute([':sid' => trim($studentId)]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /** Dapatkan maklumat pelajar dan senarai penyelaras fakulti yang aktif */
    public function getCoordinatorsForApplication(int $appId): array {
        $sql = "SELECT a.id AS application_id, 
                       a.student_id, 
                       u_stu.f_nama AS student_name,
                       u_stu.f_jabatanKod AS faculty_code,
                       u_stu.f_namajabatan AS faculty_name,
                       fc.coordinator_id,
                       u_coor.f_nama AS coordinator_name,
                       u_coor.f_email AS coordinator_email
                FROM apelq_applications a
                JOIN tbl_m_user u_stu ON a.student_id COLLATE utf8mb4_unicode_ci = u_stu.f_loginID
                JOIN apelq_faculty_coordinators fc ON u_stu.f_jabatanKod = fc.faculty_code AND fc.status = 1
                JOIN tbl_m_user u_coor ON fc.coordinator_id COLLATE utf8mb4_unicode_ci = u_coor.f_stafID
                WHERE a.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$appId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}