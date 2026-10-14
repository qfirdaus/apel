<?php
class NonFormalLearning {
    private PDO $db;

    public const ALLOWED_EXT   = ['pdf', 'jpg', 'jpeg'];
    public const MAX_FILE_SIZE = 5 * 1024 * 1024;

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

    public function getDurationTypes(): array {
        $stmt = $this->db->query("SELECT id, label_name FROM lp_duration_type ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOutcomes(): array {
        $stmt = $this->db->query("SELECT outcomeDesc, outcomeDesc_EN FROM lp_outcome WHERE active_flag = 1 ORDER BY outcomeID ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    public function getTrainingCourses(string $studentId): array {
        $sql = "SELECT * FROM apelq_training_course WHERE student_id = ? ORDER BY course_date DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 

    public function saveTraining(array $data): int {
        $this->setAuditUser($data['student_id'] ?? null);

        $sql = "INSERT INTO apelq_training_course 
                (student_id, course_name, organizer, course_date, duration, duration_type_id, knowledge_desc, skills_learned) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['student_id'], 
            $data['course_name'], 
            $data['organizer'], 
            $data['course_date'], 
            $data['duration'], 
            $data['duration_type_id'], 
            $data['knowledge_desc'] ?? '', 
            $data['skills_learned'] // Disimpan sebagai format JSON
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateTraining(array $data): bool {
        $this->setAuditUser($data['student_id'] ?? null);

        $sql = "UPDATE apelq_training_course 
                SET course_name = ?, organizer = ?, course_date = ?, duration = ?, duration_type_id = ?, knowledge_desc = ?, skills_learned = ? 
                WHERE id = ? AND student_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['course_name'], 
            $data['organizer'], 
            $data['course_date'], 
            $data['duration'], 
            $data['duration_type_id'], 
            $data['knowledge_desc'] ?? '', 
            $data['skills_learned'],
            $data['record_id'],
            $data['student_id']
        ]);
    }

    public function deleteTraining(int $id, string $studentId): bool {
        $this->setAuditUser($studentId);

        $files = $this->getEvidenceFiles('TRAINING', $id);

        $this->db->beginTransaction();
        try {
            $stmtFile = $this->db->prepare("DELETE FROM apelq_evidence_files WHERE module_type = 'TRAINING' AND record_id = ?");
            $stmtFile->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM apelq_training_course WHERE id = ? AND student_id = ?");
            $stmt->execute([$id, $studentId]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        // Padam fail fizikal
        foreach ($files as $file) {
            $physicalPath = __DIR__ . '/../pages/portfolio/non-formal/' . $file['file_path'];
            if (is_file($physicalPath)) {
                @unlink($physicalPath);
            }
        }
        return true;
    }

    public function saveEvidenceFiles(string $studentId, string $moduleType, int $recordId, array $files): void {
        $this->setAuditUser($studentId);
        $sql = "INSERT INTO apelq_evidence_files (student_id, module_type, record_id, file_name, file_path) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $uploadDir = __DIR__ . '/../pages/portfolio/non-formal/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $key => $name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($ext, self::ALLOWED_EXT, true) || (int)$files['size'][$key] > self::MAX_FILE_SIZE) continue;
                    
                    $newName = strtolower($moduleType) . '_' . $recordId . '_' . time() . '_' . $key . '.' . $ext;
                    if (move_uploaded_file($files['tmp_name'][$key], $uploadDir . $newName)) {
                        $stmt->execute([$studentId, $moduleType, $recordId, $name, 'uploads/' . $newName]);
                    }
                }
            }
        }
    }

    public function getEvidenceFiles(string $moduleType, int $recordId): array {
        $stmt = $this->db->prepare("SELECT * FROM apelq_evidence_files WHERE module_type = ? AND record_id = ?");
        $stmt->execute([$moduleType, $recordId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteEvidenceFileById(int $fileId): bool {
        $this->setAuditUser();
        $stmt = $this->db->prepare("DELETE FROM apelq_evidence_files WHERE id = ?");
        return $stmt->execute([$fileId]);
    }
}