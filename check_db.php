<?php
require_once 'config.php';

echo "<h2>Database Check Results:</h2>";

if ($link === false) {
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}

// Check for 'seasons' table
$sql_check_seasons_table = "SHOW TABLES LIKE 'seasons'";
$result_seasons_table = mysqli_query($link, $sql_check_seasons_table);

if ($result_seasons_table && mysqli_num_rows($result_seasons_table) == 0) {
    echo "<p style=\"color: orange;\">'seasons' table not found. Creating it now...</p>";
    $sql_create_seasons_table = "CREATE TABLE seasons (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        season_name VARCHAR(255) NOT NULL,\n        registrations_open BOOLEAN NOT NULL DEFAULT 0,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n    )";
    if (mysqli_query($link, $sql_create_seasons_table)) {
        echo "<p style=\"color: green;\">'seasons' table created successfully.</p>";
    } else {
        echo "<p style=\"color: red;\">Error creating 'seasons' table: " . mysqli_error($link) . "</p>";
    }
} else {
    echo "<p style=\"color: green;\">'seasons' table already exists.</p>";
}

// Check for 'is_active' column in 'seasons' table and drop it if exists
$sql_check_is_active_column = "SHOW COLUMNS FROM seasons LIKE 'is_active'";
$result_is_active_column = mysqli_query($link, $sql_check_is_active_column);

if ($result_is_active_column && mysqli_num_rows($result_is_active_column) > 0) {
    echo "<p style=\"color: orange;\">'is_active' column found in 'seasons' table. Dropping it now...</p>";
    $sql_drop_is_active_column = "ALTER TABLE seasons DROP COLUMN is_active";
    if (mysqli_query($link, $sql_drop_is_active_column)) {
        echo "<p style=\"color: green;\">'is_active' column dropped successfully.</p>";
    } else {
        echo "<p style=\"color: red;\">Error dropping 'is_active' column: " . mysqli_error($link) . "</p>";
    }
} else {
    echo "<p style=\"color: green;\">'is_active' column not found in 'seasons' table (or already dropped).</p>";
}

// Check for 'season_id' column in 'scouts' table and add it if not exists
$sql_check_season_id_column = "SHOW COLUMNS FROM scouts LIKE 'season_id'";
$result_season_id_column = mysqli_query($link, $sql_check_season_id_column);

if ($result_season_id_column && mysqli_num_rows($result_season_id_column) == 0) {
    echo "<p style=\"color: orange;\">'season_id' column not found in 'scouts' table. Adding it now...</p>";
    $sql_add_season_id_column = "ALTER TABLE scouts ADD season_id INT";
    if (mysqli_query($link, $sql_add_season_id_column)) {
        echo "<p style=\"color: green;\">'season_id' column added successfully to 'scouts' table.</p>";
    } else {
        echo "<p style=\"color: red;\">Error adding 'season_id' column: " . mysqli_error($link) . "</p>";
    }
} else {
    echo "<p style=\"color: green;\">'season_id' column already exists in 'scouts' table.</p>";
}

// Check for 'registration_enabled' setting in 'settings' table and remove it if exists
$sql_check_settings_table = "SHOW TABLES LIKE 'settings'";
$result_settings_table = mysqli_query($link, $sql_check_settings_table);

if ($result_settings_table && mysqli_num_rows($result_settings_table) > 0) {
    $sql_check_registration_enabled_setting = "SELECT setting_name FROM settings WHERE setting_name = 'registration_enabled'";
    $result_registration_enabled_setting = mysqli_query($link, $sql_check_registration_enabled_setting);

    if ($result_registration_enabled_setting && mysqli_num_rows($result_registration_enabled_setting) > 0) {
        echo "<p style=\"color: orange;\">'registration_enabled' setting found in 'settings' table. Removing it now...</p>";
        $sql_delete_registration_enabled_setting = "DELETE FROM settings WHERE setting_name = 'registration_enabled'";
        if (mysqli_query($link, $sql_delete_registration_enabled_setting)) {
            echo "<p style=\"color: green;\">'registration_enabled' setting removed successfully.</p>";
        } else {
            echo "<p style=\"color: red;\">Error removing 'registration_enabled' setting: " . mysqli_error($link) . "</p>";
        }
    } else {
        echo "<p style=\"color: green;\">'registration_enabled' setting not found in 'settings' table (or already removed).</p>";
    }
} else {
    echo "<p style=\"color: green;\">'settings' table not found (or no 'registration_enabled' setting).</p>";
}

// Check for 'admin_users' table
$sql_check_admin_users_table = "SHOW TABLES LIKE 'admin_users'";
$result_admin_users_table = mysqli_query($link, $sql_check_admin_users_table);

if ($result_admin_users_table && mysqli_num_rows($result_admin_users_table) == 0) {
    echo "<p style=\"color: orange;\">'admin_users' table not found. Creating it now...</p>";
    $sql_create_admin_users_table = "CREATE TABLE admin_users (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        username VARCHAR(50) NOT NULL UNIQUE,\n        password VARCHAR(255) NOT NULL,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n    )";
    if (mysqli_query($link, $sql_create_admin_users_table)) {
        echo "<p style=\"color: green;\">'admin_users' table created successfully.</p>";

        // Add default admin user if table is empty
        $sql_check_admin_users_count = "SELECT COUNT(*) AS count FROM admin_users";
        $result_admin_users_count = mysqli_query($link, $sql_check_admin_users_count);
        $row_admin_users_count = mysqli_fetch_assoc($result_admin_users_count);

        if ($row_admin_users_count['count'] == 0) {
            $default_username = 'admin';
            $default_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $sql_insert_default_admin = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
            if ($stmt = mysqli_prepare($link, $sql_insert_default_admin)) {
                mysqli_stmt_bind_param($stmt, "ss", $default_username, $default_password_hash);
                if (mysqli_stmt_execute($stmt)) {
                    echo "<p style=\"color: green;\">Default admin user ('admin') added successfully.</p>";
                } else {
                    echo "<p style=\"color: red;\">Error adding default admin user: " . mysqli_error($link) . "</p>";
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "<p style=\"color: red;\">Error preparing default admin user insert statement: " . mysqli_error($link) . "</p>";
            }
        }
    } else {
        echo "<p style=\"color: red;\">Error creating 'admin_users' table: " . mysqli_error($link) . "</p>";
    }
} else {
    echo "<p style=\"color: green;\">'admin_users' table already exists.</p>";
}

// Check for 'units' table
$sql_check_units_table = "SHOW TABLES LIKE 'units'";
$result_units_table = mysqli_query($link, $sql_check_units_table);

if ($result_units_table && mysqli_num_rows($result_units_table) == 0) {
    echo "<p style=\"color: orange;\">'units' table not found. Creating it now...</p>";
    $sql_create_units_table = "CREATE TABLE units (\n        unit_name VARCHAR(255) NOT NULL PRIMARY KEY,\n        quota INT NOT NULL DEFAULT 0,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n    )";
    if (mysqli_query($link, $sql_create_units_table)) {
        echo "<p style=\"color: green;\">'units' table created successfully.</p>";
    } else {
        echo "<p style=\"color: red;\">Error creating 'units' table: " . mysqli_error($link) . "</p>";
    }
} else {
    echo "<p style=\"color: green;\">'units' table already exists.</p>";
}

mysqli_close($link);
?>