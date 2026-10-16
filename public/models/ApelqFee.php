<?php
declare(strict_types=1);

class ApelqFee {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getActiveSettings(): ?array {
        $sql = "SELECT * FROM apelq_fee_settings ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getServiceFeePayment(string $studentId, string $semester): ?array {
        $sql = "SELECT * FROM apelq_payments 
                WHERE student_id = :student_id 
                AND semester = :semester 
                AND payment_category = 'service_fee' 
                ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'student_id' => $studentId, 
            'semester' => $semester
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // Semak status yuran instrumen (Portfolio/CT/dll)
    public function getInstrumentPayment(string $studentId, string $semester, string $category): ?array {
        $sql = "SELECT * FROM apelq_payments 
                WHERE student_id = :student_id 
                AND semester = :semester 
                AND payment_category = :category 
                ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'student_id' => $studentId, 
            'semester' => $semester,
            'category' => $category
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

}