<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../../controllers/RefereesController.php';
require_once __DIR__ . '/../../../../controllers/ApplicationController.php'; 

try {    
    $studentId = (string)($_SESSION['f_stafID'] ?? $_SESSION['student_id'] ?? '');
    $appController = new ApplicationController();
    $activeApp = $appController->getStudentApplication($studentId);
    
    if (!$activeApp) {
        echo json_encode(['status' => 'error', 'message' => 'Sesi permohonan tidak sah.']);
        exit;
    }
    
    $_POST['application_id'] = (int)$activeApp['id'];

    $controller = new RefereesController();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_referees') {
        $response = $controller->saveReferees($_POST);
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