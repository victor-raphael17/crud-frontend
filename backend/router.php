<?php

if ($_SERVER['REQUEST_URI'] === '/api') {
    require __DIR__ . '/api.php';
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}
