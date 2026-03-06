<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dataFile = __DIR__ . '/../data/data.json';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $json = file_get_contents($dataFile);
        echo $json;
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['name']) || !isset($input['age'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and age are required']);
            exit;
        }

        $json = file_get_contents($dataFile);
        $data = json_decode($json, true);

        $newUser = [
            'name' => $input['name'],
            'age' => (int) $input['age'],
            'email' => $input['email'],
        ];

        $data['users'][] = $newUser;

        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        http_response_code(201);
        echo json_encode($newUser);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
