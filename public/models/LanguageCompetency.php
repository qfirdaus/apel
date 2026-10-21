<?php
class LanguageCompetency {
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

    public function getLanguageList(): array {
        $stmt = $this->db->query("SELECT id, languageDesc FROM lp_language ORDER BY languageDesc ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSkillLevels(): array {
        $stmt = $this->db->query("SELECT id, levelDesc, levelDesc_EN FROM lp_levelskill ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveLanguage(array $data): bool {
        $this->setAuditUser($data['student_id']);
        
        $sql = "INSERT INTO apelq_language (student_id, application_id, language_id, reading_level, speaking_level, writing_level) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['student_id'], 
            $data['application_id'], 
            $data['language_id'], 
            $data['reading_level'], 
            $data['speaking_level'], 
            $data['writing_level']
        ]);
    }

    public function updateLanguage(array $data): bool {
        $this->setAuditUser($data['student_id']);
        $sql = "UPDATE apelq_language 
                SET language_id = ?, reading_level = ?, speaking_level = ?, writing_level = ? 
                WHERE id = ? AND student_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['language_id'], $data['reading_level'], $data['speaking_level'], $data['writing_level'], $data['record_id'], $data['student_id']
        ]);
    }

    public function deleteLanguage(int $id, string $studentId): bool {
        $this->setAuditUser($studentId);
        $sql = "DELETE FROM apelq_language WHERE id = ? AND student_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $studentId]);
    }

    public function getLanguageRecords(string $studentId, int $applicationId): array {
        $sql = "SELECT * FROM apelq_language WHERE student_id = ? AND application_id = ? ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $applicationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}