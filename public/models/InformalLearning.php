<?php
class InformalLearning {
    private PDO $db;

    // Had fail bukti
    public const ALLOWED_EXT   = ['pdf', 'jpg', 'jpeg'];
    public const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

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

    // WORK EXPERIENCE
    public function saveWorkExperience(array $data): int {
        $this->setAuditUser($data['student_id'] ?? null);

        $sql = "INSERT INTO apelq_work_experience 
                (student_id, application_id, employer_name, contact_address, start_date, end_date, position_held, what_learned) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['student_id'] ?? '', 
            $data['application_id'],  
            $data['employer_name'] ?? '', 
            $data['contact_address'] ?? '', 
            $data['start_date'], 
            $data['end_date'], 
            $data['position_held'] ?? '', 
            $data['what_learned'] ?? ''
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateWorkExperience(array $data): bool {
        $this->setAuditUser($data['student_id'] ?? null);

        $sql = "UPDATE apelq_work_experience 
                SET employer_name = ?, contact_address = ?, start_date = ?, end_date = ?, position_held = ?, what_learned = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['employer_name'] ?? '', 
            $data['contact_address'] ?? '', 
            $data['start_date'], 
            $data['end_date'], 
            $data['position_held'] ?? '', 
            $data['what_learned'] ?? '',
            $data['record_id']
        ]);
    }

    public function deleteWorkExperience(int $id): bool {
        $this->setAuditUser(); // Akan ambil f_stafID dari session

        $files = $this->getEvidenceFiles('WORK', $id);
        foreach ($files as $file) {
            $physicalPath = __DIR__ . '/../' . $file['file_path']; 
            if (file_exists($physicalPath) && is_file($physicalPath)) {
                @unlink($physicalPath); 
            }
        }

        $sqlFile = "DELETE FROM apelq_evidence_files WHERE module_type = 'WORK' AND record_id = ?";
        $stmtFile = $this->db->prepare($sqlFile);
        $stmtFile->execute([$id]);

        $sql = "DELETE FROM apelq_work_experience WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }    

    // EVIDENCE FILES
    public function saveEvidenceFiles(string $studentId, string $moduleType, int $recordId, array $files): void {
        $this->setAuditUser($studentId); // Set Audit User

        $sql = "INSERT INTO apelq_evidence_files (student_id, module_type, record_id, file_name, file_path) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $uploadDir = __DIR__ . '/../pages/student/portfolio/informal/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $key => $name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($ext, self::ALLOWED_EXT, true) || (int)$files['size'][$key] > self::MAX_FILE_SIZE) {
                        continue;
                    }
                    $newName = strtolower($moduleType) . '_' . $recordId . '_' . time() . '_' . $key . '.' . $ext;
                    $dest = $uploadDir . $newName;
                    
                    if (move_uploaded_file($files['tmp_name'][$key], $dest)) {
                        $dbFilePath = 'uploads/' . $newName;
                        $stmt->execute([$studentId, $moduleType, $recordId, $name, $dbFilePath]);
                    }
                }
            }
        }
    }

    public function getEvidenceFiles(string $moduleType, int $recordId): array {
        $sql = "SELECT * FROM apelq_evidence_files WHERE module_type = ? AND record_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$moduleType, $recordId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEvidenceFileById(int $fileId) {
        $stmt = $this->db->prepare("SELECT * FROM apelq_evidence_files WHERE id = ?");
        $stmt->execute([$fileId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteEvidenceFileById(int $fileId): bool {
        $this->setAuditUser(); // Set Audit User
        $stmt = $this->db->prepare("DELETE FROM apelq_evidence_files WHERE id = ?");
        return $stmt->execute([$fileId]);
    }

    public function getWorkExperience(string $studentId, int $applicationId): array {
        $sql = "SELECT * FROM apelq_work_experience WHERE student_id = ? AND application_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $applicationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    // OTHER LEARNING ACTIVITIES
    public function getSkillLevels(): array {
        $stmt = $this->db->query("SELECT levelID, levelDesc, levelDescEN FROM lp_levelskillOther ORDER BY levelID");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveOtherActivity(array $data): int {
        $this->setAuditUser($data['student_id'] ?? null);

        $sql = "INSERT INTO apelq_other_activities
                (student_id, application_id, activity_name, start_date, end_date, organizer, skill_level, what_learned)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['student_id'],
            $data['application_id'], // [+] TAMBAH INI
            $data['activity_name'],
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['organizer'] ?? null,
            $data['skill_level'] ?? null,
            $data['what_learned'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getOtherActivities(string $studentId): array {
        $sql = "SELECT * FROM apelq_other_activities WHERE student_id = ? ORDER BY start_date DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOtherActivityById(int $id, string $studentId) {
        $stmt = $this->db->prepare("SELECT * FROM apelq_other_activities WHERE id = ? AND student_id = ?");
        $stmt->execute([$id, $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOtherActivity(array $data): bool {
        $this->setAuditUser($data['student_id'] ?? null); // Set Audit User

        $sql = "UPDATE apelq_other_activities
                SET activity_name = ?, start_date = ?, end_date = ?, organizer = ?, skill_level = ?, what_learned = ?
                WHERE id = ? AND student_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['activity_name'],
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['organizer'] ?? null,
            $data['skill_level'] ?? null,
            $data['what_learned'] ?? null,
            $data['record_id'],
            $data['student_id']
        ]);
    }

    public function deleteOtherActivity(int $id, string $studentId): bool {
        if (!$this->getOtherActivityById($id, $studentId)) {
            return false;
        }

        $this->setAuditUser($studentId);

        $this->db->beginTransaction();
        try {
            $stmtFile = $this->db->prepare("DELETE FROM apelq_evidence_files WHERE module_type = 'OTHER' AND record_id = ?");
            $stmtFile->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM apelq_other_activities WHERE id = ? AND student_id = ?");
            $stmt->execute([$id, $studentId]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }   

    // OTHER ACTIVITIES FLAG
    public function getOtherActivitiesFlag(string $studentId, int $applicationId): ?int {
        $stmt = $this->db->prepare("SELECT has_other_activities FROM apelq_other_activities_status WHERE student_id = ? AND application_id = ?");
        $stmt->execute([$studentId, $applicationId]);
        $result = $stmt->fetchColumn();
        return $result !== false && $result !== null ? (int)$result : null;
    }

    public function updateOtherActivitiesFlag(string $studentId, int $applicationId, int $flag): bool {
        $this->setAuditUser($studentId);

        $sql = "INSERT INTO apelq_other_activities_status (student_id, application_id, has_other_activities) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE has_other_activities = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$studentId, $applicationId, $flag, $flag]);
    }
}