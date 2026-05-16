<?php
session_start();
require_once 'config.php';
require_once 'calculate_loyalty_points.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle order status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action_type = $_POST['action_type'] ?? 'order'; // Determine if this is an order or care request action
    $id = $_POST['order_id'] ?? $_POST['care_id'];
    $action = trim($_POST['action']); // Clean whitespace
    
    if ($action_type == 'care') {
        // Handle customer care request actions
        if ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM customer_care WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<script>alert('Care request deleted successfully!'); window.location.href='admin_dashboard.php';</script>";
            }
        } else {
            $action = ucfirst(strtolower($action)); // Capitalize
            $stmt = $conn->prepare("UPDATE customer_care SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $action, $id);
            if ($stmt->execute()) {
                echo "<script>alert('Care request marked as " . $action . "!'); window.location.href='admin_dashboard.php';</script>";
            }
        }
    } else {
        // Handle order actions (existing code)
        if ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<script>alert('Order deleted successfully!'); window.location.href='admin_dashboard.php';</script>";
            }
        } else {
            // Normalize the status value before storing
            $action = ucfirst(strtolower($action)); // Capitalize first letter, lowercase rest (Pending, Completed, Cancelled)
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $action, $id);
            if ($stmt->execute()) {
                // If order is marked as Completed, award loyal points
                if ($action === 'Completed') {
                    $order_result = $conn->query("SELECT customer_id FROM orders WHERE id = $id");
                    $order_row = $order_result->fetch_assoc();
                    if ($order_row) {
                        calculateLoyalPoints($order_row['customer_id']);
                    }
                }
                echo "<script>alert('Order updated to " . $action . "!'); window.location.href='admin_dashboard.php';</script>";
            }
        }
    }
    $stmt->close();
}

// Get all customers
$customers_result = $conn->query("SELECT * FROM customers ORDER BY id DESC");

// Get orders based on status
$pending_orders = $conn->query("SELECT * FROM orders WHERE LOWER(TRIM(status)) = 'pending' ORDER BY created_at DESC");
$completed_orders = $conn->query("SELECT * FROM orders WHERE LOWER(TRIM(status)) = 'completed' ORDER BY created_at DESC");
$cancelled_orders = $conn->query("SELECT * FROM orders WHERE LOWER(TRIM(status)) = 'cancelled' ORDER BY created_at DESC");

// Get customer care requests by status
$pending_care = $conn->query("SELECT * FROM customer_care WHERE LOWER(TRIM(status)) = 'pending' ORDER BY created_at DESC");
$resolved_care = $conn->query("SELECT * FROM customer_care WHERE LOWER(TRIM(status)) = 'resolved' ORDER BY created_at DESC");
$cancelled_care = $conn->query("SELECT * FROM customer_care WHERE LOWER(TRIM(status)) = 'cancelled' ORDER BY created_at DESC");

// Stats
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];
$total_pending = $conn->query("SELECT COUNT(*) as count FROM orders WHERE LOWER(TRIM(status)) = 'pending'")->fetch_assoc()['count'];
$total_completed = $conn->query("SELECT COUNT(*) as count FROM orders WHERE LOWER(TRIM(status)) = 'completed'")->fetch_assoc()['count'];
$total_cancelled = $conn->query("SELECT COUNT(*) as count FROM orders WHERE LOWER(TRIM(status)) = 'cancelled'")->fetch_assoc()['count'];
$total_requests = $conn->query("SELECT COUNT(*) as count FROM customer_care")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total) as total FROM orders WHERE LOWER(TRIM(status)) = 'completed'")->fetch_assoc()['total'];

// Analytics Data
// Daily revenue for last 30 days
$daily_revenue = $conn->query("
    SELECT DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as order_count
    FROM orders 
    WHERE LOWER(TRIM(status)) = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$daily_revenue_data = [];
while($row = $daily_revenue->fetch_assoc()) {
    $daily_revenue_data[] = $row;
}

// Order status breakdown
$order_status = [
    'Pending' => $total_pending,
    'Completed' => $total_completed,
    'Cancelled' => $total_cancelled
];

// Coffee preference distribution
$coffee_prefs = $conn->query("
    SELECT coffee_preference, COUNT(*) as count 
    FROM customers 
    WHERE coffee_preference IS NOT NULL AND coffee_preference != ''
    GROUP BY coffee_preference 
    ORDER BY count DESC
");
$coffee_prefs_data = [];
while($row = $coffee_prefs->fetch_assoc()) {
    $coffee_prefs_data[] = $row;
}

// Peak order times (hourly distribution)
$peak_hours = $conn->query("
    SELECT HOUR(created_at) as hour, COUNT(*) as total_orders, SUM(total) as revenue
    FROM orders
    WHERE LOWER(TRIM(status)) = 'completed'
    GROUP BY HOUR(created_at)
    ORDER BY hour ASC
");
$peak_hours_data = [];
while($row = $peak_hours->fetch_assoc()) {
    $peak_hours_data[] = $row;
}

// Monthly customer growth
$monthly_growth = $conn->query("
    SELECT DATE_FORMAT(join_date, '%Y-%m') as month, COUNT(*) as new_customers
    FROM customers
    GROUP BY DATE_FORMAT(join_date, '%Y-%m')
    ORDER BY month ASC
    LIMIT 12
");
$monthly_growth_data = [];
while($row = $monthly_growth->fetch_assoc()) {
    $monthly_growth_data[] = $row;
}

// Average order value
$avg_order = $conn->query("
    SELECT AVG(total) as avg_value, MAX(total) as max_value, MIN(total) as min_value
    FROM orders
    WHERE LOWER(TRIM(status)) = 'completed'
")->fetch_assoc();
?>

<!DOCTYPE html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sunrise Breeders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><style>circle{fill:%238B4513;}path{fill:%23D2691E;}.sun{fill:%23FFD700;}</style><circle cx='50' cy='50' r='45'/><path d='M30,65 L70,65 L75,85 L25,85 Z'/><circle class='sun' cx='80' cy='20' r='15'/><path d='M80,5 L80,35 M65,20 L95,20 M70,10 L90,30 M70,30 L90,10' stroke='%23FFD700' stroke-width='3'/></svg>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        /* YOUR EXISTING STYLES - KEPT EXACTLY THE SAME */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: #F5EFE7;
            min-height: 100vh;
            zoom: 85%;
            transform-origin: top left;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100%;
            background: linear-gradient(180deg, #2C1810 0%, #3D2317 100%);
            color: white;
            z-index: 100;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-header h2 {
            color: #F5DEB3;
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
        }

        .sidebar-header p {
            color: #CD853F;
            font-size: 0.7rem;
            letter-spacing: 2px;
        }

        .sidebar-menu {
            padding: 1.5rem 0;
        }

        .menu-item {
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #D4C5B0;
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }

        .menu-item.active {
            background: rgba(205, 133, 63, 0.2);
            border-left-color: #CD853F;
            color: white;
        }

        .menu-item i {
            width: 24px;
            font-size: 1.1rem;
        }

        /* Notification Badge */
        .notification-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #FF4444;
            color: white;
            border-radius: 50%;
            min-width: 24px;
            height: 24px;
            font-size: 0.75rem;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(255, 68, 68, 0.4);
            animation: pulse-notification 2s infinite;
            margin-left: auto;
        }

        @keyframes pulse-notification {
            0%, 100% {
                box-shadow: 0 2px 8px rgba(255, 68, 68, 0.4);
            }
            50% {
                box-shadow: 0 2px 15px rgba(255, 68, 68, 0.7);
            }
        }

        .notification-dot {
            width: 10px;
            height: 10px;
            background: #FF4444;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(255, 68, 68, 0.6);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% {
                box-shadow: 0 0 5px rgba(255, 68, 68, 0.6);
            }
            50% {
                box-shadow: 0 0 12px rgba(255, 68, 68, 0.9);
            }
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 1.5rem;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .page-title h1 {
            font-size: 1.5rem;
            color: #2C1810;
            font-weight: 600;
        }

        .page-title p {
            font-size: 0.8rem;
            color: #8B7355;
            margin-top: 0.2rem;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-name {
            text-align: right;
        }

        .admin-name span {
            font-size: 0.7rem;
            color: #8B7355;
        }

        .admin-name strong {
            font-size: 0.9rem;
            color: #2C1810;
        }

        .admin-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .logout-btn {
            background: none;
            border: 1px solid #E8E0D5;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            color: #8B4513;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #8B4513;
            color: white;
            border-color: #8B4513;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-info h3 {
            font-size: 0.75rem;
            color: #8B7355;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2C1810;
        }

        .stat-icon {
            width: 55px;
            height: 55px;
            background: rgba(139, 69, 19, 0.1);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8B4513;
            font-size: 1.8rem;
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }

        .section-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #F0EAE0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            font-size: 1.1rem;
            color: #2C1810;
            font-weight: 600;
        }

        .section-header i {
            color: #CD853F;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 1rem 1.2rem;
            background: #FAF6F0;
            color: #5C4B3A;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid #F0EAE0;
            color: #3D2B1F;
            font-size: 0.85rem;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* Address column - full visibility */
        td:nth-child(6) {
            max-width: 250px;
            word-break: break-word;
            white-space: normal;
            line-height: 1.4;
        }

        tr:hover {
            background: #FAF6F0;
        }

        /* Status Badges */
        .badge-pending {
            background: #FEF3C7;
            color: #D97706;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-completed {
            background: #D1FAE5;
            color: #059669;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-cancelled {
            background: #FEF3C7;
            color: #D97706;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }

        /* Clickable Items */
        .clickable-item {
            color: #8B4513;
            cursor: pointer;
            text-decoration: underline;
        }

        .clickable-item:hover {
            color: #D2691E;
        }

        /* Action Buttons */
        .action-select {
            padding: 0.3rem 0.5rem;
            border-radius: 0.4rem;
            border: 1px solid #E8E0D5;
            background: white;
            font-size: 0.7rem;
            cursor: pointer;
        }

        .action-select:hover {
            border-color: #8B4513;
        }

        .btn-resolve {
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            border: none;
            padding: 0.3rem 0.8rem;
            border-radius: 0.4rem;
            cursor: pointer;
            font-size: 0.7rem;
            transition: all 0.2s;
        }

        .btn-resolve:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #8B7355;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            max-width: 700px;
            width: 90%;
            border-radius: 1rem;
            padding: 1.5rem;
            max-height: 95vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #F0EAE0;
        }

        .close-modal {
            cursor: pointer;
            font-size: 1.5rem;
            color: #8B7355;
        }

        .items-list {
            list-style: none;
            padding: 0;
        }

        .items-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #F0EAE0;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
            }
            .sidebar.active {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Sunrise Breeders</h2>
            <p>ADMIN PORTAL</p>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" data-section="customers">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </div>
            <div class="menu-item" data-section="orders">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
                <?php if ($total_pending > 0): ?>
                    <span class="notification-badge"><?php echo $total_pending; ?></span>
                <?php endif; ?>
            </div>
            <div class="menu-item" data-section="care">
                <i class="fas fa-headset"></i>
                <span>Customer Care</span>
                <?php 
                    $pending_care_count = $conn->query("SELECT COUNT(*) as count FROM customer_care WHERE LOWER(TRIM(status)) = 'pending'")->fetch_assoc()['count'];
                    if ($pending_care_count > 0): 
                ?>
                    <span class="notification-badge"><?php echo $pending_care_count; ?></span>
                <?php endif; ?>
            </div>
            <div class="menu-item" data-section="analytics">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-title">
                <h1>Dashboard</h1>
                <p>Welcome back to your administrative control center</p>
            </div>
            <div class="admin-info">
                <div class="admin-name">
                    <span>Logged in as</span>
                    <strong><?php echo $_SESSION['admin_username']; ?></strong>
                </div>
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                </div>
                <a href="admin_logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Customers</h3>
                    <div class="stat-number"><?php echo $total_customers; ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Pending Orders</h3>
                    <div class="stat-number"><?php echo $total_pending; ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Completed Orders</h3>
                    <div class="stat-number"><?php echo $total_completed; ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Revenue</h3>
                    <div class="stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <!-- Customers Section -->
        <div id="customers-section" class="content-section section-active" style="display: block;">
            <div class="section-header">
                <h2><i class="fas fa-users" style="margin-right: 0.5rem;"></i> Registered Customers</h2>
                <i class="fas fa-download"></i>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Coffee Preference</th><th>Points</th><th>Joined</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($customers_result->num_rows > 0): ?>
                            <?php while($row = $customers_result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><strong><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></strong></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['phone']; ?></td>
                                <td><?php echo $row['coffee_preference'] ?: 'Not set'; ?></td>
                                <td><span style="background: #FFD700; color: #2C1810; padding: 0.3rem 0.8rem; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">⭐ <?php echo $row['loyal_points'] ?? 0; ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($row['join_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="empty-state"><i class="fas fa-users"></i><br>No customers yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Section - RECENT ORDERS (PENDING) -->
        <div id="orders-section" class="content-section" style="display: none;">
            <div class="section-header">
                <h2><i class="fas fa-shopping-cart" style="margin-right: 0.5rem;"></i> Recent Orders - Pending</h2>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Special Instructions</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pending_orders && $pending_orders->num_rows > 0): ?>
                            <?php while($row = $pending_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><strong><?php echo $row['customer_name']; ?></strong></td>
                                <td><span class="clickable-item" data-items='<?php echo json_encode($row['items'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT); ?>' onclick="viewItemsFromData(this)" style="font-size: 15px; color: #059669; text-decoration: underline;">View</span></td>
                                <td><strong>$<?php echo number_format($row['total'], 2); ?></strong></td>
                                <td><?php echo $row['phone'] ?? 'N/A'; ?></td>
                                <td title="<?php echo htmlspecialchars($row['address']); ?>"><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><span class="badge-pending">Pending</span></td>
                                <td>
                                    <?php if (!empty($row['instructions']) && $row['instructions'] != 'None'): ?>
                                    <span class="clickable-item" data-instructions='<?php echo json_encode($row['instructions'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>' onclick="viewInstructionsFromData(this)"><i class="fas fa-comment"></i> View</span>
                                    <?php else: ?>
                                    <span style="color: #C4B8A8;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirmAction(event, this)">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <select name="action" class="action-select" required>
                                            <option value="">Select Action</option>
                                            <option value="Completed">✓ Completed</option>
                                            <option value="Cancelled">✗ Cancelled</option>
                                            <option value="delete">🗑 Delete</option>
                                        </select>
                                        <button type="submit" class="btn-resolve" style="margin-top: 0.3rem;">Apply</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="empty-state"><i class="fas fa-shopping-cart"></i><br>No pending orders</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COMPLETED ORDERS SECTION -->
        <div id="completed-section" class="content-section" style="display: none;">
            <div class="section-header">
                <h2><i class="fas fa-check-circle" style="margin-right: 0.5rem; color: #059669;"></i> Completed Orders</h2>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($completed_orders && $completed_orders->num_rows > 0): ?>
                            <?php while($row = $completed_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><strong><?php echo $row['customer_name']; ?></strong></td>
                                <td><span class="clickable-item" data-items='<?php echo json_encode($row['items'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT); ?>' onclick="viewItemsFromData(this)">View Items</span></td>
                                <td>$<?php echo number_format($row['total'], 2); ?></td>
                                <td><?php echo $row['phone'] ?? 'N/A'; ?></td>
                                <td title="<?php echo htmlspecialchars($row['address']); ?>"><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><span class="badge-completed">Completed</span></td>
                                <td><?php echo date('M d, H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="empty-state"><i class="fas fa-check-circle"></i><br>No completed orders</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CANCELLED ORDERS SECTION -->
        <div id="cancelled-section" class="content-section" style="display: none;">
            <div class="section-header">
                <h2><i class="fas fa-times-circle" style="margin-right: 0.5rem; color: #D97706;"></i> Cancelled Orders</h2>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($cancelled_orders && $cancelled_orders->num_rows > 0): ?>
                            <?php while($row = $cancelled_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><strong><?php echo $row['customer_name']; ?></strong></td>
                                <td><span class="clickable-item" data-items='<?php echo json_encode($row['items'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT); ?>' onclick="viewItemsFromData(this)">View Items</span></td>
                                <td>$<?php echo number_format($row['total'], 2); ?></td>
                                <td><?php echo $row['phone'] ?? 'N/A'; ?></td>
                                <td title="<?php echo htmlspecialchars($row['address']); ?>"><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><span class="badge-cancelled">Cancelled</span></td>
                                <td><?php echo date('M d, H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="empty-state"><i class="fas fa-times-circle"></i><br>No cancelled orders</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Customer Care Section -->
        <div id="care-section" class="content-section" style="display: none;">
            <div class="section-header">
                <h2><i class="fas fa-headset" style="margin-right: 0.5rem;"></i> Customer Care Services</h2>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-navigation" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #E8E0D5; padding-bottom: 0;">
                <button class="tab-btn active" onclick="switchCareTab('pending')" style="background: transparent; border: none; padding: 0.8rem 1.5rem; color: #8B4513; font-weight: 600; border-bottom: 3px solid #8B4513; cursor: pointer; margin-bottom: -2px;">
                    <i class="fas fa-hourglass-half"></i> Pending
                </button>
                <button class="tab-btn" onclick="switchCareTab('resolved')" style="background: transparent; border: none; padding: 0.8rem 1.5rem; color: #8B7355; font-weight: 600; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.3s;">
                    <i class="fas fa-check-circle"></i> Resolved Services
                </button>
                <button class="tab-btn" onclick="switchCareTab('cancelled')" style="background: transparent; border: none; padding: 0.8rem 1.5rem; color: #8B7355; font-weight: 600; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.3s;">
                    <i class="fas fa-times-circle"></i> Cancelled Services
                </button>
            </div>

            <!-- Pending Care Requests -->
            <div id="pending-care-tab" class="care-tab" style="display: block;">
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #FAF6F0; border-bottom: 2px solid #D2691E;">
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Customer</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Order ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Issue Type</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Priority</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Message</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Date</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pending_care && $pending_care->num_rows > 0): ?>
                                <?php while($row = $pending_care->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #D2691E hover-effect;">
                                    <td style="padding: 0.8rem 1rem; color: #8B4513; font-weight: 600;">#<?php echo $row['id']; ?></td>
                                    <td style="padding: 0.8rem 1rem; color: #2C1810;"><strong><?php echo $row['customer_name']; ?></strong><br><small style="color: #8B7355;"><?php echo $row['customer_email']; ?></small></td>
                                    <td style="padding: 0.8rem 1rem; color: #2C1810;"><?php echo $row['order_id'] ? '#' . $row['order_id'] : '-'; ?></td>
                                    <td style="padding: 0.8rem 1rem; color: #2C1810;"><?php echo $row['issue_type']; ?></td>
                                    <td style="padding: 0.8rem 1rem;">
                                        <span style="padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
                                            <?php echo $row['priority'] == 'high' ? 'background: #FEE2E2; color: #DC2626;' : ($row['priority'] == 'medium' ? 'background: #FEF3C7; color: #92400E;' : 'background: #DBEAFE; color: #1E40AF;'); ?>">
                                            <?php echo ucfirst($row['priority'] ?? 'medium'); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 0.8rem 1rem;"><span class="clickable-item" data-message='<?php echo json_encode($row['message'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>' onclick="viewMessageFromData(this)" style="color: #8B4513; cursor: pointer; text-decoration: underline;"><i class="fas fa-eye"></i> View Message</span></td>
                                    <td style="padding: 0.8rem 1rem; color: #8B7355; font-size: 0.85rem;"><?php echo date('M d, Y<br>H:i', strtotime($row['created_at'])); ?></td>
                                    <td style="padding: 0.8rem 1rem;">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action_type" value="care">
                                            <input type="hidden" name="care_id" value="<?php echo $row['id']; ?>">
                                            <select name="action" style="padding: 0.4rem 0.6rem; border: 1px solid #D2691E; border-radius: 0.4rem; background: white; color: #2C1810; cursor: pointer; font-size: 0.75rem;">
                                                <option value="">-- Select --</option>
                                                <option value="resolved">✓ Resolved</option>
                                                <option value="cancelled">✕ Cancelled</option>
                                                <option value="delete">🗑 Delete</option>
                                            </select>
                                            <button type="submit" onclick="return confirm('Confirm this action?')" style="padding: 0.4rem 0.8rem; margin-left: 0.3rem; background: #8B4513; color: white; border: none; border-radius: 0.4rem; cursor: pointer; font-size: 0.75rem; font-weight: 600;">Apply</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" style="text-align: center; padding: 2rem; color: #8B7355;"><i class="fas fa-headset" style="font-size: 1.5rem; opacity: 0.5;"></i><br>No pending care requests</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Resolved Care Requests -->
            <div id="resolved-care-tab" class="care-tab" style="display: none;">
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #FAF6F0; border-bottom: 2px solid #27ae60;">
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Customer</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Order ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Issue Type</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Priority</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Message</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Date</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resolved_care && $resolved_care->num_rows > 0): ?>
                                <?php while($row = $resolved_care->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #D1FAE5; background: #F0FDF4;">
                                    <td style="padding: 0.8rem 1rem; color: #27ae60; font-weight: 600;">#<?php echo $row['id']; ?></td>
                                    <td style="padding: 0.8rem 1rem; color: #2C1810;"><strong><?php echo $row['customer_name']; ?></strong><br><small style="color: #8B7355;"><?php echo $row['customer_email']; ?></small></td>
                                    <td style="padding: 0.8rem 1rem; color: #2C1810;"><?php echo $row['order_id'] ? '#' . $row['order_id'] : '-'; ?></td>
                                    <td style="padding: 0.8rem 1rem; color: #2C1810;"><?php echo $row['issue_type']; ?></td>
                                    <td style="padding: 0.8rem 1rem;">
                                        <span style="padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; <?php echo $row['priority'] == 'high' ? 'background: #FEE2E2; color: #DC2626;' : ($row['priority'] == 'medium' ? 'background: #FEF3C7; color: #92400E;' : 'background: #DBEAFE; color: #1E40AF;'); ?>">
                                            <?php echo ucfirst($row['priority'] ?? 'medium'); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 0.8rem 1rem;"><span class="clickable-item" data-message='<?php echo json_encode($row['message'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>' onclick="viewMessageFromData(this)" style="color: #27ae60; cursor: pointer; text-decoration: underline;"><i class="fas fa-eye"></i> View Message</span></td>
                                    <td style="padding: 0.8rem 1rem; color: #8B7355; font-size: 0.85rem;"><?php echo date('M d, Y<br>H:i', strtotime($row['created_at'])); ?></td>
                                    <td style="padding: 0.8rem 1rem;">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action_type" value="care">
                                            <input type="hidden" name="care_id" value="<?php echo $row['id']; ?>">
                                            <select name="action" style="padding: 0.4rem 0.6rem; border: 1px solid #27ae60; border-radius: 0.4rem; background: white; color: #2C1810; cursor: pointer; font-size: 0.75rem;">
                                                <option value="">-- Select --</option>
                                                <option value="pending">⟲ Reopen</option>
                                                <option value="cancelled">✕ Cancel</option>
                                                <option value="delete">🗑 Delete</option>
                                            </select>
                                            <button type="submit" onclick="return confirm('Confirm this action?')" style="padding: 0.4rem 0.8rem; margin-left: 0.3rem; background: #27ae60; color: white; border: none; border-radius: 0.4rem; cursor: pointer; font-size: 0.75rem; font-weight: 600;">Apply</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" style="text-align: center; padding: 2rem; color: #8B7355;"><i class="fas fa-check-circle" style="font-size: 1.5rem; opacity: 0.5;"></i><br>No resolved services</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cancelled Care Requests -->
            <div id="cancelled-care-tab" class="care-tab" style="display: none;">
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #FAF6F0; border-bottom: 2px solid #D97706;">
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Customer</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Order ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Issue Type</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Priority</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Message</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Date</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #2C1810;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($cancelled_care && $cancelled_care->num_rows > 0): ?>
                                <?php while($row = $cancelled_care->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #FED7AA; background: #FFFBEB;">
                                    <td style="padding: 0.8rem 1rem; color: #D97706; font-weight: 600;">#<?php echo $row['id']; ?></td>
                                    <td style="padding: 0.8rem 1rem; color: #2C1810;"><strong><?php echo $row['customer_name']; ?></strong><br><small style="color: #8B7355;"><?php echo $row['customer_email']; ?></small></td>
                                    <td style="padding: 0.8rem 1rem; color: #2C1810;"><?php echo $row['order_id'] ? '#' . $row['order_id'] : '-'; ?></td>
                                    <td style="padding: 0.8rem 1rem; color: #2C1810;"><?php echo $row['issue_type']; ?></td>
                                    <td style="padding: 0.8rem 1rem;">
                                        <span style="padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; <?php echo $row['priority'] == 'high' ? 'background: #FEE2E2; color: #DC2626;' : ($row['priority'] == 'medium' ? 'background: #FEF3C7; color: #92400E;' : 'background: #DBEAFE; color: #1E40AF;'); ?>">
                                            <?php echo ucfirst($row['priority'] ?? 'medium'); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 0.8rem 1rem;"><span class="clickable-item" data-message='<?php echo json_encode($row['message'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>' onclick="viewMessageFromData(this)" style="color: #D97706; cursor: pointer; text-decoration: underline;"><i class="fas fa-eye"></i> View Message</span></td>
                                    <td style="padding: 0.8rem 1rem; color: #8B7355; font-size: 0.85rem;"><?php echo date('M d, Y<br>H:i', strtotime($row['created_at'])); ?></td>
                                    <td style="padding: 0.8rem 1rem;">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action_type" value="care">
                                            <input type="hidden" name="care_id" value="<?php echo $row['id']; ?>">
                                            <select name="action" style="padding: 0.4rem 0.6rem; border: 1px solid #D97706; border-radius: 0.4rem; background: white; color: #2C1810; cursor: pointer; font-size: 0.75rem;">
                                                <option value="">-- Select --</option>
                                                <option value="pending">⟲ Reopen</option>
                                                <option value="resolved">✓ Resolve</option>
                                                <option value="delete">🗑 Delete</option>
                                            </select>
                                            <button type="submit" onclick="return confirm('Confirm this action?')" style="padding: 0.4rem 0.8rem; margin-left: 0.3rem; background: #D97706; color: white; border: none; border-radius: 0.4rem; cursor: pointer; font-size: 0.75rem; font-weight: 600;">Apply</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" style="text-align: center; padding: 2rem; color: #8B7355;"><i class="fas fa-times-circle" style="font-size: 1.5rem; opacity: 0.5;"></i><br>No cancelled services</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div id="analytics-section" class="content-section" style="display: none;">
            <div class="section-header">
                <h2><i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i> Analytics Overview</h2>
            </div>
            <div style="padding: 1.5rem;">
                <!-- Key Metrics Row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                    <div style="background: #FAF6F0; padding: 1.2rem; border-radius: 0.8rem; border-left: 4px solid #8B4513;">
                        <div style="font-size: 0.7rem; color: #8B7355; font-weight: 600; margin-bottom: 0.5rem;">AVG ORDER VALUE</div>
                        <div style="font-size: 1.8rem; font-weight: 700; color: #2C1810;">$<?php echo number_format($avg_order['avg_value'] ?? 0, 2); ?></div>
                        <div style="font-size: 0.7rem; color: #8B7355; margin-top: 0.5rem;">Max: $<?php echo number_format($avg_order['max_value'] ?? 0, 2); ?></div>
                    </div>
                    <div style="background: #FAF6F0; padding: 1.2rem; border-radius: 0.8rem; border-left: 4px solid #27ae60;">
                        <div style="font-size: 0.7rem; color: #8B7355; font-weight: 600; margin-bottom: 0.5rem;">TOTAL REVENUE (30 DAYS)</div>
                        <div style="font-size: 1.8rem; font-weight: 700; color: #2C1810;">$<?php echo number_format(array_sum(array_column($daily_revenue_data, 'revenue')), 2); ?></div>
                        <div style="font-size: 0.7rem; color: #8B7355; margin-top: 0.5rem;"><?php echo count($daily_revenue_data); ?> days recorded</div>
                    </div>
                    <div style="background: #FAF6F0; padding: 1.2rem; border-radius: 0.8rem; border-left: 4px solid #CD853F;">
                        <div style="font-size: 0.7rem; color: #8B7355; font-weight: 600; margin-bottom: 0.5rem;">TOTAL ORDERS (30 DAYS)</div>
                        <div style="font-size: 1.8rem; font-weight: 700; color: #2C1810;"><?php echo array_sum(array_column($daily_revenue_data, 'order_count')); ?></div>
                        <div style="font-size: 0.7rem; color: #8B7355; margin-top: 0.5rem;">Completed orders</div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                    <!-- Daily Revenue Chart -->
                    <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <h3 style="color: #2C1810; margin-bottom: 1rem; font-size: 1rem;">📈 Daily Revenue (Last 30 Days)</h3>
                        <canvas id="dailyRevenueChart" height="80"></canvas>
                    </div>

                    <!-- Order Status Distribution -->
                    <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <h3 style="color: #2C1810; margin-bottom: 1rem; font-size: 1rem;">📊 Order Status Distribution</h3>
                        <canvas id="orderStatusChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Second Row Charts -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                    <!-- Peak Hours Chart -->
                    <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <h3 style="color: #2C1810; margin-bottom: 1rem; font-size: 1rem;">⏰ Peak Order Hours</h3>
                        <canvas id="peakHoursChart" height="80"></canvas>
                    </div>

                    <!-- Coffee Preferences -->
                    <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <h3 style="color: #2C1810; margin-bottom: 1rem; font-size: 1rem;">☕ Popular Coffee Preferences</h3>
                        <canvas id="coffeePrefsChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Customer Growth Chart -->
                <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <h3 style="color: #2C1810; margin-bottom: 1rem; font-size: 1rem;">👥 Customer Growth Trend</h3>
                    <canvas id="customerGrowthChart" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart JS Data -->
    <script>
        // Data from PHP
        const dailyRevenueData = <?php echo json_encode($daily_revenue_data); ?>;
        const orderStatusData = <?php echo json_encode($order_status); ?>;
        const coffeePrefsData = <?php echo json_encode($coffee_prefs_data); ?>;
        const peakHoursData = <?php echo json_encode($peak_hours_data); ?>;
        const monthlyGrowthData = <?php echo json_encode($monthly_growth_data); ?>;

        // Colors
        const colors = {
            primary: '#8B4513',
            secondary: '#CD853F',
            success: '#27ae60',
            danger: '#DC2626',
            warning: '#F59E0B',
            info: '#3B82F6'
        };

        // Chart Options
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    labels: {
                        font: { family: "'Inter', sans-serif", size: 12 },
                        color: '#5C4B3A'
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        font: { family: "'Inter', sans-serif", size: 11 },
                        color: '#8B7355'
                    },
                    grid: {
                        color: 'rgba(139, 115, 85, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        font: { family: "'Inter', sans-serif", size: 11 },
                        color: '#8B7355'
                    },
                    grid: {
                        color: 'rgba(139, 115, 85, 0.05)'
                    }
                }
            }
        };

        // Daily Revenue Chart
        if(dailyRevenueData.length > 0) {
            const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: dailyRevenueData.map(d => new Date(d.date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})),
                    datasets: [{
                        label: 'Revenue ($)',
                        data: dailyRevenueData.map(d => parseFloat(d.revenue) || 0),
                        borderColor: colors.primary,
                        backgroundColor: 'rgba(139, 69, 19, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: colors.secondary,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: chartOptions
            });
        }

        // Order Status Chart
        const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(orderStatusData),
                datasets: [{
                    data: Object.values(orderStatusData),
                    backgroundColor: [
                        'rgba(241, 158, 11, 0.8)',
                        'rgba(39, 174, 96, 0.8)',
                        'rgba(220, 38, 38, 0.8)'
                    ],
                    borderColor: ['#F59E0B', '#27ae60', '#DC2626'],
                    borderWidth: 2
                }]
            },
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 12 } }
                    }
                }
            }
        });

        // Peak Hours Chart
        if(peakHoursData.length > 0) {
            const hoursCtx = document.getElementById('peakHoursChart').getContext('2d');
            new Chart(hoursCtx, {
                type: 'bar',
                data: {
                    labels: peakHoursData.map(h => h.hour + ':00'),
                    datasets: [{
                        label: 'Orders',
                        data: peakHoursData.map(h => h.total_orders),
                        backgroundColor: colors.secondary,
                        borderColor: colors.primary,
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });
        }

        // Coffee Preferences Chart
        if(coffeePrefsData.length > 0) {
            const prefsCtx = document.getElementById('coffeePrefsChart').getContext('2d');
            new Chart(prefsCtx, {
                type: 'bar',
                data: {
                    labels: coffeePrefsData.map(p => p.coffee_preference),
                    datasets: [{
                        label: 'Customers',
                        data: coffeePrefsData.map(p => p.count),
                        backgroundColor: colors.info,
                        borderColor: colors.primary,
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });
        }

        // Customer Growth Chart
        if(monthlyGrowthData.length > 0) {
            const growthCtx = document.getElementById('customerGrowthChart').getContext('2d');
            new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: monthlyGrowthData.map(m => m.month),
                    datasets: [{
                        label: 'New Customers',
                        data: monthlyGrowthData.map(m => m.new_customers),
                        borderColor: colors.success,
                        backgroundColor: 'rgba(39, 174, 96, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: colors.success,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: chartOptions
            });
        }
    </script>

    <!-- Modals -->
    <div id="itemsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Order Items</h3><span class="close-modal" onclick="closeModal('itemsModal')">&times;</span></div>
            <div id="itemsContent"></div>
        </div>
    </div>

    <div id="instructionsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Special Instructions</h3><span class="close-modal" onclick="closeModal('instructionsModal')">&times;</span></div>
            <div id="instructionsContent"></div>
        </div>
    </div>

    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3>Customer Message</h3><span class="close-modal" onclick="closeModal('messageModal')">&times;</span></div>
            <div id="messageContent"></div>
        </div>
    </div>

    <script>
        // Sidebar menu switching
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                // Update active state
                document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
                this.classList.add('active');
                
                // Get section from data-section attribute
                const section = this.getAttribute('data-section');
                
                // Hide all sections
                document.getElementById('customers-section').style.display = 'none';
                document.getElementById('orders-section').style.display = 'none';
                document.getElementById('completed-section').style.display = 'none';
                document.getElementById('cancelled-section').style.display = 'none';
                document.getElementById('care-section').style.display = 'none';
                document.getElementById('analytics-section').style.display = 'none';
                
                // Show selected sections
                if (section === 'orders') {
                    document.getElementById('orders-section').style.display = 'block';
                    document.getElementById('completed-section').style.display = 'block';
                    document.getElementById('cancelled-section').style.display = 'block';
                } else if (section === 'customers') {
                    document.getElementById('customers-section').style.display = 'block';
                } else if (section === 'care') {
                    document.getElementById('care-section').style.display = 'block';
                } else if (section === 'analytics') {
                    document.getElementById('analytics-section').style.display = 'block';
                }
            });
        });

        // FIXED viewItems function - properly handles all items
        function viewItemsFromData(element) {
            const items = element.getAttribute('data-items');
            console.log("Items from data attribute:", items);
            viewItems(items);
        }

        function viewItems(items) {
            if (!items || items.trim() === '') {
                document.getElementById('itemsContent').innerHTML = `
                    <div style="padding: 20px;">
                        <div style="background: linear-gradient(135deg, #6B7280, #9CA3AF); color: white; padding: 15px; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);">
                            <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; margin-right: 10px;"></i>
                            <strong>No Items Data</strong>
                        </div>
                    </div>
                `;
                document.getElementById('itemsModal').style.display = 'flex';
                return;
            }

            try {
                // Decode HTML entities
                let itemsText = items;
                itemsText = itemsText.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
                
                // Decode Unicode escape sequences (e.g., \u0026 -> &)
                itemsText = itemsText.replace(/\\u([0-9a-fA-F]{4})/g, (match, hex) => {
                    return String.fromCharCode(parseInt(hex, 16));
                });
                
                // Handle both escaped and literal newlines and carriage returns
                itemsText = itemsText.replace(/\\r\\n/g, '\n');  // Escaped CRLF
                itemsText = itemsText.replace(/\\r/g, '');        // Escaped CR only
                itemsText = itemsText.replace(/\\n/g, '\n');      // Escaped LF
                itemsText = itemsText.replace(/\r\n/g, '\n');     // Literal CRLF
                itemsText = itemsText.replace(/\r/g, '');         // Literal CR only
                
                // Remove surrounding quotes
                itemsText = itemsText.replace(/^["']|["']$/g, '').trim();
                
                // Split by newlines and clean
                const lines = itemsText.split(/\n/).map(line => line.trim()).filter(line => line.length > 0);
                
                let orderItems = [];
                let totalLine = '';
                
                // Process each line
                for (let line of lines) {
                    // Skip headers
                    if (/^your order includes/i.test(line) || /^order summary/i.test(line)) {
                        continue;
                    }
                    
                    // Extract total
                    if (/^total\s*[:]?/i.test(line)) {
                        totalLine = line.replace(/total\s*[:]?/i, '').trim();
                        continue;
                    }
                    
                    // Collect item lines that start with "- "
                    if (line.startsWith('- ')) {
                        const itemText = line.substring(2).trim();
                        if (itemText.length > 0 && orderItems.length < 60) {
                            orderItems.push(itemText);
                        }
                    }
                }
                
                // Fallback for different formats
                if (orderItems.length === 0) {
                    for (let line of lines) {
                        if (!/^(your order includes|order summary|total)/i.test(line) && line.length > 0 && orderItems.length < 60) {
                            orderItems.push(line);
                        }
                    }
                }
                
                // Build HTML
                let html = `
                    <div style="padding: 10px;">
                        <div style="background: linear-gradient(135deg, #8B4513, #D2691E); color: white; padding: 18px 20px; border-radius: 10px; margin-bottom: 18px; text-align: center; box-shadow: 0 4px 15px rgba(139, 69, 19, 0.25);">
                            <i class="fas fa-shopping-cart" style="font-size: 1.4rem; margin-right: 10px;"></i>
                            <strong style="font-size: 1.1rem; letter-spacing: 0.5px;">Ordered Items (${orderItems.length})</strong>
                        </div>
                        <div style="background: #FAF6F0; border-radius: 12px; padding: 20px; border: 1px solid #E8E0D5; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
                            <ol style="margin: 0; padding-left: 1.5rem; color: #2C1810; font-size: 0.95rem; line-height: 2.2;">
                `;
                
                // Add each item
                if (orderItems.length > 0) {
                    orderItems.forEach((item, index) => {
                        html += `<li style="background: white; margin-bottom: 8px; padding: 10px 12px; border-radius: 6px; border: 1px solid #F0EAE0;">${item}</li>`;
                    });
                } else {
                    html += `<li style="background: white; margin-bottom: 8px; padding: 10px 12px; border-radius: 6px; border: 1px solid #F0EAE0; color: #999;">No items found</li>`;
                }
                
                html += `
                            </ol>
                        </div>
                `;
                
                // Add total below items
                if (totalLine) {
                    html += `
                        <div style="margin-top: 18px; padding: 14px 18px; background: linear-gradient(135deg, #F8ECE0, #FCF5EB); border-radius: 10px; border: 2px solid #E8D8C3; display: flex; justify-content: space-between; align-items: center; font-weight: 700; color: #2C1810; font-size: 1.05rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                            <span>Total</span>
                            <span>${totalLine}</span>
                        </div>
                    `;
                }
                
                // Add inventory notice if at limit
                if (orderItems.length >= 60) {
                    html += `
                        <div style="margin-top: 12px; padding: 10px; background: #FEF3C7; border-radius: 8px; border: 1px solid #F59E0B; text-align: center; font-size: 0.85rem; color: #92400E;">
                            <i class="fas fa-info-circle"></i> Showing first 60 items
                        </div>
                    `;
                }
                
                html += `</div>`;
                document.getElementById('itemsContent').innerHTML = html;
                
            } catch (error) {
                console.error("Error parsing items:", error);
                document.getElementById('itemsContent').innerHTML = `
                    <div style="padding: 20px;">
                        <div style="background: linear-gradient(135deg, #D97706, #F59E0B); color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);">
                            <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; margin-right: 10px;"></i>
                            <strong>Display Error</strong>
                        </div>
                        <div style="background: #FEF3C7; border-radius: 12px; padding: 20px; border: 2px solid #F59E0B; font-family: 'Courier New', monospace; white-space: pre-wrap; line-height: 1.6; color: #92400E; max-height: 200px; overflow-y: auto; font-size: 0.8rem;">
                            <strong>Error:</strong> ${error.message}<br><br>
                            <strong>First 200 chars:</strong><br>
                            ${items.substring(0, 200)}
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('itemsModal').style.display = 'flex';
        }

        function viewInstructionsFromData(element) {
            const instructionsJSON = element.getAttribute('data-instructions');
            try {
                const instructions = JSON.parse(instructionsJSON);
                viewInstructions(instructions);
            } catch(e) {
                console.error('Error parsing instructions:', e);
                viewInstructions(instructionsJSON);
            }
        }

        function viewMessageFromData(element) {
            const messageJSON = element.getAttribute('data-message');
            try {
                const message = JSON.parse(messageJSON);
                viewMessage(message);
            } catch(e) {
                console.error('Error parsing message:', e);
                viewMessage(messageJSON);
            }
        }

        function viewInstructions(instructions) {
            if (!instructions || instructions === 'None') {
                document.getElementById('instructionsContent').innerHTML = '<p>No special instructions provided.</p>';
            } else {
                document.getElementById('instructionsContent').innerHTML = `<p style="white-space: pre-wrap; background: #FAF6F0; padding: 1rem; border-radius: 0.5rem;">${instructions}</p>`;
            }
            document.getElementById('instructionsModal').style.display = 'flex';
        }

        function viewMessage(message) {
            document.getElementById('messageContent').innerHTML = `<p style="white-space: pre-wrap; background: #FAF6F0; padding: 1rem; border-radius: 0.5rem;">${message}</p>`;
            document.getElementById('messageModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function confirmAction(event, form) {
            const select = form.querySelector('select[name="action"]');
            const action = select.value;
            if (!action) {
                alert('Please select an action');
                event.preventDefault();
                return false;
            }
            if (action === 'delete') {
                return confirm('⚠️ WARNING: This order will be PERMANENTLY DELETED with no history. Continue?');
            }
            return confirm(`Are you sure you want to mark this order as ${action}?`);
        }

        function switchSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            // Show selected section
            document.getElementById(sectionId).style.display = 'block';
        }

        function switchCareTab(tabName) {
            // Hide all care tabs
            document.querySelectorAll('.care-tab').forEach(tab => {
                tab.style.display = 'none';
            });
            // Show selected tab
            document.getElementById(tabName + '-care-tab').style.display = 'block';
            
            // Update tab button styling
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.style.color = '#8B7355';
                btn.style.borderColor = 'transparent';
            });
            event.target.closest('.tab-btn').style.color = '#8B4513';
            event.target.closest('.tab-btn').style.borderColor = '#8B4513';
        }

        function resolveIssue(id) {
            if(confirm('Mark this issue as resolved?')) {
                window.location.href = 'resolve_issue.php?id=' + id;
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Real-time Notification System
        function updateNotifications() {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    // Update Orders notification badge
                    const ordersMenuItem = document.querySelector('[data-section="orders"]');
                    let ordersBadge = ordersMenuItem.querySelector('.notification-badge');
                    
                    if (data.pending_orders > 0) {
                        if (!ordersBadge) {
                            ordersBadge = document.createElement('span');
                            ordersBadge.className = 'notification-badge';
                            ordersMenuItem.appendChild(ordersBadge);
                        }
                        ordersBadge.textContent = data.pending_orders;
                        ordersBadge.style.display = 'inline-flex';
                    } else if (ordersBadge) {
                        ordersBadge.style.display = 'none';
                    }

                    // Update Customer Care notification badge  
                    const careMenuItem = document.querySelector('[data-section="care"]');
                    let careBadge = careMenuItem.querySelector('.notification-badge');
                    
                    if (data.pending_care > 0) {
                        if (!careBadge) {
                            careBadge = document.createElement('span');
                            careBadge.className = 'notification-badge';
                            careMenuItem.appendChild(careBadge);
                        }
                        careBadge.textContent = data.pending_care;
                        careBadge.style.display = 'inline-flex';
                    } else if (careBadge) {
                        careBadge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error updating notifications:', error));
        }

        // Update notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateNotifications();
            // Auto-update every 10 seconds
            setInterval(updateNotifications, 10000);
            
            // Initialize loyalty system if not already done
            initializeLoyaltySystem();
        });

        // Initialize Loyalty Points System
        function initializeLoyaltySystem() {
            fetch('loyalty_management.php?action=init')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('✅ Loyalty System:', data.message);
                    }
                })
                .catch(error => console.error('Loyalty system check failed:', error));
        }
    </script>
</body>
</html>
