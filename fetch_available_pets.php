<?php
// Database connection configuration
$host = 'localhost';
$db = 'pawdopter_care';
$user = 'root';
$pass = ''; // Replace with your database password

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch available pets
    $sql = "SELECT id, name, breed, age, description FROM pets WHERE available = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Check if there are any available pets
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($pets) > 0) {
        foreach ($pets as $pet) {
            echo '<div class="pet-item">';
          
            echo '<h3>' . htmlspecialchars($pet['name']) . '</h3>';
            echo '<p><strong>Breed:</strong> ' . htmlspecialchars($pet['breed']) . '</p>';
            echo '<p><strong>Age:</strong> ' . htmlspecialchars($pet['age']) . ' years</p>';
            echo '<p>' . htmlspecialchars($pet['description']) . '</p>';
            echo '<a href="adoption-form.html?pet_id=' . $pet['id'] . '" class="btn">Adopt This Pet</a>';
            echo '</div>';
        }
    } else {
        echo '<p>No available pets at the moment. Please check back later.</p>';
    }
} catch (PDOException $e) {
    // Handle database errors
    echo "Error: " . $e->getMessage();
    error_reporting(E_ALL);
ini_set('display_errors', 1);

}
?>
