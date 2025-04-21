<?php
session_start();

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
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

    // Handle form submissions
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['add_doctor'])) {
            // Add doctor
            $doctor_name = $_POST['doctor_name'];
            $available_date = $_POST['available_date'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $fees = $_POST['fees'];

            $sql = "INSERT INTO doctors (doctor_name, available_date, start_time, end_time, fees) 
                    VALUES (:doctor_name, :available_date, :start_time, :end_time, :fees)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':doctor_name' => $doctor_name,
                ':available_date' => $available_date,
                ':start_time' => $start_time,
                ':end_time' => $end_time,
                ':fees' => $fees
            ]);
            $success_message = "Doctor added successfully!";
        }

        if (isset($_POST['approve_adoption'])) {
            // Approve adoption request
            $request_id = $_POST['request_id'];

            // Get the pet_id from the adoption request
            $sql = "SELECT pet_id FROM adoption_requests WHERE id = :request_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':request_id' => $request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($request) {
                // Start transaction
                $pdo->beginTransaction();
                try {
                    // First check if pet is still available
                    $sql = "SELECT available FROM pets WHERE id = :pet_id FOR UPDATE";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':pet_id' => $request['pet_id']]);
                    $pet = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$pet || !$pet['available']) {
                        throw new Exception("Pet is no longer available for adoption");
                    }

                    // Update adoption request status and decline other pending requests for this pet
                    $sql = "UPDATE adoption_requests 
                           SET status = CASE 
                               WHEN id = :request_id THEN 'approved'
                               WHEN pet_id = :pet_id AND status = 'pending' THEN 'declined'
                               ELSE status 
                           END
                           WHERE id = :request_id OR (pet_id = :pet_id AND status = 'pending')";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':request_id' => $request_id,
                        ':pet_id' => $request['pet_id']
                    ]);

                    // Update pet availability with explicit locking
                    $sql = "UPDATE pets SET available = 0 WHERE id = :pet_id AND available = 1";
                    $stmt = $pdo->prepare($sql);
                    $result = $stmt->execute([':pet_id' => $request['pet_id']]);

                    if ($stmt->rowCount() === 0) {
                        throw new Exception("Failed to update pet availability");
                    }

                    // Add debug log
                    error_log("Adoption request {$request_id} approved for pet {$request['pet_id']}");

                    // Commit transaction
                    $pdo->commit();
                    $success_message = "Adoption request approved successfully!";
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $pdo->rollBack();
                    throw $e;
                }
            }
        }

        if (isset($_POST['remove_doctor'])) {
            // Remove doctor
            $doctor_id = $_POST['doctor_id'];

            $sql = "DELETE FROM doctors WHERE id = :doctor_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':doctor_id' => $doctor_id]);
            $success_message = "Doctor removed successfully!";
        }

        if (isset($_POST['add_pet'])) {
            // Add pet
            $name = $_POST['name'];
            $breed = $_POST['breed'];
            $age = $_POST['age'];
            $description = $_POST['description'];

            $sql = "INSERT INTO pets (name, breed, age, description, available) 
                    VALUES (:name, :breed, :age, :description, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':breed' => $breed,
                ':age' => $age,
                ':description' => $description
            ]);
            $success_message = "Pet added successfully!";
        }

        if (isset($_POST['accept_petcare'])) {
            // Accept petcare appointment
            $appointment_id = $_POST['appointment_id'];

            $sql = "UPDATE petcare_appointments SET status = 'accepted' WHERE id = :appointment_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':appointment_id' => $appointment_id]);
            $success_message = "Pet care appointment accepted successfully!";
        }

        if (isset($_POST['reject_petcare'])) {
            // Reject petcare appointment
            $appointment_id = $_POST['appointment_id'];

            $sql = "UPDATE petcare_appointments SET status = 'rejected' WHERE id = :appointment_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':appointment_id' => $appointment_id]);
            $success_message = "Pet care appointment rejected successfully!";
        }

        if (isset($_POST['delete_message'])) {
            // Delete contact message
            $message_id = $_POST['message_id'];

            $sql = "DELETE FROM contact_messages WHERE id = :message_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':message_id' => $message_id]);
            $success_message = "Contact message deleted successfully!";
        }
    }

    // Fetch all necessary data including contact messages
    $doctors = $pdo->query("SELECT * FROM doctors")->fetchAll(PDO::FETCH_ASSOC);
    $contact_messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $adoption_requests = $pdo->query("SELECT ar.id, p.name AS pet_name, u.username, ar.status 
                                      FROM adoption_requests ar
                                      JOIN pets p ON ar.pet_id = p.id
                                      JOIN users u ON ar.user_id = u.id")->fetchAll(PDO::FETCH_ASSOC);
    $pets = $pdo->query("SELECT * FROM pets")->fetchAll(PDO::FETCH_ASSOC);
    $petcare_appointments = $pdo->query("SELECT pa.id, d.doctor_name, pa.user_name, pa.pet_name, pa.breed, pa.species, pa.status, pa.created_at 
                                         FROM petcare_appointments pa
                                         JOIN doctors d ON pa.doctor_id = d.id")->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css"> <!-- Main CSS -->
    <link rel="stylesheet" href="logout.css"> <!-- Logout Button CSS -->
    <link rel="stylesheet" href="admin.css">
 
</head>
<body>
    <div class="header-area">
        <div class="bottom-header">
            <h2>Admin Panel</h2>
            <ul class="navigation">
                <li><a href="index.html">Home</a></li>
                <li><a href="logout.php" class="logout-button">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Success Message -->
    <?php if (isset($success_message)) : ?>
        <div style="text-align: center; color: green;">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>

    <!-- Add Doctor -->
    <div class="admin-section">
        <h1>Add Doctor</h1>
        <form method="POST" action="">
            <label for="doctor_name">Doctor Name:</label>
            <input type="text" id="doctor_name" name="doctor_name" required>

            <label for="available_date">Available Date:</label>
            <input type="date" id="available_date" name="available_date" required>

            <label for="start_time">Start Time:</label>
            <input type="time" id="start_time" name="start_time" required>

            <label for="end_time">End Time:</label>
            <input type="time" id="end_time" name="end_time" required>

            <label for="fees">Fees:</label>
            <input type="number" id="fees" name="fees" step="0.01" required>

            <button type="submit" name="add_doctor">Add Doctor</button>
        </form>
    </div>

    <!-- Adoption Requests -->
    <div class="admin-section">
        <h1>Adoption Requests</h1>
        <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Pet Name</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adoption_requests as $request) : ?>
                    <tr>
                        <td><?php echo $request['id']; ?></td>
                        <td><?php echo $request['pet_name']; ?></td>
                        <td><?php echo $request['username']; ?></td>
                        <td><?php echo $request['status']; ?></td>
                        <td>
                            <?php if ($request['status'] === 'pending') : ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="approve_adoption">Approve</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Available Doctors -->
    <div class="admin-section">
        <h1>Available Doctors</h1>
        <table>
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Name</th>
                    <th>Available Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Fees</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor) : ?>
                    <tr>
                        <td><?php echo $doctor['id']; ?></td>
                        <td><?php echo $doctor['doctor_name']; ?></td>
                        <td><?php echo $doctor['available_date']; ?></td>
                        <td><?php echo $doctor['start_time']; ?></td>
                        <td><?php echo $doctor['end_time']; ?></td>
                        <td><?php echo $doctor['fees']; ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                <button type="submit" name="remove_doctor">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Manage Pets -->
    <div class="admin-section">
        <h1>Manage Pets</h1>
        <form method="POST" action="">
            <label for="name">Pet Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="breed">Breed:</label>
            <input type="text" id="breed" name="breed" required>

            <label for="age">Age:</label>
            <input type="number" id="age" name="age" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <button type="submit" name="add_pet">Add Pet</button>
        </form>

        <h2>Available Pets</h2>
        <table>
            <thead>
                <tr>
                    <th>Pet ID</th>
                    <th>Name</th>
                    <th>Breed</th>
                    <th>Age</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pets as $pet) : ?>
                    <tr>
                        <td><?php echo $pet['id']; ?></td>
                        <td><?php echo $pet['name']; ?></td>
                        <td><?php echo $pet['breed']; ?></td>
                        <td><?php echo $pet['age']; ?></td>
                        <td><?php echo $pet['description']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Pet Care Appointment Requests -->
    <div class="admin-section">
        <h1>Pet Care Appointment Requests</h1>
        <table>
            <thead>
                <tr>
                    <th>Appointment ID</th>
                    <th>Doctor</th>
                    <th>User Name</th>
                    <th>Pet Name</th>
                    <th>Breed</th>
                    <th>Species</th>
                    <th>Status</th>
                    <th>Requested On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($petcare_appointments as $appointment) : ?>
                    <tr>
                        <td><?php echo $appointment['id']; ?></td>
                        <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['breed']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['species']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                        <td><?php echo $appointment['created_at']; ?></td>
                        <td>
                            <?php if ($appointment['status'] === 'pending') : ?>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                    <button type="submit" name="accept_petcare">Accept</button>
                                </form>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                    <button type="submit" name="reject_petcare">Reject</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Contact Messages -->
    <div class="admin-section">
        <h1>Contact Messages</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Received On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contact_messages as $message) : ?>
                    <tr>
                        <td><?php echo $message['id']; ?></td>
                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                        <td><?php echo htmlspecialchars($message['message']); ?></td>
                        <td><?php echo $message['created_at']; ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                <button type="submit" name="delete_message">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
