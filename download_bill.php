<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;

// Get request data
$user_name = $_GET['user_name'] ?? 'Unknown';
$pet_name = $_GET['pet_name'] ?? 'Unknown';
$species = $_GET['species'] ?? 'Unknown';
$days = $_GET['days'] ?? 0;
$total_cost = $_GET['total_cost'] ?? 0;

// Create PDF content
$html = "

<link rel='stylesheet' type='text/css' href='bill.css'>
<div class='bill-container'>
    <h1>Foster Care Bill</h1>
    <p><strong>Name:</strong> $user_name</p>
    <p><strong>Pet Name:</strong> $pet_name</p>
    <p><strong>Species:</strong> $species</p>
    <p><strong>Days:</strong> $days</p>
    <p><strong>Total Cost:</strong> ₹$total_cost</p>
</div>
";


// Initialize Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the generated PDF for download
$dompdf->stream("foster_care_bill.pdf", ["Attachment" => 1]);
