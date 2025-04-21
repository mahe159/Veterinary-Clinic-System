<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

require 'db.php';

$accepted_foster_requests = [];
$accepted_adoptions = [];
$payment_history = [];
$pets = [];
$adoption_requests = [];
$foster_requests = [];
$success_message = '';

try {
    // Create a new PDO instance
    $pdo = $conn;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];

    // Query to fetch available pets
    $sql = "SELECT id, name, breed, age, description FROM pets WHERE available = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Query to fetch user's past adoption requests
    $sql = "SELECT ar.id, p.name AS pet_name, ar.status, ar.created_at 
            FROM adoption_requests ar
            JOIN pets p ON ar.pet_id = p.id
            WHERE ar.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $adoption_requests = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Query to fetch pending foster care requests
    $sql = "SELECT fcr.id, u.username, fcr.pet_name, fcr.species, fcr.days, fcr.status, fcr.created_at
            FROM foster_care_requests fcr
            JOIN users u ON fcr.user_id = u.id
            WHERE fcr.status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $foster_requests = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Query to fetch accepted foster care requests (without volunteer_id filter due to missing column)
    $sql = "SELECT fcr.pet_name, fcr.species, fcr.days, fcr.status, fcr.created_at
            FROM foster_care_requests fcr
            WHERE fcr.status = 'accepted'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $accepted_foster_requests = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Query to fetch volunteer's adopted pets
    $sql = "SELECT p.name AS pet_name, ar.status, ar.created_at
            FROM adoption_requests ar
            JOIN pets p ON ar.pet_id = p.id
            WHERE ar.status = 'accepted' AND ar.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $accepted_adoptions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Query to fetch volunteer's payment history
    $sql = "SELECT amount, payment_date, status
            FROM volunteer_payments
            WHERE volunteer_id = :volunteer_id
            ORDER BY payment_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':volunteer_id' => $user_id]);
    $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Handle adoption request submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['pet_id'])) {
            $pet_id = $_POST['pet_id'];

            // Insert the adoption request into the database
            $sql = "INSERT INTO adoption_requests (pet_id, user_id) VALUES (:pet_id, :user_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':pet_id' => $pet_id,
                ':user_id' => $user_id
            ]);

            $success_message = "Your adoption request has been submitted successfully!";

            // Redirect to avoid form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } elseif (isset($_POST['foster_request_id'])) {
            $foster_request_id = $_POST['foster_request_id'];

            // Update foster care request status to accepted (without volunteer_id due to missing column)
            $sql = "UPDATE foster_care_requests SET status = 'accepted' WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $foster_request_id
            ]);

            // Insert payment record for volunteer
            $payment_amount = 100.00; // Fixed payment amount, adjust as needed
            $volunteer_id = $user_id;
            $sql = "INSERT INTO volunteer_payments (volunteer_id, foster_request_id, amount, status) VALUES (:volunteer_id, :foster_request_id, :amount, 'paid')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':volunteer_id' => $volunteer_id,
                ':foster_request_id' => $foster_request_id,
                ':amount' => $payment_amount
            ]);

            $success_message = "Foster care request accepted successfully!";

            // Redirect to avoid form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Volunteer Dashboard</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="logout.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header-area {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .bottom-header {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .bottom-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 28px;
            letter-spacing: 1px;
        }
        .navigation {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 25px;
        }
        .navigation li a {
            color: #ecf0f1;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .navigation li a:hover {
            color: #1abc9c;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .welcome-message {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        .logout-button {
            background: #e74c3c;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s ease;
            display: inline-block;
            margin-bottom: 30px;
        }
        .logout-button:hover {
            background: #c0392b;
        }
        .section-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 25px 30px;
            margin-bottom: 30px;
        }
        .section-card h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #34495e;
            border-bottom: 2px solid #1abc9c;
            padding-bottom: 8px;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        thead tr {
            background: #1abc9c;
            color: #fff;
            text-align: left;
            font-weight: 600;
            border-radius: 10px;
        }
        thead tr th {
            padding: 12px 15px;
        }
        tbody tr {
            background: #ecf0f1;
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        tbody tr:hover {
            background: #d1f0e7;
        }
        tbody tr td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        .pet-item {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: box-shadow 0.3s ease;
        }
        .pet-item:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .pet-item h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .pet-item p {
            margin: 6px 0;
            color: #555;
        }
        button {
            background: #1abc9c;
            border: none;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #16a085;
        }
        .success-message {
            text-align: center;
            margin-top: 20px;
            color: #27ae60;
            font-weight: 600;
            font-size: 18px;
        }
    </style>
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

    <!-- Welcome Message -->
    <div style="text-align: center; padding: 20px; background: #f7f7f7; margin-bottom: 20px; border-bottom: 1px solid #ddd;">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>View available pets and submit adoption requests below.</p>
    </div>

    <!-- Logout Button -->
    <div style="text-align: right; margin: 10px;">
        <a href="logout.php" class="logout-button">Logout</a>
    </div>

    <!-- Available Pets Section -->
    <div class="adoption-form">
        <h1>Available Pets for Adoption</h1>
        <div class="pets-list">
            <?php if (count($pets) > 0) : ?>
                <?php foreach ($pets as $pet) : ?>
                    <div class="pet-item">
                        <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                        <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?></p>
                        <p><strong>Age:</strong> <?php echo htmlspecialchars($pet['age']); ?> years</p>
                        <p><?php echo htmlspecialchars($pet['description']); ?></p>
                        <form method="POST" action="">
                            <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                            <button type="submit">Request to Adopt</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No available pets at the moment. Please check back later.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Success Message -->
    <?php if (!empty($success_message)) : ?>
        <div class="success-message">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- User's Past Adoption Requests -->
    <div class="adoption-form">
        <h1>Your Adoption Requests</h1>
        <table>
            <thead>
                <tr>
                    <th>Pet Name</th>
                    <th>Status</th>
                    <th>Requested On</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($adoption_requests) > 0) : ?>
                    <?php foreach ($adoption_requests as $request) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['pet_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['status']); ?></td>
                            <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">No adoption requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pending Foster Care Requests -->
    <div class="adoption-form">
        <h1>Pending Foster Care Requests</h1>
        <?php if (count($foster_requests) > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Requester</th>
                        <th>Pet Name</th>
                        <th>Species</th>
                        <th>Days</th>
                        <th>Requested On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($foster_requests as $request) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['username']); ?></td>
                            <td><?php echo htmlspecialchars($request['pet_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['species']); ?></td>
                            <td><?php echo htmlspecialchars($request['days']); ?></td>
                            <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="foster_request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit">Accept</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No pending foster care requests.</p>
        <?php endif; ?>
    </div>

    <!-- Accepted Foster Care Requests -->
    <div class="adoption-form section-card">
        <h1>Your Accepted Foster Care Pets</h1>
        <?php
            // Debug output for accepted foster care requests count and volunteer id
            // echo "<p>Debug: Volunteer ID: " . htmlspecialchars($user_id) . "</p>";
            // echo "<p>Debug: Accepted foster care pets count: " . count($accepted_foster_requests) . "</p>";
        ?>
        <?php if (count($accepted_foster_requests) > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Pet Name</th>
                        <th>Species</th>
                        <th>Days</th>
                        <th>Accepted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accepted_foster_requests as $request) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['pet_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['species']); ?></td>
                            <td><?php echo htmlspecialchars($request['days']); ?></td>
                            <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p style="font-weight: 600; color: #7f8c8d;">You have not accepted any foster care pets yet.</p>
        <?php endif; ?>
    </div>

    <!-- Accepted Adoptions -->
    <div class="adoption-form">
        <h1>Your Adopted Pets</h1>
        <?php if (count($accepted_adoptions) > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Pet Name</th>
                        <th>Adoption Status</th>
                        <th>Adopted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accepted_adoptions as $adoption) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($adoption['pet_name']); ?></td>
                            <td><?php echo htmlspecialchars($adoption['status']); ?></td>
                            <td><?php echo htmlspecialchars($adoption['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>You have not adopted any pets yet.</p>
        <?php endif; ?>
    </div>

    <!-- Payment History -->
    <div class="adoption-form">
        <h1>Your Payment History</h1>
        <?php if (count($payment_history) > 0) : ?>
            <?php
                $total_payments = 0;
                foreach ($payment_history as $payment) {
                    $total_payments += floatval($payment['amount']);
                }
            ?>
            <div style="margin-bottom: 15px; font-weight: 700; font-size: 18px; color: #34495e;">
                Total Payments Received: ৳<?php echo number_format($total_payments, 2); ?>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Payment Slip</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payment_history as $payment) : ?>
                        <tr>
                            <td>৳<?php echo htmlspecialchars($payment['amount']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                            <td>
                                <?php
                                    $status = strtolower($payment['status']);
                                    $color = '#7f8c8d'; // default gray
                                    if ($status === 'paid') {
                                        $color = '#27ae60'; // green
                                    } elseif ($status === 'pending') {
                                        $color = '#f39c12'; // orange
                                    } elseif ($status === 'failed') {
                                        $color = '#c0392b'; // red
                                    }
                                ?>
                                <span style="background-color: <?php echo $color; ?>; color: #fff; padding: 5px 10px; border-radius: 12px; font-weight: 600; font-size: 14px; text-transform: capitalize;">
                                    <?php echo htmlspecialchars($payment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="showPaymentSlip('<?php echo htmlspecialchars($payment['amount']); ?>', '<?php echo htmlspecialchars($payment['payment_date']); ?>', '<?php echo htmlspecialchars($payment['status']); ?>')" style="background:#3498db; color:#fff; border:none; padding:5px 10px; border-radius:6px; cursor:pointer;">View Slip</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No payment records found.</p>
        <?php endif; ?>
    </div>

    <!-- Payment Slip Modal -->
    <div id="paymentSlipModal" style="display:none; position:fixed; top:10%; left:50%; transform:translateX(-50%); background:#fff; padding:20px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.2); max-width:400px; z-index:1000;">
        <h2>Payment Slip</h2>
        <div id="paymentSlipContent"></div>
        <button onclick="closePaymentSlip()" style="margin-top:15px; background:#e74c3c; color:#fff; border:none; padding:10px 15px; border-radius:6px; cursor:pointer;">Close</button>
    </div>
    <div id="modalOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;" onclick="closePaymentSlip()"></div>

    <script>
        function showPaymentSlip(amount, date, status) {
            const content = `
                <p><strong>Amount:</strong> ৳${amount}</p>
                <p><strong>Payment Date:</strong> ${date}</p>
                <p><strong>Status:</strong> ${status}</p>
                <p>Thank you for your support!</p>
            `;
            document.getElementById('paymentSlipContent').innerHTML = content;
            document.getElementById('paymentSlipModal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }
        function closePaymentSlip() {
            document.getElementById('paymentSlipModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }
    </script>
</body>
</html>
