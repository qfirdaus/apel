<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../controllers/ApelAdminOfficerController.php';

function json_out(array $payload): void {
    echo json_encode($payload);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        json_out(['status' => 'error', 'message' => 'Kaedah tidak dibenarkan.']);
    }

    $adminId = (string)($_SESSION['f_stafID'] ?? $_SESSION['user_id'] ?? '');
    if ($adminId === '') {
        http_response_code(401);
        json_out(['status' => 'error', 'message' => 'Sesi tidak sah. Sila log masuk semula.']);
    }

    $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
    $postedToken  = (string)($_POST['csrf_token'] ?? '');
    if ($sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
        http_response_code(403);
        json_out(['status' => 'error', 'message' => 'Token keselamatan tamat.']);
    }

    $controller = new ApelAdminOfficerController();
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'assign_faculty_coordinator') {
        json_out($controller->assignFacultyCoordinator($_POST, $adminId));
    }

    if ($action === 'remove_faculty_coordinator') {
        $id = (int)($_POST['id'] ?? 0);
        json_out($controller->removeFacultyCoordinator($id, $adminId));
    }

    json_out(['status' => 'error', 'message' => 'Tindakan tidak sah.']);

} catch (Throwable $e) {
    json_out(['status' => 'error', 'message' => $e->getMessage()]);
}