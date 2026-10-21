<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../controllers/AdminFinanceController.php';

try {    
    $controller = new AdminFinanceController();
    $action = $_POST['action'] ?? '';

    if ($action === 'admin_process_receipt') {
        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $approvalAction = $_POST['approval_action'] ?? ''; // 'approve' atau 'reject'
        $remarks = $_POST['remarks'] ?? '';
        $adminId = (string)($_SESSION['f_stafID'] ?? 'ADMIN');

        $response = $controller->processApproval($paymentId, $adminId, $approvalAction, $remarks);
        echo json_encode($response);
        exit;

    } else {
        $response = ['status' => 'error', 'message' => 'Tindakan tidak sah.'];
    }

    echo json_encode($response);
    exit;

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}