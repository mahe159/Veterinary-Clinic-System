<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$db = 'pawdopter_care';
$user = 'root';
$pass = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];

    // Handle accepting foster care request
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accept_foster_id'])) {
        $foster_id = $_POST['accept_foster_id'];

        // Update foster care request to accepted and assign volunteer
        $sql = "UPDATE foster_care_requests SET status = 'accepted', volunteer_id = :volunteer_id WHERE id = :foster_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':volunteer_id' => $user_id,
            ':foster_id' => $foster_id
        ]);

        // Insert into adoption_requests table with cart status
        $sql = "INSERT INTO adoption_requests (pet_id, user_id, status)
                SELECT pet_id, :user_id, 'cart'
                FROM foster_care_requests WHERE id = :foster_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':foster_id' => $foster_id
        ]);

        $success_message = "Foster care request accepted successfully!";
    }

    // Fetch pending foster care requests
    $sql = "SELECT fcr.id, u.username AS foster_name, fcr.pet_name, fcr.species, fcr.days, fcr.status, fcr.created_at
            FROM foster_care_requests fcr
            JOIN users u ON fcr.user_id = u.id
            WHERE fcr.status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $foster_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Volunteer Foster Care Requests</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="logout.css" />
</head>
<body>
    <div class="header-area">
        <div class="bottom-header">
            <h2>Volunteer Foster Care Requests</h2>
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
        <p>View and accept pending foster care requests below.</p>
    </div>

    <div style="text-align: right; margin: 10px;">
        <a href="logout.php" class="logout-button">Logout</a>
    </div>

    <?php if (isset($success_message)) : ?>
        <div style="text-align: center; margin-top: 20px; color: green;">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>

    <div class="adoption-form">
        <h1>Pending Foster Care Requests</h1>
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
