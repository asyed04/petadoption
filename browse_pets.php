<?php
require_once '../includes/db_connect.php';  // Include the DB connection

// Fetch the search term and filters from the GET request
$search = $_GET['search'] ?? '';
$species_filter = $_GET['species'] ?? '';
$breed_filter = $_GET['breed'] ?? '';
$min_age = $_GET['min_age'] ?? '';
$max_age = $_GET['max_age'] ?? '';

// Pagination setup
$results_per_page = 5; // Number of pets per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Base SQL query to fetch pets
$sql = "SELECT * FROM pets WHERE 1";

// Add search condition if provided
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (name LIKE '%$search%' OR breed LIKE '%$search%' OR species LIKE '%$search%')";
}

// Add species filter if provided
if (!empty($species_filter)) {
    $species_filter = mysqli_real_escape_string($conn, $species_filter);
    $sql .= " AND species = '$species_filter'";
}

// Add breed filter if provided
if (!empty($breed_filter)) {
    $breed_filter = mysqli_real_escape_string($conn, $breed_filter);
    $sql .= " AND breed = '$breed_filter'";
}

// Add age range filter if provided
if (!empty($min_age)) {
    $min_age = (int)$min_age;
    $sql .= " AND age >= $min_age";
}
if (!empty($max_age)) {
    $max_age = (int)$max_age;
    $sql .= " AND age <= $max_age";
}

// Fetch total number of pets for pagination
$total_pets_query = "SELECT COUNT(*) AS total FROM pets WHERE 1";

// Apply the same filters to the total count query
if (!empty($search)) {
    $total_pets_query .= " AND (name LIKE '%$search%' OR breed LIKE '%$search%' OR species LIKE '%$search%')";
}
if (!empty($species_filter)) {
    $total_pets_query .= " AND species = '$species_filter'";
}
if (!empty($breed_filter)) {
    $total_pets_query .= " AND breed = '$breed_filter'";
}
if (!empty($min_age)) {
    $total_pets_query .= " AND age >= $min_age";
}
if (!empty($max_age)) {
    $total_pets_query .= " AND age <= $max_age";
}

$total_pets_result = mysqli_query($conn, $total_pets_query);
$total_pets = mysqli_fetch_assoc($total_pets_result)['total'];
$total_pages = ceil($total_pets / $results_per_page);

// Add LIMIT for pagination
$sql .= " LIMIT $start_from, $results_per_page";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error fetching pets: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Pets</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        header h1 {
            font-size: 2.5em;
            color: #fdf0d5;
            margin: 0;
        }
        .search-bar {
            text-align: center;
            margin: 20px 0;
        }
        .search-bar input, .search-bar select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
        }
        .search-bar button {
            padding: 8px 15px;
            background-color: #780000;
            color: #fdf0d5;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #c1121f;
        }
        .pets-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 20px;
        }
        .pet-card {
            width: 300px;
            background-color: #fdf0d5;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 10px;
        }
        .carousel-container {
            position: relative;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 10px;
        }
        .carousel {
            display: flex;
        }
        .carousel-item {
            flex: 0 0 100%;
            max-height: 200px;
            display: none;
        }
        .carousel-item img {
            width: 100%;
            height: auto;
        }
        .carousel-item.active {
            display: block;
        }
        button.prev,
        button.next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            z-index: 100;
        }
        button.prev { left: 10px; }
        button.next { right: 10px; }
        .pet-card h3 { margin: 10px 0; color: #780000; }
        .pet-card a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #780000;
            color: #fdf0d5;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination {
            text-align: center;
            margin: 20px 0;
        }
        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            background-color: #003049;
            color: #fdf0d5;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination a.active {
            background-color: #780000;
        }
    </style>
</head>
<body>
    <header style="background-color: #780000; color: #fdf0d5; padding: 20px; text-align: center;">
        <h1>Browse Available Pets</h1>
    </header>

    <!-- Search Bar -->
    <div class="search-bar">
        <form action="browse_pets.php" method="get">
            <input type="text" name="search" placeholder="Search by name, breed, or species" value="<?php echo htmlspecialchars($search); ?>">
            <select name="species">
                <option value="">All Species</option>
                <option value="Dog" <?php if ($species_filter === 'Dog') echo 'selected'; ?>>Dog</option>
                <option value="Cat" <?php if ($species_filter === 'Cat') echo 'selected'; ?>>Cat</option>
            </select>
            <select name="breed">
                <option value="">All Breeds</option>
                <option value="Labrador" <?php if ($breed_filter === 'Labrador') echo 'selected'; ?>>Labrador</option>
                <option value="Beagle" <?php if ($breed_filter === 'Beagle') echo 'selected'; ?>>Beagle</option>
            </select>
            <input type="number" name="min_age" placeholder="Min Age" value="<?php echo htmlspecialchars($min_age); ?>">
            <input type="number" name="max_age" placeholder="Max Age" value="<?php echo htmlspecialchars($max_age); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <main>
        <div class="pets-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($pet = mysqli_fetch_assoc($result)): ?>
                    <div class="pet-card">
                        <div class="carousel-container">
                            <div class="carousel" id="carousel-<?php echo $pet['id']; ?>">
                                <div class="carousel-item active">
                                    <?php if (!empty($pet['image']) && file_exists("../" . $pet['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($pet['image']); ?>" alt="Image of <?php echo htmlspecialchars($pet['name']); ?>">
                                    <?php else: ?>
                                        <img src="../assets/images/default_pet.jpg" alt="No image available">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                        <p><strong>Species:</strong> <?php echo htmlspecialchars($pet['species']); ?></p>
                        <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?></p>
                        <p><strong>Age:</strong> <?php echo htmlspecialchars($pet['age']); ?> years</p>
                        <a href="submit_application.php?pet_id=<?php echo $pet['id']; ?>">Adopt Me</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No pets found matching your search criteria.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination Links -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="browse_pets.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&species=<?php echo urlencode($species_filter); ?>&breed=<?php echo urlencode($breed_filter); ?>&min_age=<?php echo urlencode($min_age); ?>&max_age=<?php echo urlencode($max_age); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </main>

    <footer style="background-color: #003049; color: #fdf0d5; padding: 10px; text-align: center;">
        <p>&copy; <?php echo date("Y"); ?> Pet Adoption CMS</p>
    </footer>

    <script>
        function moveCarousel(petId, step) {
            const carousel = document.querySelector(`#carousel-${petId}`);
            const items = carousel.querySelectorAll(".carousel-item");
            let activeIndex = Array.from(items).findIndex(item => item.classList.contains("active"));

            // Remove current active class
            items[activeIndex].classList.remove("active");

            // Calculate new active index
            activeIndex += step;
            if (activeIndex >= items.length) activeIndex = 0; // Loop to start
            if (activeIndex < 0) activeIndex = items.length - 1; // Loop to end

            // Set new active class
            items[activeIndex].classList.add("active");
        }
    </script>
</body>
</html>
