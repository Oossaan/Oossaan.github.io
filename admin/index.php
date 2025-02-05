<?php
session_start(); // Start session

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$username = htmlspecialchars($_SESSION['username']);

// Load driver data
$driversFile = 'data/data_drivers.json'; // Path to driver data
$drivers = json_decode(file_get_contents($driversFile), true);

// Load order data
$ordersFile = 'data/data_setoran.json'; // Path to order data
$orders = json_decode(file_get_contents($ordersFile), true);

// Load restaurant data
$restaurantsFile = '../restoran/data/resto.json'; // Path to restaurant data
$restaurants = json_decode(file_get_contents($restaurantsFile), true);

// Create an associative array to hold total orders and earnings by driver name
$totalOrdersByDriverName = [];
$totalEarningsByDriverName = [];

// Calculate total orders and earnings for all orders
foreach ($orders as $order) {
    $driverName = $order['driver'];
    $totalOrders = (int) $order['totalOrders'];
    $uangKas = (int) $order['uangKas'];

    // Initialize if not set
    if (!isset($totalOrdersByDriverName[$driverName])) {
        $totalOrdersByDriverName[$driverName] = 0;
        $totalEarningsByDriverName[$driverName] = 0; // Initialize earnings
    }

    // Sum total orders
    $totalOrdersByDriverName[$driverName] += $totalOrders;

    // Calculate total earnings
    $totalEarningsByDriverName[$driverName] += ($totalOrders * 2000) + $uangKas;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Utama</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <a href="index.php" class="text-white font-bold text-lg mb-2 md:mb-0">Dashboard</a>
            <div class="flex flex-col md:flex-row items-center">
                <a href="resto.php" class="text-white hover:underline mx-2 mb-2 md:mb-0 flex items-center">
                    <i class="fas fa-utensils mr-1"></i> Manajemen Restoran
                </a>
                <a href="rekapan_orderan.php" class="text-white hover:underline mx-2 mb-2 md:mb-0 flex items-center">
                    <i class="fas fa-receipt mr-1"></i> Rekapan Setoran Orderan
                </a>
                <a href="logout.php" class="text-white hover:underline flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-10">
        <h2 class="text-xl font-bold mb-4 text-center">Data Driver</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($drivers as $driver): ?>
                <div class="card bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($driver['name']); ?></h3>
                    <p class="text-gray-700 flex items-center ">
                        <i class="fas fa-shopping-cart mr-2"></i> Total Orders:
                        <?php echo isset($totalOrdersByDriverName[$driver['name']]) ? htmlspecialchars($totalOrdersByDriverName[$driver['name']]) : 0; ?>
                    </p>
                    <p class="text-gray-700 flex items-center">
                        <i class="fas fa-money-bill-wave mr-2"></i> Total Earnings: Rp
                        <?php echo number_format(isset($totalEarningsByDriverName[$driver['name']]) ? $totalEarningsByDriverName[$driver['name']] : 0, 0, ',', '.'); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 class="text-xl font-bold mb-4 mt-10 text-center">Data Restoran</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="card bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($restaurant['nama']); ?></h3>
                    <p class="text-gray-700 flex items-center ">
                        <i class="fas fa-map-marker-alt mr-2"></i> Alamat:
                        <?php echo htmlspecialchars($restaurant['alamat']); ?>
                    </p>
                    <p class="text-gray-700 flex items-center">
                        <i class="fas fa-star mr-2"></i> Rating:
                        <?php echo htmlspecialchars($restaurant['rating']); ?>
                    </p>
                    <h4 class="text-md font-semibold mt-4">Menu:</h4>
                    <ul class="list-disc list-inside">
                        <?php foreach ($restaurant['menu'] as $menuItem): ?>
                            <li><?php echo htmlspecialchars($menuItem['nama']) . ' - Rp ' . htmlspecialchars($menuItem['harga']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>