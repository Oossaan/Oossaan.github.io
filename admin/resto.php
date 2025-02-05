<?php
session_start(); // Start session

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$username = htmlspecialchars($_SESSION['username']);

// Path to the JSON file
$jsonFilePath = '../restoran/data/resto.json';

// Load existing restaurants from the JSON file
$existing_restaurants = [];
if (file_exists($jsonFilePath)) {
    $existing_restaurants = json_decode(file_get_contents($jsonFilePath), true);
}

// Function to get a new ID
function getNewId($existing_restaurants)
{
    $ids = array_column($existing_restaurants, 'id');
    return (string) (count($ids) + 1); // Generate new ID based on the count
}

// Handle form submission for adding menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_menu') {
    $restaurantId = $_POST['restaurant_id'];
    $menuName = htmlspecialchars($_POST['menu_name']);
    $menuPrice = htmlspecialchars($_POST['menu_price']);

    // Find the restaurant by ID
    foreach ($existing_restaurants as &$restaurant) {
        if ($restaurant['id'] == $restaurantId) {
            // Add the new menu item
            $restaurant['menu'][] = [
                "nama" => $menuName,
                "harga" => $menuPrice
            ];
            break;
        }
    }

    // Save the updated array back to the JSON file
    file_put_contents($jsonFilePath, json_encode($existing_restaurants, JSON_PRETTY_PRINT));

    // Set success message and redirect
    $_SESSION['message'] = 'Menu berhasil ditambahkan!';
    header('Location: resto.php');
    exit();
}

// Handle form submission for adding restaurant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_restaurant') {
    $newRestaurantName = htmlspecialchars($_POST['restaurant_name']);

    // Check if restaurant already exists
    foreach ($existing_restaurants as $restaurant) {
        if ($restaurant['nama'] === $newRestaurantName) {
            $_SESSION['message'] = 'Restoran sudah ada!';
            header('Location: resto.php');
            exit();
        }
    }

    $newRestaurant = [
        "id" => getNewId($existing_restaurants),
        "nama" => $newRestaurantName,
        "alamat" => htmlspecialchars($_POST['restaurant_address']),
        "rating" => (float) $_POST['restaurant_rating'],
        "menu" => []
    ];

    // Add the new restaurant to the existing array
    $existing_restaurants[] = $newRestaurant;

    // Save the updated array back to the JSON file
    file_put_contents($jsonFilePath, json_encode($existing_restaurants, JSON_PRETTY_PRINT));

    // Set success message and redirect
    $_SESSION['message'] = 'Restoran berhasil ditambahkan!';
    header('Location: resto.php');
    exit();
}

// Handle restaurant deletion
if (isset($_GET['delete'])) {
    $restaurantIdToDelete = $_GET['delete'];
    foreach ($existing_restaurants as $key => $restaurant) {
        if ($restaurant['id'] == $restaurantIdToDelete) {
            unset($existing_restaurants[$key]);
            break;
        }
    }
    // Re-index the array
    $existing_restaurants = array_values($existing_restaurants);
    // Save the updated array back to the JSON file
    file_put_contents($jsonFilePath, json_encode($existing_restaurants, JSON_PRETTY_PRINT));
    $_SESSION['message'] = 'Restoran berhasil dihapus!';
    header('Location: resto.php');
    exit();
}

// Handle menu deletion
if (isset($_GET['delete_menu'])) {
    $restaurantId = $_GET['restaurant_id'];
    $menuNameToDelete = $_GET['delete_menu'];

    foreach ($existing_restaurants as &$restaurant) {
        if ($restaurant['id'] == $restaurantId) {
            foreach ($restaurant['menu'] as $key => $menu) {
                if ($menu['nama'] == $menuNameToDelete) {
                    unset($restaurant['menu'][$key]);
                    break;
                }
            }
            break;
        }
    }

    // Save the updated array back to the JSON file
    file_put_contents($jsonFilePath, json_encode($existing_restaurants, JSON_PRETTY_PRINT));
    $_SESSION['message'] = 'Menu berhasil dihapus!';
    header('Location: resto.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Restoran</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1000;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgb(0, 0, 0);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            /* Could be more or less, depending on screen size */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Toast styles */
        .toast {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4caf50;
            color: white;
            padding: 16px;
            border-radius: 5px;
            z-index: 1000;
            transition: opacity 0.5s;
        }

        /* Card styles */
        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="container mx-auto p-5">
        <h1 class="text-2xl font-bold mb-5">Manajemen Restoran</h1>

        <div class="mb-5">
            <button id="addRestaurantBtn" class="bg-green-500 text-white p-2 rounded">Tambah Restoran</button>
            <button id="addMenuBtn" class="bg-blue-500 text-white p-2 rounded">Tambah Menu</button>
        </div>

        <h2 class="text-xl font-bold mb-3">Daftar Restoran</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($existing_restaurants as $restaurant): ?>
                <div class="card bg-white p-4 rounded shadow-md hover:shadow-lg transition-shadow duration-300">
                    <h3 class="text-lg font-semibold"><?php echo $restaurant['nama']; ?></h3>
                    <p>Alamat: <?php echo $restaurant['alamat']; ?></p>
                    <p>Rating: <?php echo $restaurant['rating']; ?></p>
                    <h4 class="font-semibold mt-2">Menu:</h4>
                    <ul>
                        <?php foreach ($restaurant['menu'] as $menu): ?>
                            <li>
                                <?php echo $menu['nama']; ?> - <?php echo $menu['harga']; ?>
                                <a href="?delete_menu=<?php echo $menu['nama']; ?>&restaurant_id=<?php echo $restaurant['id']; ?>"
                                    class="text-red-500 ml-2"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?');">Hapus</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-2">
                        <a href="?delete=<?php echo $restaurant['id']; ?>" class="text-red-500"
                            onclick="return confirm('Apakah Anda yakin ingin menghapus restoran ini?');">Hapus Restoran</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal for Adding Restaurant -->
    <div id="restaurantModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeRestaurantModal">&times;</span>
            <h2 class="text-xl font-bold mb-3">Tambah Restoran</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_restaurant">
                <label class="block mb-2" for="restaurant_name">Nama Restoran:</label>
                <input class="border rounded w-full p-2 mb-4" type="text" name="restaurant_name" required>

                <label class="block mb-2" for="restaurant_address">Alamat:</label>
                <input class="border rounded w-full p-2 mb-4" type="text" name="restaurant_address" required>

                <label class="block mb-2" for="restaurant_rating">Rating:</label>
                <input class="border rounded w-full p-2 mb-4" type="number" name="restaurant_rating" step="0.1" min="0"
                    max="5" required>

                <button type="submit" class="bg-blue-500 text-white p-2 rounded">Tambah Restoran</button>
            </form>
        </div>
    </div>

    <!-- Modal for Adding Menu -->
    <div id="menuModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeMenuModal">&times;</span>
            <h2 class="text-xl font-bold mb-3">Tambah Menu Restoran</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_menu">
                <label class="block mb-2" for="restaurant_id">Pilih Restoran:</label>
                <select name="restaurant_id" class="border rounded w-full p-2 mb-4" required>
                    <option value="">-- Pilih Restoran --</option>
                    <?php foreach ($existing_restaurants as $restaurant): ?>
                        <option value="<?php echo $restaurant['id']; ?>"><?php echo $restaurant['nama']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="block mb-2" for="menu_name">Nama Menu:</label>
                <input class="border rounded w-full p-2 mb-4" type="text" name="menu_name" required>

                <label class="block mb-2" for="menu_price">Harga Menu:</label>
                <input class="border rounded w-full p-2 mb-4" type="text" name="menu_price" required
                    placeholder="Contoh: 25.000">

                <button type="submit" class="bg-blue-500 text-white p-2 rounded">Tambah Menu</button>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        // Get the modal
        var restaurantModal = document.getElementById("restaurantModal");
        var menuModal = document.getElementById("menuModal");

        // Get the button that opens the modal
        var addRestaurantBtn = document.getElementById("addRestaurantBtn");
        var addMenuBtn = document.getElementById("addMenuBtn");

        // Get the <span> element that closes the modal
        var closeRestaurantModal = document.getElementById("closeRestaurantModal");
        var closeMenuModal = document.getElementById("closeMenuModal");

        // When the user clicks the button, open the modal 
        addRestaurantBtn.onclick = function () {
            restaurantModal.style.display = "block";
        }

        addMenuBtn.onclick = function () {
            menuModal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        closeRestaurantModal.onclick = function () {
            restaurantModal.style.display = "none";
        }

        closeMenuModal.onclick = function () {
            menuModal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target == restaurantModal) {
                restaurantModal.style.display = "none";
            }
            if (event.target == menuModal) menuModal.style.display = "none";
        }

        // Show toast notification
        function showToast(message) {
            var toast = document.getElementById("toast");
            toast.innerText = message;
            toast.style.display = "block";
            toast.style.opacity = 1;

            setTimeout(function () {
                toast.style.opacity = 0;
                setTimeout(function () {
                    toast.style.display = "none";
                }, 500);
            }, 3000);
        }

        // Display success message if exists
        window.onload = function () {
            <?php if (isset($_SESSION['message'])): ?>
                showToast('<?php echo $_SESSION['message']; ?>');
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
        }
    </script>

</body>

</html>