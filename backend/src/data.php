<?php

function getUsers(string $dataFile): array
{
    return json_decode(file_get_contents($dataFile), true);
}

function saveUsers(string $dataFile, array $data): void
{
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function findUser(string $dataFile, int $index): ?array
{
    $data = getUsers($dataFile);

    return $data['users'][$index] ?? null;
}
