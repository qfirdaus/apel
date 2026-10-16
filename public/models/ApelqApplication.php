<?php
declare(strict_types=1);

class ApelqApplication {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getActiveApplication(string $studentId): ?array {
        $sql = "SELECT a.*, s.status_student, s.status_staff 
                FROM apelq_applications a
                LEFT JOIN lp_status s ON a.portfolio_status_id = s.status_code AND s.module_type = 'portfolio'
                WHERE a.student_id = :student_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function registerPortfolio(string $studentId): bool {
        $sql = "INSERT INTO apelq_applications (student_id, portfolio_status_id, portfolio_attempt_count) 
                VALUES (:student_id, 0, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['student_id' => $studentId]);
    }
}