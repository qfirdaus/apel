<?php
declare(strict_types=1);

class AdminFinance {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getPendingReceipts(): array {
        $sql = "SELECT * FROM apelq_payments 
                WHERE payment_status = 'processing' 
                ORDER BY created_at ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function approveReceipt(int $paymentId, string $adminId): bool {
        try {
            $this->db->beginTransaction();

            // A. Dapatkan maklumat bayaran & Kunci baris ini sementara (FOR UPDATE)
            $stmt = $this->db->prepare("SELECT student_id FROM apelq_payments WHERE id = ? AND payment_status = 'processing' FOR UPDATE");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new Exception("Rekod resit tidak ditemui atau sudah diproses.");
            }
            $studentId = $payment['student_id'];

            $stmtCheck = $this->db->prepare("SELECT id FROM apelq_applications WHERE student_id = ? LIMIT 1");
            $stmtCheck->execute([$studentId]);
            $existingApp = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$existingApp) {
                $sqlMaster = "INSERT INTO apelq_applications (student_id, portfolio_status_id, portfolio_attempt_count) VALUES (?, 0, 1)";
                $this->db->prepare($sqlMaster)->execute([$studentId]);
                $appId = $this->db->lastInsertId();
            } else {
                $appId = $existingApp['id'];
            }

            $sqlUpdate = "UPDATE apelq_payments 
                          SET payment_status = 'success', application_id = ?, verified_by = ?, verified_at = NOW() 
                          WHERE id = ?";
            $this->db->prepare($sqlUpdate)->execute([$appId, $adminId, $paymentId]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function rejectReceipt(int $paymentId, string $adminId, string $remarks): bool {
        $sql = "UPDATE apelq_payments 
                SET payment_status = 'failed', verified_by = ?, verified_at = NOW(), remarks = ? 
                WHERE id = ? AND payment_status = 'processing'";
        return $this->db->prepare($sql)->execute([$adminId, $remarks, $paymentId]);
    }
}