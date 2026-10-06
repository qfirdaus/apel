<?php
// Fail ini diletakkan di dalam folder pages/portfolio/informal/
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../../../controllers/InformalController.php';

// Pastikan ia adalah POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new InformalController();
    $controller->handleRequest();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Permintaan tidak sah (Bukan POST).'
    ]);
}