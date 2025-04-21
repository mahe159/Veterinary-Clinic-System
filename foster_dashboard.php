<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "John Doe"; // Replace with dynamic username from your application
}

$username = $_SESSION['username'];

// Fetch user ID from database based on username
try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $user ? $user['id'] : null;
} catch (PDOException $e) {
    die("Error fetching user ID: " . $e->getMessage());
}

// Handle form submission
$success_message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pet_name = $_POST['pet_name'] ?? '';
    $species = $_POST['species'] ?? '';
    $days = $_POST['days'] ?? 0;

    if ($user_id && $pet_name && $species && $days > 0) {
        try {
            $insert_stmt = $conn->prepare("INSERT INTO foster_care_requests (user_id, pet_name, species, days, status) VALUES (:user_id, :pet_name, :species, :days, 'pending')");
            $insert_stmt->execute([
                ':user_id' => $user_id,
                ':pet_name' => $pet_name,
                ':species' => $species,
                ':days' => $days
            ]);
            $success_message = "Foster care request submitted successfully!";
        } catch (PDOException $e) {
            die("Error submitting request: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foster Care Dashboard</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="logout.css">
</head>
<body>
    <div class="header-area">
        <div class="bottom-header">
            <h2>Foster Dashboard</h2>
            <ul class="navigation">
                <li><a href="index.html">Home</a></li>
                <li><a href="#">Gallery</a></li>
                <li><a href="petcare_dashboard.php">Veterinary</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
    </div>

    <div style="text-align: center; padding: 20px; background: #f7f7f7; margin-bottom: 20px; border-bottom: 1px solid #ddd;">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>We're glad to see you. Manage your foster care requests below.</p>
    </div>

    <div style="text-align: right; margin: 10px;">
        <a href="logout.php" class="logout-button">Logout</a>
    </div>

    <div class="adoption-form">
        <h1>Request Foster Care</h1>
        <?php if ($success_message): ?>
            <p style="color: green; font-weight: bold;"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="pet_name">Pet's Name:</label>
            <input type="text" id="pet_name" name="pet_name" required>

            <label for="species">Species:</label>
            <select id="species" name="species" required>
                <option value="cat">Cat</option>
                <option value="dog">Dog</option>
            </select>

            <label for="days">Number of Days:</label>
            <input type="number" id="days" name="days" min="1" required>

            <button type="submit">Submit</button>
        </form>
    </div>

    <div class="Service">
        <h1>Foster Care Pricing</h1>
        <p>The daily charge for fostering a cat is <strong>300</strong> and for a dog is <strong>500</strong>.</p>
    </div>
</body>
</html>
