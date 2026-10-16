<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../../controllers/RefereesController.php';

try {    
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
