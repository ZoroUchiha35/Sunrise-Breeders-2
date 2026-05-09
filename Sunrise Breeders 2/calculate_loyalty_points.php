<?php
/**
 * Loyalty Points Calculator
 * Calculates loyal points based on cumulative completed orders
 * 1 point per $200 spent on completed orders
 */

require_once 'config.php';

function calculateLoyalPoints($customer_id) {
    global $conn;
    
    // Get total spent on completed orders only
    $result = $conn->query("SELECT SUM(total) as total_spent FROM orders WHERE customer_id = $customer_id AND LOWER(TRIM(status)) = 'completed'");
    $row = $result->fetch_assoc();
    $total_spent = $row['total_spent'] ?? 0;
    
    // Calculate points: 1 point per $200
    $loyal_points = intval($total_spent / 200);
    
    // Update customer's loyal points
    $update_sql = "UPDATE customers SET loyal_points = $loyal_points WHERE id = $customer_id";
    $conn->query($update_sql);
    
    return $loyal_points;
}

function awardLoyalPoints($customer_id) {
    return calculateLoyalPoints($customer_id);
}

// If called directly
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['recalculate_all'])) {
    if ($_GET['recalculate_all'] === 'yes') {
        // Get all customers
        $customers = $conn->query("SELECT id FROM customers");
        $count = 0;
        
        while ($customer = $customers->fetch_assoc()) {
            calculateLoyalPoints($customer['id']);
            $count++;
        }
        
        echo "✅ Recalculated loyal points for $count customers!";
    }
}
?>
