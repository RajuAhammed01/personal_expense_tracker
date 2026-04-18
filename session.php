<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config
require_once __DIR__ . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user details from session
$userid = $_SESSION['user_id'];
$firstname = $_SESSION['firstname'] ?? '';
$lastname = $_SESSION['lastname'] ?? '';
$username = trim($firstname . ' ' . $lastname);
$useremail = $_SESSION['email'] ?? '';
$userprofile = $_SESSION['profile_path'] ?? 'default_profile.png';

// Fetch fresh user data from database
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($con, $user_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);

if ($user_data = mysqli_fetch_assoc($user_result)) {
    $firstname = $user_data['firstname'];
    $lastname = $user_data['lastname'];
    $username = trim($firstname . ' ' . $lastname);
    $useremail = $user_data['email'];
    $userprofile = $user_data['profile_path'] ?? 'default_profile.png';
    
    // Update session
    $_SESSION['firstname'] = $firstname;
    $_SESSION['lastname'] = $lastname;
    $_SESSION['email'] = $useremail;
    $_SESSION['profile_path'] = $userprofile;
}
?>