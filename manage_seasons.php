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

// Handle POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle Create New Season
    if (isset($_POST['create_season'])) {
        $season_name = trim($_POST['season_name']);
        if (!empty($season_name)) {
            // Check if season already exists
            $check_sql = "SELECT id FROM seasons WHERE season_name = ?";
            if($check_stmt = mysqli_prepare($link, $check_sql)){
                mysqli_stmt_bind_param($check_stmt, "s", $season_name);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);
                if(mysqli_stmt_num_rows($check_stmt) > 0){
                    set_flash_message("❌ এই নামে ইতিমধ্যে একটি সেশন আছে।", 'danger');
                } else {
                    $insert_sql = "INSERT INTO seasons (season_name, registrations_open) VALUES (?, 0)";
                    if ($stmt = mysqli_prepare($link, $insert_sql)) {
                        mysqli_stmt_bind_param($stmt, "s", $season_name);
                        if (mysqli_stmt_execute($stmt)) {
                            set_flash_message("✅ নতুন সেশন সফলভাবে তৈরি হয়েছে!", 'success');
                        } else {
                            set_flash_message("❌ ত্রুটি: সেশন তৈরি করা যায়নি।", 'danger');
                        }
                        mysqli_stmt_close($stmt);
                    }
                }
                mysqli_stmt_close($check_stmt);
            }
        } else {
            set_flash_message("❌ সেশনের নাম দিন।", 'danger');
        }
    }

    // Handle Toggle Registration for a season (now also acts as active toggle)
    if (isset($_POST['toggle_registration'])) {
        $season_id = $_POST['season_id'];
        $current_status = $_POST['current_status'];
        $new_status = ($current_status == 1) ? 0 : 1;

        $toggle_sql = "UPDATE seasons SET registrations_open = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $toggle_sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $new_status, $season_id);
            if(mysqli_stmt_execute($stmt)){
                set_flash_message("✅ রেজিস্ট্রেশন স্ট্যাটাস আপডেট করা হয়েছে।", 'success');
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Handle Delete Season
    if (isset($_POST['delete_season'])) {
        $season_id = $_POST['season_id'];

        // Delete associated scouts first
        $delete_scouts_sql = "DELETE FROM scouts WHERE season_id = ?";
        if ($stmt_scouts = mysqli_prepare($link, $delete_scouts_sql)) {
            mysqli_stmt_bind_param($stmt_scouts, "i", $season_id);
            mysqli_stmt_execute($stmt_scouts);
            mysqli_stmt_close($stmt_scouts);
        }

        $delete_sql = "DELETE FROM seasons WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $delete_sql)) {
            mysqli_stmt_bind_param($stmt, "i", $season_id);
            if(mysqli_stmt_execute($stmt)){
                set_flash_message("✅ সেশন সফলভাবে মুছে ফেলা হয়েছে।", 'success');
            } else {
                set_flash_message("❌ ত্রুটি: সেশন মুছে ফেলা যায়নি।" . mysqli_error($link), 'danger');
            }
            mysqli_stmt_close($stmt);
        } else {
            set_flash_message("❌ প্রস্তুতি ব্যর্থ: " . mysqli_error($link), 'danger');
        }
    }

    // Redirect after processing POST request to prevent re-submission
    header("Location: manage_seasons.php");
    exit();
}

// Fetch all seasons for display
$seasons = [];
$sql = "SELECT id, season_name, registrations_open, created_at FROM seasons ORDER BY created_at DESC";
if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $seasons[] = $row;
    }
} 
// No else block here. If query fails, $seasons will remain empty.

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সেশন ম্যানেজ করুন</title>
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
        .status-active { color: #198754; font-weight: bold; }
        .status-inactive { color: #6c757d; font-weight: bold; }

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
                <li class="nav-item">
                    <a class="nav-link" href="view_scouts.php"><i class="fa fa-users"></i>স্কাউট তথ্য দেখুন</a>
                </li>
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
            <h1 class="my-4">সেশন ম্যানেজ করুন</h1>

            <?php display_flash_message(); ?>

            <!-- Create New Season Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">নতুন সেশন তৈরি করুন</h5>
                </div>
                <div class="card-body">
                    <form action="manage_seasons.php" method="POST">
                        <div class="input-group">
                            <input type="text" class="form-control" name="season_name" placeholder="সেশনের নাম দিন (যেমন: বার্ষিক ক্যাম্প ২০২৩)" required>
                            <button class="btn btn-primary" type="submit" name="create_season">তৈরি করুন</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Seasons List Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">সকল সেশনের তালিকা</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>সেশনের নাম</th>
                                    <th>তৈরির তারিখ</th>
                                    <th>রেজিস্ট্রেশন</th>
                                    <th>অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($seasons)): ?>
                                    <?php foreach ($seasons as $season): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($season['season_name']); ?></td>
                                            <td><?php echo date("d-m-Y", strtotime($season['created_at'])); ?></td>
                                            <td>
                                                <?php
                                                $status_class = $season['registrations_open'] ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis';
                                                $status_text = $season['registrations_open'] ? 'চালু' : 'বন্ধ';
                                                ?>
                                                <span class="p-2 rounded <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <?php
                                            $toggle_button_class = $season['registrations_open'] ? 'btn-secondary' : 'btn-success';
                                            $toggle_button_text = $season['registrations_open'] ? 'বন্ধ করুন' : 'চালু করুন';

                                            

                                            $actions_html = <<<HTML
                                                <div class="btn-group">
                                                    <form action="manage_seasons.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="season_id" value="{$season['id']}">
                                                        <input type="hidden" name="current_status" value="{$season['registrations_open']}">
                                                        <button type="submit" name="toggle_registration" class="btn btn-sm {$toggle_button_class}">
                                                            {$toggle_button_text}
                                                        </button>
                                                    </form>
                                                    <a href="edit_season.php?id={$season['id']}" class="btn btn-sm btn-info ms-2">এডিট</a>
                                                    <form action="manage_seasons.php" method="POST" class="d-inline delete-season-form">
                                                        <input type="hidden" name="season_id" value="{$season['id']}">
                                                        <button type="submit" name="delete_season" class="btn btn-sm btn-danger ms-2 delete-season-btn">মুছে ফেলুন</button>
                                                    </form>
                                                </div>
HTML;
                                            echo '<td>' . $actions_html . '</td>';
                                            ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">কোনো সেশন তৈরি করা হয়নি।</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var deleteButtons = document.querySelectorAll('.delete-season-btn');
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function(event) {
                    var confirmMessage = 'আপনি কি নিশ্চিত যে আপনি এই সেশনটি মুছে ফেলতে চান? এই সেশনের সাথে যুক্ত সকল স্কাউট ডেটা মুছে যাবে!';
                    if (!confirm(confirmMessage)) {
                        event.preventDefault(); // Prevent form submission if user cancels
                    }
                });
            });
        });
    </script>
</body>
</html>
