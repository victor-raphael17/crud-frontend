<?php

function validateRequiredFields(array $input, array $fields): ?string
{
    $missing = [];

    foreach ($fields as $field) {
        if (!isset($input[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        return implode(', ', $missing) . ' are required';
    }

    return null;
}
