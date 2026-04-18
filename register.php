<?php
session_start();

// ============================================
// AUTO-CREATE TABLES IF THEY DON'T EXIST
// ============================================
$setup_con = mysqli_connect("localhost", "root", "");

if (!$setup_con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if not exists
mysqli_query($setup_con, "CREATE DATABASE IF NOT EXISTS dailyexpense");
mysqli_select_db($setup_con, "dailyexpense");

// Create users table if not exists
mysqli_query($setup_con, "CREATE TABLE IF NOT EXISTS users (
    user_id INT(11) NOT NULL AUTO_INCREMENT,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL,
    profile_path VARCHAR(255) DEFAULT 'default_profile.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Create categories table if not exists
mysqli_query($setup_con, "CREATE TABLE IF NOT EXISTS expense_categories (
    category_id INT(11) NOT NULL AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL,
    PRIMARY KEY (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Insert default categories if table is empty
$check_cats = mysqli_query($setup_con, "SELECT COUNT(*) as count FROM expense_categories");
if ($check_cats) {
    $count = mysqli_fetch_assoc($check_cats);
    if ($count['count'] == 0) {
        $categories = ['Food', 'Transport', 'Shopping', 'Entertainment', 
                       'Bills & Utilities', 'Healthcare', 'Education', 
                       'Rent', 'Travel', 'Others'];
        foreach ($categories as $cat) {
            mysqli_query($setup_con, "INSERT INTO expense_categories (category_name) VALUES ('$cat')");
        }
    }
}

// Create expenses table if not exists
mysqli_query($setup_con, "CREATE TABLE IF NOT EXISTS expenses (
    expense_id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    expense DECIMAL(10,2) NOT NULL,
    expensedate DATE NOT NULL,
    expensecategory VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (expense_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Close the setup connection
mysqli_close($setup_con);
// ============================================
// END OF AUTO-CREATION
// ============================================

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Check if email already exists
        $check_query = "SELECT user_id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($con, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Email already registered";
        } else {
            // Insert new user
            $hashed_password = md5($password);
            $insert_query = "INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "ssss", $firstname, $lastname, $email, $hashed_password);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $success = "Registration successful! You can now login.";
                // Clear form data
                $firstname = $lastname = $email = '';
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register - Expense Manager</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <style>
        .register-form {
            width: 450px;
            margin: 50px auto;
        }
        .register-form form {
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .register-form h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #28a745;
        }
        .btn-danger {
            border-radius: 0;
        }
    </style>
</head>
<body class="bg-light">
    <div class="register-form">
        <form method="POST" action="">
            <h2>Register</h2>
            <p class="text-center text-muted">Create your account</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="text" name="firstname" class="form-control" placeholder="First Name" 
                           value="<?php echo htmlspecialchars($firstname ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <input type="text" name="lastname" class="form-control" placeholder="Last Name" 
                           value="<?php echo htmlspecialchars($lastname ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-group mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" 
                       value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            <div class="form-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="form-group mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            <button type="submit" class="btn btn-danger btn-block w-100">Register</button>
        </form>
        <p class="text-center mt-3">
            Already have an account? <a href="login.php" class="text-success">Login Here</a>
        </p>
    </div>
</body>
</html>