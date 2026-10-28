<?php
declare(strict_types=1);

class ApelqApplication {
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

    public function getActiveApplication(string $studentId): ?array {
        $sql = "SELECT a.*,
                       s.status_student,
                       s.status_student_EN,
                       rev.reviewer_role AS latest_reviewer_role,
                       rev.reviewer_id AS latest_reviewer_id,
                       rev.reviewer_name AS latest_reviewer_name,
                       rev.remarks AS latest_rework_remarks,
                       rev.created_at AS latest_reviewed_at
                FROM apelq_applications a
                LEFT JOIN lp_status s
                       ON s.module_type = 'portfolio'
                      AND s.status_code = a.portfolio_status_id
                -- Ambil HANYA 1 ulasan semakan terkini (sama ada dari Penasihat atau Penyelaras)
                LEFT JOIN (
                    SELECT 
                        sub.application_id,
                        sub.reviewer_role,
                        sub.reviewer_id,
                        u.f_nama AS reviewer_name,
                        sub.remarks,
                        sub.created_at
                    FROM (
                        SELECT 
                            application_id,
                            'ADVISOR' AS reviewer_role,
                            advisor_id AS reviewer_id,
                            remarks,
                            created_at
                        FROM apelq_advisor_reviews
                        UNION ALL
                        SELECT 
                            application_id,
                            'COORDINATOR' AS reviewer_role,
                            coordinator_id AS reviewer_id,
                            remarks,
                            created_at
                        FROM apelq_coordinator_reviews
                    ) sub
                    LEFT JOIN tbl_m_user u ON sub.reviewer_id COLLATE utf8mb4_unicode_ci = u.f_stafID
                    INNER JOIN (
                        SELECT application_id, MAX(created_at) AS max_created
                        FROM (
                            SELECT application_id, created_at FROM apelq_advisor_reviews
                            UNION ALL
                            SELECT application_id, created_at FROM apelq_coordinator_reviews
                        ) all_rev
                        GROUP BY application_id
                    ) latest_t ON sub.application_id = latest_t.application_id AND sub.created_at = latest_t.max_created
                ) rev ON rev.application_id = a.id
                WHERE a.student_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function registerPortfolio(string $studentId): bool {
        $sql = "INSERT INTO apelq_applications (student_id, portfolio_status_id, portfolio_attempt_count) 
                VALUES (:student_id, 0, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['student_id' => $studentId]);
    }

    public function getAllApplications(string $studentId): array {
        $sql = "SELECT a.*,
                       s.status_student,
                       s.status_student_EN,
                       rev.reviewer_role AS latest_reviewer_role,
                       rev.reviewer_id AS latest_reviewer_id,
                       rev.reviewer_name AS latest_reviewer_name,
                       rev.remarks AS latest_rework_remarks,
                       rev.created_at AS latest_reviewed_at
                FROM apelq_applications a
                LEFT JOIN lp_status s
                       ON s.module_type = 'portfolio'
                      AND s.status_code = a.portfolio_status_id
                -- Ambil ulasan terkini (Penasihat atau Penyelaras)
                LEFT JOIN (
                    SELECT 
                        sub.application_id,
                        sub.reviewer_role,
                        sub.reviewer_id,
                        u.f_nama AS reviewer_name,
                        sub.remarks,
                        sub.created_at
                    FROM (
                        SELECT 
                            application_id,
                            'ADVISOR' AS reviewer_role,
                            advisor_id AS reviewer_id,
                            remarks,
                            created_at
                        FROM apelq_advisor_reviews
                        UNION ALL
                        SELECT 
                            application_id,
                            'COORDINATOR' AS reviewer_role,
                            coordinator_id AS reviewer_id,
                            remarks,
                            created_at
                        FROM apelq_coordinator_reviews
                    ) sub
                    LEFT JOIN tbl_m_user u ON sub.reviewer_id COLLATE utf8mb4_unicode_ci = u.f_stafID
                    INNER JOIN (
                        SELECT application_id, MAX(created_at) AS max_created
                        FROM (
                            SELECT application_id, created_at FROM apelq_advisor_reviews
                            UNION ALL
                            SELECT application_id, created_at FROM apelq_coordinator_reviews
                        ) all_rev
                        GROUP BY application_id
                    ) latest_t ON sub.application_id = latest_t.application_id AND sub.created_at = latest_t.max_created
                ) rev ON rev.application_id = a.id
                WHERE a.student_id = ?
                ORDER BY a.portfolio_attempt_count DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $isEnglish = (($_SESSION['lang'] ?? 'ms') === 'en' || (function_exists('__') && __('lang_code') === 'en'));

        foreach ($results as &$row) {
            $row['status_student_display'] = $isEnglish && !empty($row['status_student_EN']) 
                ? $row['status_student_EN'] 
                : ($row['status_student'] ?? 'Draf');
        }

        return $results;
    }

    public function getAdditionalDocuments(int $applicationId): array {
        $sql = "SELECT * FROM apelq_additional_documents 
                WHERE application_id = ? 
                ORDER BY uploaded_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$applicationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function insertAdditionalDocument(array $data): bool {
        $sql = "INSERT INTO apelq_additional_documents 
                (application_id, student_id, portfolio_attempt_count, document_title, document_remarks, file_path, original_file_name, file_size) 
                VALUES (:app_id, :student_id, :attempt_count, :title, :remarks, :file_path, :orig_name, :file_size)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'app_id'        => $data['application_id'],
            'student_id'    => $data['student_id'],
            'attempt_count' => $data['portfolio_attempt_count'],
            'title'         => $data['document_title'],
            'remarks'       => $data['document_remarks'],
            'file_path'     => $data['file_path'],
            'orig_name'     => $data['original_file_name'],
            'file_size'     => $data['file_size']
        ]);
    }

    public function deleteAdditionalDocument(int $docId, int $appId): bool {
        $sql = "DELETE FROM apelq_additional_documents WHERE id = ? AND application_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$docId, $appId]);
    }

    public function getFullPortfolioDetails(int $applicationId): array {
        $details = [];

        // 1. Pengalaman Kerja
        $stmt = $this->db->prepare("SELECT * FROM apelq_work_experience WHERE application_id = ? ORDER BY id DESC");
        $stmt->execute([$applicationId]);
        $details['work'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 2. Aktiviti Pembelajaran Lain
        $stmt = $this->db->prepare("SELECT * FROM apelq_other_activities WHERE application_id = ? ORDER BY id DESC");
        $stmt->execute([$applicationId]);
        $details['other'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 3. Kursus Latihan
        $stmt = $this->db->prepare("SELECT * FROM apelq_training_course WHERE application_id = ? ORDER BY id DESC");
        $stmt->execute([$applicationId]);
        $details['training'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 4. Kemahiran Bahasa (Tarikh nama Bahasa & Level sebenar)
        $sqlLang = "SELECT l.*, 
                           ref_l.languageDesc, 
                           ref_r.levelDesc AS read_desc, 
                           ref_s.levelDesc AS speak_desc, 
                           ref_w.levelDesc AS write_desc
                    FROM apelq_language l
                    LEFT JOIN lp_language ref_l ON l.language_id = ref_l.id
                    LEFT JOIN lp_levelskill ref_r ON l.reading_level = ref_r.id
                    LEFT JOIN lp_levelskill ref_s ON l.speaking_level = ref_s.id
                    LEFT JOIN lp_levelskill ref_w ON l.writing_level = ref_w.id
                    WHERE l.application_id = ?";
        $stmt = $this->db->prepare($sqlLang);
        $stmt->execute([$applicationId]);
        $details['language'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 5. Refleksi Kendiri
        $stmt = $this->db->prepare("SELECT * FROM apelq_self_reflection WHERE application_id = ? LIMIT 1");
        $stmt->execute([$applicationId]);
        $details['reflection'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // 6. Perujuk
        $stmt = $this->db->prepare("SELECT * FROM apelq_referees WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $details['referees'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 7. Dokumen Sokongan
        $stmt = $this->db->prepare("SELECT * FROM apelq_student_documents WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $details['documents'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $details;
    }  

    // get advisor / coordinator
    public function getApplicationAssignees(int $applicationId): ?array {
        if ($applicationId <= 0) {
            return null;
        }

        $sql = "SELECT a.*, 
                       u_adv.f_nama AS advisor_name
                FROM apelq_application_advisors a
                LEFT JOIN tbl_m_user u_adv ON a.advisor_id COLLATE utf8mb4_unicode_ci = u_adv.f_stafID
                WHERE a.application_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$applicationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /** checklist 7 modul portfolio */
    public function getPortfolioChecklistStatus(int $applicationId): array {
        $status = [
            'work' => false, 'other' => false, 'training' => false, 'language' => false, 
            'reflection' => false, 'referees' => false, 'documents' => false, 'all_complete' => false
        ];

        if ($applicationId <= 0) return $status;

        // 1. Pengalaman Kerja
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM apelq_work_experience WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['work'] = ($stmt->fetchColumn() > 0);

        // 2. Aktiviti Lain (Data wujud ATAU status = 0 'Tiada')
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM apelq_other_activities WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $otherCount = (int)$stmt->fetchColumn();

        $stmtFlag = $this->db->prepare("SELECT has_other_activities FROM apelq_other_activities_status WHERE application_id = ?");
        $stmtFlag->execute([$applicationId]);
        $otherFlag = $stmtFlag->fetchColumn();
        $status['other'] = ($otherCount > 0 || $otherFlag === 0);

        // 3. Kursus Latihan
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM apelq_training_course WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['training'] = ($stmt->fetchColumn() > 0);

        // 4. Kemahiran Bahasa
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM apelq_language WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['language'] = ($stmt->fetchColumn() > 0);

        // 5. Refleksi Kendiri
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM apelq_self_reflection WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['reflection'] = ($stmt->fetchColumn() > 0);

        // 6. Perujuk (Wajib sekurang-kurangnya 2 orang)
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM apelq_referees WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        $status['referees'] = ($stmt->fetchColumn() >= 2);

        // 7. Dokumen Sokongan
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM apelq_student_documents WHERE application_id = ? AND file_path IS NOT NULL");
        $stmt->execute([$applicationId]);
        $status['documents'] = ($stmt->fetchColumn() > 0);

        $status['all_complete'] = (
            $status['work'] && $status['other'] && $status['training'] && 
            $status['language'] && $status['reflection'] && $status['referees'] && $status['documents']
        );

        return $status;
    }

    /** Kemaskini status permohonan ke Status 1 (Dihantar) dengan audit trigger @app_use  */
    public function submitPortfolioStatus(int $applicationId, string $studentId): bool {
        try {
            $this->setAuditUser($studentId);

            $sql = "UPDATE apelq_applications 
                    SET portfolio_status_id = 1, 
                        portfolio_submitted_at = NOW() 
                    WHERE id = ? AND student_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$applicationId, $studentId]);
        } catch (Throwable $e) {
            error_log("ApelqApplication::submitPortfolioStatus Error: " . $e->getMessage());
            return false;
        }
    }  
}