<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../includes/init.php';
require_once __DIR__ . '/../../../includes/functions-page.php';

require_once __DIR__ . '/../../../controllers/AdvisorController.php';
require_once __DIR__ . '/../../../controllers/ApplicationController.php';

try {
    $advisorId = (string)($_SESSION['f_stafID'] ?? $_SESSION['user_id'] ?? '');
    if (empty($advisorId)) {
        echo json_encode(['status' => 'error', 'message' => 'Sesi tidak sah.']);
        exit;
    }

    $controller = new AdvisorController();
    $action = $_POST['action'] ?? '';

    if ($action === 'submit_advisor_decision') {
        $response = $controller->processDecision($_POST, $advisorId);
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