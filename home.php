<?php
// home.php
// Start the session (not really necessary for the home page unless you plan to show logged-in user details)
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Pet Adoption CMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Global Styles for Header */
        header {
            background-color: #780000; /* Dark red from your palette */
            color: #fdf0d5;           /* Light beige for contrast */
            padding: 20px;
            text-align: center;
        }

        header h1 {
            font-size: 2.5em;
            color:#fdf0d5;
            margin: 0;
        }

        header nav a {
            color: #fdf0d5;           /* Light beige for navigation links */
            text-decoration: none;
            font-weight: bold;
            margin: 0 10px;
        }

        header nav a:hover {
            color: #669bbc;           /* Highlight navigation links on hover */
        }

        /* Main Content Styling */
        main {
            padding: 20px;
            background-color: #fdf0d5; /* Light beige */
            color: #003049;           /* Deep blue for contrast */
            text-align: center;
        }

        main h2 {
            color: #780000;           /* Dark red for headings */
        }

        /* Slideshow Container */
        .slideshow-container {
            position: relative;
            width: 80%;               /* Adjust width to fit nicely */
            max-width: 900px;         /* Prevent images from being too large */
            margin: 20px auto;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Slides */
        .slide {
            display: none;
            text-align: center;
        }

        .slide img {
            width: 100%;
            height: auto;             /* Maintain image aspect ratio */
            border-radius: 10px;
        }

        /* Navigation Dots */
        .dot-container {
            text-align: center;
            margin-top: 10px;
        }

        .dot {
            height: 12px;
            width: 12px;
            margin: 5px;
            background-color: #c4c4c4; /* Default dot color */
            border-radius: 50%;
            display: inline-block;
            cursor: pointer;
        }

        .dot.active {
            background-color: #780000; /* Highlight active dot */
        }

        /* Footer */
        footer {
            background-color: #003049; /* Deep blue */
            color: #fdf0d5;           /* Light beige */
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <h1>Welcome to Pet Adoption CMS</h1>
        <nav>
            <a href="login.php">Admin Login</a> | 
            <a href="user/browse_pets.php">Browse Pets</a> |
            <a href="user/submit_application.php">Adoption Application</a>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <h2>Our Mission</h2>
        <p>We help find loving homes for pets in need. Explore our pets and apply to adopt today!</p>

        <!-- Slideshow -->
        <div class="slideshow-container">
            <div class="slide">
                <img src="assets/images/slide1.jpg" alt="Adorable Dog">
            </div>
            <div class="slide">
                <img src="assets/images/slide2.jpg" alt="Cute Cat">
            </div>
            <div class="slide">
                <img src="assets/images/slide3.jpg" alt="Lovely Rabbit">
            </div>
        </div>

        <!-- Dots -->
        <div class="dot-container">
            <span class="dot" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Pet Adoption CMS</p>
    </footer>

    <!-- Slideshow Script -->
    <script>
        let slideIndex = 0;
        showSlides();

        function showSlides() {
            const slides = document.getElementsByClassName("slide");
            const dots = document.getElementsByClassName("dot");
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slideIndex++;
            if (slideIndex > slides.length) {
                slideIndex = 1;
            }
            for (let i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            slides[slideIndex - 1].style.display = "block";
            dots[slideIndex - 1].className += " active";
            setTimeout(showSlides, 4000); // Change slide every 4 seconds
        }

        function currentSlide(n) {
            slideIndex = n - 1;
            showSlides();
        }
    </script>
</body>
</html>
