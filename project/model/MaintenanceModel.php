<?php
require_once __DIR__ . '/db.php';

/** Create a maintenance request (maps old "service") */
function addService(int $room_id, string $issue, string $status='Open'): bool {
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO maintenance_requests (room_id, issue, status, reported_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $room_id, $issue, $status);
    return $stmt->execute();
}

/** Delete request by id */
function deleteService(int $id): bool {
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM maintenance_requests WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/** List all requests (optionally by room) */
function getServicesByRoom(?int $room_id=null): array {
    $conn = getConnection();
    if ($room_id) {
        $stmt = $conn->prepare("SELECT mr.*, r.room_number FROM maintenance_requests mr JOIN rooms r ON mr.room_id=r.id WHERE mr.room_id=? ORDER BY mr.reported_at DESC");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $sql = "SELECT mr.*, r.room_number FROM maintenance_requests mr JOIN rooms r ON mr.room_id=r.id ORDER BY mr.reported_at DESC";
        $res = $conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}

/** Update status (Open/In Progress/Closed) */
function setServiceStatus(int $id, string $status): bool {
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE maintenance_requests SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
}
