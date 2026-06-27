<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Check user credentials
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, coffee_preference, loyal_points FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $first_name, $last_name, $db_email, $hashed_password, $coffee_preference, $loyalty_points);
        $stmt->fetch();
        
        // Verify password
        if (password_verify($password, NULL . $hashed_password)) {
            // Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $_SESSION['user_email'] = $db_email;
            $_SESSION['coffee_preference'] = $coffee_preference;
            $_SESSION['loyalty_points'] = $loyalty_points;
            
            // Redirect to homepage
            header("Location: homepage.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }
    $stmt->close();
}
?>

<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><style>circle{fill:%238B4513;}path{fill:%23D2691E;}.sun{fill:%23FFD700;}</style><circle cx='50' cy='50' r='45'/><path d='M30,65 L70,65 L75,85 L25,85 Z'/><circle class='sun' cx='80' cy='20' r='15'/><path d='M80,5 L80,35 M65,20 L95,20 M70,10 L90,30 M70,30 L90,10' stroke='%23FFD700' stroke-width='3'/></svg>">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sunrise Breeders Coffee</title>
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

        .login-wrapper {
            display: flex;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(139, 69, 19, 0.3);
            backdrop-filter: blur(2px);
        }

        /* Left Side - Branding with Background Image */
        .brand-side {
            flex: 1;
            background: linear-gradient(rgba(34, 24, 16, 0.75), rgba(34, 24, 16, 0.85)), url('https://i.pinimg.com/1200x/5f/68/27/5f68279bd5321932f1d83739a7ec97f5.jpg');
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
            margin: 2.5rem 0;
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
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.95);
        }

        .form-header {
            margin-bottom: 1.5rem;
        }

        .form-header h3 {
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

        .input-group {
            margin-bottom: 1.2rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.4rem;
            color: #2C1810;
            font-size: 0.75rem;
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
            left: 1rem;
            color: #CD853F;
            font-size: 0.9rem;
        }

        .input-wrapper input {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.5rem;
            border: 1.5px solid rgba(232, 224, 213, 0.8);
            border-radius: 0.8rem;
            font-size: 0.85rem;
            background: white;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #CD853F;
            box-shadow: 0 0 0 3px rgba(205, 133, 63, 0.15);
        }

        .input-wrapper input::placeholder {
            color: #C4B8A8;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-size: 0.75rem;
            color: #5C4B3A;
        }

        .checkbox-label input {
            width: 0.9rem;
            height: 0.9rem;
            accent-color: #CD853F;
            cursor: pointer;
        }

        .forgot-link {
            color: #CD853F;
            font-size: 0.75rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #8B4513;
            text-decoration: underline;
        }

        .signin-btn {
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
            margin-bottom: 1.2rem;
        }

        .signin-btn:hover {
            background: linear-gradient(135deg, #A0522D 0%, #CD853F 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.4);
        }

        .signin-btn span:first-child {
            flex: 1;
            text-align: center;
        }

        .divider {
            text-align: center;
            margin: 1.2rem 0;
            position: relative;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: calc(50% - 60px);
            height: 1px;
            background: rgba(119, 118, 118, 0.8);
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 1rem;
            color: #8B7355;
            font-size: 0.7rem;
        }

        .social-buttons {
            display: flex;
            gap: 0.8rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .social-btn {
            flex: 1;
            padding: 0.5rem;
            background: white;
            border: 1.5px solid rgba(232, 224, 213, 0.8);
            border-radius: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-size: 0.7rem;
            color: #2C1810;
            font-weight: 500;
        }

        .social-btn:hover {
            background: rgba(232, 220, 200, 0.8);
            border-color: #CD853F;
            transform: translateY(-1px);
        }

        .register-link {
            text-align: center;
            font-size: 0.75rem;
            color: #8B7355;
        }

        .register-link a {
            color: #CD853F;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: rgba(254, 242, 242, 0.95);
            border: 1px solid #FECACA;
            color: #DC2626;
            padding: 0.6rem 1rem;
            border-radius: 0.7rem;
            margin-bottom: 1.2rem;
            font-size: 0.75rem;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .container {
                width: 95%;
            }
            
            .login-wrapper {
                flex-direction: column;
                border-radius: 1.2rem;
            }
            
            .brand-side {
                padding: 1.8rem;
                text-align: center;
                min-height: 280px;
            }
            
            .brand-message {
                margin: 1.5rem 0;
            }
            
            .brand-message p {
                max-width: 100%;
            }
            
            .brand-message h2 {
                font-size: 1.5rem;
            }
            
            .form-side {
                padding: 1.8rem;
            }
        }

        .container {
        display: flex;
        }

        .Btn {
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition-duration: 0.4s;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        margin-left: 10px;
        }

        .instagram {
        background: #f09433;
        background: -moz-linear-gradient(
            45deg,
            #f09433 0%,
            #e6683c 25%,
            #dc2743 50%,
            #cc2366 75%,
            #bc1888 100%
        );
        background: -webkit-linear-gradient(
            45deg,
            #f09433 0%,
            #e6683c 25%,
            #dc2743 50%,
            #cc2366 75%,
            #bc1888 100%
        );
        background: linear-gradient(
            45deg,
            #f09433 0%,
            #e6683c 25%,
            #dc2743 50%,
            #cc2366 75%,
            #bc1888 100%
        );
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f09433', endColorstr='#bc1888',GradientType=1 );
        }

        .youtube {
        background-color: #ff0000;
        }

        .facebook {
        background-color: #003cff;
        }

        .twitter {
        background-color: #1da1f2;
        }

        .Btn:hover {
        width: 110px;
        transition-duration: 0.4s;
        border-radius: 30px;
        }

        .Btn:hover .text {
        opacity: 1;
        transition-duration: 0.4s;
        }

        .Btn:hover .svgIcon {
        opacity: 0;
        transition-duration: 0.3s;
        }

        .text {
        position: absolute;
        color: rgb(255, 255, 255);
        width: 120px;
        font-weight: 600;
        opacity: 0;
        transition-duration: 0.4s;
        }

        .svgIcon {
        transition-duration: 0.3s;
        }

        .svgIcon path {
        fill: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-wrapper">
            <!-- Left Side - Branding -->
            <div class="brand-side">
                <div class="brand-logo">
                    <h1>Sunrise Breeders</h1>
                    <div class="tagline">"Where every cup is a sunrise for your soul"</div>
                </div>
                
                <div class="brand-message">
                    <h2>Welcome back, <span>Coffee Lover</span></h2>
                    <p>Enter your details to rejoin the warmth of our digital hearth and order your favorite brew.</p>
                </div>
                
                <div class="brand-footer">
                    <p>© 2026 Sunrise Breeders. Artisanal coffee since 2010.</p>
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <div class="form-side">
                <div class="form-header">
                    <h3>Welcome back</h3>
                    <p>Enter your details to rejoin the warmth of our digital hearth.</p>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
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
                        <label>PASSWORD</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <br><br>
                    
                    <button type="submit" class="signin-btn">
                        <span>SIGN IN</span>
                    </button>
                </form>
                
                <div class="divider">
                    <span>OR CONTINUE WITH</span>
                </div>
                
                <div class="social-buttons">
                    <button class="Btn instagram" onclick="alert('Instagram login coming soon!')">
                        <svg
                        class="svgIcon"
                        viewBox="0 0 448 512"
                        height="1.5em"
                        xmlns="http://www.w3.org/2000/svg"
                        >
                        <path
                            d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"
                        ></path>
                        </svg>
                        <span class="text">Instagram</span>
                    </button>

                    <button class="Btn youtube" onclick="alert('YouTube login coming soon!')">
                        <svg
                        class="svgIcon"
                        viewBox="0 0 576 512"
                        height="1.5em"
                        xmlns="http://www.w3.org/2000/svg"
                        >
                        <path
                            d="M549.655 148.28c-6.281-23.64-24.041-42.396-47.655-48.685C462.923 85 288 85 288 85S113.077 85 74 99.595c-23.614 6.289-41.374 25.045-47.655 48.685-12.614 47.328-12.614 147.717-12.614 147.717s0 100.39 12.614 147.718c6.281 23.64 24.041 42.396 47.655 48.684C113.077 427 288 427 288 427s174.923 0 214-14.595c23.614-6.289 41.374-25.045 47.655-48.685 12.614-47.328 12.614-147.718 12.614-147.718s0-100.389-12.614-147.717zM240 336V176l144 80-144 80z"
                        ></path>
                        </svg>
                        <span class="text">YouTube</span>
                    </button>

                    <button class="Btn facebook" onclick="window.location.href='admin_login.php'">
                        <svg
                        class="svgIcon"
                        viewBox="0 0 24 24"
                        height="1.5em"
                        xmlns="http://www.w3.org/2000/svg"
                        >
                        <path
                            d="M22 12.07C22 6.49 17.5 2 12 2S2 6.49 2 12.07C2 17.13 5.66 21.33 10.44 22v-7.04H7.9v-2.89h2.54V9.41c0-2.52 1.49-3.91 3.77-3.91 1.09 0 2.23.2 2.23.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.89h-2.33V22c4.78-.67 8.44-4.87 8.44-9.93z"
                        ></path>
                        </svg>
                        <span class="text">Facebook</span>
                    </button>

                    <button class="Btn twitter" onclick="alert('Twitter login coming soon!')">
                        <svg
                        class="svgIcon"
                        viewBox="0 0 512 512"
                        height="1.5em"
                        xmlns="http://www.w3.org/2000/svg"
                        >
                        <path
                            d="M512 97.248c-18.84 8.36-39.082 14.008-60.277 16.54 21.62-12.92 38.212-33.216 46.042-57.45-20.242 12-42.71 20.67-66.61 25.41-19.128-20.412-46.344-33.21-76.51-33.21-58 0-105 47-105 105 0 8.22.926 16.188 2.714 23.914-87.18-4.376-164.66-46.2-216.45-109.97-9.066 15.508-14.254 33.586-14.254 52.836 0 36.37 18.54 68.542 46.844 87.428-17.272-.554-33.52-5.286-47.754-13.158v1.32c0 50.828 36.13 93.15 84.198 102.79-8.826 2.396-18.14 3.686-27.734 3.686-6.78 0-13.34-.664-19.676-1.902 13.36 41.77 52.164 72.198 98.116 73.052-35.96 28.17-81.38 44.99-130.76 44.99-8.54 0-16.94-.5-25.14-1.476 46.684 29.922 101.99 47.31 161.18 47.31 193.32 0 298.924-160.078 298.924-298.926 0-4.554-.106-9.086-.306-13.594 20.546-14.824 38.364-33.298 52.456-54.422z"
                        ></path>
                        </svg>
                        <span class="text">Twitter</span>
                    </button>
                </div>
                
                <div class="register-link">
                    New to our bakery? <a href="register.php">Create your account</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
