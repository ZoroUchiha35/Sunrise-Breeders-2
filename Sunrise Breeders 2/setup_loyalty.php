<?php
/**
 * Loyalty Points System Setup
 * This script adds the loyal_points column to customers table if it doesn't exist
 * Run this once to initialize the loyalty system
 */

require_once 'config.php';

// Check if loyal_points column exists
$result = $conn->query("SHOW COLUMNS FROM customers LIKE 'loyal_points'");

if ($result->num_rows == 0) {
    // Add loyal_points column if it doesn't exist
    $sql = "ALTER TABLE customers ADD COLUMN loyal_points INT DEFAULT 0 AFTER password";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Successfully added 'loyal_points' column to customers table!";
    } else {
        echo "❌ Error adding column: " . $conn->error;
    }
} else {
    echo "ℹ️ 'loyal_points' column already exists in customers table.";
}

$conn->close();
?>
