<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit();
}

// Function to set flash messages
function set_flash_message($message, $type) {
    $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
}

// Function to display flash messages
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message']['message'];
        $type = $_SESSION['flash_message']['type'];
        echo "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">" . htmlspecialchars($message) . "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>";
        unset($_SESSION['flash_message']);
    }
}

$season_data = [];
$season_id = $_GET['id'] ?? null;

if ($season_id === null) {
    set_flash_message("❌ কোনো সেশন নির্বাচন করা হয়নি।", 'danger');
    header('Location: manage_seasons.php');
    exit();
}

// Fetch season data
$sql = "SELECT id, season_name, registrations_open FROM seasons WHERE id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $season_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) == 1) {
        $season_data = mysqli_fetch_assoc($result);
    } else {
        set_flash_message("❌ সেশন পাওয়া যায়নি।", 'danger');
        header('Location: manage_seasons.php');
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    set_flash_message("❌ ডাটাবেস ত্রুটি: " . mysqli_error($link), 'danger');
    header('Location: manage_seasons.php');
    exit();
}

// Handle form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_season'])) {
    $new_season_name = trim($_POST['season_name']);
    $new_registrations_open = isset($_POST['registrations_open']) ? 1 : 0;

    if (empty($new_season_name)) {
        set_flash_message("❌ সেশনের নাম খালি রাখা যাবে না।", 'danger');
    } else {
        // Check for duplicate season name (excluding current season)
        $check_duplicate_sql = "SELECT id FROM seasons WHERE season_name = ? AND id != ?";
        if ($check_duplicate_stmt = mysqli_prepare($link, $check_duplicate_sql)) {
            mysqli_stmt_bind_param($check_duplicate_stmt, "si", $new_season_name, $season_id);
            mysqli_stmt_execute($check_duplicate_stmt);
            mysqli_stmt_store_result($check_duplicate_stmt);
            if (mysqli_stmt_num_rows($check_duplicate_stmt) > 0) {
                set_flash_message("❌ এই নামে ইতিমধ্যে একটি সেশন আছে।", 'danger');
            } else {
                $update_sql = "UPDATE seasons SET season_name = ?, registrations_open = ? WHERE id = ?";
                if ($stmt = mysqli_prepare($link, $update_sql)) {
                    mysqli_stmt_bind_param($stmt, "sii", $new_season_name, $new_registrations_open, $season_id);
                    if (mysqli_stmt_execute($stmt)) {
                        set_flash_message("✅ সেশন সফলভাবে আপডেট হয়েছে!", 'success');
                        header('Location: manage_seasons.php');
                        exit();
                    } else {
                        set_flash_message("❌ ত্রুটি: আপডেট করা যায়নি।" . mysqli_error($link), 'danger');
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    set_flash_message("❌ প্রস্তুতি ব্যর্থ: " . mysqli_error($link), 'danger');
                }
            }
            mysqli_stmt_close($check_duplicate_stmt);
        }
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সেশন এডিট করুন</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.maateen.me/solaiman-lipi/font.css');
        body { font-family: 'SolaimanLipi', Arial, sans-serif; background-color: #f8f9fa; }
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
        .sidebar .nav-link { color: #adb5bd; font-size: 1.1rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white; /* White text for active link */
            background-color: #28a745; /* Green background for active link */
        }
        .sidebar .nav-link .fa { margin-right: 10px; }
        .main-content {
            margin-left: 0; /* No margin on mobile */
            padding: 20px;
        }
        .navbar-brand { font-weight: bold; color: white !important; }

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
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fa fa-tachometer-alt"></i>ড্যাশবোর্ড</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php" target="_blank"><i class="fa fa-home"></i>হোম পেজ <span style="margin-left: auto; padding-left: 10px;"><i class="fa fa-external-link-alt"></i></span></a>
                </li>
                <li class="nav-item"><a class="nav-link" href="view_scouts.php"><i class="fa fa-users"></i>স্কাউট তথ্য দেখুন</a></li>
                <li class="nav-item"><a class="nav-link active" href="manage_seasons.php"><i class="fa fa-cogs"></i>সেশন ম্যানেজ</a></li>
                <li class="nav-item mt-auto">
                    <a class="nav-link btn-logout" href="logout.php"><i class="fa fa-sign-out-alt"></i>লগআউট</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="my-4">সেশন এডিট করুন</h1>

            <?php display_flash_message(); ?>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">সেশনের তথ্য</h5>
                </div>
                <div class="card-body">
                    <form action="edit_season.php?id=<?php echo htmlspecialchars($season_data['id']); ?>" method="POST">
                        <input type="hidden" name="season_id" value="<?php echo htmlspecialchars($season_data['id']); ?>">
                        
                        <div class="mb-3">
                            <label for="season_name" class="form-label">সেশনের নাম</label>
                            <input type="text" class="form-control" id="season_name" name="season_name" value="<?php echo htmlspecialchars($season_data['season_name']); ?>" required>
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="registrations_open" name="registrations_open" <?php echo $season_data['registrations_open'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="registrations_open">রেজিস্ট্রেশন চালু</label>
                        </div>

                        <button type="submit" name="update_season" class="btn btn-primary">আপডেট করুন</button>
                        <a href="manage_seasons.php" class="btn btn-secondary">ফিরে যান</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>