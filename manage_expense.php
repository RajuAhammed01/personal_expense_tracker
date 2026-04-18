<?php
require_once 'session.php';

// Get sorting option
$selectedSort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';

// Build ORDER BY clause
switch ($selectedSort) {
    case 'month':
        $orderBy = "expensedate DESC";
        break;
    case 'category':
        $orderBy = "expensecategory ASC";
        break;
    default:
        $orderBy = "expense_id DESC";
}

// Fetch expenses with sorting
$query = "SELECT * FROM expenses WHERE user_id = ? ORDER BY $orderBy";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$expenses = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manage Expenses - Expense Manager</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="js/feather.min.js"></script>
    <style>
        .try {
            font-size: 28px;
            padding: 15px 0px 5px 0px;
        }
        .table th, .table td {
            vertical-align: middle;
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
                <a href="manage_expense.php" class="list-group-item list-group-item-action sidebar-active"><span data-feather="dollar-sign"></span> Manage Expenses</a>
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
                <div class="col-md-11 text-center">
                    <h3 class="try">Manage Expenses</h3>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <form method="GET" class="mt-3">
                            <div class="form-group">
                                <label>Sort By:</label>
                                <select class="form-control" name="sort" onchange="this.form.submit()">
                                    <option value="recent" <?php echo $selectedSort == 'recent' ? 'selected' : ''; ?>>Recently Added</option>
                                    <option value="month" <?php echo $selectedSort == 'month' ? 'selected' : ''; ?>>Date (Oldest First)</option>
                                    <option value="category" <?php echo $selectedSort == 'category' ? 'selected' : ''; ?>>Category (A-Z)</option>
                                </select>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Amount (৳)</th>
                                        <th>Category</th>
                                        <th colspan="2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $count = 1;
                                    while ($row = mysqli_fetch_assoc($expenses)): 
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $count++; ?></td>
                                        <td class="text-center"><?php echo date('d M Y', strtotime($row['expensedate'])); ?></td>
                                        <td class="text-center"><?php echo number_format($row['expense'], 2); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['expensecategory']); ?></td>
                                        <td class="text-center">
                                            <a href="add_expense.php?edit=<?php echo $row['expense_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                        </td>
                                        <td class="text-center">
                                            <a href="add_expense.php?delete=<?php echo $row['expense_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this expense?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if ($count == 1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No expenses found. Add your first expense!</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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