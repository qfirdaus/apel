<?php
class InformalLearning {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function saveWorkExperience(array $data): int {
        $sql = "INSERT INTO apelq_work_experience 
                (student_id, employer_name, contact_address, start_date, end_date, position_held, what_learned) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['student_id'] ?? '', 
            $data['employer_name'] ?? '', 
            $data['contact_address'] ?? '', 
            $data['start_date'], 
            $data['end_date'], 
            $data['position_held'] ?? '', 
            $data['what_learned'] ?? ''
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function saveEvidenceFiles(string $studentId, string $moduleType, int $recordId, array $files): void {
        $sql = "INSERT INTO apelq_evidence_files (student_id, module_type, record_id, file_name, file_path) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $uploadDir = __DIR__ . '/../pages/portfolio/informal/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $key => $name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
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

    public function getWorkExperience(string $studentId): array {
        $sql = "SELECT * FROM apelq_work_experience WHERE student_id = ? ORDER BY start_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    public function deleteWorkExperience(int $id): bool {
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

    public function updateWorkExperience(array $data): bool {
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
    
    public function getEvidenceFileById(int $fileId) {
        $stmt = $this->db->prepare("SELECT * FROM apelq_evidence_files WHERE id = ?");
        $stmt->execute([$fileId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteEvidenceFileById(int $fileId): bool {
        $stmt = $this->db->prepare("DELETE FROM apelq_evidence_files WHERE id = ?");
        return $stmt->execute([$fileId]);
    }    
}