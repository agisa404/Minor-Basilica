<?php
require_once __DIR__ . '/core.php';

function normalize_date(?string $value): ?string
{
    if (!$value) {
        return null;
    }
    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : null;
}

function normalize_time(?string $value): ?string
{
    if (!$value) {
        return null;
    }
    $ts = strtotime($value);
    return $ts ? date('H:i:s', $ts) : null;
}

function create_service_request(
    int $userId,
    string $formType,
    string $title,
    array $data,
    ?string $requestedDate = null,
    ?string $requestedTime = null
): int {
    global $conn;

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $dateValue = $requestedDate !== null ? normalize_date($requestedDate) : null;
    $timeValue = $requestedTime !== null ? normalize_time($requestedTime) : null;

    $stmt = $conn->prepare(
        'INSERT INTO service_requests (user_id, form_type, title, details, requested_date, requested_time) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('isssss', $userId, $formType, $title, $json, $dateValue, $timeValue);
    $stmt->execute();
    $id = (int)$stmt->insert_id;
    $stmt->close();

    return $id;
}
?>
