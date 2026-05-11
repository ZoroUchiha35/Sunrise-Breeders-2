<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, loyal_points FROM customers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email, $phone, $loyalty_points);
$stmt->fetch();
$stmt->close();

$full_name = $first_name . ' ' . $last_name;

$message = '';
$error = '';
$selected_type = isset($_GET['type']) ? $_GET['type'] : '';

// Map URL parameter to issue type
$type_mapping = [
    'wrong-order' => 'Wrong Order Received',
    'missing-items' => 'Missing Items',
    'general-support' => 'General Support'
];

$preselected_type = isset($type_mapping[$selected_type]) ? $type_mapping[$selected_type] : '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $issue_type = $_POST['issue_type'];
    $issue_message = trim($_POST['message']);
    $order_id = !empty($_POST['order_id']) ? $_POST['order_id'] : null;
    $priority = $_POST['priority'] ?? 'high';
    
    if (empty($issue_message)) {
        $error = "Please describe your issue so we can help you better.";
    } elseif (empty($issue_type)) {
        $error = "Please select an issue type.";
    } elseif (($issue_type == 'Wrong Order Received' || $issue_type == 'Missing Items') && empty($order_id)) {
        $error = "Please select the order related to this issue.";
    } else {
        $stmt = $conn->prepare("INSERT INTO customer_care (customer_id, customer_name, customer_email, issue_type, message, order_id, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("issssss", $user_id, $full_name, $email, $issue_type, $issue_message, $order_id, $priority);
        
        if ($stmt->execute()) {
            $message = "✅ Thank you, $first_name! Your request has been submitted successfully. Our customer care team will contact you within 24 hours.";
            // Clear form
            $_POST = array();
        } else {
            $error = "❌ Sorry, there was an error submitting your request. Please try again.";
        }
        $stmt->close();
    }
}

// Get user's recent orders
$recent_orders = $conn->query("SELECT id, total, created_at, status FROM orders WHERE customer_id = $user_id ORDER BY created_at DESC LIMIT 5");
?>

<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><style>circle{fill:%238B4513;}path{fill:%23D2691E;}.sun{fill:%23FFD700;}</style><circle cx='50' cy='50' r='45'/><path d='M30,65 L70,65 L75,85 L25,85 Z'/><circle class='sun' cx='80' cy='20' r='15'/><path d='M80,5 L80,35 M65,20 L95,20 M70,10 L90,30 M70,30 L90,10' stroke='%23FFD700' stroke-width='3'/></svg>">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Care - Sunrise Breeders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(rgba(44, 24, 16, 0.88), rgba(44, 24, 16, 0.94)), url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Back Button */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: #F5DEB3;
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-3px);
        }

        /* Hero Header */
        .hero-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .hero-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #F5DEB3, #CD853F);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .hero-header p {
            color: #D4C5B0;
            font-size: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }

        /* Trust Badge */
        .trust-badge {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border-radius: 60px;
            padding: 0.8rem 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 1.5rem;
            width: auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #F5DEB3;
        }

        .trust-item i {
            color: #27ae60;
            font-size: 0.9rem;
        }

        /* Main Card */
        .card {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: linear-gradient(135deg, #2C1810, #3D2317);
            color: white;
            padding: 1.2rem 1.8rem;
            border-bottom: 3px solid #CD853F;
        }

        .card-header h2 {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h2 i {
            color: #CD853F;
        }

        .card-body {
            padding: 1.8rem;
        }

        /* User Info Bar */
        .user-info-bar {
            background: #FAF6F0;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .user-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #5C4B3A;
            font-size: 0.85rem;
        }

        .user-detail i {
            color: #8B4513;
            width: 20px;
        }

        .loyalty-badge {
            background: linear-gradient(135deg, #CD853F, #8B4513);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Issue Type Cards */
        .issue-types {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .issue-option {
            background: #FAF6F0;
            border: 2px solid #E8E0D5;
            border-radius: 1rem;
            padding: 1.2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .issue-option:hover {
            border-color: #CD853F;
            background: #F5EFE7;
            transform: translateY(-2px);
        }

        .issue-option.selected {
            border-color: #8B4513;
            background: rgba(139, 69, 19, 0.08);
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.15);
        }

        .issue-option.selected::before {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 15px;
            color: #27ae60;
            font-weight: bold;
            font-size: 1rem;
        }

        .issue-option i {
            font-size: 2rem;
            color: #8B4513;
            margin-bottom: 0.8rem;
            display: block;
        }

        .issue-option h4 {
            font-size: 0.9rem;
            color: #2C1810;
            margin-bottom: 0.3rem;
            font-weight: 600;
        }

        .issue-option p {
            font-size: 0.7rem;
            color: #8B7355;
        }

        /* Response Time Badge */
        .response-time {
            background: #E8E0D5;
            border-radius: 0.5rem;
            padding: 0.8rem;
            text-align: center;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 0.8rem;
            color: #5C4B3A;
        }

        .response-time i {
            color: #27ae60;
            font-size: 1rem;
        }

        /* Recent Orders */
        .orders-section {
            margin-bottom: 1.5rem;
        }

        .orders-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #5C4B3A;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Order Cards - Grid Style */
        .order-card {
            background: #FAF6F0;
            border: 2px solid #E8E0D5;
            border-radius: 0.8rem;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .order-card:hover {
            border-color: #CD853F;
            background: #F5EFE7;
            transform: translateY(-2px);
        }

        .order-card.selected {
            border-color: #8B4513;
            background: rgba(139, 69, 19, 0.08);
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.15);
        }

        .order-card.selected::before {
            content: '✓';
            position: absolute;
            top: 8px;
            right: 12px;
            color: #27ae60;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .order-list {
            background: #FAF6F0;
            border-radius: 0.8rem;
            overflow: hidden;
            border: 1px solid #E8E0D5;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 1rem;
            border-bottom: 1px solid #E8E0D5;
            cursor: pointer;
            transition: background 0.2s;
        }

        .order-item:hover {
            background: #F0EAE0;
        }

        .order-item.selected {
            background: rgba(139, 69, 19, 0.1);
            border-left: 3px solid #8B4513;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-info {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .order-id {
            font-weight: bold;
            color: #8B4513;
            font-size: 0.85rem;
        }

        .order-date {
            font-size: 0.7rem;
            color: #8B7355;
        }

        .order-total {
            font-weight: bold;
            color: #2C1810;
            font-size: 0.85rem;
        }

        .order-status {
            font-size: 0.65rem;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            background: #E8E0D5;
            color: #5C4B3A;
        }

        .order-status.completed {
            background: #D1FAE5;
            color: #059669;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2C1810;
            font-weight: 600;
            font-size: 0.85rem;
        }

        label span {
            color: #DC2626;
        }

        textarea {
            width: 100%;
            padding: 1rem;
            border: 1.5px solid #E8E0D5;
            border-radius: 0.8rem;
            font-size: 0.9rem;
            transition: all 0.3s;
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
        }

        textarea:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            border: none;
            border-radius: 0.8rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 69, 19, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

            button span {
            display: block;
            margin-left: 0.3em;
            transition: all 0.3s ease-in-out;
            }

            button svg {
            display: block;
            transform-origin: center center;
            transition: transform 0.3s ease-in-out;
            }

            button:hover .svg-wrapper {
            animation: fly-1 0.6s ease-in-out infinite alternate;
            }

            button:hover svg {
            transform: translateX(1.2em) rotate(45deg) scale(1.1);
            }

            button:hover span {
            transform: translateX(1em);
            }

            button:active {
            transform: scale(0.95);
            }

            @keyframes fly-1 {
            from {
                transform: translateY(0.1em);
            }

            to {
                transform: translateY(-0.1em);
            }
            }

        /* Message Alerts */
        .alert {
            padding: 1rem;
            border-radius: 0.8rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #D1FAE5;
            color: #059669;
            border: 1px solid #A7F3D0;
        }

        .alert-error {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        /* Contact Info */
        .contact-info {
            background: linear-gradient(135deg, #2C1810, #3D2317);
            color: white;
            border-radius: 1rem;
            padding: 1.2rem;
            text-align: center;
        }

        .contact-info h3 {
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
            color: #CD853F;
        }

        .contact-details {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .contact-details span {
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .contact-details i {
            color: #CD853F;
        }

        /* Guarantee Section */
        .guarantee {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            margin-top: 1rem;
        }

        .guarantee p {
            font-size: 0.75rem;
            color: #D4C5B0;
        }

        /* Process Steps */
        .process-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }

        .step {
            text-align: center;
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            position: relative;
            border: 1px solid #E8E0D5;
            transition: all 0.3s;
        }

        .step:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .step::before {
            content: attr(data-step);
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #8B4513, #D2691E);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .step i {
            font-size: 2rem;
            color: #8B4513;
            margin-bottom: 0.8rem;
            display: block;
        }

        .step h4 {
            font-size: 0.9rem;
            color: #2C1810;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .step p {
            font-size: 0.75rem;
            color: #8B7355;
        }

        /* Testimonials */
        .testimonials-section {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .testimonials-title {
            font-size: 1.3rem;
            color: #2C1810;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .testimonial {
            background: #FAF6F0;
            padding: 1.2rem;
            border-radius: 1rem;
            border-left: 4px solid #CD853F;
        }

        .testimonial .stars {
            color: #FFD700;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .testimonial-text {
            font-size: 0.8rem;
            color: #5C4B3A;
            margin-bottom: 0.8rem;
            font-style: italic;
        }

        .testimonial-author {
            font-size: 0.75rem;
            color: #8B7355;
            font-weight: 600;
        }

        .testimonial-author::before {
            content: "— ";
        }

        /* Stats */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(139, 69, 19, 0.1), rgba(210, 105, 30, 0.1));
            border: 1px solid rgba(139, 69, 19, 0.2);
            padding: 1.5rem;
            border-radius: 1rem;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #8B4513;
            margin-bottom: 0.3rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #5C4B3A;
            font-weight: 500;
        }

        .stat-card i {
            font-size: 1.5rem;
            color: #D2691E;
            margin-bottom: 0.8rem;
        }

        /* FAQ Section */
        .faq-section {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .faq-title {
            font-size: 1.3rem;
            color: #2C1810;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        .faq-item {
            margin-bottom: 1rem;
            border-bottom: 1px solid #E8E0D5;
            padding-bottom: 1rem;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #2C1810;
            font-size: 0.9rem;
            cursor: pointer;
            transition: color 0.3s;
        }

        .faq-question:hover {
            color: #8B4513;
        }

        .faq-question i {
            color: #8B4513;
            font-size: 1rem;
        }

        .faq-answer {
            margin-top: 0.8rem;
            padding-left: 30px;
            color: #5C4B3A;
            font-size: 0.8rem;
            line-height: 1.5;
        }

        /* Priority Badge */
        .priority-control {
            margin-bottom: 1.2rem;
        }

        .priority-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2C1810;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .priority-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.8rem;
        }

        .priority-option {
            padding: 0.8rem;
            border: 1.5px solid #E8E0D5;
            border-radius: 0.6rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #FAF6F0;
        }

        .priority-option:hover {
            border-color: #8B4513;
        }

        .priority-option.selected {
            border-color: #8B4513;
            background: rgba(139, 69, 19, 0.08);
        }

        .priority-option i {
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }

        .priority-option-label {
            font-size: 0.75rem;
            color: #5C4B3A;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .process-steps {
                grid-template-columns: repeat(2, 1fr);
            }
            .testimonials-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .stats-section {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            .issue-types {
                grid-template-columns: 1fr;
            }
            .user-info-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            .order-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            .process-steps {
                grid-template-columns: 1fr;
            }
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            .stats-section {
                grid-template-columns: 1fr;
            }
            .priority-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="homepage.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Homepage</a>

        <div class="hero-header">
            <h1><i class="fas fa-headset"></i> Customer Care</h1>
            <p>We're here to make things right. Your satisfaction is our promise.</p>
        </div>

        <div style="display: flex; justify-content: center;">
            <div class="trust-badge">
                <div class="trust-item"><i class="fas fa-clock"></i> 24hr Response</div>
                <div class="trust-item"><i class="fas fa-shield-alt"></i> 100% Satisfaction</div>
                <div class="trust-item"><i class="fas fa-headset"></i> Real Support Team</div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-user-check"></i> Your Information</h2>
            </div>
            <div class="card-body">
                <div class="user-info-bar">
                    <div class="user-detail"><i class="fas fa-user"></i> <?php echo htmlspecialchars($full_name); ?></div>
                    <div class="user-detail"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email); ?></div>
                    <div class="user-detail"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($phone ?? 'Not provided'); ?></div>
                    <div class="loyalty-badge"><i class="fas fa-star"></i> <?php echo $loyalty_points; ?> Loyalty Points</div>
                </div>

                <div class="response-time">
                    <i class="fas fa-stopwatch"></i>
                    <span>Average response time: <strong>2-4 hours</strong> during business hours</span>
                    <i class="fas fa-check-circle"></i>
                </div>

                <form method="POST" action="" id="careForm">
                    <input type="hidden" name="issue_type" id="issue_type" value="<?php echo htmlspecialchars($preselected_type ?? ''); ?>">

                    <div class="issue-types">
                        <div class="issue-option <?php echo $preselected_type == 'Wrong Order Received' ? 'selected' : ''; ?>" data-type="Wrong Order Received">
                            <i class="fas fa-exchange-alt"></i>
                            <h4>Wrong Order Received</h4>
                            <p>Received incorrect items?</p>
                        </div>
                        <div class="issue-option <?php echo $preselected_type == 'Missing Items' ? 'selected' : ''; ?>" data-type="Missing Items">
                            <i class="fas fa-box-open"></i>
                            <h4>Missing Items</h4>
                            <p>Some items not delivered?</p>
                        </div>
                        <div class="issue-option <?php echo $preselected_type == 'General Support' ? 'selected' : ''; ?>" data-type="General Support">
                            <i class="fas fa-question-circle"></i>
                            <h4>General Support</h4>
                            <p>Questions or feedback?</p>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="orders-section">
                        <div class="orders-title">
                            <i class="fas fa-receipt"></i> Related Order
                            <span style="font-size: 0.7rem; font-weight: normal;">(Required for Wrong Order & Missing Items)</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.8rem;">
                            <div class="order-card order-card-empty" data-order-id="">
                                <i class="fas fa-question-circle" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block; color: #999;"></i>
                                <strong style="display: block; color: #999; font-size: 0.85rem; margin-bottom: 0.2rem;">No Order</strong>
                                <small style="color: #999;">N/A</small>
                            </div>
                            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                                <?php while($order = $recent_orders->fetch_assoc()): ?>
                                <div class="order-card" data-order-id="<?php echo $order['id']; ?>">
                                    <strong style="display: block; color: #8B4513; font-size: 0.9rem; margin-bottom: 0.3rem;">Order #<?php echo $order['id']; ?></strong>
                                    <small style="color: #8B7355; display: block; margin-bottom: 0.2rem;"><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></small>
                                    <small style="color: #2C1810; font-weight: 600; display: block; margin-bottom: 0.2rem;">$<?php echo number_format($order['total'], 2); ?></small>
                                    <span class="order-status <?php echo strtolower($order['status']); ?>" style="font-size: 0.7rem;"><?php echo $order['status']; ?></span>
                                </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="order_id" id="order_id" value="">
                        <p style="font-size: 0.7rem; color: #8B7355; margin-top: 0.8rem;">
                            <i class="fas fa-info-circle"></i> Select the order related to your issue to help us resolve it faster.
                        </p>
                    </div>

                    <!-- Message -->
                    <div class="form-group">
                        <label>Tell us what happened <span>*</span></label>
                        <textarea name="message" id="message" placeholder="Please describe your issue in detail. Include any relevant information that will help us assist you better..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>

                    <!-- Priority Level -->
                    <div class="priority-control">
                        <label class="priority-label">Issue Priority</label>
                        <div class="priority-options">
                            <div class="priority-option" data-priority="medium" title="I can wait a bit for this">
                                <i class="fas fa-hourglass-end" style="color: #F39C12;"></i>
                                <div class="priority-option-label">Standard</div>
                            </div>
                            <div class="priority-option selected" data-priority="high" title="This needs prompt attention">
                                <i class="fas fa-exclamation-circle" style="color: #E74C3C;"></i>
                                <div class="priority-option-label">Urgent</div>
                            </div>
                            <div class="priority-option" data-priority="low" title="Not time-sensitive">
                                <i class="fas fa-info-circle" style="color: #3498DB;"></i>
                                <div class="priority-option-label">Low Priority</div>
                            </div>
                        </div>
                        <input type="hidden" name="priority" id="priority" value="high">
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn" <?php echo empty($preselected_type) ? 'disabled' : ''; ?>>
                        <div class="svg-wrapper-1">
                            <div class="svg-wrapper">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                width="24"
                                height="24"
                            >
                                <path fill="none" d="M0 0h24v24H0z"></path>
                                <path
                                fill="currentColor"
                                d="M1.946 9.315c-.522-.174-.527-.455.01-.634l19.087-6.362c.529-.176.832.12.684.638l-5.454 19.086c-.15.529-.455.547-.679.045L12 14l6-8-8 6-8.054-2.685z"
                                ></path>
                            </svg>
                            </div>
                        </div>
                        <span>Submit Request</span>
                    </button>

                </form>
            </div>
        </div>

        <!-- Contact Information Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-phone-alt"></i>For more Info Contact:</h2>
            </div>
            <div class="card-body">
                <div style="background: white; padding: 1.5rem; border-radius: 1rem;">
                    <div style="margin-bottom: 1.2rem; padding-bottom: 1.2rem; border-bottom: 1px solid #E8E0D5;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 0.3rem;">
                            <i class="fas fa-phone" style="color: #8B4513; font-size: 1rem;"></i>
                            <strong style="color: #2C1810;">Phone:</strong>
                            <span style="color: #5C4B3A;">(+254) 704-296-337</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.2rem; padding-bottom: 1.2rem; border-bottom: 1px solid #E8E0D5;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 0.3rem;">
                            <i class="fas fa-envelope" style="color: #8B4513; font-size: 1rem;"></i>
                            <strong style="color: #2C1810;">Email:</strong>
                            <span style="color: #5C4B3A;">care@sunrisebreeders.com</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 0.3rem;">
                            <i class="fas fa-clock" style="color: #8B4513; font-size: 1rem;"></i>
                            <strong style="color: #2C1810;">Hours:</strong>
                            <span style="color: #5C4B3A;">Monday - Friday: 9am - 6pm</span>
                        </div>
                    </div>

                    <div style="background: #FEF3C7; border-radius: 0.6rem; padding: 0.8rem; text-align: center; margin-top: 1rem; border: 1px solid #F59E0B;">
                        <p style="font-size: 0.8rem; color: #92400E; margin: 0;">
                            <i class="fas fa-info-circle"></i> We typically respond within 2-4 hours during business hours.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h3 class="faq-title"><i class="fas fa-lightbulb"></i> Frequently Asked Questions</h3>
            
            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    How quickly will I hear back about my issue?
                </div>
                <div class="faq-answer">
                    We aim to respond to all customer care requests within 4 hours during business hours (Mon-Fri, 9am-6pm). For urgent issues, we prioritize responses to get you an answer even faster.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    What if I have a problem but don't remember my order number?
                </div>
                <div class="faq-answer">
                    No problem! Our system can look up your orders using your email address. If needed, you can also provide details like the order date, items ordered, or delivery date, and we can find your order that way.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    Am I guaranteed a refund or replacement?
                </div>
                <div class="faq-answer">
                    Yes! We're committed to 100% customer satisfaction. If you received the wrong items or items are missing, we'll either send a replacement ASAP or provide a full refund—your choice.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    Can I contact you outside business hours?
                </div>
                <div class="faq-answer">
                    Yes! You can submit your request anytime through this form, and our team will respond first thing when we're back in the office. We typically respond by 10am the next business day.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    How do I know the status of my issue?
                </div>
                <div class="faq-answer">
                    Once you submit your request, you'll receive confirmation emails at each step—when we receive it, when we start investigating, and when it's resolved. You can also follow up anytime by contacting us directly.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <i class="fas fa-chevron-right"></i>
                    What if I had a problem but already received a replacement?
                </div>
                <div class="faq-answer">
                    We still want to hear about it! Please let us know what happened so we can review our processes and prevent similar issues in the future. Your feedback helps us serve you better.
                </div>
            </div>
        </div>
    </div>

    <script>
        // Issue Type Selection
        const issueOptions = document.querySelectorAll('.issue-option');
        const issueTypeInput = document.getElementById('issue_type');
        const submitBtn = document.getElementById('submitBtn');
        const messageInput = document.getElementById('message');
        
        let selectedIssue = '<?php echo $preselected_type; ?>';

        issueOptions.forEach(option => {
            option.addEventListener('click', function() {
                issueOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                selectedIssue = this.dataset.type;
                issueTypeInput.value = selectedIssue;
                validateForm();
            });
        });

        // Order Card Selection
        const orderCards = document.querySelectorAll('.order-card');
        const orderIdInput = document.getElementById('order_id');
        let selectedOrderId = '';

        orderCards.forEach(card => {
            card.addEventListener('click', function() {
                orderCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                selectedOrderId = this.dataset.orderId;
                orderIdInput.value = selectedOrderId;
                validateForm();
            });
        });

        // Priority Selection
        const priorityOptions = document.querySelectorAll('.priority-option');
        const priorityInput = document.getElementById('priority');

        priorityOptions.forEach(option => {
            option.addEventListener('click', function() {
                priorityOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                priorityInput.value = this.dataset.priority;
            });
        });

        // Form Validation
        function validateForm() {
            const hasMessage = messageInput.value.trim() !== '';
            const hasIssue = selectedIssue !== '';
            
            // Check if order is required for this issue type
            let orderRequired = (selectedIssue === 'Wrong Order Received' || selectedIssue === 'Missing Items');
            let hasOrder = selectedOrderId !== '';
            
            if (hasMessage && hasIssue && (!orderRequired || hasOrder)) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        // Message input validation
        messageInput.addEventListener('input', function() {
            validateForm();
        });

        // Initial state
        if (selectedIssue !== '' && messageInput.value.trim() !== '') {
            validateForm();
        }

        // FAQ Accordion
        const faqQuestions = document.querySelectorAll('.faq-question');
        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                if (answer.style.display === 'none' || !answer.style.display) {
                    answer.style.display = 'block';
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-chevron-down');
                } else {
                    answer.style.display = 'none';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-right');
                }
            });
        });

        // Hide all FAQ answers initially
        document.querySelectorAll('.faq-answer').forEach(answer => {
            answer.style.display = 'none';
        });
    </script>
</body>
</html>