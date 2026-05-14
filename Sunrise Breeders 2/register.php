<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $coffee_preference = $_POST['coffee_preference'];
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists
        $check_email = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();
        
        if ($check_email->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, phone, password, coffee_preference) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone, $hashed_password, $coffee_preference);
            
            if ($stmt->execute()) {
                $success = "Registration successful! Welcome to Sunrise Breeders!";
                $_POST = array(); // Clear form
            } else {
                $error = "Registration failed: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_email->close();
    }
}
?>

<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><style>circle{fill:%238B4513;}path{fill:%23D2691E;}.sun{fill:%23FFD700;}</style><circle cx='50' cy='50' r='45'/><path d='M30,65 L70,65 L75,85 L25,85 Z'/><circle class='sun' cx='80' cy='20' r='15'/><path d='M80,5 L80,35 M65,20 L95,20 M70,10 L90,30 M70,30 L90,10' stroke='%23FFD700' stroke-width='3'/></svg>">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sunrise Breeders Coffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(rgba(44, 24, 16, 0.85), rgba(44, 24, 16, 0.92)), url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        /* Coffee decoration overlay */
        body::before {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 100px;
            opacity: 0.08;
            pointer-events: none;
        }

        body::after {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 100px;
            opacity: 0.08;
            pointer-events: none;
        }

        .container {
            max-width: 1000px;
            width: 90%;
            margin: 0 auto;
        }

        .register-wrapper {
            display: flex;
            background: rgba(250, 243, 224, 0.97);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(139, 69, 19, 0.3);
            backdrop-filter: blur(2px);
        }

        /* Left Side - Branding with Background Image */
        .brand-side {
            flex: 1;
            background: linear-gradient(rgba(34, 24, 16, 0.75), rgba(34, 24, 16, 0.85)), url('https://i.pinimg.com/736x/a1/8a/e3/a18ae3ce1d14d4f396c1241d6e593d30.jpg');
            background-size: cover;
            background-position: center;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .brand-side::before {
            position: absolute;
            bottom: -30px;
            right: -30px;
            font-size: 180px;
            opacity: 0.1;
            pointer-events: none;
        }

        .brand-logo {
            position: relative;
            z-index: 1;
        }

        .brand-logo h1 {
            color: #F5DEB3;
            font-size: 1.8rem;
            letter-spacing: 2px;
            margin-bottom: 0.3rem;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }

        .brand-logo .tagline {
            color: #CD853F;
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .brand-message {
            position: relative;
            z-index: 1;
            margin: 2rem 0;
        }

        .brand-message h2 {
            color: white;
            font-size: 2rem;
            line-height: 1.2;
            margin-bottom: 1rem;
            font-weight: 500;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
        }

        .brand-message h2 span {
            color: #CD853F;
            font-style: italic;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .brand-message p {
            color: #F5DEB3;
            font-size: 0.85rem;
            line-height: 1.5;
            max-width: 250px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .brand-footer {
            position: relative;
            z-index: 1;
            color: #D4C5B0;
            font-size: 0.7rem;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
        }

        /* Right Side - Form */
        .form-side {
            flex: 1;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            max-height: 85vh;
            overflow-y: auto;
        }

        /* Custom scrollbar for form side */
        .form-side::-webkit-scrollbar {
            width: 5px;
        }

        .form-side::-webkit-scrollbar-track {
            background: rgba(232, 224, 213, 0.5);
            border-radius: 10px;
        }

        .form-side::-webkit-scrollbar-thumb {
            background: #CD853F;
            border-radius: 10px;
        }

        .form-header {
            margin-bottom: 1.2rem;
        }

        .form-header h1 {
            color: #2C1810;
            font-size: 2rem;
            font-weight: 500;
            margin-bottom: 0.3rem;
        }

        .form-header p {
            color: #8B7355;
            font-size: 0.85rem;
        }

        .customer-badge {
            display: inline-block;
            background: rgba(232, 220, 200, 0.9);
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.65rem;
            color: #8B4513;
            margin-bottom: 0.8rem;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.8rem;
        }

        .form-row .input-group {
            flex: 1;
            margin-bottom: 0;
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.3rem;
            color: #2C1810;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 0.8rem;
            color: #CD853F;
            font-size: 0.8rem;
        }

        .input-wrapper input,
        .input-wrapper select {
            width: 100%;
            padding: 0.6rem 0.8rem 0.6rem 2.2rem;
            border: 1.5px solid rgba(232, 224, 213, 0.8);
            border-radius: 0.7rem;
            font-size: 0.8rem;
            background: white;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .input-wrapper input:focus,
        .input-wrapper select:focus {
            outline: none;
            border-color: #CD853F;
            box-shadow: 0 0 0 3px rgba(205, 133, 63, 0.15);
        }

        .input-wrapper input::placeholder {
            color: #C4B8A8;
        }

        .register-btn {
            width: 100%;
            padding: 0.7rem;
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            color: white;
            border: none;
            border-radius: 0.8rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }

        .register-btn:hover {
            background: linear-gradient(135deg, #A0522D 0%, #CD853F 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.4);
        }

        .register-btn span:first-child {
            flex: 1;
            text-align: center;
        }

        .login-link {
            text-align: center;
            font-size: 0.75rem;
            color: #8B7355;
        }

        .login-link a {
            color: #CD853F;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 0.6rem 1rem;
            border-radius: 0.7rem;
            margin-bottom: 1rem;
            font-size: 0.75rem;
            text-align: center;
        }

        .success {
            background: rgba(212, 237, 218, 0.95);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: rgba(254, 242, 242, 0.95);
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .container {
                width: 95%;
            }
            
            .register-wrapper {
                flex-direction: column;
                border-radius: 1.2rem;
            }
            
            .brand-side {
                padding: 1.5rem;
                text-align: center;
                min-height: 220px;
            }
            
            .brand-message {
                margin: 1rem 0;
            }
            
            .brand-message p {
                max-width: 100%;
            }
            
            .brand-message h2 {
                font-size: 1.3rem;
            }
            
            .form-side {
                padding: 1.5rem;
                max-height: none;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-wrapper">
            <!-- Left Side - Branding -->
            <div class="brand-side">
                <div class="brand-logo">
                    <h1>Sunrise Breeders</h1>
                    <div class="tagline">"Where every cup is a sunrise for your soul"</div>
                </div>
                
                <div class="brand-message">
                    <h2>Join the <span>Family</span></h2>
                    <p>Create your account and start your journey with the finest artisanal coffee experience.</p>
                </div>
                
                <div class="brand-footer">
                    <p>© 2026 Sunrise Breeders. Artisanal coffee since 2010.</p>
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <div class="form-side">
                <div class="form-header">
                    <h1>Create account</h1>
                    <p>Enter your details to join our coffee community.</p>
                </div>
                
                <?php if (!empty($success)): ?>
                    <div class="message success"><?php echo $success; ?></div>
                <?php elseif (!empty($error)): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="input-group">
                            <label>FIRST NAME</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="first_name" placeholder="John" 
                                       value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>LAST NAME</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="last_name" placeholder="Doe" 
                                       value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" 
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>EMAIL ADDRESS</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="coffeelover@sunrise.com" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>PHONE NUMBER</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="phone" placeholder="+1 234 567 8900" 
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>COFFEE PREFERENCE</label>
                        <div class="input-wrapper">
                            <i class="fas fa-mug-hot"></i>
                            <select name="coffee_preference">
                                <option value="">Select your favorite brew</option>
                                <option value="Espresso" <?php echo (isset($_POST['coffee_preference']) && $_POST['coffee_preference'] == 'Espresso') ? 'selected' : ''; ?>>Espresso</option>
                                <option value="Latte" <?php echo (isset($_POST['coffee_preference']) && $_POST['coffee_preference'] == 'Latte') ? 'selected' : ''; ?>>Latte</option>
                                <option value="Cappuccino" <?php echo (isset($_POST['coffee_preference']) && $_POST['coffee_preference'] == 'Cappuccino') ? 'selected' : ''; ?>>Cappuccino</option>
                                <option value="Cold Brew" <?php echo (isset($_POST['coffee_preference']) && $_POST['coffee_preference'] == 'Cold Brew') ? 'selected' : ''; ?>>Cold Brew</option>
                                <option value="Americano" <?php echo (isset($_POST['coffee_preference']) && $_POST['coffee_preference'] == 'Americano') ? 'selected' : ''; ?>>Americano</option>
                                <option value="Mocha" <?php echo (isset($_POST['coffee_preference']) && $_POST['coffee_preference'] == 'Mocha') ? 'selected' : ''; ?>>Mocha</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="input-group">
                            <label>PASSWORD</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="••••••••" required>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>CONFIRM PASSWORD</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="confirm_password" placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="register-btn">
                        <span>CREATE ACCOUNT</span>
                    </button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in here</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>