<?php
require_once __DIR__ . '/db.php';

function ensureLoyaltyRow(int $user_id): void {
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT IGNORE INTO loyalty_points (user_id, points, updated_at) VALUES (?, 0, NOW())");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

/** Get current loyalty points and computed tier */
function getLoyaltyPoints(int $user_id): array {
    ensureLoyaltyRow($user_id);
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT points, updated_at FROM loyalty_points WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: ['points'=>0,'updated_at'=>null];
    $row['tier'] = calculateLoyaltyTier((int)$row['points']);
    return $row;
}

function updateLoyaltyPoints(int $user_id, int $delta): array {
    ensureLoyaltyRow($user_id);
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE loyalty_points SET points = GREATEST(0, points + ?), updated_at = NOW() WHERE user_id = ?");
    $stmt->bind_param("ii", $delta, $user_id);
    $stmt->execute();
    return getLoyaltyPoints($user_id);
}

function redeemLoyaltyPoints(int $user_id, int $redeem): array {
    $redeem = max(0, (int)$redeem);
    return updateLoyaltyPoints($user_id, -$redeem);
}

/** Compute tier without DB column */
function calculateLoyaltyTier(int $points): string {
    if ($points >= 4000) return "Platinum";
    if ($points >= 2000) return "Gold";
    return "Silver";
}

function getLoyaltyProgress(int $points, string $tier, int $maxPoints = 5000): array {
    if ($tier === "Platinum") {
        return ["nextTierPoints" => "Max tier reached 🎉", "progress" => min(100, ($points / max(1,$maxPoints)) * 100)];
    }
    $next = ($tier === "Gold") ? 4000 : 2000;
    $toNext = max(0, $next - $points);
    $progress = min(100, ($points / max(1,$maxPoints)) * 100);
    return ["nextTierPoints" => $toNext, "progress" => $progress];
}
