<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit();
}

require_once 'config.php';

$scout_data = [];
$scout_id = '';
$message = '';
$is_error = false;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $scout_id = $_GET['id'];

    $sql = "SELECT s.scout_id, s.name, s.unit, s.district, s.father_name, s.mother_name, s.mobile_number, s.image_url, se.season_name FROM scouts s LEFT JOIN seasons se ON s.season_id = se.id WHERE s.scout_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $scout_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $scout_data = mysqli_fetch_assoc($result);
        } else {
            $message = "❌ স্কাউট ডেটা পাওয়া যায়নি!";
            $is_error = true;
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "❌ প্রস্তুতি ব্যর্থ: " . htmlspecialchars(mysqli_error($link));
        $is_error = true;
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $scout_id = $_POST['scout_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $district = $_POST['district'] ?? '';
    $father = $_POST['father'] ?? '';
    $mother = $_POST['mother'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $image_url = $_POST['image_url'] ?? '';

    if (empty($scout_id) || empty($name)) {
        $message = "❌ BSID ও নাম আবশ্যক!";
        $is_error = true;
    } else {
        $sql = "UPDATE scouts SET name = ?, unit = ?, district = ?, father_name = ?, mother_name = ?, mobile_number = ?, image_url = ? WHERE scout_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssss", $name, $unit, $district, $father, $mother, $mobile, $image_url, $scout_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "✅ তথ্য সফলভাবে আপডেট হয়েছে!";
            } else {
                $message = "❌ ত্রুটি: " . htmlspecialchars(mysqli_error($link));
                $is_error = true;
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "❌ প্রস্তুতি ব্যর্থ: " . htmlspecialchars(mysqli_error($link));
            $is_error = true;
        }
    }
} else {
    $message = "Invalid request.";
    $is_error = true;
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>স্কাউট ডেটা এডিট করুন</title>
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
        .sidebar .nav-link {
            color: #adb5bd;
            font-size: 1.1rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: #495057;
        }
        .sidebar .nav-link .fa {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a class="navbar-brand px-3" href="#">অ্যাডমিন প্যানেল</a>
        <ul class="nav flex-column mt-4">
            <li class="nav-item">
                <a class="nav-link" href="admin_dashboard.php"><i class="fa fa-tachometer-alt"></i>ড্যাশবোর্ড</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php"><i class="fa fa-home"></i>হোম</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="view_scouts.php"><i class="fa fa-users"></i>স্কাউট দেখুন</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_seasons.php"><i class="fa fa-cogs"></i>সেশন ম্যানেজ</a>
            </li>
            <li class="nav-item mt-auto">
                <a class="nav-link btn-logout" href="logout.php"><i class="fa fa-sign-out-alt"></i>লগআউট</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="my-4">স্কাউট ডেটা এডিট করুন</h1>

            <?php if ($message): ?>
                <div class="alert <?php echo $is_error ? 'alert-danger' : 'alert-success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($scout_data) && !$is_error): ?>
                <div class="card">
                    <div class="card-body">
                        <form action="edit_scout.php" method="POST">
                            <input type="hidden" name="scout_id" value="<?php echo htmlspecialchars($scout_data['scout_id']); ?>">
                            <div class="mb-3">
                                <label for="bsid" class="form-label">BSID</label>
                                <input type="text" id="bsid" class="form-control" value="<?php echo htmlspecialchars($scout_data['scout_id']); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">নাম</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($scout_data['name'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="season" class="form-label">সেশন</label>
                                <input type="text" id="season" class="form-control" value="<?php echo htmlspecialchars($scout_data['season_name'] ?? 'N/A'); ?>" disabled readonly>
                            </div>
                            <div class="mb-3">
                                <label for="unit" class="form-label">দলের নাম</label>
                                <input type="text" id="unit" name="unit" class="form-control" value="<?php echo htmlspecialchars($scout_data['unit'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="district" class="form-label">জেলার নাম</label>
                                <input type="text" id="district" name="district" class="form-control" value="<?php echo htmlspecialchars($scout_data['district'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="father" class="form-label">পিতার নাম</label>
                                <input type="text" id="father" name="father" class="form-control" value="<?php echo htmlspecialchars($scout_data['father_name'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="mother" class="form-label">মায়ের নাম</label>
                                <input type="text" id="mother" name="mother" class="form-control" value="<?php echo htmlspecialchars($scout_data['mother_name'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="mobile" class="form-label">মোবাইল</label>
                                <input type="text" id="mobile" name="mobile" class="form-control" value="<?php echo htmlspecialchars($scout_data['mobile_number'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="image_url" class="form-label">ছবির URL</label>
                                <input type="url" id="image_url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($scout_data['image_url'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <button type="submit" name="update_scout" class="btn btn-primary">আপডেট করুন</button>
                            <a href="view_scouts.php" class="btn btn-secondary">ফিরে যান</a>
                        </form>
                    </div>
                </div>
            <?php elseif ($is_error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <a href="view_scouts.php" class="btn btn-secondary">ফিরে যান</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>