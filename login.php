<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user is an admin
    $sql_admin = "SELECT * FROM admin WHERE username = :username";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->execute([':username' => $username]);
    $admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);

    if ($admin && $password === $admin['password_hash']) { // Direct comparison
        // Set session variables for admin
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        // Redirect to admin panel
        header("Location: admin.php");
        exit;
    }

    // Check if the user is a regular user
    $sql_user = "SELECT * FROM users WHERE username = :username";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->execute([':username' => $username]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session variables for user
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type']; // Assuming 'user_type' is a column in your table

        // Redirect based on user type
        if ($user['user_type'] === 'volunteer') {
            header("Location: volunteer_dashboard.php");
        } elseif ($user['user_type'] === 'foster care') {
            header("Location: foster_dashboard.php");
        } elseif ($user['user_type'] === 'pet care') {
            header("Location: petcare_dashboard.php");
        } else {
            // Default or unknown user type
            header("Location: index.html");
        }
        exit;
    } else {
        // Invalid credentials
        echo "Invalid username or password.";
    }
}
?>
