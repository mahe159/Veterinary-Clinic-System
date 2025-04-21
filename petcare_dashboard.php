<?php
require 'db.php'; // Database connection
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's name
$username = $_SESSION['username'] ?? "Guest";

// Fetch doctors from the database
$sql = "SELECT * FROM doctors WHERE available_date >= CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch logged-in user's pet care appointments
$user_name = $_SESSION['username'] ?? '';
$sql = "SELECT pa.id, d.doctor_name, pa.pet_name, pa.breed, pa.species, pa.status, pa.created_at
        FROM petcare_appointments pa
        JOIN doctors d ON pa.doctor_id = d.id
        WHERE pa.user_name = :user_name
        ORDER BY pa.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([':user_name' => $user_name]);
$user_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $doctor_id = $_POST['doctor_id'];
    $user_name = $_POST['user_name'];
    $pet_name = $_POST['pet_name'];
    $breed = $_POST['breed'];
    $species = $_POST['species'];

    // Fetch selected doctor's details
    $sql = "SELECT doctor_name, available_date, start_time, fees FROM doctors WHERE id = :doctor_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':doctor_id' => $doctor_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    $doctor_name = $doctor['doctor_name'];
    $available_date = $doctor['available_date'];
    $start_time = $doctor['start_time'];
    $fees = $doctor['fees'];

    // Insert appointment into the database
    $sql = "INSERT INTO petcare_appointments (doctor_id, user_name, pet_name, breed, species) 
            VALUES (:doctor_id, :user_name, :pet_name, :breed, :species)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':doctor_id' => $doctor_id,
        ':user_name' => $user_name,
        ':pet_name' => $pet_name,
        ':breed' => $breed,
        ':species' => $species
    ]);

    $success_message = "Your appointment has been scheduled successfully!";
    $bill_data = [
        'doctor_name' => $doctor_name,
        'appointment_date' => $available_date,
        'appointment_time' => $start_time,
        'doctor_fees' => $fees,
        'user_name' => $user_name,
        'pet_name' => $pet_name,
        'breed' => $breed,
        'species' => $species,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Care Dashboard</title>
    <link rel="stylesheet" href="style.css"> <!-- Main CSS -->
    <link rel="stylesheet" href="logout.css"> <!-- Logout Button CSS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script> <!-- jsPDF Library -->
</head>
<body>
    <div class="header-area">
        <div class="bottom-header">
            <h2>Pet Care Dashboard</h2>
            <ul class="navigation">
                <li><a href="index.html">Home</a></li>
                <li><a href="foster_dashboard.php">Foster Care</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
    </div>

    <!-- Welcome Message -->
    <div style="text-align: center; padding: 20px; background: #f7f7f7; margin-bottom: 20px; border-bottom: 1px solid #ddd;">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>Find a doctor and schedule an appointment for your pet below.</p>
    </div>

    <!-- Logout Button -->
    <div style="text-align: right; margin: 10px;">
        <a href="logout.php" class="logout-button">Logout</a>
    </div>

    <!-- Available Doctors -->
    <div class="adoption-form">
        <h1>Available Doctors</h1>
        <?php if (!empty($doctors)) : ?>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background: #f7f7f7; border-bottom: 1px solid #ddd;">
                        <th style="padding: 10px;">Doctor Name</th>
                        <th style="padding: 10px;">Available Date</th>
                        <th style="padding: 10px;">Start Time</th>
                        <th style="padding: 10px;">End Time</th>
                        <th style="padding: 10px;">Fees</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctors as $doctor) : ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px;"><?php echo htmlspecialchars($doctor['doctor_name']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($doctor['available_date']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($doctor['start_time']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($doctor['end_time']); ?></td>
                            <td style="padding: 10px;">৳<?php echo htmlspecialchars($doctor['fees']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No doctors are currently available.</p>
        <?php endif; ?>

    </div>

    <!-- Appointment Form -->
    <div class="adoption-form">
        <h1>Schedule an Appointment</h1>
        <?php if (isset($success_message)) : ?>
            <p style="color: green;"><?php echo $success_message; ?></p>
            <!-- Show Download Bill Button -->
            <button onclick="generatePDF()">Download Bill</button>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="doctor_id">Select Doctor:</label>
            <select id="doctor_id" name="doctor_id" required>
                <option value="">-- Choose a Doctor --</option>
                <?php foreach ($doctors as $doctor) : ?>
                    <option value="<?php echo $doctor['id']; ?>">
                        <?php echo htmlspecialchars($doctor['doctor_name'] . " - ৳" . $doctor['fees']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="user_name">Your Name:</label>
            <input type="text" id="user_name" name="user_name" required>

            <label for="pet_name">Pet's Name:</label>
            <input type="text" id="pet_name" name="pet_name" required>

            <label for="breed">Breed:</label>
            <input type="text" id="breed" name="breed" required>

            <label for="species">Species:</label>
            <select id="species" name="species" required>
                <option value="cat">Cat</option>
                <option value="dog">Dog</option>
            </select>

            <button type="submit">Submit</button>
        </form>
    </div>

    <!-- User's Appointment Requests -->
    <div class="adoption-form">
        <h1>Your Appointment Requests</h1>
        <?php if (!empty($user_appointments)) : ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #f7f7f7; border-bottom: 1px solid #ddd;">
                        <th style="padding: 10px;">Doctor Name</th>
                        <th style="padding: 10px;">Pet Name</th>
                        <th style="padding: 10px;">Breed</th>
                        <th style="padding: 10px;">Species</th>
                        <th style="padding: 10px;">Status</th>
                        <th style="padding: 10px;">Requested On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_appointments as $appointment) : ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px;"><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($appointment['pet_name']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($appointment['breed']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($appointment['species']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($appointment['status']); ?></td>
                            <td style="padding: 10px;"><?php echo $appointment['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>You have no appointment requests.</p>
        <?php endif; ?>
    </div>

    <!-- Generate PDF -->
    <script>
        function generatePDF() {
            const billData = <?php echo isset($bill_data) ? json_encode($bill_data) : 'null'; ?>;

            if (!billData) {
                alert("No bill data available.");
                return;
            }

            const { doctor_name, appointment_date, appointment_time, user_name, pet_name, breed, species, doctor_fees } = billData;

            // Import jsPDF from the global scope
            const { jsPDF } = window.jspdf;

            // Create a new jsPDF instance
            const doc = new jsPDF();

            // Add content to the PDF
            doc.setFontSize(16);
            doc.text("Pet Care Appointment Bill", 10, 10);
            doc.setFontSize(12);
            doc.text(`Doctor: ${doctor_name}`, 10, 30);
            doc.text(`Appointment Date: ${appointment_date}`, 10, 40);
            doc.text(`Appointment Time: ${appointment_time}`, 10, 50);
            doc.text(`Name: ${user_name}`, 10, 60);
            doc.text(`Pet's Name: ${pet_name}`, 10, 70);
            doc.text(`Breed: ${breed}`, 10, 80);
            doc.text(`Species: ${species}`, 10, 90);
            doc.text(`Doctor's Fee: ৳${doctor_fees}`, 10, 100);

            // Save the PDF
            doc.save("pet_care_bill.pdf");
        }
    </script>
</body>
</html>
