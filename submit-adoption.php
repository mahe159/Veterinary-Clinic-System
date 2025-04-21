<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection configuration
$host = 'localhost';
$db = 'pawdopter_care';
$user = 'root';
$pass = ''; // Replace with your database password

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['pet_id'])) {
            throw new Exception("Pet ID is required");
        }

        $pet_id = $_POST['pet_id'];
        $user_id = $_SESSION['user_id'];

        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Check if pet is still available
            $sql = "SELECT available FROM pets WHERE id = :pet_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':pet_id' => $pet_id]);
            $pet = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pet || !$pet['available']) {
                throw new Exception("Sorry, this pet is no longer available for adoption.");
            }

            // Insert the adoption request
            $sql = "INSERT INTO adoption_requests (pet_id, user_id, status) 
                    VALUES (:pet_id, :user_id, 'pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':pet_id' => $pet_id,
                ':user_id' => $user_id
            ]);

            // Commit transaction
            $pdo->commit();

            // Redirect to volunteer dashboard with success message
            $_SESSION['success_message'] = "Your adoption request has been submitted successfully!";
            header("Location: volunteer_dashboard_temp.php");
            exit;

        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            throw $e;
        }
    }
} catch (Exception $e) {
    // Handle errors
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header("Location: volunteer_dashboard_temp.php");
    exit;
}
?>
