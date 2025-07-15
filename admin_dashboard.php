<?php
session_start();
require_once 'config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit();
}

$total_scouts = 0;
$seasons = [];
$selected_season_id = $_GET['season_id'] ?? null;
$selected_season_name = 'সকল সেশন';

// Fetch all seasons for the dropdown
$season_fetch_sql = "SELECT id, season_name FROM seasons ORDER BY created_at DESC";
if ($season_result = mysqli_query($link, $season_fetch_sql)) {
    while ($row = mysqli_fetch_assoc($season_result)) {
        $seasons[] = $row;
    }
    mysqli_free_result($season_result);
}

// Determine the selected season and its name
if ($selected_season_id !== null) {
    foreach ($seasons as $season) {
        if ($season['id'] == $selected_season_id) {
            $selected_season_name = $season['season_name'];
            break;
        }
    }
}

$where_clause = '';
$params = [];
$types = '';

if ($selected_season_id !== null) {
    $where_clause = ' WHERE season_id = ?';
    $params[] = $selected_season_id;
    $types .= 'i';
}

// Fetch total scouts
$sql_total = "SELECT COUNT(*) AS total FROM scouts" . $where_clause;
if ($stmt = mysqli_prepare($link, $sql_total)) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result_total = mysqli_stmt_get_result($stmt);
    if ($result_total) {
        $total_scouts = mysqli_fetch_assoc($result_total)['total'];
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন ড্যাশবোর্ড</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.maateen.me/solaiman-lipi/font.css');
        body {
            font-family: 'SolaimanLipi', Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .offcanvas-header {
            background-color: #343a40;
            color: white;
        }
        .offcanvas-body {
            padding-top: 0;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            font-size: 1.1rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white; /* White text for active link */
            background-color: #28a745; /* Green background for active link */
        }
        .sidebar .nav-link .fa {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 0; /* No margin on mobile */
            padding: 20px;
        }
        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .stat-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card .display-4 {
            font-weight: bold;
        }
        .stat-icon {
            font-size: 3rem;
            opacity: 0.6;
        }
        .card-title {
            font-weight: bold;
            color: #495057;
        }
        .toggle-card .form-check-input {
            width: 3rem;
            height: 1.5rem;
        }
        .btn-logout {
            width: 100%;
            text-align: left;
        }

        @media (min-width: 768px) {
            .sidebar {
                width: 250px;
                display: block; /* Show sidebar on larger screens */
            }
            .main-content {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex d-md-none bg-dark text-white p-3 justify-content-between align-items-center">
        <a class="navbar-brand" href="#">অ্যাডমিন প্যানেল</a>
        <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
            <i class="fa fa-bars"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <div class="sidebar offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarMenuLabel">অ্যাডমিন প্যানেল</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav flex-column mt-4">
                <li class="nav-item">
                    <a class="nav-link active" href="admin_dashboard.php"><i class="fa fa-tachometer-alt"></i>ড্যাশবোর্ড</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php" target="_blank"><i class="fa fa-home"></i>হোম পেজ <span style="margin-left: auto; padding-left: 10px;"><i class="fa fa-external-link-alt"></i></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_scouts.php"><i class="fa fa-users"></i>স্কাউট তথ্য দেখুন</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_seasons.php"><i class="fa fa-cogs"></i>সেশন ম্যানেজ</a>
                </li>
                <li class="nav-item mt-auto">
                    <a class="nav-link btn-logout" href="logout.php"><i class="fa fa-sign-out-alt"></i>লগআউট</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="my-4">ড্যাশবোর্ড - <?php echo htmlspecialchars($selected_season_name); ?></h1>

            <!-- Season Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="admin_dashboard.php" method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="season_filter" class="form-label">সেশন নির্বাচন করুন:</label>
                        </div>
                        <div class="col-auto">
                            <select id="season_filter" name="season_id" class="form-select" onchange="this.form.submit()">
                                <option value="">সকল সেশন</option>
                                <?php foreach ($seasons as $season): ?>
                                    <option value="<?php echo htmlspecialchars($season['id']); ?>" <?php echo ($selected_season_id == $season['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($season['season_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <div>
                                <h5 class="card-title text-white">মোট স্কাউট</h5>
                                <p class="display-4"><?php echo $total_scouts; ?></p>
                            </div>
                            <i class="fa fa-users stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>