<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

$scout_id = '';
$scout_data = [];
$error_message = '';
$active_season_id = $_GET['season_id'] ?? null;
$season_name = '';

// Fetch season name for display
if ($active_season_id) {
    $season_sql = "SELECT season_name FROM seasons WHERE id = ? AND registrations_open = 1";
    if ($stmt = mysqli_prepare($link, $season_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $active_season_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $season_row = mysqli_fetch_assoc($result);
            $season_name = $season_row['season_name'];
        } else {
            $error_message = "নির্বাচিত সেশনটি রেজিস্ট্রেশনের জন্য চালু নেই বা পাওয়া যায়নি।";
            $active_season_id = null; // Invalidate season if not active/found
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "ডাটাবেস ত্রুটি: সেশন লোড করা যায়নি।";
    }
} else {
    $error_message = "কোনো সেশন নির্বাচন করা হয়নি।";
}

// Function definition should be outside the conditional block
function parse_scout_data($html) {
    $data = [];
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $finder = new DomXPath($dom);

    $rows = $finder->query("//table[contains(@class, 'tg')]//tr");

    foreach ($rows as $row) {
        $ths = $row->getElementsByTagName("th");
        $tds = $row->getElementsByTagName("td");

        if ($ths->length > 0 && $tds->length > 0) {
            $label = trim($ths->item(0)->textContent);
            $value = trim($tds->item(0)->textContent);

            if ($label == 'নাম') {
                $data['name'] = $value;
            }
            else if (strpos($label, 'দলের নাম') !== false) {
                $data['unit'] = $value;
            }
            else if (strpos(trim($label), 'জেলা নাম') !== false) {
                $data['district'] = $value;
            }
            else if (strpos($label, 'পিতার নাম') !== false) {
                $data['father'] = $value;
            }
            else if (strpos($label, 'মায়ের নাম') !== false) {
                $data['mother'] = $value;
            }
            else if (strpos($label, 'মোবাইল') !== false) {
                $data['mobile'] = $value;
            }
        }

        // স্কাউট আইডি আলাদা করে ধরার জন্য
        foreach ($tds as $td) {
            $text = trim($td->textContent);
            if (strpos($text, 'স্কাউট আইডি') !== false) {
                preg_match('/স্কাউট আইডি\s*:\s*([A-Z0-9]+)/iu', $text, $matches);
                if (!empty($matches[1])) {
                    $data['scout_id'] = strtoupper($matches[1]);
                }
            }
        }
    }

    // Extract image URL
    $image_node = $finder->query("//table[contains(@class, 'tg')]//img")->item(0);
    if ($image_node) {
        $data['image_url'] = $image_node->getAttribute('src');
    }

    return $data;
}

if ($active_season_id !== null) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $scout_id = strtoupper(trim($_POST["scout_id"] ?? '')); // Use ?? '' for safety

        // Server-side validation for scout_id format
        if (!preg_match("/^[A-Z]{2}[0-9]{4}$/", $scout_id) || strlen($scout_id) != 6) {
            $error_message = "BSID ফরম্যাট ভুল। আইডি অবশ্যই 2টি অক্ষর এবং 4টি সংখ্যা হতে হবে (যেমন: AB1234)।";
            $scout_data = []; // Clear any parsed data if format is invalid
        } else if (empty($scout_id)) {
            $error_message = "অনুগ্রহ করে একটি BSID দিন।";
        } else {
            // Check if scout_id already exists for this season
            $check_sql = "SELECT id FROM scouts WHERE scout_id = ? AND season_id = ?";
            if ($check_stmt = mysqli_prepare($link, $check_sql)) {
                mysqli_stmt_bind_param($check_stmt, "si", $scout_id, $active_season_id);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) > 0) {
                    $error_message = "এই BSID টি এই সেশনে ইতিমধ্যেই নিবন্ধিত আছে!";
                }
                mysqli_stmt_close($check_stmt);
            }

            if (empty($error_message)) { // Only proceed if no duplicate found
                $url = "http://service.scouts.gov.bd/user-verify?scoutID=" . urlencode($scout_id);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $response = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($response !== false && $httpcode == 200) {
                    $scout_data = parse_scout_data($response);
                    if (empty($scout_data['name']) && empty($scout_data['unit']) && empty($scout_data['district'])) { // Check if essential data is parsed
                        $error_message = "স্কাউট ডেটা পার্স করা যায়নি। ওয়েবসাইট কাঠামো পরিবর্তিত হতে পারে বা আইডিটি অবৈধ।";
                    }
                } else {
                    $error_message = "স্কাউট পরিষেবা থেকে ডেটা আনতে সমস্যা হয়েছে। BSID পরীক্ষা করুন এবং আবার চেষ্টা করুন।";
                }
            }
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
    <title>স্কাউট তথ্য যাচাই</title>
	
    <style>
        .form-group {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px; /* Adjusted space for mobile */
            padding-left: 0; /* Remove indent for mobile */
        }
        .form-group label {
            font-weight: bold;
            color: #00796b; /* Matching button color */
            font-size: 1em; /* Adjusted font size for mobile */
            padding-right: 0; /* Remove padding for mobile */
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 1.1em;
            box-sizing: border-box; /* Ensure padding and border are included in the element's total width and height */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            margin-right: 0; /* Remove indent for mobile */
        }
        input[type="text"]:focus {
            border-color: #00796b;
            box-shadow: 0 0 0 0.25rem rgba(0, 121, 107, 0.25);
            outline: none;
        }
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
            max-width: 400px; /* Adjusted for better mobile view */
            width: 90%;
            margin: 20px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }
        h1 {
            color: #00796b; /* Teal color for heading */
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
            font-weight: bold;
        }
        h2 {
            color: #00796b;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        .data-preview, .error {
            border: 1px solid #b2dfdb;
            background-color: #e0f2f7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            border-color: #ef9a9a;
            background-color: #ffebee;
            color: #d32f2f;
            text-align: center;
            font-weight: bold;
        }
        .data-preview p {
            margin: 0 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #c8e6c9;
        }
        .data-preview p:last-child {
            border-bottom: none;
        }
        .data-preview strong {
            display: inline-block;
            width: 150px;
            color: #004d40;
        }
        button {
            background-color: #00796b; /* Teal button */
            color: white;
            border: none;
            padding: 12px 25px;
            cursor: pointer;
            border-radius: 6px;
            font-size: 1.1em;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 10px;
        }
        button:hover {
            background-color: #004d40; /* Darker teal on hover */
            transform: translateY(-2px);
        }
        button:active {
            transform: translateY(0);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #00796b;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #004d40;
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
            h2 {
                font-size: 1.3em;
            }
            .data-preview strong {
                width: 100px;
            }
            input, button {
                font-size: 0.9em;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>স্কাউট তথ্য যাচাই - <?php echo htmlspecialchars($season_name); ?></h1>

        <?php if ($error_message): ?>
            <div class="error">
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($active_season_id !== null && empty($scout_data) && empty($_POST['scout_id'])): ?>
            <form action="submit_form.php?season_id=<?php echo htmlspecialchars($active_season_id); ?>" method="POST">
                <div class="form-group">
                    <label for="scout_id">BSID</label>
                    <input type="text" id="scout_id" name="scout_id" placeholder="আপনার BSID লিখুন" pattern="[A-Za-z]{2}[0-9]{4}" title="BSID অবশ্যই 2টি অক্ষর এবং 4টি সংখ্যা হতে হবে (যেমন: AB1234)" maxlength="6" required>
                </div>
                <button type="submit">তথ্য যাচাই করুন</button>
            </form>
        <?php elseif (!empty($scout_data)): ?>
            <div class="data-preview">
                <h2>প্রাপ্ত তথ্য</h2>
                <div style="text-align: center; margin-bottom: 15px;">
                    <?php if (!empty($scout_data['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($scout_data['image_url']); ?>" alt="Scout Image" style="max-width: 150px; border-radius: 5px;">
                    <?php endif; ?>
                </div>
                <p><strong>BSID:</strong> <?php echo htmlspecialchars(strtoupper($scout_id)); ?></p>
                <p><strong>নাম:</strong> <?php echo htmlspecialchars($scout_data['name'] ?? 'N/A'); ?></p>
                <p><strong>পিতার নাম:</strong> <?php echo htmlspecialchars($scout_data['father'] ?? 'N/A'); ?></p>
                <p><strong>মায়ের নাম:</strong> <?php echo htmlspecialchars($scout_data['mother'] ?? 'N/A'); ?></p>
                <p><strong>দলের নাম:</strong> <?php echo htmlspecialchars($scout_data['unit'] ?? 'N/A'); ?></p>
                <p><strong>জেলার নাম:</strong> <?php echo htmlspecialchars($scout_data['district'] ?? 'N/A'); ?></p>
                <p><strong>মোবাইল:</strong> <?php echo htmlspecialchars($scout_data['mobile'] ?? 'N/A'); ?></p>
            </div>

            <form action="save_scout.php" method="POST">
                <input type="hidden" name="scout_id" value="<?php echo htmlspecialchars($scout_id); ?>">
                <input type="hidden" name="season_id" value="<?php echo htmlspecialchars($active_season_id); ?>">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($scout_data['name'] ?? ''); ?>">
                <input type="hidden" name="unit" value="<?php echo htmlspecialchars($scout_data['unit'] ?? ''); ?>">
                <input type="hidden" name="district" value="<?php echo htmlspecialchars($scout_data['district'] ?? ''); ?>">
                <input type="hidden" name="father" value="<?php echo htmlspecialchars($scout_data['father'] ?? ''); ?>">
                <input type="hidden" name="mother" value="<?php echo htmlspecialchars($scout_data['mother'] ?? ''); ?>">
                <input type="hidden" name="mobile" value="<?php echo htmlspecialchars($scout_data['mobile'] ?? ''); ?>">
                <input type="hidden" name="image_url" value="<?php echo htmlspecialchars($scout_data['image_url'] ?? ''); ?>">
                
                <button type="submit">তথ্য জমা দিন</button>
            </form>
        <?php endif; ?>

        <a href="index.php" class="back-link">ফিরে যান</a>
    </div>
</body>
</html>
