<?php
session_start();
require_once 'config.php';

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

// Redirect to login if not logged in
$is_admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

$scouts = [];
$unit_names = [];
$seasons_with_stats = [];
$selected_unit = $_GET['unit'] ?? '';
$selected_season_id = $_GET['season_id'] ?? '';
$selected_season_name = 'সকল সেশন';

// Fetch unique unit names for the filter dropdown
$unit_sql = "SELECT DISTINCT unit FROM scouts WHERE unit IS NOT NULL AND unit != '' ORDER BY unit ASC";
if ($unit_result = mysqli_query($link, $unit_sql)) {
    while ($row = mysqli_fetch_assoc($unit_result)) {
        $unit_names[] = $row['unit'];
    }
    mysqli_free_result($unit_result);
}

// Fetch all seasons and their scout counts
$season_fetch_sql = "SELECT id, season_name FROM seasons ORDER BY created_at DESC";
if ($season_result = mysqli_query($link, $season_fetch_sql)) {
    while ($season = mysqli_fetch_assoc($season_result)) {
        $season_id = $season['id'];
        $season_name = $season['season_name'];

        // Fetch total scouts for this season
        $sql_total = "SELECT COUNT(*) AS total FROM scouts WHERE season_id = ?";
        $stmt_total = mysqli_prepare($link, $sql_total);
        mysqli_stmt_bind_param($stmt_total, "i", $season_id);
        mysqli_stmt_execute($stmt_total);
        $result_total = mysqli_stmt_get_result($stmt_total);
        $total_scouts = mysqli_fetch_assoc($result_total)['total'];
        mysqli_stmt_close($stmt_total);

        $seasons_with_stats[] = [
            'id' => $season_id,
            'season_name' => $season_name,
            'total_scouts' => $total_scouts
        ];

        if ($selected_season_id == $season_id) {
            $selected_season_name = $season_name;
        }
    }
    mysqli_free_result($season_result);
}

if ($is_admin_logged_in) {
    $sql = "SELECT s.scout_id, s.name, s.unit, s.district, s.father_name, s.mother_name, s.mobile_number, s.image_url, s.created_at, se.season_name 
            FROM scouts s LEFT JOIN seasons se ON s.season_id = se.id";
} else {
    $sql = "SELECT s.name, s.unit 
            FROM scouts s";
}
$conditions = [];
$params = [];
$types = '';

if (!empty($selected_unit)) {
    $conditions[] = "s.unit = ?";
    $params[] = $selected_unit;
    $types .= 's';
}

if (!empty($selected_season_id)) {
    $conditions[] = "s.season_id = ?";
    $params[] = $selected_season_id;
    $types .= 'i';
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY s.created_at DESC";

if ($stmt = mysqli_prepare($link, $sql)) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $scouts[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p>Error: Could not retrieve data. " . mysqli_error($link) . "</p>";
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নিবন্ধিত স্কাউট তালিকা</title>
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
        .scout-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #dee2e6;
        }
        .table-responsive {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-radius: 10px;
            background: white;
        }
        .action-btn {
            font-size: 0.9rem;
        }
        .season-box {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            color: white; /* Ensure text is white on colored backgrounds */
        }
        .season-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        /* Colors for season boxes */
        .season-bg-1 { background-color: #007bff; }
        .season-bg-2 { background-color: #28a745; }
        .season-bg-3 { background-color: #dc3545; }
        .season-bg-4 { background-color: #ffc107; }
        .season-bg-5 { background-color: #6f42c1; }
        .season-bg-6 { background-color: #20c997; }
        .season-bg-7 { background-color: #fd7e14; }
        .season-bg-8 { background-color: #6610f2; }

        /* Colors for season names in table */
        .season-text-color-1 { color: #007bff; font-weight: bold; }
        .season-text-color-2 { color: #28a744; font-weight: bold; }
        .season-text-color-3 { color: #dc3545; font-weight: bold; }
        .season-text-color-4 { color: #ffc107; font-weight: bold; }
        .season-text-color-5 { color: #6f42c1; font-weight: bold; }
        .season-text-color-6 { color: #20c997; font-weight: bold; }
        .season-text-color-7 { color: #fd7e14; font-weight: bold; }
        .season-text-color-8 { color: #6610f2; font-weight: bold; }

        @media (min-width: 768px) {
            .sidebar {
                width: 250px;
                display: block; /* Show sidebar on larger screens */
            }
            .main-content {
                margin-left: 250px;
            }
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
            .sidebar, .navbar-brand, .d-flex.d-md-none, .card.mb-4 form, .action-btn, .modal, .btn-logout, .alert {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .container-fluid {
                width: 210mm; /* A4 width */
                min-height: 297mm; /* A4 height */
                padding: 10mm; /* Margins for A4 */
                box-shadow: none;
                margin: 0 auto;
            }
            .table-responsive {
                box-shadow: none;
                border-radius: 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }
            .scout-image {
                width: 40px;
                height: 40px;
            }
            h1 {
                text-align: center;
                font-size: 1.5em;
                margin-bottom: 10px;
            }
            .season-box-container {
                display: none !important; /* Hide season boxes in print */
            }
        }
    </style>
</head>
<body>
    <?php if ($is_admin_logged_in): ?>
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
                    <a class="nav-link" href="admin_dashboard.php"><i class="fa fa-tachometer-alt"></i>ড্যাশবোর্ড</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php" target="_blank"><i class="fa fa-home"></i>হোম পেজ <span style="margin-left: auto; padding-left: 10px;"><i class="fa fa-external-link-alt"></i></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="view_scouts.php"><i class="fa fa-users"></i>স্কাউট তথ্য দেখুন</a>
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
    <?php endif; ?>

    <!-- Main Content -->
    <div class="main-content" <?php echo $is_admin_logged_in ? '' : 'style="margin-left: 0;"'; ?>>
        <div class="container-fluid">
            <?php if ($is_admin_logged_in) display_flash_message(); ?>

            <!-- Session Boxes -->
            <div class="row mb-4">
                <?php if (!empty($seasons_with_stats)): ?>
                    <?php foreach ($seasons_with_stats as $season): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card season-box season-bg-<?php echo ($season['id'] % 8) + 1; ?>" onclick="window.location.href='view_scouts.php?season_id=<?php echo $season['id']; ?>'">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($season['season_name']); ?></h5>
                                    <p class="card-text">মোট: <?php echo $season['total_scouts']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center" role="alert">
                            কোনো সেশন পাওয়া যায়নি।
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <h1 class="my-4">নিবন্ধিত স্কাউট তালিকা - <?php echo htmlspecialchars($selected_season_name); ?></h1>
            <button class="btn btn-primary mb-4" onclick="window.print()"><i class="fa fa-print"></i> প্রিন্ট করুন</button>

            <!-- Unit Filter Section (Optional, keep if still needed) -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="view_scouts.php" method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="unit-filter" class="form-label">দলের নাম দিয়ে ফিল্টার করুন:</label>
                        </div>
                        <div class="col-auto">
                            <select id="unit-filter" name="unit" class="form-select" onchange="this.form.submit()">
                                <option value="">সকল দল</option>
                                <?php foreach ($unit_names as $unit_name): ?>
                                    <option value="<?php echo htmlspecialchars($unit_name); ?>" <?php echo ($selected_unit == $unit_name) ? 'selected' : ''; ?>><?php echo htmlspecialchars($unit_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Scouts Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <?php if ($is_admin_logged_in): ?>
                                <th>ছবি</th>
                                <th>BSID</th>
                            <?php endif; ?>
                            <th>নাম</th>
                            <th>দল</th>
                            <th>মোবাইল</th>
                            <?php if ($is_admin_logged_in): ?>
                                <th class="print-hide">জেলা</th>
                                <th class="print-hide">পিতার নাম</th>
                                <th class="print-hide">মাতার নাম</th>
                                <th class="print-hide">নিবন্ধন</th>
                                <th class="print-hide">অ্যাকশন</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($scouts)): ?>
                            <?php $serial_no = 1; ?>
                            <?php foreach ($scouts as $scout): ?>
                                <tr>
                                    <td><?php echo $serial_no++; ?></td>
                                    <?php if ($is_admin_logged_in): ?>
                                        <td>
                                            <?php if (!empty($scout['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($scout['image_url']); ?>" alt="Scout Image" class="scout-image">
                                            <?php else: ?>
                                                <span class="text-muted">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($scout['scout_id']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($scout['name']); ?></td>
                                    <td><?php echo htmlspecialchars($scout['unit']); ?></td>
                                    <td><?php echo htmlspecialchars($scout['mobile_number']); ?></td>
                                    <?php if ($is_admin_logged_in): ?>
                                        <td class="print-hide"><?php echo htmlspecialchars($scout['district']); ?></td>
                                        <td class="print-hide"><?php echo htmlspecialchars($scout['father_name']); ?></td>
                                        <td class="print-hide"><?php echo htmlspecialchars($scout['mother_name']); ?></td>
                                        <td class="print-hide"><?php echo date("d-m-Y", strtotime($scout['created_at'])); ?></td>
                                        <td class="print-hide">
                                            <button type="button" class="btn btn-sm btn-info action-btn" data-bs-toggle="modal" data-bs-target="#actionModal" data-scoutid="<?php echo htmlspecialchars($scout['scout_id']); ?>">
                                                অ্যাকশন
                                            </button>
                                        </td>
                                    <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">কোনো স্কাউট ডেটা পাওয়া যায়নি।</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($is_admin_logged_in): ?>
    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalLabel">অ্যাকশন নির্বাচন করুন</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <a href="#" id="editScoutLink" class="btn btn-primary me-2"><i class="fa fa-edit"></i> এডিট করুন</a>
                    <a href="#" id="deleteScoutLink" class="btn btn-danger" onclick="return confirm('আপনি কি নিশ্চিত যে আপনি এই স্কাউটকে মুছে ফেলতে চান?');"><i class="fa fa-trash"></i> মুছে ফেলুন</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($is_admin_logged_in): ?>
    <script>
        var actionModal = document.getElementById('actionModal');
        actionModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var scoutId = button.getAttribute('data-scoutid');
            
            var editLink = actionModal.querySelector('#editScoutLink');
            var deleteLink = actionModal.querySelector('#deleteScoutLink');

            editLink.href = 'edit_scout.php?id=' + scoutId;
            deleteLink.href = 'delete_scout.php?id=' + scoutId;
        });
    </script>
    <?php endif; ?>
</body>
</html>