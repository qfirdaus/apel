<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../../controllers/LanguageController.php';

try {    
    $controller = new LanguageController();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_language') {
        $response = $controller->addLanguage($_POST, $_FILES);
    } elseif ($action === 'update_language') {
        $response = $controller->updateLanguage($_POST, $_FILES);
    } elseif ($action === 'delete_language') {
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->deleteLanguage($id);
    } else {
        $response = ['status' => 'error', 'message' => 'Tindakan tidak sah.'];
    }

    echo json_encode($response);
    exit;

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}