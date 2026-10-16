<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../../controllers/ApplicationController.php';

try {    
    $controller = new ApplicationController();
    $action = $_POST['action'] ?? '';    
    $studentId = (string)($_SESSION['f_stafID'] ?? '');

    if ($action === 'register_portfolio') {
        $response = $controller->processRegistrationFPX($studentId);

    }else if($action === 'register_portfolio_manual') {
        $file = $_FILES['receipt_file'] ?? null;
        if (!$file) {
            echo json_encode(['status' => 'error', 'message' => 'Tiada fail resit ditemui.']);
            exit;
        }

        $response = $controller->processRegistrationManual($studentId, $file);

    } else {
        $response = ['status' => 'error', 'message' => 'Tindakan tidak sah.'];
    }

    echo json_encode($response);
    exit;

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

