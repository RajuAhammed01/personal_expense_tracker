<?php
require_once 'session.php';

$reportData = [];
$reportType = '';
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');
$tableHeader = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['report_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    
    // Build query based on report type
    switch ($reportType) {
        case 'datewise':
            $query = "SELECT expensedate as period, SUM(expense) as total 
                      FROM expenses WHERE user_id = ? AND expensedate BETWEEN ? AND ? 
                      GROUP BY expensedate ORDER BY expensedate";
            $tableHeader = 'Date';
            break;
        case 'monthwise':
            $query = "SELECT DATE_FORMAT(expensedate, '%Y-%m') as period, SUM(expense) as total 
                      FROM expenses WHERE user_id = ? AND expensedate BETWEEN ? AND ? 
                      GROUP BY DATE_FORMAT(expensedate, '%Y-%m') ORDER BY period";
            $tableHeader = 'Month';
            break;
        case 'yearwise':
            $query = "SELECT YEAR(expensedate) as period, SUM(expense) as total 
                      FROM expenses WHERE user_id = ? AND expensedate BETWEEN ? AND ? 
                      GROUP BY YEAR(expensedate) ORDER BY period";
            $tableHeader = 'Year';
            break;
        default:
            $query = '';
    }
    
    if ($query) {
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "iss", $userid, $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $reportData = mysqli_stmt_get_result($stmt);
    }
}

// Get total for selected period
$totalQuery = "SELECT COALESCE(SUM(expense), 0) as total FROM expenses WHERE user_id = ? AND expensedate BETWEEN ? AND ?";
$totalStmt = mysqli_prepare($con, $totalQuery);
mysqli_stmt_bind_param($totalStmt, "iss", $userid, $startDate, $endDate);
mysqli_stmt_execute($totalStmt);
$totalResult = mysqli_stmt_get_result($totalStmt);
$totalAmount = mysqli_fetch_assoc($totalResult)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Expense Report - Expense Manager</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="js/feather.min.js"></script>
    <style>
        .try {
            font-size: 28px;
            padding: 15px 0px 5px 0px;
        }
        @media print {
            .no-print, .sidebar-wrapper, .navbar, .btn, form {
                display: none !important;
            }
            #page-content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
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
                <a href="expensereport.php" class="list-group-item list-group-item-action sidebar-active"><span data-feather="file-text"></span> Expense Report</a>
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
                    <h3 class="try">Expense Report</h3>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form method="POST" class="mt-3">
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label"><b>Report Type:</b></label>
                                <div class="col-md-8">
                                    <select class="form-control" name="report_type" required>
                                        <option value="datewise" <?php echo $reportType == 'datewise' ? 'selected' : ''; ?>>Datewise Report</option>
                                        <option value="monthwise" <?php echo $reportType == 'monthwise' ? 'selected' : ''; ?>>Monthwise Report</option>
                                        <option value="yearwise" <?php echo $reportType == 'yearwise' ? 'selected' : ''; ?>>Yearwise Report</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label"><b>Start Date:</b></label>
                                <div class="col-md-8">
                                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label"><b>End Date:</b></label>
                                <div class="col-md-8">
                                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success btn-block">Generate Report</button>
                                </div>
                            </div>
                        </form>

                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && mysqli_num_rows($reportData) > 0): ?>
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4>Report: <?php echo ucfirst($reportType); ?> (<?php echo date('d M Y', strtotime($startDate)); ?> - <?php echo date('d M Y', strtotime($endDate)); ?>)</h4>
                                    <button onclick="window.print()" class="btn btn-secondary no-print">Print Report</button>
                                </div>
                                
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th><?php echo $tableHeader; ?></th>
                                            <th>Total Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $count = 1;
                                        $grandTotal = 0;
                                        while ($row = mysqli_fetch_assoc($reportData)): 
                                            $period = $row['period'];
                                            if ($reportType == 'monthwise') {
                                                $period = date('F Y', strtotime($period . '-01'));
                                            }
                                            $grandTotal += $row['total'];
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo $count++; ?></td>
                                            <td><?php echo $period; ?></td>
                                            <td class="text-right">₹ <?php echo number_format($row['total'], 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <tr class="table-success">
                                            <td colspan="2" class="text-right"><strong>Grand Total:</strong></td>
                                            <td class="text-right"><strong>₹ <?php echo number_format($grandTotal, 2); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                            <div class="alert alert-info mt-4 text-center">
                                No expenses found for the selected period.
                            </div>
                        <?php endif; ?>
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