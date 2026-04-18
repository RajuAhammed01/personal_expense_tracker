<?php
require_once 'session.php';

$success = '';
$error = '';

// Update profile info
if (isset($_POST['save'])) {
    $firstname = trim($_POST['first_name']);
    $lastname = trim($_POST['last_name']);
    
    $query = "UPDATE users SET firstname = ?, lastname = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $firstname, $lastname, $userid);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
        $success = "Profile updated successfully!";
        // Refresh user data
        $username = trim($firstname . ' ' . $lastname);
    } else {
        $error = "Failed to update profile.";
    }
}

// Upload profile picture
if (isset($_POST['upload_picture']) && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/";
    
    // Create uploads directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $new_filename = $userid . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($file_extension, $allowed_types)) {
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $query = "UPDATE users SET profile_path = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "si", $new_filename, $userid);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['profile_path'] = $new_filename;
                $userprofile = $new_filename;
                $success = "Profile picture updated!";
            }
        } else {
            $error = "Failed to upload image.";
        }
    } else {
        $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Profile - Expense Manager</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="js/feather.min.js"></script>
    <style>
        .try {
            font-size: 28px;
            padding: 15px 65px 5px 0px;
        }
        .avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="border-right" id="sidebar-wrapper">
            <div class="user text-center">
                <img class="img-fluid rounded-circle" src="uploads/<?php echo $userprofile; ?>" width="120">
                <h5><?php echo htmlspecialchars($username); ?></h5>
                <p><?php echo htmlspecialchars($useremail); ?></p>
            </div>
            <div class="sidebar-heading">Management</div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item list-group-item-action"><span data-feather="home"></span> Dashboard</a>
                <a href="add_expense.php" class="list-group-item list-group-item-action"><span data-feather="plus-square"></span> Add Expenses</a>
                <a href="manage_expense.php" class="list-group-item list-group-item-action"><span data-feather="dollar-sign"></span> Manage Expenses</a>
                <a href="expensereport.php" class="list-group-item list-group-item-action"><span data-feather="file-text"></span> Expense Report</a>
            </div>
            <div class="sidebar-heading">Settings</div>
            <div class="list-group list-group-flush">
                <a href="profile.php" class="list-group-item list-group-item-action sidebar-active"><span data-feather="user"></span> Profile</a>
                <a href="change_password.php" class="list-group-item list-group-item-action"><span data-feather="lock"></span> Change Password</a>
                <a href="logout.php" class="list-group-item list-group-item-action"><span data-feather="power"></span> Logout</a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light border-bottom">
                <button class="toggler" type="button" id="menu-toggle">
                    <span data-feather="menu"></span>
                </button>
                <div class="col-md-12 text-center">
                    <h3 class="try">Update Profile</h3>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <!-- Profile Picture Section -->
                        <div class="card mb-4">
                            <div class="card-header">Profile Picture</div>
                            <div class="card-body text-center">
                                <img src="uploads/<?php echo $userprofile; ?>" class="rounded-circle avatar mb-3" alt="Profile Picture">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <input type="file" class="form-control-file" name="profile_picture" accept="image/*" required>
                                    </div>
                                    <button type="submit" name="upload_picture" class="btn btn-primary">Upload New Picture</button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Profile Info Section -->
                        <div class="card">
                            <div class="card-header">Personal Information</div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>First Name</label>
                                                <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($firstname); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Last Name</label>
                                                <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($lastname); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($useremail); ?>" disabled>
                                        <small class="text-muted">Email cannot be changed</small>
                                    </div>
                                    <button type="submit" name="save" class="btn btn-success btn-block">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery.slim.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });
        feather.replace();
    </script>
</body>
</html>