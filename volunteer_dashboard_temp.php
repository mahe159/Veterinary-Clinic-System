<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
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

    $user_id = $_SESSION['user_id'];

    // Query to fetch available pets
    $sql = "SELECT id, name, breed, age, description FROM pets WHERE available = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query to fetch user's adoption requests (both pending and approved)
    $sql = "SELECT 
                ar.id,
                p.name AS pet_name,
                p.breed,
                p.age,
                p.description,
                ar.status,
                ar.created_at,
                CASE 
                    WHEN ar.status = 'approved' AND p.available = 0 THEN 1 
                    ELSE 0 
                END as is_adopted,
                CASE 
                    WHEN ar.status = 'approved' AND p.available = 0 THEN 'Adopted'
                    WHEN ar.status = 'pending' THEN 'Pending'
                    WHEN ar.status = 'declined' THEN 'Declined'
                    ELSE ar.status
                END as status_display,
                p.available
            FROM adoption_requests ar
            JOIN pets p ON ar.pet_id = p.id
            WHERE ar.user_id = :user_id
            ORDER BY 
                CASE 
                    WHEN ar.status = 'approved' AND p.available = 0 THEN 0
                    WHEN ar.status = 'pending' THEN 1
                    ELSE 2
                END,
                ar.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $adoption_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug information
    echo "<!-- Debug Info:";
    echo "\nUser ID: " . $user_id;
    echo "\nAdoption Requests: ";
    print_r($adoption_requests);
    echo "\n-->";

    // Query to fetch accepted foster care requests with payment information
    $sql = "SELECT fcr.id, fcr.pet_name, fcr.species, fcr.days, fcr.status as foster_status, fcr.created_at,
            vp.amount as payment_amount, vp.payment_date, vp.status as payment_status
            FROM foster_care_requests fcr
            LEFT JOIN volunteer_payments vp ON fcr.id = vp.foster_request_id
            WHERE fcr.volunteer_id = :user_id AND fcr.status = 'accepted'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $accepted_foster_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query to fetch pending foster care requests
    $sql = "SELECT fcr.id, u.username AS foster_name, fcr.pet_name, fcr.species, fcr.days, fcr.status, fcr.created_at
            FROM foster_care_requests fcr
            JOIN users u ON fcr.user_id = u.id
            WHERE fcr.status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $foster_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle adoption request submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['pet_id'])) {
            $pet_id = $_POST['pet_id'];

            // Insert the adoption request into the database with pending status
            $sql = "INSERT INTO adoption_requests (pet_id, user_id, status) VALUES (:pet_id, :user_id, 'pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':pet_id' => $pet_id,
                ':user_id' => $user_id
            ]);

            $success_message = "Your adoption request has been submitted successfully!";
        } elseif (isset($_POST['accept_foster_id'])) {
            $foster_id = $_POST['accept_foster_id'];

            // Start transaction
            $pdo->beginTransaction();
            try {
                // Update foster care request status
                $sql = "UPDATE foster_care_requests SET status = 'accepted', volunteer_id = :volunteer_id WHERE id = :foster_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':volunteer_id' => $user_id,
                    ':foster_id' => $foster_id
                ]);

                // Get the foster request details for payment calculation
                $sql_foster = "SELECT days FROM foster_care_requests WHERE id = :foster_id";
                $stmt_foster = $pdo->prepare($sql_foster);
                $stmt_foster->execute([':foster_id' => $foster_id]);
                $foster_request = $stmt_foster->fetch(PDO::FETCH_ASSOC);
                
                // Calculate payment amount ($10 per day)
                $payment_amount = $foster_request['days'] * 10;

                // Insert payment record
                $sql_payment = "INSERT INTO volunteer_payments (volunteer_id, foster_request_id, amount) 
                               VALUES (:volunteer_id, :foster_request_id, :amount)";
                $stmt_payment = $pdo->prepare($sql_payment);
                $stmt_payment->execute([
                    ':volunteer_id' => $user_id,
                    ':foster_request_id' => $foster_id,
                    ':amount' => $payment_amount
                ]);

                // Commit transaction
                $pdo->commit();
                $success_message = "Foster care request accepted successfully! Payment of $" . number_format($payment_amount, 2) . " has been recorded.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                throw $e;
            }
        }
    }
} catch (PDOException $e) {
    // Handle database errors
    echo "Error: " . $e->getMessage();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="logout.css">
</head>
<body>
    <div class="header-area">
        <div class="bottom-header">
            <h2>Volunteer Dashboard</h2>
            <ul class="navigation">
                <li><a href="index.php">Home</a></li>
                <li><a href="foster_dashboard.php">Foster Care</a></li>
                <li><a href="petcare_dashboard.php">Veterinary</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
    </div>

    <div style="text-align: center; padding: 20px; background: #f7f7f7; margin-bottom: 20px; border-bottom: 1px solid #ddd;">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>View and manage your adoption and foster care requests below.</p>
    </div>

    <div style="text-align: right; margin: 10px;">
        <a href="logout.php" class="logout-button">Logout</a>
    </div>

    <?php if (isset($success_message)) : ?>
        <div style="text-align: center; margin-top: 20px; color: green;">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>

    <!-- Your Adopted Pets Section -->
    <div class="adoption-form">
        <h2>Your Adopted Pets</h2>
        <div class="adopted-pets-list">
            <?php 
            $adopted_pets = array_filter($adoption_requests, function($request) {
                return $request['status'] === 'approved' && $request['available'] == 0;
            });
            
            if (count($adopted_pets) > 0) : ?>
                <?php foreach ($adopted_pets as $pet) : ?>
                    <div class="adopted-pet-item" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; background-color: #f9f9f9;">
                        <h3><?php echo htmlspecialchars($pet['pet_name']); ?></h3>
                        <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?></p>
                        <p><strong>Age:</strong> <?php echo htmlspecialchars($pet['age']); ?> years</p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($pet['description']); ?></p>
                        <p><strong>Adoption Date:</strong> <?php echo htmlspecialchars($pet['created_at']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>You haven't adopted any pets yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Your Pending Adoption Requests Section -->
    <div class="adoption-form">
        <h2>Your Pending Adoption Requests</h2>
        <div class="adoption-requests-list">
            <?php 
            $pending_requests = array_filter($adoption_requests, function($request) {
                return $request['status'] == 'pending';
            });
            
            if (count($pending_requests) > 0) : ?>
                <?php foreach ($pending_requests as $request) : ?>
                    <div class="adoption-request-item" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        <p><strong>Pet Name:</strong> <?php echo htmlspecialchars($request['pet_name']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($request['status_display']); ?></p>
                        <p><strong>Requested On:</strong> <?php echo htmlspecialchars($request['created_at']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No pending adoption requests.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Accepted Foster Care Requests Section -->
    <div class="adoption-form">
        <h2>Accepted Foster Care Requests</h2>
        <div class="foster-requests-list">
            <?php if (count($accepted_foster_requests) > 0) : ?>
                <?php foreach ($accepted_foster_requests as $request) : ?>
                    <div class="foster-request-item" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        <p><strong>Pet Name:</strong> <?php echo htmlspecialchars($request['pet_name']); ?></p>
                        <p><strong>Species:</strong> <?php echo htmlspecialchars($request['species']); ?></p>
                        <p><strong>Days:</strong> <?php echo htmlspecialchars($request['days']); ?></p>
                        <p><strong>Payment Amount:</strong> $<?php echo number_format($request['payment_amount'], 2); ?></p>
                        <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($request['payment_status']); ?></p>
                        <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($request['payment_date']); ?></p>
                        <p><strong>Accepted On:</strong> <?php echo htmlspecialchars($request['created_at']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No accepted foster care requests found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pending Foster Care Requests Section -->
    <div class="adoption-form">
        <h2>Pending Foster Care Requests</h2>
        <div class="foster-requests-list">
            <?php if (count($foster_requests) > 0) : ?>
                <?php foreach ($foster_requests as $request) : ?>
                    <div class="foster-request-item" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        <p><strong>Foster Caregiver:</strong> <?php echo htmlspecialchars($request['foster_name']); ?></p>
                        <p><strong>Pet Name:</strong> <?php echo htmlspecialchars($request['pet_name']); ?></p>
                        <p><strong>Species:</strong> <?php echo htmlspecialchars($request['species']); ?></p>
                        <p><strong>Days:</strong> <?php echo htmlspecialchars($request['days']); ?></p>
                        <p><strong>Requested On:</strong> <?php echo htmlspecialchars($request['created_at']); ?></p>
                        <form method="POST" action="">
                            <input type="hidden" name="accept_foster_id" value="<?php echo $request['id']; ?>">
                            <button type="submit">Accept Request</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No pending foster care requests at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
