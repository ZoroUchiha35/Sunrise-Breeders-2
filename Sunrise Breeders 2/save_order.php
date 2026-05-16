<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data
    $customer_id = $_SESSION['user_id'];
    $customer_name = $_SESSION['user_name'];
    $customer_email = $_SESSION['user_email'];
    $delivery_name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $instructions = $_POST['instructions'] ?? '';
    $items = $_POST['items'] ?? '';
    $total = floatval($_POST['total'] ?? 0);
    
    // Debug logging - log the actual data received
    error_log("=== ORDER RECEIVED ===");
    error_log("Customer: $customer_name");
    error_log("Phone: $phone");
    error_log("Total: $total");
    error_log("Items length: " . strlen($items) . " characters");
    error_log("Items first 200 chars: " . substr($items, 0, 200));
    error_log("Items last 200 chars: " . substr($items, -200));
    error_log("=== END LOG ===");
    
    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, customer_name, customer_email, delivery_name, address, phone, instructions, items, total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo "Database Error: " . $conn->error;
        exit();
    }
    
    $stmt->bind_param("isssssssd", $customer_id, $customer_name, $customer_email, $delivery_name, $address, $phone, $instructions, $items, $total);
    
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;
        error_log("Order #$order_id successfully saved");
        echo "Success! Order #$order_id saved. Total: $" . number_format($total, 2);
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo "Database Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "No POST data received";
}

$conn->close();
?>