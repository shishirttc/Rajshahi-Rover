<?php
session_start();
require_once 'config.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'ইউজারনেম এবং পাসওয়ার্ড উভয়ই আবশ্যক!';
    header('Location: admin.php');
    exit();
}

$sql = "SELECT password FROM admin_users WHERE username = ?";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $hashed_password);
        mysqli_stmt_fetch($stmt);

        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin_dashboard.php');
            exit();
        } else {
            $_SESSION['login_error'] = 'ভুল ইউজারনেম অথবা পাসওয়ার্ড!';
            header('Location: admin.php');
            exit();
        }
    } else {
        $_SESSION['login_error'] = 'ভুল ইউজারনেম অথবা পাসওয়ার্ড!';
        header('Location: admin.php');
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['login_error'] = 'ডাটাবেস ত্রুটি: ' . mysqli_error($link);
    header('Location: admin.php');
    exit();
}

mysqli_close($link);
?>