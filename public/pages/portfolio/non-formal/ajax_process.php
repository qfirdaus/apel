<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../controllers/NonFormalController.php';

try {    
    $controller = new NonFormalController();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_training') {
        $response = $controller->addTraining($_POST, $_FILES);
    } elseif ($action === 'update_training') {
        $response = $controller->updateTraining($_POST, $_FILES);
    } elseif ($action === 'delete_training') {
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->deleteTraining($id);
    } elseif ($action === 'delete_file') {
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->deleteSingleEvidenceFile($id);
    } else {
        $response = ['status' => 'error', 'message' => 'Tindakan tidak sah.'];
    }

    echo json_encode($response);
    exit;

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}