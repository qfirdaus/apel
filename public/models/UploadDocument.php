<?php
declare(strict_types=1);

class UploadDocument {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getStudentDocumentsList(string $studentId, int $applicationId): array {
        $sql = "SELECT dt.id AS doc_type_id, dt.document_name, 
                       sd.file_name, sd.file_path 
                FROM lp_document_types dt
                LEFT JOIN apelq_student_documents sd 
                       ON dt.id = sd.document_type_id 
                       AND sd.student_id = :student_id 
                       AND sd.application_id = :application_id 
                WHERE dt.is_active = 1
                ORDER BY dt.id ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'student_id' => $studentId,
            'application_id' => $applicationId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveDocumentRecord(array $data): bool {
        $sql = "INSERT INTO apelq_student_documents (student_id, application_id, document_type_id, file_name, file_path) 
                VALUES (:student_id, :application_id, :doc_type_id, :file_name, :file_path)
                ON DUPLICATE KEY UPDATE 
                file_name = VALUES(file_name),
                file_path = VALUES(file_path)";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'student_id'     => $data['student_id'],
            'application_id' => $data['application_id'], 
            'doc_type_id'    => $data['document_type_id'],
            'file_name'      => $data['file_name'],
            'file_path'      => $data['file_path']
        ]);
    }
}