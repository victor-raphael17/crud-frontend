<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_URI'] === '/api/users') {
    require __DIR__ . '/api.php';
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}
