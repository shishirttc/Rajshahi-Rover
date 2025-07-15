<?php
require_once 'config.php';

$active_open_seasons = [];
$error_message = '';

// Fetch active and open seasons
$sql = "SELECT id, season_name FROM seasons WHERE registrations_open = 1 ORDER BY created_at DESC";
$result = mysqli_query($link, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $active_open_seasons[] = $row;
    }
} else {
    $error_message = "ডাটাবেস ত্রুটি: সেশন লোড করা যায়নি।";
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>স্কাউট রেজিস্ট্রেশন ফর্ম</title>
    <link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAABtJREFUOE9jZGBgYGDgYmBgYGBgYGBgYAAAB9AAJgYGBgYAAAAASUVORK5CYII=" type="image/png">
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
            box-sizing: border-box; /* Include padding in element's total width and height */
            text-align: center; /* Center content inside container */
        }
        h1 {
            color: #00796b; /* Teal color for heading */
            text-align: center;
            margin-bottom: 20px; /* Adjusted margin */
            font-size: 2em;
            font-weight: bold;
        }
        .logo-container {
            margin-bottom: 20px;
        }
        .logo-container img {
            max-width: 100px; /* Adjusted for smaller logo */
            height: auto;
            display: block;
            margin: 0 auto 10px; /* Center image and add space below */
        }
        .logo-text {
            font-size: 1.4em; /* Slightly increased font size */
            font-weight: 900 !important; /* Made it extra bold */
            text-shadow: 0.5px 0.5px 0.5px rgba(0,0,0,0.3); /* Added text shadow for more emphasis */
            margin-bottom: 20px;
        }
        .red-text {
            color: #FF0000; /* Red color */
        }
        .green-text {
            color: #008000; /* Green color */
        }
        .season-box-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
            border: 1px solid #00796b; /* Single border for the container */
            padding: 20px; /* Padding inside the border */
            border-radius: 10px; /* Rounded corners for the border */
        }
        .season-box {
            border-radius: 6px;
            padding: 2px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            flex: 1 1 calc(100% - 10px); /* Increased width to be closer to border */
            max-width: calc(100% - 10px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            color: white; /* Ensure text is white on colored backgrounds */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .season-box:hover {
            transform: translateY(-3px);
        }
        .season-box h5 {
            color: inherit; /* Inherit color from parent */
            font-weight: 900; /* Made it extra bold */
            font-size: 1.2em; /* Slightly increased font size */
            text-align: center;
        }
        /* Colors for season boxes */
        .season-bg-1 { background-color: #007bff; }
        .season-bg-2 { background-color: #28a745; }
        .season-bg-3 { background-color: #dc3545; }
        .season-bg-4 { background-color: #ffc107; color: white; }
        .season-bg-5 { background-color: #6f42c1; }
        .season-bg-6 { background-color: #20c997; }
        .season-bg-7 { background-color: #fd7e14; }
        .season-bg-8 { background-color: #6610f2; }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .container {
                width: 95%;
                padding: 20px;
            }
            h1 {
                font-size: 1.8em;
            }
            .season-box {
                flex: 1 1 100%; /* Single column on small screens */
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logo">
            <p class="logo-text"><span class="green-text">বাংলাদেশ স্কাউটস</span><br><span class="red-text">রাজশাহী জেলা রোভার</span></p>
        </div>
        <h1>স্কাউট রেজিস্ট্রেশন সেশন</h1>
        
        <?php if ($error_message): ?>
            <div class="message error">
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php elseif (empty($active_open_seasons)): ?>
            <div class="message error">
                <p>বর্তমানে কোনো রেজিস্ট্রেশন সেশন চালু নেই।</p>
            </div>
        <?php else: ?>
        <div class="season-box-container">
            <?php foreach ($active_open_seasons as $season): ?>
                <div class="season-box season-bg-<?php echo ($season['id'] % 8) + 1; ?>" onclick="window.location.href='submit_form.php?season_id=<?php echo htmlspecialchars($season['id']); ?>'">
                    <h5><?php echo htmlspecialchars($season['season_name']); ?></h5>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div style="text-align: center; margin-top: 20px;">
            <a href="view_scouts.php" style="display: inline-block; padding: 10px 20px; background-color: #00796b; color: white; text-decoration: none; border-radius: 6px; transition: background-color 0.3s ease, transform 0.2s ease;">নিবন্ধিত স্কাউট তালিকা দেখুন</a>
        </div>
    </div>
</body>
</html>
