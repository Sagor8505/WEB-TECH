<?php
// model/RoomAdminModel.php
require_once __DIR__ . '/db.php';

function ra_conn() {
    if (function_exists('getConnection')) return getConnection();
    global $conn; if ($conn) return $conn;
    throw new Exception('No DB connection found');
}

/** Optional lookups (keep if you show names for features/type lists) */
function ra_getRoomTypes(): array {
    $c = ra_conn();
    $res = $c->query("SELECT id, name FROM room_types ORDER BY name ASC");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
function ra_getRoomFeatures(): array {
    $c = ra_conn();
    $res = $c->query("SELECT id, name FROM room_features ORDER BY name ASC");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

/** List rooms — type stored as TEXT in rooms.type */
function ra_getRooms(string $search='', string $status='All', string $type='', int $page=1, int $per=10): array {
    $c = ra_conn();
    $page = max(1,$page); $per = max(1,min(100,$per)); $off = ($page-1)*$per;

    $sql = "SELECT id, room_number, price, status, features, path, type AS type_name
            FROM rooms WHERE 1=1";
    $params = []; $types='';

    if ($search !== '') {
        $sql .= " AND (room_number LIKE ? OR type LIKE ?)";
        $like = "%$search%"; $params[]=$like; $params[]=$like; $types.='ss';
    }
    if (strtolower($status) !== 'all') {
        $sql .= " AND LOWER(status) = LOWER(?)";
        $params[] = $status; $types.='s';
    }
    if ($type !== '') {
        $sql .= " AND type LIKE ?";
        $params[] = $type; $types.='s';
    }
    $sql .= " ORDER BY room_number ASC LIMIT ? OFFSET ?";
    $params[] = $per; $params[] = $off; $types.='ii';

    $stmt = $c->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $row['id']       = (int)$row['id'];
        $row['price']    = (float)$row['price'];
        $rows[] = $row;
    }
    return $rows;
}

function ra_countRooms(string $search='', string $status='All', string $type=''): int {
    $c = ra_conn();
    $sql = "SELECT COUNT(*) AS c FROM rooms WHERE 1=1";
    $params = []; $types='';

    if ($search !== '') {
        $sql .= " AND (room_number LIKE ? OR type LIKE ?)";
        $like = "%$search%"; $params[]=$like; $params[]=$like; $types.='ss';
    }
    if (strtolower($status) !== 'all') {
        $sql .= " AND LOWER(status) = LOWER(?)";
        $params[] = $status; $types.='s';
    }
    if ($type !== '') {
        $sql .= " AND type LIKE ?";
        $params[] = $type; $types.='s';
    }

    $stmt = $c->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)($res['c'] ?? 0);
}

function ra_getRoomById(int $id): ?array {
    $c = ra_conn();
    $stmt = $c->prepare("SELECT *, type AS type_name FROM rooms WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) return null;
    $row['id']      = (int)$row['id'];
    $row['price']   = (float)$row['price'];
    return $row;
}

function ra_createRoom(string $room_number, float $price, string $type_name, string $status, string $featuresCsv='', ?string $imageFile=null): bool {
    $c = ra_conn();
    $stmt = $c->prepare("INSERT INTO rooms (room_number, price, type, status, features, path, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sdssss", $room_number, $price, $type_name, $status, $featuresCsv, $imageFile);
    return $stmt->execute();
}

function ra_updateRoom(int $id, string $room_number, float $price, string $type_name, string $status, string $featuresCsv='', ?string $imageFile=null): bool {
    $c = ra_conn();
    if ($imageFile) {
        $stmt = $c->prepare("UPDATE rooms SET room_number=?, price=?, type=?, status=?, features=?, path=? WHERE id=?");
        $stmt->bind_param("sdssssi", $room_number, $price, $type_name, $status, $featuresCsv, $imageFile, $id);
    } else {
        $stmt = $c->prepare("UPDATE rooms SET room_number=?, price=?, type=?, status=?, features=? WHERE id=?");
        $stmt->bind_param("sdsssi", $room_number, $price, $type_name, $status, $featuresCsv, $id);
    }
    return $stmt->execute();
}

function ra_deleteRoom(int $id): bool {
    $c = ra_conn();
    $stmt = $c->prepare("DELETE FROM rooms WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
