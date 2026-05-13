<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

require_once 'config.php';

// Get pending orders count
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE LOWER(TRIM(status)) = 'pending'")->fetch_assoc()['count'];

// Get pending customer care requests count
$pending_care = $conn->query("SELECT COUNT(*) as count FROM customer_care WHERE LOWER(TRIM(status)) = 'pending'")->fetch_assoc()['count'];

// Get new (unreviewed) customer care requests - you can modify this logic as needed
// For now, we'll show all pending as "new"
$new_care = $pending_care;

// Get cancelled orders count (if you want to highlight)
$pending_completed = $conn->query("SELECT COUNT(*) as count FROM orders WHERE LOWER(TRIM(status)) = 'completed' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'];

// Return notification counts
echo json_encode([
    'pending_orders' => $pending_orders,
    'pending_care' => $pending_care,
    'new_care' => $new_care,
    'today_completed' => $pending_completed
]);

$conn->close();
?>
