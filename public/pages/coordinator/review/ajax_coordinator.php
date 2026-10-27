<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../controllers/CoordinatorController.php';
require_once __DIR__ . '/../../../../controllers/ApplicationController.php';

try {
    $coordId = (string)($_SESSION['f_stafID'] ?? $_SESSION['user_id'] ?? '');
    if (empty($coordId)) {
        echo json_encode(['status' => 'error', 'message' => 'Sesi pengguna tidak sah.']);
        exit;
    }

    $controller = new CoordinatorController();
    $action = $_POST['action'] ?? '';

    if ($action === 'submit_coordinator_decision') {
        $response = $controller->processDecision($_POST, $coordId);
        echo json_encode($response);
        exit;

    } elseif ($action === 'get_portfolio_pdf_details') {
        $appId = (int)($_POST['app_id'] ?? 0);
        if ($appId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID Permohonan tidak sah.']);
            exit;
        }

        $appController = new ApplicationController();
        $pdfData = $appController->getPortfolioPdfData($appId);

        ob_start();
        include __DIR__ . '/../../student/portfolio/status/view-pdf-template.php';
        $html = ob_get_clean();

        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tindakan tidak sah.']);
        exit;
    }

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}