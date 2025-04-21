<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Pets for Adoption</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>
<body>
    <header class="header-area">
        <div class="bottom-header">
            <div class="bottom-header-logo">
                <h2><i class="fa-solid fa-paw"></i> PawShelter</h2>
            </div>
            <div class="main-menu">
                <nav>
                    <ul class="navigation">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about-us.html">About Us</a></li>
                        <li><a href="adoption-form.html">Adoption Form</a></li>
                        <li><a href="availablepet.php">Available Pets</a></li> <!-- Update the link here -->
                        <li><a href="contact-us.html">Contact Us</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <section class="available-pets">
        <div class="container">
            <h1>Available Pets for Adoption</h1>
            <div class="pets-list">
                <!-- Include PHP file to fetch pets -->
                <?php include('fetch_available_pets.php'); ?>
            </div>
        </div>
    </section>

    <footer class="footer-section">
        <div class="footer-bottom">
            <p>&copy; 2025 PawShelter. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
