<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../controllers/InformalLearningController.php';

try {    
    $controller = new InformalLearningController();

    $action = $_POST['action'] ?? '';

    if ($action === 'add_work') {
        $response = $controller->addWorkExperience($_POST, $_FILES);
        
    } elseif ($action === 'update_work') {
        $response = $controller->updateWorkExperience($_POST, $_FILES);

    } elseif ($action === 'delete_work') {
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->deleteWorkExperience($id);
        
    } elseif ($action === 'delete_file') {
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->deleteSingleEvidenceFile($id);

    } elseif ($action === 'add_other') {
        $response = $controller->addOtherActivity($_POST, $_FILES);

    } elseif ($action === 'update_other') {
        $response = $controller->updateOtherActivity($_POST, $_FILES);

    } elseif ($action === 'delete_other') {
        $id = (int)($_POST['id'] ?? 0);
        $response = $controller->deleteOtherActivity($id);

    } elseif ($action === 'update_other_flag') {
        $response = $controller->updateOtherActivitiesFlag($_POST);

    } else {
        $response = ['status' => 'error', 'message' => 'Tindakan (action) borang tidak sah.'];
    }

    echo json_encode($response);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}