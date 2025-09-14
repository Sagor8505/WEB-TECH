<?php
require_once('db.php');

function getActivityLog($fromDate, $toDate, $username) {
    $conn = getConnection();

    if (!empty($username)) {
        $query = "SELECT * FROM activity_logs WHERE user=? AND date BETWEEN ? AND ? ORDER BY date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $username, $fromDate, $toDate);
    } else {
        $query = "SELECT * FROM activity_logs WHERE date BETWEEN ? AND ? ORDER BY date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $fromDate, $toDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    return $logs;
}
?>
