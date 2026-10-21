<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../models/AdminFinance.php';

class AdminFinanceController {
    private AdminFinance $model;
    private PDO $pdoApel;

    public function __construct() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $this->pdoApel = Database::pdoMysql();
        $this->pdoApel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new AdminFinance($this->pdoApel);
    }

    public function getPendingList(): array {
        return $this->model->getPendingReceipts();
    }
    
    public function getHistoryList(): array {
        return $this->model->getTransactionHistory();
    }

    public function processApproval(int $paymentId, string $adminId, string $action, string $remarks = ''): array {
        try {
            if ($action === 'approve') {
                $this->model->approveReceipt($paymentId, $adminId);
                return ['status' => 'success', 'message' => 'Resit diluluskan. Modul persediaan pelajar telah dibuka.'];
            
            } elseif ($action === 'reject') {
                if (empty(trim($remarks))) {
                    return ['status' => 'error', 'message' => 'Sila nyatakan sebab penolakan.'];
                }
                $this->model->rejectReceipt($paymentId, $adminId, $remarks);
                return ['status' => 'success', 'message' => 'Resit ditolak. Pelajar perlu membuat pembayaran baharu.'];
            }
            
            return ['status' => 'error', 'message' => 'Tindakan tidak sah.'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Ralat Pangkalan Data: ' . $e->getMessage()];
        }
    }
}