<?php

require_once __DIR__ . '/../config/config.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

in_array($origin, $allowedOrigins) ?
    header("Access-Control-Allow-Origin: $origin") : null;
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uri = strtok($_SERVER['REQUEST_URI'], '?');

match ($uri) {
    '/api/users' => require __DIR__ . '/../src/api.php',
    '/docs' => serveView(__DIR__ . '/../views/docs.html'),
    '/openapi.json' => serveJson(__DIR__ . '/../openapi.json'),
    default => notFound(),
};

function serveJson(string $file): void
{
    header('Content-Type: application/json');
    echo file_get_contents($file);
}

function serveView(string $file): void
{
    header('Content-Type: text/html');
    echo file_get_contents($file);
}

function notFound(): void
{
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}
