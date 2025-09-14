<?php
require_once(__DIR__ . '/db.php');

function search_all($q = '', $category = 'All', $status = 'All', $limit = 50) {
    $q = trim((string)$q);
    $category = (string)$category;
    $status = (string)$status;
    $limit = max(1, min(200, (int)$limit));

    $out = [];
    $like = fn($col) => "$col LIKE ?";
    $wrap = fn($s) => '%' . $s . '%';
    $con = getConnection();

    if ($category === 'All' || $category === 'Rooms') {
        $sql = "SELECT 
                    r.id,
                    r.room_number,
                    r.status,
                    r.price,
                    COALESCE(rt.name, CONCAT('Type#', r.type)) AS room_type
                FROM rooms r
                LEFT JOIN room_types rt ON r.type = rt.id
                WHERE 1=1";
        $params = []; $types = '';
        if ($q !== '')  { $sql .= " AND (". $like('r.room_number') ." OR ". $like('rt.name') .")"; $params[]=$wrap($q); $params[]=$wrap($q); $types.='ss'; }
        if ($status !== 'All') { $sql .= " AND r.status = ?"; $params[]=$status; $types.='s'; }
        $sql .= " ORDER BY r.room_number ASC LIMIT ?";
        $params[]=$limit; $types.='i';

        $stmt = $con->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $out[] = [
                'kind'     => 'room',
                'id'       => (int)$r['id'],
                'name'     => $r['room_number'].' — '.$r['room_type'],
                'status'   => $r['status'],
                'extra'    => 'Price: '.number_format((float)$r['price'], 2),
                'link'     => 'room_details.php?room_id='.(int)$r['id'],
            ];
        }
    }

    if ($category === 'All' || $category === 'Users') {
        $sql = "SELECT id, username, email, role FROM users WHERE 1=1";
        $params = []; $types  = '';
        if ($q !== '') { $sql .= " AND (". $like('username') ." OR ". $like('email') .")"; $params[]=$wrap($q); $params[]=$wrap($q); $types.='ss'; }
        $sql .= " ORDER BY username ASC LIMIT ?";
        $params[]=$limit; $types.='i';

        $stmt = $con->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($u = $res->fetch_assoc()) {
            $out[] = [
                'kind'   => 'user',
                'id'     => (int)$u['id'],
                'name'   => $u['username'],
                'status' => $u['role'],
                'extra'  => $u['email'],
                'link'   => 'profile.php?user_id='.(int)$u['id'],
            ];
        }
    }

    return $out;
}
