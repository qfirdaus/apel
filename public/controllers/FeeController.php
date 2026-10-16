<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/ApelqFee.php';

class FeeController {
    private ApelqFee $model;
    private PDO $pdoApel;

    public function __construct() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        
        $this->pdoApel = Database::pdoMysql();
        $this->pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->model = new ApelqFee($this->pdoApel);
    }

    public function getSettings(): array {
        $settings = $this->model->getActiveSettings();
        if (!$settings) {
            // Nilai default (fallback) jika database tetapan kosong
            return [
                'active_semester' => 'Tiada',
                'service_fee' => 0.00,
                'portfolio_fee' => 300.00
            ];
        }
        return $settings;
    }

    // Semak jika pelajar dah bayar Yuran Perkhidmatan
    public function checkGatekeeperServiceFee(string $studentId, string $semester): array {
        $payment = $this->model->getServiceFeePayment($studentId, $semester);
        
        // Jika tiada rekod langsung, atau rekod 'failed'
        if (!$payment || $payment['payment_status'] === 'failed') {
            return ['is_allowed' => false, 'status' => 'unpaid', 'data' => null];
        }

        // Jika resit sedang diproses
        if ($payment['payment_status'] === 'processing') {
            return ['is_allowed' => false, 'status' => 'processing', 'data' => $payment];
        }

        // Jika LULUS / BERJAYA
        if ($payment['payment_status'] === 'success') {
            return ['is_allowed' => true, 'status' => 'success', 'data' => $payment];
        }

        return ['is_allowed' => false, 'status' => 'unknown', 'data' => null];
    }

    // Semak status bayaran instrumen
    public function checkInstrumentPayment(string $studentId, string $semester, string $category = 'portfolio_fee'): array {
        $payment = $this->model->getInstrumentPayment($studentId, $semester, $category);
        
        if (!$payment || $payment['payment_status'] === 'failed') {
            return ['status' => 'unpaid', 'data' => null];
        }
        if ($payment['payment_status'] === 'processing') {
            return ['status' => 'processing', 'data' => $payment];
        }
        return ['status' => 'success', 'data' => $payment];
    }    
}