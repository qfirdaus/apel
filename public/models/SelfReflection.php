<?php
class SelfReflection {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getReflection(string $studentId): array {
        $stmt = $this->db->prepare("SELECT * FROM apelq_self_reflection WHERE student_id = :student_id LIMIT 1");
        $stmt->execute(['student_id' => $studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: [];
    }

    // Fungsi UPSERT untuk menyimpan atau mengemas kini rekod
    public function saveReflection(array $data): bool {
        $sql = "INSERT INTO apelq_self_reflection (student_id, q1_answer, q2_answer, q3_answer, q4_answer) 
                VALUES (:student_id, :q1, :q2, :q3, :q4)
                ON DUPLICATE KEY UPDATE 
                q1_answer = VALUES(q1_answer),
                q2_answer = VALUES(q2_answer),
                q3_answer = VALUES(q3_answer),
                q4_answer = VALUES(q4_answer)";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'student_id' => $data['student_id'],
            'q1'         => $data['q1_answer'],
            'q2'         => $data['q2_answer'],
            'q3'         => $data['q3_answer'],
            'q4'         => $data['q4_answer']
        ]);
    }
}