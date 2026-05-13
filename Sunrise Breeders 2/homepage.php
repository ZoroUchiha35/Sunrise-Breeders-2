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
$join_date_formatted = date('F j, Y', strtotime($join_date));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunrise Breeders | Rodeo Coffee</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><style>circle{fill:%238B4513;}path{fill:%23D2691E;}.sun{fill:%23FFD700;}</style><circle cx='50' cy='50' r='45'/><path d='M30,65 L70,65 L75,85 L25,85 Z'/><circle class='sun' cx='80' cy='20' r='15'/><path d='M80,5 L80,35 M65,20 L95,20 M70,10 L90,30 M70,30 L90,10' stroke='%23FFD700' stroke-width='3'/></svg>">

    <style>
        :root {
            --primary: #8B4513;
            --primary-light: #A0522D;
            --secondary: #D2691E;
            --accent: #CD853F;
            --dark: #2C1810;
            --light: #FDF8F0;
            --white: #ffffff;
            --text-main: #3E2723;
            --text-muted: #6D4C41;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1);
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #FAF6F0;
            color: var(--text-main);
            line-height: 1.7;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, .logo h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* --- User Profile Bar --- */
        .user-bar {
            background-color: var(--dark);
            color: rgba(255,255,255,0.9);
            padding: 10px 0;
            font-size: 13px;
            font-weight: 500;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .user-bar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-stats {
            display: flex;
            gap: 20px;
        }

        .stat-item i {
            color: var(--accent);
            margin-right: 6px;
        }

        .logout-link {
            color: var(--accent);
            text-decoration: none;
            transition: var(--transition);
        }

        .logout-link:hover {
            color: var(--white);
        }

        /* --- Navigation --- */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 15px 0;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        header.scrolled {
            padding: 10px 0;
            background-color: var(--white);
            box-shadow: var(--shadow-md);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo h1 {
            font-size: 1.5rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: -4px;
        }

        .logo .motto {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
            position: relative;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: var(--transition);
        }

        nav ul li a:hover::after, nav ul li a.active::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-trigger, .cart-icon {
            font-size: 1.2rem;
            color: var(--text-main);
            cursor: pointer;
            transition: var(--transition);
        }

        .cart-icon {
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -10px;
            background-color: var(--secondary);
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* --- Hero Section --- */
        .hero {
            position: relative;
            height: 80vh;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=2070&q=80') center/cover;
            color: var(--white);
            margin-bottom: 80px;
        }

        .hero-content h2 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .hero-content p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        .btn {
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            transition: var(--transition);
            display: inline-block;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        /* --- Products Section --- */
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .category-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .cat-btn {
            padding: 10px 25px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .cat-btn.active, .cat-btn:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 100px;
        }

        .product-card {
            background: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .product-img {
            height: 220px;
            background-size: cover;
            background-position: center;
            transition: var(--transition);
        }

        .product-details {
            padding: 25px;
        }

        .product-details h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .product-details p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            height: 4.5em;
            overflow: hidden;
        }

        .product-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-tag {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
        }

        .add-cart-btn {
            background: var(--dark);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .add-cart-btn:hover {
            background: var(--primary);
        }

        /* --- About Section (Modern Grid) --- */
        .about-section {
            padding: 100px 0;
            background-color: var(--white);
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-visual {
            display: flex;
            gap: 15px;
            height: 500px;
        }

        .visual-item {
            flex: 1;
            border-radius: var(--radius);
            overflow: hidden;
            transition: flex 0.5s ease;
            position: relative;
        }

        .visual-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .visual-item:hover {
            flex: 3;
        }

        .about-content h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--primary);
        }

        .about-content p {
            margin-bottom: 20px;
            color: var(--text-muted);
        }

        /* --- Delivery Section --- */
        .delivery-cta {
            background: var(--dark);
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .delivery-card {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255,255,255,0.05);
            padding: 40px;
            border-radius: var(--radius);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .form-input {
            width: 100%;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: white;
            margin-bottom: 20px;
            font-family: inherit;
        }

        .form-input::placeholder {
            color: rgba(255,255,255,0.5);
        }

        /* --- Footer --- */
        footer {
            background: var(--dark);
            color: white;
            padding: 80px 0 40px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }

        .footer-col h4 {
            color: var(--accent);
            margin-bottom: 25px;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .footer-col p, .footer-col a {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            text-decoration: none;
            margin-bottom: 12px;
            display: block;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.8rem;
            color: rgba(255,255,255,0.5);
        }

        /* --- Animations --- */
        [data-reveal] {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        [data-reveal].active {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 992px) {
            .about-grid, .footer-grid { grid-template-columns: 1fr 1fr; }
            .hero-content h2 { font-size: 3rem; }
        }

        @media (max-width: 768px) {
            nav ul { display: none; }
            .about-grid, .footer-grid { grid-template-columns: 1fr; }
            .about-visual { height: 300px; }
        }
    </style>
</head>
<body>

    <div class="user-bar">
        <div class="container">
            <div class="user-stats">
                <span class="stat-item"><i class="fas fa-user"></i> Howdy, <?php echo htmlspecialchars($first_name); ?></span>
                <span class="stat-item"><i class="fas fa-star"></i> <?php echo $loyalty_points; ?> Points</span>
                <span class="stat-item d-none-mobile"><i class="fas fa-calendar-alt"></i> Joined <?php echo $join_date_formatted; ?></span>
            </div>
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </div>
    </div>

    <header id="main-header">
        <div class="container header-content">
            <div class="logo">
                <h1>Sunrise Breeders</h1>
                <div class="motto">Crafted Vigilante Coffee</div>
            </div>
            
            <nav>
                <ul>
                    <li><a href="#home" class="active">Home</a></li>
                    <li><a href="#products">Products</a></li>
                    <li><a href="#delivery">Delivery</a></li>
                    <li><a href="#about">Our Story</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <div class="search-trigger"><i class="fas fa-search"></i></div>
                <div class="cart-icon" id="cart-icon">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count">0</span>
                </div>
            </div>
        </div>
    </header>

    <section class="hero" id="home">
        <div class="hero-content">
            <h2 data-reveal>Saddle Up for Perfection</h2>
            <p data-reveal>Experience the bold, vigilante spirit of Sunrise Breeders coffee. From bean to cup, we deliver the rodeo-inspired energy your soul craves.</p>
            <a href="#products" class="btn btn-primary" data-reveal>Explore the Roast</a>
        </div>
    </section>

    <section class="container" id="products">
        <div class="section-header" data-reveal>
            <h2>The Collection</h2>
            <div class="category-nav">
                <button class="cat-btn active" data-category="all">All Items</button>
                <button class="cat-btn" data-category="drinks">Signature Drinks</button>
                <button class="cat-btn" data-category="food">Fresh Bites</button>
                <button class="cat-btn" data-category="meal">Hardy Meals</button>
            </div>
        </div>
        
        <div class="products-grid" id="products-grid">
            </div>
    </section>

    <section class="about-section" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-visual" data-reveal>
                    <div class="visual-item"><img src="https://i.pinimg.com/736x/ca/65/73/ca65731de1028efc642f6541ad26713d.jpg" alt="Coffee 1"></div>
                    <div class="visual-item"><img src="https://i.pinimg.com/736x/9a/21/13/9a211344ac13f46a318d28a5d3698bb9.jpg" alt="Coffee 2"></div>
                    <div class="visual-item"><img src="https://i.pinimg.com/736x/8c/13/62/8c136290c433afa9fcd0dd2a9fe523af.jpg" alt="Coffee 3"></div>
                </div>
                <div class="about-content" data-reveal>
                    <h3>The Vigilante Spirit</h3>
                    <p>Founded in Austin, Texas, Sunrise Breeders was born from a simple mission: to bring the rugged intensity of the rodeo to the refined world of specialty coffee.</p>
                    <p>We source single-origin beans and roast them with precision, ensuring every sip is a sunrise for your soul. Our shops aren't just cafes; they are outposts for the adventurous.</p>
                    <a href="#" class="btn btn-primary">Our Full Story</a>
                </div>
            </div>
        </div>
    </section>

    <section class="delivery-cta" id="delivery">
        <div class="container" data-reveal>
            <div class="delivery-card">
                <h2>Ready for a Delivery?</h2>
                <p style="margin-bottom: 30px; opacity: 0.8;">Fill in your details below and we'll have your favorites at your door.</p>
                <form id="delivery-form">
                    <input type="text" placeholder="Full Name" class="form-input" required>
                    <input type="text" placeholder="Delivery Address" class="form-input" required>
                    <input type="tel" placeholder="Phone Number" class="form-input" required>
                    <textarea placeholder="Special Instructions" class="form-input" rows="3"></textarea>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Place Order Now</button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h1 style="color: var(--white); font-size: 1.5rem; margin-bottom: 15px;">Sunrise Breeders</h1>
                    <p>Providing high-octane coffee for the vigilante soul since 2010. Every cup is a testament to the West.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Location</h4>
                    <p>123 Rodeo Drive<br>Austin, TX 78701</p>
                    <p>(512) 555-BREW</p>
                </div>
                <div class="footer-col">
                    <h4>Hours</h4>
                    <p>Mon-Fri: 6am - 9pm</p>
                    <p>Sat-Sun: 7am - 10pm</p>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <a href="#products">Products</a>
                    <a href="#about">About Us</a>
                    <a href="#delivery">Delivery</a>
                    <a href="#">Support</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Sunrise Breeders Coffee Shop. All rights reserved. | Refined by Zoro Uchiha</p>
            </div>
        </div>
    </footer>

    <script>
        // Sample Products (Subset for Demo)
        const products = [
            { id: 1, name: "Rodeo Rider Espresso", category: "drinks", description: "Bold, dark, and intense kickstart.", price: 3.50, image: "https://i.pinimg.com/1200x/c3/22/20/c32220f526a6f8530863f392b414d0bc.jpg" },
            { id: 2, name: "Vigilante Cold Brew", category: "drinks", description: "Smooth 12-hour steep with vanilla.", price: 4.75, image: "https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=800&q=80" },
            { id: 3, name: "Sunrise Latte", category: "drinks", description: "Signature latte with honey and cinnamon.", price: 4.25, image: "https://images.unsplash.com/photo-1561047029-3000c68339ca?auto=format&fit=crop&w=800&q=80" },
            { id: 4, name: "Cowboy Breakfast", category: "food", description: "Hardy burrito with ranchero sauce.", price: 7.99, image: "https://i.pinimg.com/1200x/c8/f3/6f/c8f36f68742466089d8995ccf2b1c4e1.jpg" }
        ];

        // Render Products
        function renderProducts(filter = 'all') {
            const grid = document.getElementById('products-grid');
            grid.innerHTML = '';
            
            const filtered = filter === 'all' ? products : products.filter(p => p.category === filter);
            
            filtered.forEach(p => {
                grid.innerHTML += `
                    <div class="product-card" data-reveal>
                        <div class="product-img" style="background-image: url('${p.image}')"></div>
                        <div class="product-details">
                            <h3>${p.name}</h3>
                            <p>${p.description}</p>
                            <div class="product-price-row">
                                <span class="price-tag">$${p.price.toFixed(2)}</span>
                                <div class="add-cart-btn"><i class="fas fa-plus"></i></div>
                            </div>
                        </div>
                    </div>
                `;
            });
            handleReveal(); // Trigger reveal for new items
        }

        // Scroll Reveal Logic
        function handleReveal() {
            const reveals = document.querySelectorAll('[data-reveal]');
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const revealTop = el.getBoundingClientRect().top;
                const revealPoint = 150;
                
                if (revealTop < windowHeight - revealPoint) {
                    el.classList.add('active');
                }
            });
        }

        // Header Scroll Effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('main-header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            handleReveal();
        });

        // Category Filter
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                renderProducts(this.dataset.category);
            });
        });

        // Initialize
        window.addEventListener('load', () => {
            renderProducts();
            handleReveal();
        });
    </script>
</body>
</html>
