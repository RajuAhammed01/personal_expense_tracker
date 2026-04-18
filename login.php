<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        // Use prepared statement to prevent SQL injection
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Verify password
            if (md5($password) === $user['password']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['lastname'] = $user['lastname'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['profile_path'] = $user['profile_path'] ?? 'default_profile.png';
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Email not found";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Expense Manager</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <style>
        .login-form {
            width: 400px;
            margin: 80px auto;
        }
        .login-form form {
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .login-form h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #28a745;
        }
        .btn-success {
            border-radius: 0;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="login-form">
        <form method="POST" action="">
            <h2>Personal Expense Tracker</h2>
            <p class="text-center text-muted">Login Panel</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="form-group mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="form-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-success btn-block w-100">Login</button>
        </form>
        <p class="text-center mt-3">
            Don't have an account? <a href="register.php" class="text-danger">Register Here</a>
        </p>
    </div>
</body>
</html>