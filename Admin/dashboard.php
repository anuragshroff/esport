<?php

error_reporting(error_level: E_ALL);

ini_set(option: 'display_errors', value: 1);
require_once 'config.php'; // Ensure $pdo is properly initialized

//check if user is logged in 
if (isset($_SESSION['id'])) {
    header('location: login.php'); // Redirect to login page
    exit;
}
// Initialize the variable
$active_tournaments = 0;

// Example: Fetching data from a database
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE status = 'active'");
    $stmt->execute();
    $active_tournaments = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Handle error
    $error_message = "Error fetching active tournaments: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eSports Tournament Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            font-weight: 500;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid #007bff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(0, 123, 255, 0.5);
            border-left: 3px solid #007bff;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .dashboard-card {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="pt-4 pb-3 text-center">
                    <h4 class="text-white">eSports Admin</h4>
                    <p class="text-light mb-0"><?php echo htmlspecialchars($username ?? 'Guest'); ?></p>
                </div>
                <hr class="bg-light">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-trophy"></i> Tournaments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-users"></i> Teams</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user-ninja"></i> Players</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-gamepad"></i> Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-calendar-alt"></i> Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create_admin.php"><i class="fas fa-user-shield"></i> Admin Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-cog"></i> Settings</a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> New Tournament
                        </button>
                    </div>
                </div>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                
                    <!-- Recent Admin Logins -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Admin Logins</h6>
                                <a href="#" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_logins)): ?>
                                <p class="text-center text-muted">No recent login activity.</p>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Name</th>
                                                <th>Last Login</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_logins as $login): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($login['username']); ?></td>
                                                <td><?php echo htmlspecialchars($login['full_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($login['role'] === 'super_admin') ? 'danger' : 'primary'; ?>">
                                                        <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($login['role']))); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y H:i', strtotime($login['last_login'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="#" class="btn btn-primary btn-block py-3">
                                            <i class="fas fa-trophy mr-2"></i> Add Tournament
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="#" class="btn btn-success btn-block py-3">
                                            <i class="fas fa-users mr-2"></i> Register Team
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="#" class="btn btn-info btn-block py-3">
                                            <i class="fas fa-calendar-alt mr-2"></i> Schedule Match
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="#" class="btn btn-warning btn-block py-3">
                                            <i class="fas fa-chart-line mr-2"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <footer class="bg-white rounded shadow p-3 mb-4">
                    <div class="row">
                        <div class="col-lg-6 text-center text-lg-start mb-3 mb-lg-0">
                            <p class="mb-0 text-muted">
                                &copy; 2025 eSports Tournament Management
                            </p>
                        </div>
                        <div class="col-lg-6 text-center text-lg-end">
                            <a href="#" class="text-decoration-none mx-2">About</a>
                            <a href="#" class="text-decoration-none mx-2">Help</a>
                            <a href="#" class="text-decoration-none mx-2">Contact</a>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
   
</body>
</html>