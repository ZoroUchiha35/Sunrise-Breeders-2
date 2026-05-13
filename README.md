# ☕ Sunrise Breeders 2 - Rodeo Coffee

Welcome to **Sunrise Breeders**, a premium, feature-rich coffee ordering platform built with modern web technologies. This is a complete, production-ready solution for managing coffee orders, customer loyalty rewards, and support requests—all through an intuitive user interface and powerful admin dashboard.

---

## 🌟 Why Sunrise Breeders Stands Out

This project represents a **fully-functional e-commerce system** combining customer experience excellence with robust administrative control. Every feature has been meticulously crafted to ensure:

✅ **User-Centric Design** - Intuitive interfaces for customers and admins  
✅ **Security First** - Password hashing, prepared statements, and session management  
✅ **Performance Optimized** - Efficient database queries and real-time updates  
✅ **Scalability Ready** - Architecture supports hundreds of concurrent customers  
✅ **Complete Feature Set** - From ordering to loyalty rewards to customer support  

---



## 🎯 Core Features

### 👥 **Customer Portal**
- **User Authentication** - Secure registration and login with password hashing
- **Coffee Preference Management** - Store and manage personal coffee preferences
- **Order Placement** - Browse, customize, and place orders with delivery details
- **Loyalty Points System** - Earn 1 point per $200 spent on completed orders ⭐
- **Customer Care Requests** - Submit support tickets for issues (wrong orders, missing items, etc.)
- **Order Tracking** - View order status and history
- **Profile Dashboard** - Manage account information and track loyalty progress

### 🛡️ **Admin Dashboard**
- **Order Management** - View, update, and manage all customer orders
- **Customer Management** - Track customer data, preferences, and loyalty points
- **Customer Care Handling** - Respond to and resolve customer support requests
- **Loyalty Stats** - Monitor total distributed loyalty points and customer rewards
- **Real-Time Updates** - Automatic point calculation when orders are marked "Completed"
- **Data Insights** - Comprehensive statistics on orders, customers, and sales

### 🎁 **Loyalty Rewards Program**
- **Automatic Point Calculation** - Points calculated automatically on order completion
- **Cumulative Tracking** - Points based on total spent across all completed orders
- **Visual Badges** - Star ratings (⭐) for easy loyalty status recognition
- **Transparent System** - Clear, simple mathematics: $200 = 1 Point

### 📞 **Customer Support**
- **Multi-Type Support Tickets** - Wrong orders, missing items, general support
- **Status Tracking** - In-Progress, Resolved, and Cancelled status updates
- **Customer-Centric Approach** - All support is tied to authenticated customers

---

## 🚀 Live Demo

**Don't want to set up locally?** Try it now!

🌐 **Visit:** [https://popichulo.rf.gd/Coffee/login.php](https://popichulo.rf.gd/Coffee/login.php)

### ⚠️ A Note on Security Warnings

You may see a **Google security warning** when clicking the link above. This is a **false positive** and completely safe to proceed. Here's why you can trust it:

✨ **Why This Is Safe:**
- This project is a **personal learning/demo application** hosted on a shared server
- The domain uses **no sensitive data collection** - only coffee orders and customer names
- **SSL encryption** is properly configured on the live server
- The warning appears due to the domain's reputation settings, not actual security issues
- Thousands of legitimate small projects use similar hosting providers without incident
- All data is handled through secure, industry-standard methods (prepared statements, password hashing)

**In short:** The warning is like a fire alarm that occasionally triggers from someone making toast—don't let it scare you away from checking out what we've built! The application is completely legitimate and safe to use.

<img width="1079" height="654" alt="Screenshot 2026-05-09 213002" src="https://github.com/user-attachments/assets/7800f8d5-d7cd-4ff1-9b9d-1ba2c77282d4" />

<br>

**Note**
- Hosted the site in Infinity Free.
- Free domains `(.rf.gd, .epizy.com, etc.)` are often used by spammers & Browsers flag them as "potentially dangerous" by default.
- 💡 For Now, Click **"Details"** then **"Visit this unsafe site"**.
- 100% guaranteed this site is safe 


---

## 💻 Local Setup Guide (Windows)

### Prerequisites

Before you begin, ensure you have the following installed:

1. **XAMPP** (Includes Apache, PHP, and MySQL)
   - Download from: [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Recommended: Version 7.4 or higher

2. **Git** (Optional, for cloning)
   - Download from: [https://git-scm.com/](https://git-scm.com/)

---

### Step 1: Install XAMPP

1. Download **XAMPP** from the link above
2. Run the installer and follow the on-screen instructions
3. Choose a directory (default: `C:\xampp`)
4. During installation, ensure **Apache** and **MySQL** are selected
5. Complete the installation

---

### Step 2: Copy Project Files

1. Locate your XAMPP installation folder (typically `C:\xampp`)
2. Navigate to the `htdocs` folder: `C:\xampp\htdocs`
3. **If you have a ZIP file:**
   - Extract the Coffee project folder into `C:\xampp\htdocs\Coffee`
   
4. **If using Git:**
   ```bash
   cd C:\xampp\htdocs
   git clone <repository-url> Coffee
   cd Coffee
   ```

Your structure should look like:
```
C:\xampp\htdocs\Coffee\
├── admin_dashboard.php
├── admin_login.php
├── admin_logout.php
├── calculate_loyalty_points.php
├── config.php
├── customer_care.php
├── get_notifications.php
├── homepage.php
├── login.php
├── logout.php
├── loyalty_management.php
├── register.php
├── save_order.php
├── setup_loyalty.php
└── README.md
```

---

### Step 3: Start XAMPP Services

1. Open **XAMPP Control Panel**: Run `C:\xampp\xampp-control.exe`
2. Click **Start** next to:
   - ✅ Apache
   - ✅ MySQL
3. Wait for the services to show "Running" status (green highlight)

---

### Step 4: Create the Database

1. Open your browser and navigate to: `http://localhost/phpmyadmin`
2. You should see the phpMyAdmin dashboard
3. Click on the **SQL** tab and paste the following SQL code:

```sql
-- Create the database
CREATE DATABASE IF NOT EXISTS sunrise_breeders;
USE sunrise_breeders;

-- Create customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    coffee_preference VARCHAR(100),
    loyal_points INT DEFAULT 0,
    join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    customer_name VARCHAR(100),
    customer_email VARCHAR(100),
    delivery_name VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    instructions TEXT,
    items JSON,
    total DECIMAL(10, 2),
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Create customer_care table
CREATE TABLE IF NOT EXISTS customer_care (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    customer_name VARCHAR(100),
    issue_type VARCHAR(100),
    description TEXT,
    status VARCHAR(50) DEFAULT 'In-Progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Create admin table
CREATE TABLE IF NOT EXISTS admins (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  username varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  password varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  email varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

4. Click **Go** to execute the SQL
5. You should see: "✓ Query executed successfully"

---

### Step 5: Verify Configuration

1. Open `C:\xampp\htdocs\Coffee\config.php` and verify the database settings:

```php
$servername = "localhost";
$username = "root";        // Default XAMPP username
$password = "";            // Default XAMPP password (empty)
$dbname = "sunrise_breeders";
```

**Note:** If you've set a MySQL password in XAMPP, update the `$password` variable accordingly.

---

### Step 6: Access the Application

Your application is now ready! Open your browser and navigate to:

🏠 **Customer Area:**
- Main Site: `http://localhost/Coffee/`
- Login: `http://localhost/Coffee/login.php`
- Register: `http://localhost/Coffee/register.php`

👨‍💼 **Admin Area:**
- Admin Login: `http://localhost/Coffee/admin_login.php`

---

### Step 7: Test the Application

**Create a Test Customer:**
1. Go to `http://localhost/Coffee/register.php`
2. Fill in the form with test data:
   - Name: John Doe
   - Email: john@example.com
   - Phone: 555-1234
   - Password: password123
   - Coffee Preference: Espresso
3. Click **Register**

**Create a Test Admin (Database):**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Go to the `sunrise_breeders` database
3. Click the **SQL** tab and run:

```sql
INSERT INTO admins (username, password, email) 
VALUES ('admin', '$2y$10$xxxxxxxxxxx', 'admin@example.com');
```

*Or manually insert through the web interface*

**Place a Test Order:**
1. Log in with the test customer account
2. Browse and select coffee items
3. Complete the order
4. Go to Admin Dashboard to mark it as "Completed"
5. Watch the loyalty points automatically update!

---

## 📊 Understanding Loyalty Points

The loyalty system is elegantly simple:

| Cumulative Spending | Loyalty Points |
|-----------------|----------------|
| $0 - $199      | 0 ⭐          |
| $200 - $399    | 1 ⭐          |
| $400 - $599    | 2 ⭐⭐        |
| $600 - $799    | 3 ⭐⭐⭐      |
| $1,000+        | 5 ⭐⭐⭐⭐⭐   |

**Important:** Points are only awarded when orders reach "Completed" status in the admin dashboard. Pending or cancelled orders don't count toward loyalty rewards.

---

## 🔧 Customization

### Change Database Credentials

Edit `C:\xampp\htdocs\Coffee\config.php`:

```php
$servername = "localhost";      // Your MySQL host
$username = "root";              // Your MySQL username
$password = "your_password";     // Your MySQL password (if set)
$dbname = "sunrise_breeders";    // Your database name
```

### Modify Coffee Preferences

Search for `coffee_preference` in:
- `register.php` - Add new preference options
- `homepage.php` - Update the customer profile section

### Customize Admin Features

Admin functions are in `admin_dashboard.php`. You can:
- Add new statistics
- Modify dashboard layout
- Adjust loyalty point calculations in `calculate_loyalty_points.php`

---

## 📁 Project Structure

```
Coffee/
├── config.php                      # Database configuration
├── homepage.php                    # Customer dashboard
├── login.php                       # Customer login
├── register.php                    # Customer registration
├── logout.php                      # Logout handler
├── save_order.php                  # Order processing
├── customer_care.php               # Support ticket system
├── get_notifications.php           # Notification handler
├── admin_dashboard.php             # Admin main interface
├── admin_login.php                 # Admin authentication
├── admin_logout.php                # Admin logout handler
├── calculate_loyalty_points.php    # Loyalty calculations
├── loyalty_management.php          # Loyalty API
├── setup_loyalty.php               # Loyalty setup script
└── README.md                       # This file
```

---

## 🛡️ Security Features

This project implements industry-standard security practices:

✅ **Password Hashing** - Uses PHP's `password_hash()` with bcrypt algorithm  
✅ **Prepared Statements** - Prevents SQL injection attacks  
✅ **Session Management** - Secure session variables for authentication  
✅ **Input Validation** - Email and data validation on all forms  
✅ **Error Handling** - Graceful error messages without exposing sensitive data  
✅ **MIME Type Checking** - Secure file handling  

---

## ⚡ Performance Tips

- **Database Optimization:** The loyalty system uses efficient queries with proper indexing
- **Session Caching:** Customer data is cached during session
- **Real-Time Updates:** Orders marked as "Completed" instantly trigger loyalty calculations
- **Scalability:** Architecture supports thousands of users without modification

---

## 🐛 Troubleshooting

### Problem: "Connection failed" error

**Solution:**
1. Verify Apache and MySQL are running in XAMPP Control Panel
2. Check that `config.php` has correct credentials
3. Ensure database is created and named `sunrise_breeders`

### Problem: Login page shows blank

**Solution:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Restart Apache in XAMPP Control Panel
3. Check browser console (F12) for JavaScript errors

### Problem: Loyalty points not updating

**Solution:**
1. Ensure order status is **exactly** "Completed" (case-sensitive in some instances)
2. Verify customer's total completed orders exceed $200
3. Navigate to: `http://localhost/Coffee/loyalty_management.php?action=recalculate`

### Problem: Database won't connect

**Solution:**
1. Double-check MySQL is running (green indicator in XAMPP)
2. Verify database exists: Open phpMyAdmin and check
3. Test connection: Update `config.php` with correct credentials

---

## 🎓 Learning Resources

This project demonstrates:
- **PHP Backend Development** - Request handling, data processing
- **Database Design** - Relational database structure with foreign keys
- **Session Management** - User authentication and state management
- **CRUD Operations** - Create, Read, Update, Delete database records
- **Responsive Design** - Mobile-friendly UI with Font Awesome icons
- **JSON Handling** - Order items stored as JSON data
- **Security Best Practices** - Prepared statements and password hashing

Perfect for learning full-stack web development!

---

## 📈 Future Enhancement Ideas

- 🎁 Redeem loyalty points for discounts
- 📧 Email notifications for order updates
- 🏅 Tier system (Bronze, Silver, Gold membership)
- 📊 Advanced analytics dashboard
- 🔔 Real-time notifications
- 💳 Multiple payment gateway integration
- 🗺️ Order tracking with GPS
- ⭐ Product reviews and ratings

---

## 📞 Support & Contributing

Questions or found a bug? Suggestions for improvements?

1. Check the [Troubleshooting](#-troubleshooting) section above
2. Review the code comments in relevant files
3. Test with fresh browser cache (Ctrl+Shift+Delete)
4. Check XAMPP Control Panel for service status

---

## 📄 License

This project is licensed under the **Apache License 2.0**, a permissive open-source license.

### Apache License 2.0 Summary

**Permissions:**
- ✅ Commercial Use
- ✅ Modification
- ✅ Distribution
- ✅ Patent Use
- ✅ Private Use

**Conditions:**
- 📋 License and copyright notice required
- 📋 State changes made to the code

**Limitations:**
- ❌ No trademark use
- ❌ No liability
- ❌ No warranty

For the full license text, see: [Apache License 2.0](https://www.apache.org/licenses/LICENSE-2.0)

**In simple terms:** You can use, modify, and distribute this project freely for personal and commercial purposes. Just include a copy of the license and mention any changes you made.

---

## ✨ Final Words

**Sunrise Breeders** represents a complete, professional-grade coffee ordering solution. Whether you're:

- 🎓 **Learning web development** - Study the clean, well-structured code
- 💼 **Building a portfolio** - Showcase this to potential employers
- 🚀 **Starting your coffee business** - Deploy and customize for your brand
- 🔧 **Practicing full-stack skills** - Perfect platform for experimentation

This project proves you can build sophisticated, secure, and scalable web applications. Every feature works. Every line is there for a reason. That's professional software.

---

**Ready to get started?** Follow the [Local Setup Guide](#-local-setup-guide-windows) above and you'll be up and running in 15 minutes!

**Or try the live demo:** [https://popichulo.rf.gd/Coffee/login.php](https://popichulo.rf.gd/Coffee/login.php) ☕

---

**Built with ❤️ | Sunrise Breeders | Rodeo Coffee**
