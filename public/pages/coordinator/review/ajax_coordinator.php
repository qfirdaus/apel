<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../includes/functions-page.php';
require_once __DIR__ . '/../../../controllers/CoordinatorController.php';

if (!function_exists('h')) {
    function h(?string $str): string {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

try {
    $coordId = (string)($_SESSION['f_stafID'] ?? $_SESSION['user_id'] ?? '');
    if (empty($coordId)) {
        echo json_encode(['status' => 'error', 'message' => 'Sesi pengguna tidak sah. Sila log masuk semula.']);
        exit;
    }

    $controller = new CoordinatorController();
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'submit_coordinator_decision') {
        echo json_encode($controller->processDecision($_POST, $coordId));
        exit;
    }

    if ($action === 'get_portfolio_pdf_details') {
        $appId = (int)($_POST['app_id'] ?? 0);
        echo json_encode($controller->renderPortfolioPdfPreview($appId));
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Tindakan tidak sah.']);
    exit;

} catch (Throwable $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo json_encode(['status' => 'error', 'message' => 'Ralat Sistem: ' . $e->getMessage()]);
    exit;
}