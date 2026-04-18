<?php
require_once 'session.php';

// Get expense data for dashboard
// Today's expense
$today_query = "SELECT COALESCE(SUM(expense), 0) as total FROM expenses WHERE user_id = ? AND expensedate = CURDATE()";
$stmt = mysqli_prepare($con, $today_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$today_result = mysqli_stmt_get_result($stmt);
$today_expense = mysqli_fetch_assoc($today_result)['total'];

// Yesterday's expense
$yesterday_query = "SELECT COALESCE(SUM(expense), 0) as total FROM expenses WHERE user_id = ? AND expensedate = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
$stmt = mysqli_prepare($con, $yesterday_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$yesterday_result = mysqli_stmt_get_result($stmt);
$yesterday_expense = mysqli_fetch_assoc($yesterday_result)['total'];

// This week expense
$week_query = "SELECT COALESCE(SUM(expense), 0) as total FROM expenses WHERE user_id = ? AND expensedate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$stmt = mysqli_prepare($con, $week_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$week_result = mysqli_stmt_get_result($stmt);
$week_expense = mysqli_fetch_assoc($week_result)['total'];

// This month expense
$month_query = "SELECT COALESCE(SUM(expense), 0) as total FROM expenses WHERE user_id = ? AND expensedate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$stmt = mysqli_prepare($con, $month_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$month_result = mysqli_stmt_get_result($stmt);
$month_expense = mysqli_fetch_assoc($month_result)['total'];

// This year expense
$year_query = "SELECT COALESCE(SUM(expense), 0) as total FROM expenses WHERE user_id = ? AND YEAR(expensedate) = YEAR(CURDATE())";
$stmt = mysqli_prepare($con, $year_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$year_result = mysqli_stmt_get_result($stmt);
$year_expense = mysqli_fetch_assoc($year_result)['total'];

// Total expense
$total_query = "SELECT COALESCE(SUM(expense), 0) as total FROM expenses WHERE user_id = ?";
$stmt = mysqli_prepare($con, $total_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$total_result = mysqli_stmt_get_result($stmt);
$total_expense = mysqli_fetch_assoc($total_result)['total'];

// Category data for chart
$category_query = "SELECT expensecategory, SUM(expense) as total FROM expenses WHERE user_id = ? GROUP BY expensecategory";
$stmt = mysqli_prepare($con, $category_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$category_result = mysqli_stmt_get_result($stmt);

$categories = [];
$category_amounts = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $categories[] = $row['expensecategory'];
    $category_amounts[] = $row['total'];
}

// Daily data for chart (last 7 days)
$daily_query = "SELECT DATE_FORMAT(expensedate, '%b %d') as day, SUM(expense) as total 
                FROM expenses WHERE user_id = ? AND expensedate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                GROUP BY expensedate ORDER BY expensedate";
$stmt = mysqli_prepare($con, $daily_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$daily_result = mysqli_stmt_get_result($stmt);

$days = [];
$daily_amounts = [];
while ($row = mysqli_fetch_assoc($daily_result)) {
    $days[] = $row['day'];
    $daily_amounts[] = $row['total'];
}

// Monthly data for chart (last 12 months)
$monthly_query = "SELECT DATE_FORMAT(expensedate, '%Y-%m') as month, SUM(expense) as total 
                  FROM expenses WHERE user_id = ? AND expensedate >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) 
                  GROUP BY DATE_FORMAT(expensedate, '%Y-%m') ORDER BY expensedate";
$stmt = mysqli_prepare($con, $monthly_query);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$monthly_result = mysqli_stmt_get_result($stmt);

$months = [];
$monthly_amounts = [];
while ($row = mysqli_fetch_assoc($monthly_result)) {
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_amounts[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Expense Manager - Dashboard</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="js/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card h3 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .stat-card p {
            margin-bottom: 0;
            opacity: 0.9;
        }
        .stat-card:nth-child(2) { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card:nth-child(3) { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card:nth-child(4) { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .stat-card:nth-child(5) { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .stat-card:nth-child(6) { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); }
        .try {
            font-size: 28px;
            padding: 15px 0px 5px 0px;
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
                <a href="index.php" class="list-group-item list-group-item-action sidebar-active"><span data-feather="home"></span> Dashboard</a>
                <a href="add_expense.php" class="list-group-item list-group-item-action"><span data-feather="plus-square"></span> Add Expenses</a>
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
                <div class="col-md-11 text-center">
                    <h3 class="try">Dashboard</h3>
                </div>
            </nav>

                        <div class="container-fluid">
                <div class="row mt-4">
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <p>Today</p>
                            <h3>৳<?php echo number_format($today_expense, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <p>Yesterday</p>
                            <h3>৳<?php echo number_format($yesterday_expense, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <p>Last 7 Days</p>
                            <h3>৳<?php echo number_format($week_expense, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <p>Last 30 Days</p>
                            <h3>৳<?php echo number_format($month_expense, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <p>This Year</p>
                            <h3>৳<?php echo number_format($year_expense, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <p>Total</p>
                            <h3>৳<?php echo number_format($total_expense, 2); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Daily Expenses (Last 7 Days)</div>
                            <div class="card-body">
                                <canvas id="dailyChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Expenses by Category</div>
                            <div class="card-body">
                                <canvas id="categoryChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">Monthly Expenses (Last 12 Months)</div>
                            <div class="card-body">
                                <canvas id="monthlyChart" height="100"></canvas>
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

        // Daily Chart
        new Chart(document.getElementById('dailyChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($days); ?>,
                datasets: [{
                    label: 'Expense (৳)',
                    data: <?php echo json_encode($daily_amounts); ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,0.1)',
                    fill: true
                }]
            }
        });

        // Category Chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    label: 'Expense (৳)',
                    data: <?php echo json_encode($category_amounts); ?>,
                    backgroundColor: ['#dc3545', '#28a745', '#007bff', '#ffc107', '#17a2b8', '#6f42c1', '#fd7e14']
                }]
            }
        });

        // Monthly Chart
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Expense (৳)',
                    data: <?php echo json_encode($monthly_amounts); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40,167,69,0.1)',
                    fill: true
                }]
            }
        });
    </script>
</body>
</html>