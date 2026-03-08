<?php

$dataFile = __DIR__ . '/../data/data.json';

$method = $_SERVER['REQUEST_METHOD'];

function handleGet(string $dataFile): void
{
    $json = file_get_contents($dataFile);
    echo $json;
}

function handlePost(string $dataFile): void
{
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
}

function handlePut(string $dataFile): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $index = $_GET['index'] ?? null;

    if ($index === null || !isset($input['name']) || !isset($input['age'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Index, name and age are required']);
        exit;
    }

    $data = json_decode(file_get_contents($dataFile), true);

    if (!isset($data['users'][$index])) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $data['users'][$index] = [
        'name' => $input['name'],
        'age' => (int) $input['age'],
        'email' => $input['email'],
    ];

    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode($data['users'][$index]);
}

function handlePatch(string $dataFile): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $index = $_GET['index'] ?? null;

    if ($index === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Index is required']);
        exit;
    }

    $data = json_decode(file_get_contents($dataFile), true);

    if (!isset($data['users'][$index])) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $data['users'][$index] = array_merge($data['users'][$index], $input);

    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode($data['users'][$index]);
}

function handleDelete(string $dataFile): void
{
    $index = $_GET['index'] ?? null;

    if ($index === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Index is required']);
        exit;
    }

    $data = json_decode(file_get_contents($dataFile), true);

    if (!isset($data['users'][$index])) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $removed = $data['users'][$index];
    array_splice($data['users'], $index, 1);

    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode(['deleted' => $removed]);
}

function handleMethodNotAllowed(): void
{
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

match ($method) {
    'GET' => handleGet($dataFile),
    'POST' => handlePost($dataFile),
    'PUT' => handlePut($dataFile),
    'PATCH' => handlePatch($dataFile),
    'DELETE' => handleDelete($dataFile),
    default => handleMethodNotAllowed(),
};
