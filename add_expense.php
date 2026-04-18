<?php
require_once 'session.php';

$update = false;
$delete_mode = false;
$expenseamount = "";
$expensedate = date("Y-m-d");
$expensecategory = "";

// Handle Edit - Load expense data
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $query = "SELECT * FROM expenses WHERE user_id = ? AND expense_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ii", $userid, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $update = true;
        $expenseamount = $row['expense'];
        $expensedate = $row['expensedate'];
        $expensecategory = $row['expensecategory'];
    }
}

// Handle Delete confirmation
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "SELECT * FROM expenses WHERE user_id = ? AND expense_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ii", $userid, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $delete_mode = true;
        $expenseamount = $row['expense'];
        $expensedate = $row['expensedate'];
        $expensecategory = $row['expensecategory'];
    }
}

// Handle Add Expense
if (isset($_POST['add'])) {
    $amount = (float)$_POST['expenseamount'];
    $date = $_POST['expensedate'];
    $category = $_POST['expensecategory'];
    
    $query = "INSERT INTO expenses (user_id, expense, expensedate, expensecategory) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "idss", $userid, $amount, $date, $category);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage_expense.php");
        exit();
    }
}

// Handle Update Expense
if (isset($_POST['update']) && isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $amount = (float)$_POST['expenseamount'];
    $date = $_POST['expensedate'];
    $category = $_POST['expensecategory'];
    
    $query = "UPDATE expenses SET expense = ?, expensedate = ?, expensecategory = ? WHERE user_id = ? AND expense_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "dssii", $amount, $date, $category, $userid, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage_expense.php");
        exit();
    }
}

// Handle Delete Expense
if (isset($_POST['delete']) && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $query = "DELETE FROM expenses WHERE user_id = ? AND expense_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ii", $userid, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage_expense.php");
        exit();
    }
}

// Get categories for dropdown
$categories_query = "SELECT * FROM expense_categories ORDER BY category_name";
$categories_result = mysqli_query($con, $categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Add Expense - Expense Manager</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="js/feather.min.js"></script>
    <style>
        .try {
            font-size: 28px;
            padding: 15px 70px 5px 0px;
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
                <a href="add_expense.php" class="list-group-item list-group-item-action sidebar-active"><span data-feather="plus-square"></span> Add Expenses</a>
                <a href="manage_expense.php" class="list-group-item list-group-item-action"><span data-feather="dollar-sign"></span> Manage Expenses</a>
                <a href="expensereport.php" class="list-group-item list-group-item-action"><span data-feather="file-text"></span> Expense Report</a>
            </div>
            <div class="sidebar-heading">Settings</div>
            <div class="list-group list-group-flush">
                <a href="profile.php" class="list-group-item list-group-item-action"><span data-feather="user"></span> Profile</a>
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
                    <h3 class="try">Add Your Daily Expenses</h3>
                </div>
            </nav>

            <div class="container">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <form method="POST">
                            <div class="form-group row mt-4">
                                <label class="col-sm-6 col-form-label"><b>Enter Amount</b></label>
                                <div class="col-md-6">
                                    <input type="number" class="form-control" value="<?php echo $expenseamount; ?>" name="expenseamount" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label"><b>Date</b></label>
                                <div class="col-md-6">
                                    <input type="date" class="form-control" value="<?php echo $expensedate; ?>" name="expensedate" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-6 col-form-label"><b>Category</b></label>
                                <div class="col-md-6">
                                    <select class="form-control" name="expensecategory" required>
                                        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                            <option value="<?php echo $cat['category_name']; ?>" <?php echo ($cat['category_name'] == $expensecategory) ? 'selected' : ''; ?>>
                                                <?php echo $cat['category_name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12 text-right">
                                    <?php if ($update): ?>
                                        <button class="btn btn-lg btn-block btn-warning" type="submit" name="update">Update</button>
                                    <?php elseif ($delete_mode): ?>
                                        <button class="btn btn-lg btn-block btn-danger" type="submit" name="delete">Delete</button>
                                    <?php else: ?>
                                        <button type="submit" name="add" class="btn btn-lg btn-block btn-success">Add Expense</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3"></div>
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