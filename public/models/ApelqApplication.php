<?php
declare(strict_types=1);

class ApelqApplication {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getActiveApplication(string $studentId): ?array {
        $sql = "SELECT a.*,
                       s.status_student,
                       s.status_student_EN
                FROM apelq_applications a
                LEFT JOIN lp_status s
                       ON s.module_type = 'portfolio'
                      AND s.status_code = a.portfolio_status_id
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
                       s.status_student_EN
                FROM apelq_applications a
                LEFT JOIN lp_status s
                       ON s.module_type = 'portfolio'
                      AND s.status_code = a.portfolio_status_id
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

}