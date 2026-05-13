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
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, coffee_preference, loyal_points, join_date FROM customers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email, $phone, $coffee_preference, $loyalty_points, $join_date);
$stmt->fetch();
$stmt->close();

$full_name = $first_name . ' ' . $last_name;
$join_date_formatted = date('F j, Y', strtotime(null . $join_date));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunrise Breeders | Rodeo Coffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Add to <head> section -->
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><style>circle{fill:%238B4513;}path{fill:%23D2691E;}.sun{fill:%23FFD700;}</style><circle cx='50' cy='50' r='45'/><path d='M30,65 L70,65 L75,85 L25,85 Z'/><circle class='sun' cx='80' cy='20' r='15'/><path d='M80,5 L80,35 M65,20 L95,20 M70,10 L90,30 M70,30 L90,10' stroke='%23FFD700' stroke-width='3'/></svg>">

    <style>
        /* USER PROFILE BAR - ONLY THIS IS NEW */
        .user-bar {
            background: linear-gradient(to right, #8B4513, #D2691E);
            color: white;
            padding: 10px 0;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .user-bar .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .user-welcome {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        .user-welcome i {
            color: #F5DEB3;
        }
        
        .user-stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .stat-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .logout-btn-top {
            background-color: white;
            color: #8B4513;
            padding: 6px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .logout-btn-top:hover {
            background-color: #F5DEB3;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .user-bar .container {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .user-info {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-stats {
                justify-content: center;
            }
        }
    </style>
</head>
<body>


    <!-- ORIGINAL HOMEPAGE.HTML CONTENT STARTS HERE - NO CHANGES -->
    <!-- Original CSS from homepage.html -->
    <style>
        /* Base Styles & Variables */
        :root {
            --primary: #8B4513; /* SaddleBrown for rodeo feel */
            --secondary: #D2691E; /* Chocolate */
            --accent: #CD853F; /* Peru */
            --dark: #2C1810; /* Dark brown */
            --light: #F5DEB3; /* Wheat */
            --text: #3E2723; /* Dark brown text */
            --highlight: #A0522D; /* Sienna */
            --success: #2E7D32;
            --warning: #FF8F00;
            --danger: #C62828;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #FAF3E0;
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Optimized Header - Lighter Weight */
        header {
            background-color: var(--dark);
            padding: 0.7rem 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 2px solid var(--primary);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            flex-direction: column;
        }
        
        .logo h1 {
            color: var(--light);
            font-size: 1.8rem; /* Reduced from 2.2rem */
            letter-spacing: 1.5px; /* Reduced from 2px */
            text-transform: uppercase;
            font-weight: 700;
        }
        
        .logo .motto {
            color: var(--accent);
            font-style: italic;
            font-size: 0.8rem; /* Reduced from 0.9rem */
            letter-spacing: 0.8px; /* Reduced from 1px */
            margin-top: 2px; /* Reduced from 4px */
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 1.2rem; /* Reduced from 1.5rem */
        }
        
        nav ul li a {
            color: var(--light);
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px; /* Reduced from 1px */
            padding: 6px 10px; /* Reduced from 8px 12px */
            border-radius: var(--border-radius);
            transition: var(--transition);
            font-size: 0.9rem; /* Added smaller font */
        }
        
        nav ul li a:hover,
        nav ul li a.active {
            background-color: var(--primary);
            color: white;
        }
        
        /* Search Bar */
        .search-container {
            display: flex;
            align-items: center;
            margin-left: 1.5rem;
            position: relative;
        }
        
        .search-input {
            padding: 8px 12px;
            padding-right: 35px;
            border-radius: 20px;
            border: 1px solid var(--accent);
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            width: 200px;
            transition: var(--transition);
            font-size: 0.9rem;
        }
        
        .search-input:focus {
            outline: none;
            width: 250px;
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .search-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: var(--light);
            cursor: pointer;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-top: 5px;
            max-height: 300px;
            overflow-y: auto;
            display: none;
            z-index: 101;
        }
        
        .search-result-item {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        .search-result-item:hover {
            background-color: #f5f5f5;
        }
        
        .search-result-item img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 12px;
        }
        
        .search-result-item .info h4 {
            font-size: 0.95rem;
            margin-bottom: 3px;
        }
        
        .search-result-item .info p {
            font-size: 0.8rem;
            color: #666;
        }
        
        .no-results {
            padding: 15px;
            text-align: center;
            color: #666;
        }
        
        .cart-icon {
            position: relative;
            margin-left: 1.5rem; /* Reduced from 2rem */
            cursor: pointer;
            color: #A0522D;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--accent);
            color: white;
            border-radius: 50%;
            width: 18px; /* Reduced from 20px */
            height: 18px; /* Reduced from 20px */
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.75rem; /* Reduced from 0.8rem */
            font-weight: bold;
        }
        
        .hamburger {
            display: none;
            color: white;
            font-size: 1.3rem; /* Reduced from 1.5rem */
            cursor: pointer;
            margin-left: 1rem;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(44, 24, 16, 0.8), rgba(44, 24, 16, 0.9)), url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 4rem 1rem; /* Reduced from 5rem 1rem */
            margin-bottom: 2.5rem; /* Reduced from 3rem */
        }
        
        .hero h2 {
            font-size: 2.5rem; /* Reduced from 3rem */
            margin-bottom: 0.8rem; /* Reduced from 1rem */
            text-transform: uppercase;
            letter-spacing: 2.5px; /* Reduced from 3px */
        }
        
        .hero p {
            font-size: 1.1rem; /* Reduced from 1.2rem */
            max-width: 700px;
            margin: 0 auto 1.5rem; /* Reduced from 2rem */
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 10px 24px; /* Reduced from 12px 28px */
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px; /* Reduced from 1px */
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.9rem; /* Added smaller font */
        }
        
        .btn:hover {
            background-color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        
        /* Products Section */
        .section-title {
            text-align: center;
            margin-bottom: 2rem; /* Reduced from 2.5rem */
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2rem; /* Reduced from 2.2rem */
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 1.8px; /* Reduced from 2px */
            display: inline-block;
            padding-bottom: 8px; /* Reduced from 10px */
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            width: 90px; /* Reduced from 100px */
            height: 3px; /* Reduced from 4px */
            background-color: var(--primary);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .category-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem; /* Reduced from 2.5rem */
            flex-wrap: wrap;
        }
        
        .category-tab {
            padding: 8px 20px; /* Reduced from 10px 25px */
            background-color: white;
            border: 2px solid var(--accent);
            border-radius: 30px;
            margin: 0 8px 8px; /* Reduced from 0 10px 10px */
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            font-size: 0.9rem; /* Added smaller font */
        }
        
        .category-tab.active,
        .category-tab:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.8rem; /* Reduced from 2rem */
            margin-bottom: 3.5rem; /* Reduced from 4rem */
        }
        
        .product-card {
            background-color: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .product-card:hover {
            transform: translateY(-8px); /* Reduced from -10px */
            box-shadow: 0 10px 18px rgba(0, 0, 0, 0.15); /* Reduced from 0 12px 20px */
        }
        
        .product-image {
            height: 180px; /* Reduced from 200px */
            background-size: cover;
            background-position: center;
        }
        
        .product-info {
            padding: 1.2rem; /* Reduced from 1.5rem */
        }
        
        .product-info h3 {
            font-size: 1.2rem; /* Reduced from 1.3rem */
            margin-bottom: 0.4rem; /* Reduced from 0.5rem */
        }
        
        .product-info p {
            color: #666;
            margin-bottom: 0.8rem; /* Reduced from 1rem */
            font-size: 0.9rem; /* Reduced from 0.95rem */
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .price {
            font-size: 1.3rem; /* Reduced from 1.4rem */
            font-weight: 700;
            color: var(--primary);
        }
        
        .add-to-cart {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 7px 14px; /* Reduced from 8px 16px */
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.85rem; /* Added smaller font */
        }
        
        .add-to-cart:hover {
            background-color: var(--primary);
        }
        
        /* Delivery Section */
        .delivery-section {
            background-color: var(--dark);
            padding: 3.5rem 0; /* Reduced from 4rem 0 */
            margin-bottom: 3.5rem; /* Reduced from 4rem */
            color: white;
        }
        
        .delivery-form {
            max-width: 600px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 2rem; /* Reduced from 2.5rem */
            border-radius: var(--border-radius);
            backdrop-filter: blur(10px);
        }
        
        .form-group {
            margin-bottom: 1.3rem; /* Reduced from 1.5rem */
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.4rem; /* Reduced from 0.5rem */
            font-weight: 600;
            font-size: 0.95rem; /* Added smaller font */
        }
        
        .form-control {
            width: 100%;
            padding: 10px; /* Reduced from 12px */
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 0.95rem; /* Reduced from 1rem */
        }
        
        /* About & Customer Care */
        .about-section, .customer-care-section {
            padding: 3.5rem 0; /* Reduced from 4rem 0 */
        }
        
        .about-content {
            display: flex;
            align-items: center;
            gap: 2.5rem; /* Reduced from 3rem */
            margin-bottom: 2.5rem; /* Reduced from 3rem */
        }
        
        .about-image {
            flex: 1;
            height: 320px; /* Reduced from 350px */
            background: url('https://images.unsplash.com/photo-1442512595331-e89e73853f31?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') center/cover;
            border-radius: var(--border-radius);
        }
        
        .about-text {
            flex: 1;
        }
        
        .about-text h3 {
            font-size: 1.6rem; /* Reduced from 1.8rem */
            margin-bottom: 0.8rem; /* Reduced from 1rem */
            color: var(--dark);
        }
        
        .customer-care-section {
            background-color: #f9f5f0;
        }
        
        .care-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Reduced from 300px */
            gap: 1.8rem; /* Reduced from 2rem */
            margin-top: 1.8rem; /* Reduced from 2rem */
        }
        
        .care-card {
            background-color: white;
            padding: 1.8rem; /* Reduced from 2rem */
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
        }
        
        .care-card i {
            font-size: 2.2rem; /* Reduced from 2.5rem */
            color: var(--primary);
            margin-bottom: 0.8rem; /* Reduced from 1rem */
        }
        
        .care-card h4 {
            font-size: 1.2rem; /* Reduced from 1.3rem */
            margin-bottom: 0.8rem; /* Reduced from 1rem */
        }
        
        /* Footer */
        footer {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0.7) 100%), url('https://i.pinimg.com/1200x/15/90/e7/1590e778918b6bbfb99df3d2431f8bac.jpg') center/cover no-repeat;
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
            overflow: hidden;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }
        
        footer .container {
            position: relative;
            z-index: 1;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-bottom: 3rem;
            gap: 2rem;
        }
        
        .footer-column {
            flex: 0 1 280px;
            text-align: center;
            padding: 0 1rem;
        }
        
        .footer-column h3 {
            color: #D4A574;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            letter-spacing: 1px;
        }
        
        .footer-column p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            line-height: 1.8;
            margin-bottom: 0.8rem;
            font-weight: 300;
        }
        
        .footer-column p i {
            color: #D4A574;
            margin-right: 8px;
            width: 18px;
        }
        
        .footer-column:first-child p:first-of-type {
            font-style: italic;
            color: #D4A574;
            font-size: 0.9rem;
            margin-bottom: 1.2rem;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.2rem;
        }
        
        .social-icons a {
            color: white;
            background-color: #8B4513;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .social-icons a:hover {
            background-color: #D4A574;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(212, 165, 116, 0.3);
        }
        
        .copyright {
            border-top: 1px solid rgba(212, 165, 116, 0.3);
            padding-top: 1.5rem;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
        }
        
        @media (max-width: 768px) {
            footer {
                padding: 3rem 0 1.5rem;
            }
            
            .footer-content {
                flex-direction: column;
                margin-bottom: 2rem;
                gap: 2.5rem;
            }
            
            .footer-column {
                flex: 1 1 100%;
            }
        }
        
        /* Cart Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 600px;
            border-radius: var(--border-radius);
            padding: 1.8rem; /* Reduced from 2rem */
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.3rem; /* Reduced from 1.5rem */
            padding-bottom: 0.8rem; /* Reduced from 1rem */
            border-bottom: 2px solid var(--light);
        }
        
        .close-modal {
            font-size: 1.6rem; /* Reduced from 1.8rem */
            cursor: pointer;
            color: var(--dark);
        }
        
        .cart-items {
            margin-bottom: 1.3rem; /* Reduced from 1.5rem */
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0; /* Reduced from 1rem 0 */
            border-bottom: 1px solid #eee;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-info h4 {
            margin-bottom: 0.2rem; /* Reduced from 0.3rem */
            font-size: 0.95rem; /* Added smaller font */
        }
        
        .cart-item-price {
            font-weight: bold;
            color: var(--primary);
            font-size: 0.9rem; /* Added smaller font */
        }
        
        .cart-total {
            text-align: right;
            font-size: 1.2rem; /* Reduced from 1.3rem */
            font-weight: bold;
            margin-bottom: 1.3rem; /* Reduced from 1.5rem */
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .about-content {
                flex-direction: column;
            }
            
            .about-image {
                width: 100%;
            }
            
            .search-input {
                width: 180px;
            }
            
            .search-input:focus {
                width: 220px;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-wrap: wrap;
            }
            
            .search-container {
                order: 2;
                width: 100%;
                margin: 0.8rem 0 0 0;
                display: none;
            }
            
            .search-container.active {
                display: flex;
            }
            
            .search-input {
                width: 100%;
            }
            
            .search-input:focus {
                width: 100%;
            }
            
            nav {
                order: 3;
                width: 100%;
                margin-top: 0.8rem; /* Reduced from 1rem */
                display: none;
            }
            
            nav.active {
                display: block;
            }
            
            nav ul {
                flex-direction: column;
            }
            
            nav ul li {
                margin: 0 0 0.3rem 0; /* Reduced from 0.5rem */
            }
            
            .hamburger {
                display: block;
            }
            
            .hero h2 {
                font-size: 2rem; /* Reduced from 2.2rem */
            }
            
            .footer-content {
                flex-direction: column;
            }
            
            .cart-icon {
                margin-left: auto;
            }
        }
        
        @media (max-width: 576px) {
            .hero {
                padding: 2.5rem 1rem; /* Reduced from 3rem 1rem */
            }
            
            .hero h2 {
                font-size: 1.6rem; /* Reduced from 1.8rem */
            }
            
            .delivery-form {
                padding: 1.2rem; /* Reduced from 1.5rem */
            }
            
            .care-options {
                grid-template-columns: 1fr;
            }
        }

        .contain {
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #FAF3E0;
        }

        .cardbody {
            width: 120%;
            max-width: 550px;
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: stretch;
            gap: 1.25rem;
            transition: all 400ms;
        }

        .cardo {
            flex: 1;
            height: 100%;
            transition: all 400ms;
            cursor: pointer;
        }
        .cardo > img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cardo {
            flex: 1;
            height: 100%;
            transition: all 400ms;
            cursor: pointer;
        }
        .cardo:nth-child(odd) {
            translate: 0 -20px;
        }
        .cardo:nth-child(even) {
            translate: 0 20px;
        }
        .contain:hover .cardo:not(:hover) {
            filter: grayscale(100%);
        }
        .cardo:hover {
            flex: 3;
        }
    </style>
</head>
<body>
    <!-- User Profile Bar - NEW ADDITION -->
    <div class="user-bar">
        <div class="container">
            <div class="user-info">
                <div class="user-welcome">
                    <i class="fas fa-user-circle"></i>
                    <span>Welcome, <?php echo htmlspecialchars($first_name); ?>!</span>
                </div>
                <div class="user-stats">
                    <div class="stat-badge">
                        <i class="fas fa-coffee"></i>
                        Preference: <?php echo htmlspecialchars($coffee_preference ?: 'Not set'); ?>
                    </div>
                    <div class="stat-badge">
                        <i class="fas fa-star"></i>
                        Points: <?php echo $loyalty_points; ?>
                    </div>
                    <div class="stat-badge">
                        <i class="fas fa-calendar-alt"></i>
                        Member since: <?php echo $join_date_formatted; ?>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn-top">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Original homepage.html content starts here - NO CHANGES -->
    <header>
        <div class="container header-content">
            <div class="logo">
                <h1>Sunrise Breeders</h1>
                <div class="motto">"Where every cup is a sunrise for your soul"</div>
            </div>
            
            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>
            
            <div class="search-container" id="search-container">
                <input type="text" class="search-input" id="search-input" placeholder="Search products...">
                <button class="search-btn" id="search-btn">
                    <i class="fas fa-search"></i>
                </button>
                <div class="search-results" id="search-results"></div>
            </div>

            <nav id="main-nav">
                <ul>
                    <li><a href="#home" class="active">Home</a></li>
                    <li><a href="#products">Products</a></li>
                    <li><a href="#delivery">Delivery</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#customer-care">Customer Care</a></li>
                </ul>
            </nav>
            
            <div class="cart-icon" id="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cart-count">0</span>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <h2>Saddle Up for Great Coffee</h2>
            <p>At Sunrise Breeders, we blend the spirit of the rodeo with the craftsmanship of vigilante coffee brewing. Our beans are carefully selected and roasted to perfection, delivering bold flavors that kickstart your day.</p>
            <a href="#products" class="btn">View Our Products</a>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section" id="products">
        <div class="container">
            <div class="section-title">
                <h2>Our Products</h2>
            </div>
            
            <div class="category-tabs">
                <div class="category-tab active" data-category="all">All</div>
                <div class="category-tab" data-category="drinks">Drinks</div>
                <div class="category-tab" data-category="food">Food</div>
                <div class="category-tab" data-category="meal">Meals</div>
            </div>
            
            <div class="products-grid" id="products-grid">
                <!-- Products will be dynamically inserted here -->
            </div>
        </div>
    </section>

    <!-- Delivery Section -->
    <section class="delivery-section" id="delivery">
        <div class="container">
            <div class="section-title">
                <h2 style="color: white;">Place a Delivery Order</h2>
            </div>
            
            <div class="delivery-form">
                <form id="delivery-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Delivery Address</label>
                        <textarea id="address" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="order-items">Order Items (from cart)</label>
                        <textarea id="order-items" class="form-control" rows="4" readonly></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="instructions">Special Instructions</label>
                        <textarea id="instructions" class="form-control" rows="3" placeholder="Any special requests or delivery instructions..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn" style="width: 100%;">Place Delivery Order</button>
                </form>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="section-title">
                <h2>About Sunrise Breeders</h2>
            </div>
            <br><br>
            <div class="about-content">
                <div class="about-imag">
                    <div class="contain">
                        <div class="cardbody">
                            <div class="cardo">
                                <img src="https://i.pinimg.com/736x/ca/65/73/ca65731de1028efc642f6541ad26713d.jpg" />
                            </div>
                            <div class="cardo">
                                <img src="https://i.pinimg.com/736x/9a/21/13/9a211344ac13f46a318d28a5d3698bb9.jpg" />
                            </div>
                            <div class="cardo">
                                <img src="https://i.pinimg.com/736x/8c/13/62/8c136290c433afa9fcd0dd2a9fe523af.jpg" />
                            </div>
                            <div class="cardo">
                                <img src="https://i.pinimg.com/736x/2d/5b/62/2d5b62ac0cbf77664e1a84f2a180048f.jpg" />
                            </div>
                            <div class="cardo">
                                <img src="https://i.pinimg.com/736x/99/6c/2b/996c2bb026d8bca5763441ab00becfdf.jpg" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-text">
                    <h3>Our Rodeo & Vigilante Spirit</h3>
                    <p>Founded in 2010, Sunrise Breeders began as a small coffee stand at local rodeo events. Our founders, two siblings with a passion for both coffee and the rodeo lifestyle, wanted to create a coffee experience that captures the bold, adventurous spirit of the West.</p>
                    <p>We source our beans directly from sustainable farms, roast them with vigilante precision, and serve them with the warm hospitality of a ranch house kitchen. Every cup tells a story of tradition, craftsmanship, and the pursuit of perfect flavor.</p>
                    <p>Our name "Sunrise Breeders" reflects our commitment to starting your day right – just as breeders care for the dawn of new life, we nurture the start of your day with exceptional coffee.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Care Section -->
    <section class="customer-care-section" id="customer-care">
        <div class="container">
            <div class="section-title">
                <h2>Customer Care Services</h2>
            </div>
            
            <p style="text-align: center; max-width: 800px; margin: 0 auto 1.2rem;">We're committed to making every experience with Sunrise Breeders exceptional. If you encounter any issues with your order, we're here to help.</p>
            
            <div class="care-options">
                <div class="care-card">
                    <i class="fas fa-undo-alt"></i>
                    <h4>Wrong Order Received</h4>
                    <p>If you received the wrong items in your delivery, contact us within 24 hours for a replacement or refund.</p>
                    <a href="customer_care.php?type=wrong-order" class="btn" style="margin-top: 0.8rem;">Report Issue</a>
                </div>
                
                <div class="care-card">
                    <i class="fas fa-times-circle"></i>
                    <h4>Missing Items</h4>
                    <p>If any items are missing from your order, let us know and we'll deliver them ASAP or refund the amount.</p>
                    <a href="customer_care.php?type=missing-items" class="btn" style="margin-top: 0.8rem;">Report Issue</a>
                </div>
                
                <div class="care-card">
                    <i class="fas fa-headset"></i>
                    <h4>General Support</h4>
                    <p>Have questions about our products, need brewing tips, or just want to give feedback? We're here for you.</p>
                    <a href="customer_care.php?type=general-support" class="btn" style="margin-top: 0.8rem;">Contact Support</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Sunrise Breeders</h3>
                    <p>"Where every cup is a sunrise for your soul"</p>
                    <div class="social-icons">
                        <a href="https://facebook.com/sunrisebreeders" target="_blank" rel="noopener noreferrer" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://instagram.com/sunrisebreeders" target="_blank" rel="noopener noreferrer" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://twitter.com/sunrisebreeders" target="_blank" rel="noopener noreferrer" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://tiktok.com/@sunrisebreeders" target="_blank" rel="noopener noreferrer" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Rodeo Drive, Austin, TX</p>
                    <p><i class="fas fa-phone"></i> (512) 555-BREW</p>
                    <p><i class="fas fa-envelope"></i> info@sunrisebreeders.com</p>
                </div>
                
                <div class="footer-column">
                    <h3>Opening Hours</h3>
                    <p>Monday - Friday: 6am - 9pm</p>
                    <p>Saturday - Sunday: 7am - 10pm</p>
                    <p>Holidays: 7am - 8pm</p>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2026 Sunrise Breeders Coffee Shop. All rights reserved. | Created by Zoro Uchiha</p>
            </div>
        </div>
    </footer>

    <!-- Cart Modal -->
    <div class="modal" id="cart-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Your Cart</h2>
                <span class="close-modal" id="close-modal">&times;</span>
            </div>
            
            <div class="cart-items" id="cart-items">
                <!-- Cart items will be dynamically inserted here -->
                <p id="empty-cart-message">Your cart is empty</p>
            </div>
            
            <div class="cart-total">
                Total: $<span id="cart-total">0.00</span>
            </div>
            
            <button class="btn" id="checkout-btn" style="width: 100%;" onclick="proceedToCheckout()">Proceed to Checkout</button>
        </div>
    </div>

    <!-- JavaScript from homepage.html - NO CHANGES -->
    <script>
        // Products Data - 10 products in each category (30 total)
        // Products Data - 10 products in each category (30 total)
const products = [
    // DRINKS (10 items) - FIXED
    {
        id: 1,
        name: "Rodeo Rider Espresso",
        category: "drinks",
        description: "Strong, bold espresso for those who need a kickstart to their day.",
        price: 3.50,
        image: "https://i.pinimg.com/1200x/c3/22/20/c32220f526a6f8530863f392b414d0bc.jpg"
    },
    {
        id: 2,
        name: "Vigilante Cold Brew",
        category: "drinks",
        description: "Smooth cold brew with a hint of vanilla and caramel.",
        price: 4.75,
        image: "https://images.unsplash.com/photo-1461023058943-07fcbe16d735?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 3,
        name: "Sunrise Latte",
        category: "drinks",
        description: "Our signature latte with honey and cinnamon notes.",
        price: 4.25,
        image: "https://images.unsplash.com/photo-1561047029-3000c68339ca?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 4,
        name: "Cowboy Cappuccino",
        category: "drinks",
        description: "Rich cappuccino with a dusting of cocoa and cowboy spirit.",
        price: 4.50,
        image: "https://images.unsplash.com/photo-1534778101976-62847782c213?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 5,
        name: "Desert Drip Coffee",
        category: "drinks",
        description: "Classic drip coffee with notes of walnut and dark chocolate.",
        price: 2.99,
        image: "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 6,
        name: "Ranch Mocha",
        category: "drinks",
        description: "Decadent mocha with house-made chocolate and whipped cream.",
        price: 5.25,
        image: "https://images.unsplash.com/photo-1514066558159-fc8c737ef259?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 7,
        name: "Trail Mix Tea",
        category: "drinks",
        description: "Herbal tea blend with citrus, ginger, and wild berries.",
        price: 3.75,
        image: "https://i.pinimg.com/736x/24/01/ad/2401adff1666e285041fcd0b0700856f.jpg"
    },
    {
        id: 8,
        name: "Cactus Cooler",
        category: "drinks",
        description: "Refreshing green tea with prickly pear and lime.",
        price: 4.00,
        image: "https://i.pinimg.com/736x/3e/9e/44/3e9e449a7c700f52708c7ee7b544dbef.jpg"
    },
    {
        id: 9,
        name: "Midnight Rider",
        category: "drinks",
        description: "Double-shot espresso over ice with cream and vanilla.",
        price: 4.95,
        image: "https://images.unsplash.com/photo-1517701604599-bb29b565090c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 10,
        name: "Prairie Fog",
        category: "drinks",
        description: "Earl Grey tea latte with lavender and honey.",
        price: 4.50,
        image: "https://images.unsplash.com/photo-1594631252845-29fc4cc8cde9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    
    // FOOD (10 items) - FIXED
    {
        id: 11,
        name: "Cowboy Breakfast Burrito",
        category: "food",
        description: "Hearty burrito with eggs, sausage, potatoes, and cheese.",
        price: 7.99,
        image: "https://i.pinimg.com/1200x/c8/f3/6f/c8f36f63b74b9654fbc0edd06d18b110.jpg"
    },
    {
        id: 12,
        name: "Ranch Hand Sandwich",
        category: "food",
        description: "Turkey, bacon, avocado, and Swiss on sourdough.",
        price: 8.50,
        image: "https://images.unsplash.com/photo-1481070414801-51fd732d7184?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 13,
        name: "Homestyle Biscuit",
        category: "food",
        description: "Freshly baked biscuit with butter and jam.",
        price: 2.99,
        image: "https://images.unsplash.com/photo-1586190848861-99aa4a171e90?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 14,
        name: "Saddlebag Scone",
        category: "food",
        description: "Blueberry scone with lemon glaze.",
        price: 3.50,
        image: "https://images.unsplash.com/photo-1558961363-fa8fdf82db35?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 15,
        name: "Wranglers Waffle",
        category: "food",
        description: "Belgian waffle with maple syrup and berries.",
        price: 6.75,
        image: "https://images.unsplash.com/photo-1562376552-0d160a2f238d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 16,
        name: "Rodeo Roll",
        category: "food",
        description: "Cinnamon roll with cream cheese frosting.",
        price: 4.25,
        image: "https://i.pinimg.com/1200x/e2/94/e0/e294e0ad7cb034e332262619c95e2bd7.jpg"
    },
    {
        id: 17,
        name: "Cow Chip Cookies",
        category: "food",
        description: "Chocolate chip cookies, baked fresh daily.",
        price: 2.50,
        image: "https://images.unsplash.com/photo-1499636136210-6f4ee915583e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 18,
        name: "Prairie Pound Cake",
        category: "food",
        description: "Lemon pound cake with raspberry drizzle.",
        price: 4.95,
        image: "https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 19,
        name: "Sunrise Muffin",
        category: "food",
        description: "Banana nut muffin with streusel topping.",
        price: 3.75,
        image: "https://images.unsplash.com/photo-1576867757603-05b134ebc379?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 20,
        name: "Campfire Brownie",
        category: "food",
        description: "Fudgy brownie with marshmallow and pecans.",
        price: 4.25,
        image: "https://i.pinimg.com/736x/15/87/db/1587db0c22a83a51c9d57acfb0c419b8.jpg"
    },
    
    // MEALS (10 items) - FIXED
    {
        id: 21,
        name: "Chuckwagon Chili Bowl",
        category: "meal",
        description: "Hearty beef chili with beans, cornbread on the side.",
        price: 10.99,
        image: "https://images.unsplash.com/photo-1547592166-23ac45744acd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 22,
        name: "Trailblazer Salad",
        category: "meal",
        description: "Grilled chicken, mixed greens, pecans, and balsamic dressing.",
        price: 9.75,
        image: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 23,
        name: "Sunset Steak Platter",
        category: "meal",
        description: "Grilled steak with roasted potatoes and seasonal vegetables.",
        price: 16.50,
        image: "https://images.unsplash.com/photo-1600891964092-4316c288032e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 24,
        name: "Ranch House Burger",
        category: "meal",
        description: "Beef burger with cheddar, bacon, and house sauce.",
        price: 12.99,
        image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 25,
        name: "Stampede Quesadilla",
        category: "meal",
        description: "Chicken and cheese quesadilla with pico de gallo and sour cream.",
        price: 11.50,
        image: "https://images.unsplash.com/photo-1565299585323-38d6b0865b47?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 26,
        name: "Lonesome Pizza",
        category: "meal",
        description: "Wood-fired pizza with pepperoni, mushrooms, and bell peppers.",
        price: 14.75,
        image: "https://images.unsplash.com/photo-1593246049226-ded77bf90326?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 27,
        name: "Vigilante Veggie Bowl",
        category: "meal",
        description: "Quinoa bowl with roasted vegetables and tahini dressing.",
        price: 10.25,
        image: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 28,
        name: "Cattleman Club",
        category: "meal",
        description: "Triple-decker club sandwich with turkey, ham, and bacon.",
        price: 13.25,
        image: "https://images.unsplash.com/photo-1550304943-4f24f54ddde9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 29,
        name: "Outlaw Mac & Cheese",
        category: "meal",
        description: "Three-cheese macaroni with breadcrumb topping.",
        price: 9.99,
        image: "https://images.unsplash.com/photo-1543339494-b4cd4f7ba686?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 30,
        name: "Prairie Pot Pie",
        category: "meal",
        description: "Chicken pot pie with flaky crust and mixed vegetables.",
        price: 11.95,
        image: "https://i.pinimg.com/1200x/dd/7f/ca/dd7fca6e39d277fe4d4647741deccda5.jpg"
    }
];

        // Cart state
        let cart = JSON.parse(localStorage.getItem('sunriseBreedersCart')) || [];
        
        // DOM Elements
        const productsGrid = document.getElementById('products-grid');
        const categoryTabs = document.querySelectorAll('.category-tab');
        const cartIcon = document.getElementById('cart-icon');
        const cartCount = document.getElementById('cart-count');
        const cartModal = document.getElementById('cart-modal');
        const closeModal = document.getElementById('close-modal');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const emptyCartMessage = document.getElementById('empty-cart-message');
        const orderItemsTextarea = document.getElementById('order-items');
        const deliveryForm = document.getElementById('delivery-form');
        const hamburger = document.getElementById('hamburger');
        const mainNav = document.getElementById('main-nav');
        const navLinks = document.querySelectorAll('nav a');
        
        // Search Elements
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        const searchResults = document.getElementById('search-results');
        const searchContainer = document.getElementById('search-container');

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            renderProducts('all');
            updateCart();
            setupEventListeners();
            
            // Set active nav link based on scroll position
            window.addEventListener('scroll', setActiveNavLink);
        });
        
        // Render products based on category
        function renderProducts(category, searchTerm = '') {
            productsGrid.innerHTML = '';
            
            let filteredProducts;
            
            if (searchTerm) {
                // Filter by search term
                filteredProducts = products.filter(product => 
                    product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    product.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    product.category.toLowerCase().includes(searchTerm.toLowerCase())
                );
            } else {
                // Filter by category
                filteredProducts = category === 'all' 
                    ? products 
                    : products.filter(product => product.category === category);
            }
            
            if (filteredProducts.length === 0) {
                productsGrid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #666;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; color: var(--accent);"></i>
                        <h3>No products found</h3>
                        <p>Try a different search term or category</p>
                    </div>
                `;
                return;
            }
            
            filteredProducts.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card';
                productCard.innerHTML = `
                    <div class="product-image" style="background-image: url('${product.image}')"></div>
                    <div class="product-info">
                        <h3>${product.name}</h3>
                        <p>${product.description}</p>
                        <div class="product-footer">
                            <div class="price">$${product.price.toFixed(2)}</div>
                            <button class="add-to-cart" data-id="${product.id}">Add to Cart</button>
                        </div>
                    </div>
                `;
                
                productsGrid.appendChild(productCard);
            });
            
            // Add event listeners to "Add to Cart" buttons
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = parseInt(this.getAttribute('data-id'));
                    addToCart(productId);
                });
            });
        }
        
        // Search functionality
        function performSearch(searchTerm) {
            if (searchTerm.trim() === '') {
                // If search is empty, show all products in current category
                const activeCategory = document.querySelector('.category-tab.active').getAttribute('data-category');
                renderProducts(activeCategory);
                searchResults.style.display = 'none';
                return;
            }
            
            // Filter products for search results
            const searchResultsArray = products.filter(product => 
                product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                product.description.toLowerCase().includes(searchTerm.toLowerCase())
            );
            
            // Display search results dropdown
            displaySearchResults(searchResultsArray, searchTerm);
            
            // Also filter the main product grid
            renderProducts('all', searchTerm);
            
            // Update category tabs to show "All" as active when searching
            categoryTabs.forEach(tab => {
                if (tab.getAttribute('data-category') === 'all') {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
        }
        
        // Display search results in dropdown
        function displaySearchResults(results, searchTerm) {
            searchResults.innerHTML = '';
            
            if (results.length === 0) {
                searchResults.innerHTML = `<div class="no-results">No products found for "${searchTerm}"</div>`;
                searchResults.style.display = 'block';
                return;
            }
            
            results.forEach(product => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                resultItem.innerHTML = `
                    <img src="${product.image}" alt="${product.name}">
                    <div class="info">
                        <h4>${product.name}</h4>
                        <p>${product.description.substring(0, 60)}...</p>
                        <p><strong>$${product.price.toFixed(2)}</strong> • ${product.category}</p>
                    </div>
                `;
                
                resultItem.addEventListener('click', function() {
                    // Add product to cart when clicked in search results
                    addToCart(product.id);
                    searchInput.value = '';
                    searchResults.style.display = 'none';
                    showNotification(`${product.name} added to cart from search!`);
                });
                
                searchResults.appendChild(resultItem);
            });
            
            searchResults.style.display = 'block';
        }
        
        // Add product to cart
        function addToCart(productId) {
            const product = products.find(p => p.id === productId);
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1
                });
            }
            
            updateCart();
            showNotification(`${product.name} added to cart!`);
            
            // Save cart to localStorage
            localStorage.setItem('sunriseBreedersCart', JSON.stringify(cart));
        }
        
        // Update cart UI
        function updateCart() {
            // Update cart count
            const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
            cartCount.textContent = totalItems;
            
            // Update order items textarea
            updateOrderItemsTextarea();
            
            // Update cart modal if it's open
            if (cartModal.style.display === 'flex') {
                renderCartItems();
            }
        }
        
        // Update order items textarea in delivery form
        function updateOrderItemsTextarea() {
            if (cart.length === 0) {
                orderItemsTextarea.value = "Your cart is empty. Add items from the products section.";
                return;
            }
            
            let text = "Your order includes:\n";
            cart.forEach(item => {
                text += `- ${item.name} (x${item.quantity}) - $${(item.price * item.quantity).toFixed(2)}\n`;
            });
            
            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            text += `\nTotal: $${total.toFixed(2)}`;
            
            orderItemsTextarea.value = text;
        }
        
        // Render cart items in modal
        function renderCartItems() {
            cartItems.innerHTML = '';
            
            if (cart.length === 0) {
                emptyCartMessage.style.display = 'block';
                cartTotal.textContent = '0.00';
                return;
            }
            
            emptyCartMessage.style.display = 'none';
            
            let total = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <div class="cart-item-info">
                        <h4>${item.name}</h4>
                        <div class="cart-item-price">$${item.price.toFixed(2)} × ${item.quantity}</div>
                    </div>
                    <div>
                        <span class="cart-item-price">$${itemTotal.toFixed(2)}</span>
                        <button class="btn remove-item" data-id="${item.id}" style="padding: 5px 10px; margin-left: 10px; font-size: 0.8rem;">Remove</button>
                    </div>
                `;
                
                cartItems.appendChild(cartItem);
            });
            
            // Add event listeners to remove buttons
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = parseInt(this.getAttribute('data-id'));
                    removeFromCart(productId);
                });
            });
            
            cartTotal.textContent = total.toFixed(2);
        }
        
        // Remove item from cart
        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            localStorage.setItem('sunriseBreedersCart', JSON.stringify(cart));
            updateCart();
            renderCartItems();
        }
        
        // Show notification
        function showNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 80px; /* Adjusted for lighter header */
                right: 20px;
                background-color: var(--primary);
                color: white;
                padding: 12px 18px; /* Reduced from 15px 20px */
                border-radius: var(--border-radius);
                box-shadow: var(--shadow);
                z-index: 1001;
                font-weight: 600;
                animation: slideIn 0.3s ease;
                font-size: 0.9rem; /* Added smaller font */
            `;
            
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Proceed to checkout
        function proceedToCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty. Add some items before checking out.');
                return;
            }
            
            // Close cart modal
            cartModal.style.display = 'none';
            
            // Scroll to delivery section
            document.getElementById('delivery').scrollIntoView({ behavior: 'smooth' });
            
            // Show notification
            showNotification('Scroll down to complete your delivery order!');
        }
        
        // Report issue functions
        function reportIssue(issueType) {
            const issueMessages = {
                'wrong-order': 'Please provide your order number and describe what was wrong with your order.',
                'missing-items': 'Please provide your order number and list the missing items.'
            };
            
            const message = issueMessages[issueType] || 'Please describe the issue you encountered.';
            alert(`To report this issue: ${message}\n\nOur customer care team will contact you within 24 hours.`);
        }
        
        function contactSupport() {
            alert('For general support, please call us at (512) 555-BREW or email info@sunrisebreeders.com.\n\nWe typically respond within 2-4 hours during business hours.');
        }
        
        // Setup event listeners
        function setupEventListeners() {
            // Category tabs
            categoryTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    categoryTabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Render products for selected category
                    const category = this.getAttribute('data-category');
                    renderProducts(category);
                    
                    // Clear search
                    searchInput.value = '';
                    searchResults.style.display = 'none';
                });
            });
            
            // Search functionality
            searchInput.addEventListener('input', function() {
                performSearch(this.value);
            });
            
            searchBtn.addEventListener('click', function() {
                performSearch(searchInput.value);
            });
            
            // Hide search results when clicking outside
            document.addEventListener('click', function(event) {
                if (!searchContainer.contains(event.target)) {
                    searchResults.style.display = 'none';
                }
            });
            
            // Cart icon click
            cartIcon.addEventListener('click', function() {
                renderCartItems();
                cartModal.style.display = 'flex';
            });
            
            // Close modal
            closeModal.addEventListener('click', function() {
                cartModal.style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === cartModal) {
                    cartModal.style.display = 'none';
                }
            });
            
            // Delivery form submission
            deliveryForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                console.log("Form submitted!"); // Debug log
                
                if (cart.length === 0) {
                    alert('Your cart is empty. Please add items before placing an order.');
                    return;
                }
                
                const name = document.getElementById('name').value;
                const address = document.getElementById('address').value;
                const phone = document.getElementById('phone').value;
                const instructions = document.getElementById('instructions').value;
                const orderItems = orderItemsTextarea.value;
                
                // Calculate total from cart properly
                let totalAmount = 0;
                cart.forEach(item => {
                    totalAmount += item.price * item.quantity;
                });
                
                console.log("Cart items:", cart);
                console.log("Calculated total:", totalAmount);
                console.log("Order items length:", orderItems.length);
                console.log("Sending order:", {name, address, phone, orderItems, total: totalAmount});
                
                // Use FormData instead of URLSearchParams for better character encoding
                const formData = new FormData();
                formData.append('name', name);
                formData.append('address', address);
                formData.append('phone', phone);
                formData.append('instructions', instructions);
                formData.append('items', orderItems);
                formData.append('total', totalAmount.toString());
                
                // Send order data to server
                fetch('save_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    console.log("Server response:", data);
                    if (data.includes('Error') || data.includes('error')) {
                        alert('Problem saving order: ' + data);
                    } else {
                        alert(`Thank you, ${name}! Your order has been received. Total: $${totalAmount.toFixed(2)}`);
                        
                        // Reset form and cart
                        deliveryForm.reset();
                        cart = [];
                        localStorage.removeItem('sunriseBreedersCart');
                        updateCart();
                        renderCartItems();
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error placing order. Please try again.');
                });
            });
            
            // Hamburger menu for mobile
            hamburger.addEventListener('click', function() {
                mainNav.classList.toggle('active');
                searchContainer.classList.toggle('active');
            });
            
            // Nav links click
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Close mobile menu if open
                    mainNav.classList.remove('active');
                    searchContainer.classList.remove('active');
                    
                    // Remove active class from all links
                    navLinks.forEach(l => l.classList.remove('active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                });
            });
        }
        
        // Set active nav link based on scroll position
        function setActiveNavLink() {
            const sections = document.querySelectorAll('section[id], header');
            const scrollPos = window.scrollY + 80; /* Adjusted for lighter header */
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                const sectionId = section.getAttribute('id') || 'home';
                
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }
        
        // Add CSS animations for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            
            .notification {
                transition: transform 0.3s ease, opacity 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>