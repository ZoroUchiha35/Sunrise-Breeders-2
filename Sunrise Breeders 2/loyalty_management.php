<?php
/**
 * Loyalty Points Management Handler
 * Handles initialization and recalculation of loyalty points
 */

session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    die('Not authenticated');
}

$action = $_GET['action'] ?? null;

if ($action === 'init') {
    // Check if loyal_points column exists
    $result = $conn->query("SHOW COLUMNS FROM customers LIKE 'loyal_points'");
    
    if ($result->num_rows == 0) {
        // Add loyal_points column if it doesn't exist
        $sql = "ALTER TABLE customers ADD COLUMN loyal_points INT DEFAULT 0 AFTER password";
        
        if ($conn->query($sql) === TRUE) {
            // Recalculate all customers' points
            $customers = $conn->query("SELECT id FROM customers");
            $count = 0;
            
            while ($customer = $customers->fetch_assoc()) {
                $customer_id = $customer['id'];
                $result = $conn->query("SELECT SUM(total) as total_spent FROM orders WHERE customer_id = $customer_id AND LOWER(TRIM(status)) = 'completed'");
                $row = $result->fetch_assoc();
                $total_spent = $row['total_spent'] ?? 0;
                $loyal_points = intval($total_spent / 200);
                
                $update_sql = "UPDATE customers SET loyal_points = $loyal_points WHERE id = $customer_id";
                $conn->query($update_sql);
                $count++;
            }
            
            echo json_encode(['success' => true, 'message' => "✅ Loyalty system initialized! Updated $count customers."]);
        } else {
            echo json_encode(['success' => false, 'message' => "❌ Error: " . $conn->error]);
        }
    } else {
        echo json_encode(['success' => true, 'message' => "ℹ️ Loyalty points column already exists."]);
    }
} elseif ($action === 'recalculate') {
    // Recalculate all customers' loyal points
    $customers = $conn->query("SELECT id FROM customers");
    $count = 0;
    
    while ($customer = $customers->fetch_assoc()) {
        $customer_id = $customer['id'];
        $result = $conn->query("SELECT SUM(total) as total_spent FROM orders WHERE customer_id = $customer_id AND LOWER(TRIM(status)) = 'completed'");
        $row = $result->fetch_assoc();
        $total_spent = $row['total_spent'] ?? 0;
        $loyal_points = intval($total_spent / 200);
        
        $update_sql = "UPDATE customers SET loyal_points = $loyal_points WHERE id = $customer_id";
        $conn->query($update_sql);
        $count++;
    }
    
    echo json_encode(['success' => true, 'message' => "✅ Recalculated loyal points for $count customers!"]);
}

$conn->close();
?>
