<?php

function loadData(string $dataFile): array
{
    return json_decode(file_get_contents($dataFile), true);
}

function saveData(string $dataFile, array $data): void
{
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function findAllUsers(string $dataFile): array
{
    return loadData($dataFile);
}

function findUserById(string $dataFile, int $id): ?array
{
    $data = loadData($dataFile);

    foreach ($data['users'] as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }

    return null;
}

function insertUser(string $dataFile, array $user): array
{
    $data = loadData($dataFile);

    $id = $data['nextId'] ?? 1;
    $data['nextId'] = $id + 1;

    $user['id'] = $id;
    $data['users'][] = $user;

    saveData($dataFile, $data);

    return $user;
}

function updateUser(string $dataFile, int $id, array $fields): ?array
{
    $data = loadData($dataFile);

    foreach ($data['users'] as $index => $user) {
        if ($user['id'] === $id) {
            $data['users'][$index] = array_merge($user, $fields);
            saveData($dataFile, $data);
            return $data['users'][$index];
        }
    }

    return null;
}

function deleteUser(string $dataFile, int $id): ?array
{
    $data = loadData($dataFile);

    foreach ($data['users'] as $index => $user) {
        if ($user['id'] === $id) {
            array_splice($data['users'], $index, 1);
            saveData($dataFile, $data);
            return $user;
        }
    }

    return null;
}
