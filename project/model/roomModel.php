<?php
require_once __DIR__ . "/db.php";

/** Fetch all room types */
function getRoomTypes() {
    $con = getConnection();
    $sql = "SELECT id, name FROM room_types ORDER BY name ASC";
    $res = mysqli_query($con, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    return $data;
}

/** Fetch all room features */
function getRoomFeatures() {
    $con = getConnection();
    $sql = "SELECT id, name FROM room_features ORDER BY name ASC";
    $res = mysqli_query($con, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    return $data;
}

/** Count rooms with filters */
function countRoomsFiltered($filters = []) {
    $con = getConnection();
    $where = "1=1";

    // ✅ use "type" (text) instead of type_id
    if (!empty($filters['type'])) {
        $type = mysqli_real_escape_string($con, $filters['type']);
        $where .= " AND type = '{$type}'";
    }
    // ✅ still use FIND_IN_SET for feature IDs
    if (!empty($filters['feature_id'])) {
        $where .= " AND FIND_IN_SET(" . (int)$filters['feature_id'] . ", features)";
    }
    if (!empty($filters['price'])) {
        [$min, $max] = explode("-", $filters['price']);
        $where .= " AND price BETWEEN " . (float)$min . " AND " . (float)$max;
    }

    $sql = "SELECT COUNT(*) as total FROM rooms WHERE $where";
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($res);
    return (int)$row['total'];
}

/** Get rooms with filters + pagination */
function getRoomsFiltered($filters = [], $page = 1, $perPage = 12) {
    $con = getConnection();
    $where = "1=1";

    if (!empty($filters['type'])) {
        $type = mysqli_real_escape_string($con, $filters['type']);
        $where .= " AND type = '{$type}'";
    }
    if (!empty($filters['feature_id'])) {
        $where .= " AND FIND_IN_SET(" . (int)$filters['feature_id'] . ", features)";
    }
    if (!empty($filters['price'])) {
        [$min, $max] = explode("-", $filters['price']);
        $where .= " AND price BETWEEN " . (float)$min . " AND " . (float)$max;
    }

    $offset = ($page - 1) * $perPage;
    $sql = "SELECT id, room_number, type, status, price, features, path 
            FROM rooms 
            WHERE $where 
            ORDER BY id DESC 
            LIMIT $offset, $perPage";

    $res = mysqli_query($con, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    return $data;
}

/** Create a new room */
function ra_createRoom($room_number, $price, $type_name, $status, $featuresCsv, $fileName = null) {
    $con = getConnection();
    $stmt = $con->prepare("
        INSERT INTO rooms (room_number, type, status, price, created_at, features, path)
        VALUES (?, ?, ?, ?, NOW(), ?, ?)
    ");
    $stmt->bind_param("sssiss",
        $room_number,
        $type_name,
        $status,
        $price,
        $featuresCsv,
        $fileName
    );
    return $stmt->execute();
}

/** Update an existing room */
function ra_updateRoom($id, $room_number, $price, $type_name, $status, $featuresCsv, $fileName = null) {
    $con = getConnection();

    // Delete old image if a new one is uploaded
    if ($fileName) {
        $oldStmt = $con->prepare("SELECT path FROM rooms WHERE id = ?");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRes = $oldStmt->get_result();
        if ($oldRes && $oldRes->num_rows > 0) {
            $oldRow = $oldRes->fetch_assoc();
            $oldFile = $oldRow['path'] ?? '';
            if ($oldFile) {
                $oldPath = __DIR__ . '/../uploads/rooms/' . basename($oldFile);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }
    }

    if ($fileName) {
        $stmt = $con->prepare("
            UPDATE rooms 
            SET room_number=?, type=?, status=?, price=?, features=?, path=?
            WHERE id=?
        ");
        $stmt->bind_param("sssissi",
            $room_number,
            $type_name,
            $status,
            $price,
            $featuresCsv,
            $fileName,
            $id
        );
    } else {
        $stmt = $con->prepare("
            UPDATE rooms 
            SET room_number=?, type=?, status=?, price=?, features=?
            WHERE id=?
        ");
        $stmt->bind_param("sssisi",
            $room_number,
            $type_name,
            $status,
            $price,
            $featuresCsv,
            $id
        );
    }

    return $stmt->execute();
}

/** Delete a room */
function ra_deleteRoom($id) {
    $con = getConnection();

    $oldStmt = $con->prepare("SELECT path FROM rooms WHERE id = ?");
    $oldStmt->bind_param("i", $id);
    $oldStmt->execute();
    $oldRes = $oldStmt->get_result();
    if ($oldRes && $oldRes->num_rows > 0) {
        $oldRow = $oldRes->fetch_assoc();
        $oldFile = $oldRow['path'] ?? '';
        if ($oldFile) {
            $oldPath = __DIR__ . '/../uploads/rooms/' . basename($oldFile);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
    }

    $stmt = $con->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/** Fetch single room by ID */
function getRoomById(int $id): ?array {
    $con = getConnection();
    $sql = "SELECT id, room_number, type, status, price, features, path 
            FROM rooms 
            WHERE id = " . (int)$id . " LIMIT 1";
    $res = mysqli_query($con, $sql);
    if ($res && mysqli_num_rows($res) === 1) {
        return mysqli_fetch_assoc($res);
    }
    return null;
}
