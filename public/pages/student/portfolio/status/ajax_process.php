<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../includes/functions-page.php';
require_once __DIR__ . '/../../../../controllers/ApplicationController.php';

try {
    $studentId = (string)($_SESSION['f_stafID'] ?? $_SESSION['student_id'] ?? '');
    if (empty($studentId)) {
        echo json_encode(['status' => 'error', 'message' => 'Sesi tidak sah.']);
        exit;
    }

    $appController = new ApplicationController();
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_additional_doc') {
        $activeApp = $appController->getStudentApplication($studentId);
        $appId = (int)($activeApp['id'] ?? 0);
        $title = trim((string)($_POST['document_title'] ?? ''));
        $remarks = trim((string)($_POST['document_remarks'] ?? ''));
        $file = $_FILES['document_file'] ?? null;

        $response = $appController->uploadAdditionalDocument($appId, $title, $remarks, $file);
        echo json_encode($response);
        exit;

    } elseif ($action === 'resubmit_rework') {
        $activeApp = $appController->getStudentApplication($studentId);
        $appId = (int)($activeApp['id'] ?? 0);

        $response = $appController->resubmitReworkPortfolio($appId);
        echo json_encode($response);
        exit;

    } elseif ($action === 'get_portfolio_pdf_details') {
        $appId = (int)($_POST['app_id'] ?? 0);
        if ($appId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID Permohonan tidak sah.']);
            exit;
        }

        $pdfData = $appController->getPortfolioPdfData($appId);

        ob_start();
        include __DIR__ . '/view-pdf-template.php';
        $html = ob_get_clean();

        echo json_encode(['status' => 'success', 'html' => $html]);
        exit;

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tindakan tidak sah.']);
        exit;
    }

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ralat Backend: ' . $e->getMessage()]);
    exit;
}