<?php
require_once 'config.php';

$message = '';
$is_error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $scout_id = $_POST['scout_id'] ?? '';
    $season_id = $_POST['season_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $district = $_POST['district'] ?? '';
    $father = $_POST['father'] ?? '';
    $mother = $_POST['mother'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    
    if (empty($scout_id) || empty($name) || empty($season_id)) {
        $message = "❌ স্কাউট আইডি, নাম ও সেশন আবশ্যক!";
        $is_error = true;
    } else {
        // Check if scout_id already exists
        $check_sql = "SELECT id FROM scouts WHERE scout_id = ? AND season_id = ?";
        if ($check_stmt = mysqli_prepare($link, $check_sql)) {
            mysqli_stmt_bind_param($check_stmt, "si", $scout_id, $season_id);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $message = "❌ এই স্কাউট আইডিটি এই সেশনে ইতিমধ্যেই নিবন্ধিত আছে!";
                $is_error = true;
            }
            mysqli_stmt_close($check_stmt);
        } else {
            $message = "❌ প্রস্তুতি ব্যর্থ: " . htmlspecialchars(mysqli_error($link));
            $is_error = true;
        }

        // Only proceed with insertion if no errors so far
        if (!$is_error) {
            $sql = "INSERT INTO scouts (scout_id, season_id, name, unit, district, father_name, mother_name, mobile_number, image_url, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sisssssss", $scout_id, $season_id, $name, $unit, $district, $father, $mother, $mobile, $image_url);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "✅ তথ্য সফলভাবে সংরক্ষিত হয়েছে!";
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
    <title>তথ্য সংরক্ষণের ফলাফল</title>
    <style>
        body {
            font-family: 'SolaimanLipi', Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #e0f2f7; /* Light blue background */
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 500px;
            width: 90%;
            margin: 20px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            box-sizing: border-box;
            text-align: center;
        }
        h1 {
            color: #00796b; /* Teal color for heading */
            margin-bottom: 30px;
            font-size: 2em;
            font-weight: bold;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1.1em;
            font-weight: bold;
        }
        .success {
            background-color: #e8f5e9; /* Light green */
            border: 1px solid #4caf50; /* Green */
            color: #2e7d32; /* Dark green */
        }
        .error {
            background-color: #ffebee; /* Light red */
            border: 1px solid #f44336; /* Red */
            color: #d32f2f; /* Dark red */
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .back-link:hover {
            background-color: #004d40;
            transform: translateY(-2px);
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .container {
                width: 95%;
                padding: 20px;
            }
            h1 {
                font-size: 1.8em;
            }
            .message {
                font-size: 1em;
            }
            .back-link {
                padding: 8px 15px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>তথ্য সংরক্ষণের ফলাফল</h1>
        <div class="message <?php echo $is_error ? 'error' : 'success'; ?>">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
        <a href="index.php" class="back-link">🔙 ফর্মে ফিরে যান</a>
    </div>
</body>
</html>
