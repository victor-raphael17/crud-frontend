<?php

require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/data.php';

function handleGet(string $dataFile): void
{
    try {
        echo json_encode(getUsers($dataFile));
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
}

function handlePost(string $dataFile): void
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        exit;
    }

    $error = validateRequiredFields($input, ['name', 'age', 'email']);

    if ($error) {
        http_response_code(400);
        echo json_encode(['error' => $error]);
        exit;
    }

    try {
        $data = getUsers($dataFile);

        $newUser = [
            'name' => $input['name'],
            'age' => (int) $input['age'],
            'email' => $input['email'],
        ];

        $data['users'][] = $newUser;

        saveUsers($dataFile, $data);

        http_response_code(201);
        echo json_encode($newUser);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
}

function handlePut(string $dataFile): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $index = $_GET['index'] ?? null;

    if ($index === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Index is required']);
        exit;
    }

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        exit;
    }

    $error = validateRequiredFields($input, ['name', 'age', 'email']);

    if ($error) {
        http_response_code(400);
        echo json_encode(['error' => $error]);
        exit;
    }

    try {
        $data = getUsers($dataFile);

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

        saveUsers($dataFile, $data);

        echo json_encode($data['users'][$index]);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
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

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        exit;
    }

    try {
        $data = getUsers($dataFile);

        if (!isset($data['users'][$index])) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        $data['users'][$index] = array_merge($data['users'][$index], $input);

        saveUsers($dataFile, $data);

        echo json_encode($data['users'][$index]);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
}

function handleDelete(string $dataFile): void
{
    $index = $_GET['index'] ?? null;

    if ($index === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Index is required']);
        exit;
    }

    try {
        $data = getUsers($dataFile);

        if (!isset($data['users'][$index])) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        $removed = $data['users'][$index];
        array_splice($data['users'], $index, 1);

        saveUsers($dataFile, $data);

        echo json_encode(['deleted' => $removed]);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
}

function handleMethodNotAllowed(): void
{
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
