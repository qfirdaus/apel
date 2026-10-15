<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../controllers/SelfReflectionController.php';

try {    
    $controller = new SelfReflectionController();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_reflection') {
        $response = $controller->saveReflection($_POST);
    } else {
        $response = ['status' => 'error', 'message' => 'Tindakan tidak sah.'];
    }

    echo json_encode($response);
    exit;

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
