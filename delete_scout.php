<?php
session_start();
require_once 'config.php';

// Function to set flash messages
function set_flash_message($message, $type) {
    $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $scout_id = $_GET['id'];

    $sql = "DELETE FROM scouts WHERE scout_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $scout_id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash_message("✅ স্কাউট সফলভাবে মুছে ফেলা হয়েছে!", 'success');
        } else {
            set_flash_message("❌ ত্রুটি: স্কাউট মুছে ফেলা যায়নি।" . mysqli_error($link), 'danger');
        }
        mysqli_stmt_close($stmt);
    } else {
        set_flash_message("❌ প্রস্তুতি ব্যর্থ: " . mysqli_error($link), 'danger');
    }
    mysqli_close($link);
}

header('Location: view_scouts.php');
exit();
?>