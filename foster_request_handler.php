<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $pet_name = $_POST['pet_name'] ?? '';
    $species = $_POST['species'] ?? '';
    $days = intval($_POST['days'] ?? 0);

    if (empty($pet_name) || empty($species) || $days <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    $host = 'localhost';
    $db = 'pawdopter_care';
    $user = 'root';
    $pass = ''; // Replace with your database password

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "INSERT INTO foster_care_requests (user_id, pet_name, species, days) VALUES (:user_id, :pet_name, :species, :days)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':pet_name' => $pet_name,
            ':species' => $species,
            ':days' => $days
        ]);

        echo json_encode(['success' => true, 'message' => 'Foster care request submitted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
