<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../includes/functions-page.php';
require_once __DIR__ . '/../../../../controllers/StudentPersonalController.php';

try {
    $studentId = (string)($_SESSION['f_loginID'] ?? $_SESSION['user_id'] ?? $_SESSION['f_stafID'] ?? '');
    if (empty($studentId)) {
        echo json_encode(['status' => 'error', 'message' => 'Sesi tidak sah. Sila log masuk semula.']);
        exit;
    }

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'update_personal_profile') {
        $controller = new StudentPersonalController();
        $response = $controller->saveProfile($_POST, $studentId);
        echo json_encode($response);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Tindakan tidak sah.']);
    exit;

} catch (\Throwable $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo json_encode(['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()]);
    exit;
}