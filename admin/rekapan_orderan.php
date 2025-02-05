<?php
session_start(); // Start session

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapan Setoran Lucky Si Gesit</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gray-100">

    <div class="container mx-auto mt-10">
        <h1 class="text-3xl font-bold mb-5 text-center">Rekapan Setoran Lucky Si Gesit</h1>

        <div class="mb-5 flex flex-col md:flex-row justify-between">
            <div class="flex flex-col md:flex-row">
                <input type="text" id="searchDriver" placeholder="Cari Nama Driver"
                    class="border border-gray-300 rounded p-2 mb-2 md:mb-0 md:mr-2" oninput="filterTable()">
                <input type="date" id="searchDate" class="border border-gray-300 rounded p-2 mb-2 md:mb-0 md:mr-2"
                    oninput="filterTable()">
            </div>
            <div class="flex flex-col md:flex-row">
                <button id="openModal" class="bg-blue-500 text-white px-4 py-2 rounded mb-2 md:mb-0 md:mr-2">
                    <i class="fas fa-plus"></i> Tambah Rekap</button>
                <button id="openExportModal" class="bg-green-500 text-white px-4 py-2 rounded mb-2 md:mb-0 md:mr-2"
                    onclick="exportToExcel()">
                    <i class="fas fa-file-export"></i> Ekspor ke Excel</button>
                <button onclick="window.location.href='index.php'" class="bg-gray-500 text-white px-4 py-2 rounded">
                    <i class="fas fa-arrow-left"></i> Kembali ke Index</button>
            </div>
        </div>

        <div class="mb-5">
            <button id="calculateTotalByDriver" class="bg-blue-500 text-white px-4 py-2 rounded">
                <i class="fas fa-calculator"></i> Hitung Total Berdasarkan Driver</button>
            <div id="totalDisplay" class="mt-3 text-lg font-bold"></div>
        </div>

        <div id="driverCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Driver cards will be added here -->
        </div>

        <table class="min-w-full bg-white border border-gray-300 mt-5 shadow-lg rounded-lg overflow-hidden">
            <thead class="bg-gray-200">
                <tr>
                    <th class="py-2 px-4 border-b">No</th>
                    <th class="py-2 px-4 border-b">Nama Driver</th>
                    <th class="py-2 px-4 border-b">Total Orderan</th>
                    <th class="py-2 px-4 border-b">Uang Orderan</th>
                    <th class="py-2 px-4 border-b">Uang Kas</th>
                    <th class="py-2 px-4 border-b">Tanggal dan Hari</th>
                    <th class="py-2 px-4 border-b">Status Pembayaran</th>
                    <th class="py-2 px-4 border-b">Total</th>
                    <th class="py-2 px-4 border-b">Aksi</th>
                </tr>
            </thead>
            <tbody id="rekapanTableBody">
                <!-- Data will be added here -->
            </tbody>
        </table>

        <!-- Alert Container -->
        <div id="alertContainer" class="fixed top-0 right-0 mt-4 mr-4 z-50"></div>
    </div>

    <!-- Modal for adding data -->
    <div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-5 rounded shadow-lg w-11/12 md:w-1/3">
            <h2 class="text-xl font-bold mb-4">Tambah Rekap Setoran</h2>
            <div class="mb-5">
                <label for="driverSelect" class="block mb-2">Pilih Driver:</label>
                <select id="driverSelect" class="border border-gray-300 rounded p-2">
                    <option value="">-- Pilih Driver --</option>
                </select>
            </div>

            <div class="mb-5">
                <label for="totalOrders" class="block mb-2">Total Orderan:</label>
                <input type="number" id="totalOrders" class="border border-gray-300 rounded p-2"
                    placeholder="Masukkan Total Orderan" oninput="calculateUangOrderan()">
            </div>

            <div class="mb-5">
                <label for="uangOrderan" class="block mb-2">Uang Orderan:</label>
                <input type="number" id="uangOrderan" class="border border-gray-300 rounded p-2"
                    placeholder="Uang Orderan" readonly>
            </div>

            <div class="mb-5">
                <label for="uangKas" class="block mb-2">Uang Kas:</label>
                <input type="number" id="uangKas" class="border border-gray-300 rounded p-2"
                    placeholder="Masukkan Uang Kas">
            </div>

            <div class="mb-5">
                <label for="date" class="block mb-2">Tanggal dan Hari:</label>
                <input type="date" id="date" class="border border-gray-300 rounded p-2" placeholder="YYYY-MM-DD">
            </div>

            <div class="mb-5">
                <label for="paymentStatus" class="block mb-2">Status Pembayaran:</label>
                <select id="paymentStatus" class="border border-gray-300 rounded p-2">
                    <option value="bayar">Sudah Bayar</option>
                    <option value="belum">Belum Bayar</option>
                </select>
            </div>

            <button id="addRecord" class="bg-green-500 text-white px-4 py-2 rounded">
                <i class="fas fa-save"></i> Simpan</button>
            <button id="closeModal" class="bg-red-500 text-white px-4 py-2 rounded ml-2">
                <i class="fas fa-times"></i> Batal</button>
        </div>
    </div>

    <script>
        // Fetch driver data from data_driver.php
        async function fetchDrivers() {
            try {
                const response = await fetch('data/data_drivers.json');
                if (!response.ok) throw new Error('Gagal memuat data driver');
                const drivers = await response.json();
                const driverSelect = document.getElementById('driverSelect');

                // Clear existing options
                driverSelect.innerHTML = '<option value="">-- Pilih Driver --</option>'; // Reset to default option

                drivers.forEach(driver => {
                    const option = document.createElement('option');
                    option.value = driver.id;
                    option.textContent = driver.name;
                    driverSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error fetching drivers:', error);
                showAlert('Gagal memuat data driver!', 'error');
            }
        }

        // Fetch data from server and update the table
        async function fetchDataAndUpdateTable() {
            try {
                const response = await fetch('data/data_setoran.json');
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const records = await response.json();
                // Clear existing rows
                const rekapanTableBody = document.getElementById('rekapanTableBody');
                rekapanTableBody.innerHTML = ''; // Clear existing rows

                // Add each record to the table
                records.forEach(record => addRowToTable(record));
            } catch (error) {
                console.error('Error fetching data:', error);
                showAlert('Gagal memuat data setoran!', 'error');
            }
        }

        // Show modal for adding data
        document.getElementById('openModal').addEventListener('click', () => {
            document.getElementById('modal').classList.remove('hidden');
            resetInputFields(); // Reset input fields when modal opens
        });

        // Close modals
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('modal').classList.add('hidden');
        });

        // Calculate Uang Orderan based on Total Orderan
        function calculateUangOrderan() {
            const totalOrders = document.getElementById('totalOrders').value;
            const uangOrderan = totalOrders * 2000; // Assuming each order is worth 2000
            document.getElementById('uangOrderan').value = uangOrderan;
        }

        // Add deposit record to the table
        document.getElementById('addRecord').addEventListener('click', async () => {
            const driverSelect = document.getElementById('driverSelect');
            const totalOrders = document.getElementById('totalOrders').value;
            const uangOrderan = document.getElementById('uangOrderan').value;
            const uangKas = document.getElementById('uangKas').value;
            const date = document.getElementById('date').value;
            const paymentStatus = document.getElementById('paymentStatus').value;

            if (driverSelect.value && totalOrders && uangOrderan && uangKas && date) {
                const total = calculateTotal(uangOrderan, uangKas, paymentStatus);
                const row = {
                    no: rekapanTableBody.children.length + 1,
                    driver: driverSelect.options[driverSelect.selectedIndex].text,
                    totalOrders: totalOrders,
                    uangOrderan: uangOrderan,
                    uangKas: uangKas,
                    date: new Date(date).toLocaleDateString('id-ID', { weekday: 'long' }) + ', ' + new Date(date).toLocaleDateString('id-ID'),
                    paymentStatus: paymentStatus,
                    total: total
                };

                addRowToTable(row);
                const success = await saveToServer(row);
                if (success) {
                    // Close the modal only if the save was successful
                    document.getElementById('modal').classList.add('hidden');
                    showAlert('Data berhasil disimpan!', 'success'); // Show success alert
                } else {
                    showAlert('Terjadi kesalahan saat menyimpan data!', 'error'); // Show error alert
                }
            } else {
                showAlert('Silakan lengkapi semua field!', 'error'); // Show error alert
            }
        });

        // Calculate total based on payment status
        function calculateTotal(uangOrderan, uangKas, paymentStatus) {
            return paymentStatus === 'bayar' ?
                parseInt(uangOrderan) + parseInt(uangKas) :
                parseInt(uangKas); // Total for unpaid
        }

        // Add row to table
        function addRowToTable(row) {
            const rekapanTableBody = document.getElementById('rekapanTableBody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td class="py-2 px-4 border-b">${row.no}</td>
                <td class="py-2 px-4 border-b">${row.driver}</td>
                <td class="py-2 px-4 border-b">${row.totalOrders}</td>
                <td class="py-2 px-4 border-b">Rp ${parseInt(row.uangOrderan).toLocaleString()}</td>
                <td class="py-2 px-4 border-b">Rp ${parseInt(row.uangKas).toLocaleString()}</td>
                <td class="py-2 px-4 border-b">${row.date}</td>
                <td class="py-2 px-4 border-b">${row.paymentStatus === 'belum' ? 'Belum Bayar' : 'Sudah Bayar'}</td>
                <td class="py-2 px-4 border-b">Rp ${parseInt(row.total).toLocaleString()}</td>
                <td class="py-2 px-4 border-b">
                    <button class="bg-red-500 text-white px-2 py-1 rounded deleteRecord">
                        <i class="fas fa-trash"></i> Hapus</button>
                </td>
            `;
            rekapanTableBody.appendChild(newRow);

            // Add event listener for Delete button
            newRow.querySelector('.deleteRecord').addEventListener('click', async () => {
                const confirmed = confirm(' Apakah Anda yakin ingin menghapus data ini?');
                if (confirmed) {
                    const success = await deleteFromServer(row.driver, row.date);
                    if (success) {
                        newRow.remove();
                        updateRowNumbers();
                        showAlert('Data berhasil dihapus!', 'success'); // Show success alert
                    } else {
                        showAlert('Terjadi kesalahan saat menghapus data!', 'error'); // Show error alert
                    }
                }
            });
        }

        // Save data to server
        async function saveToServer(row) {
            try {
                const response = await fetch('data/save_data_setoran.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ rekapan: row })
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal menyimpan data');
                }
                return true; // Return success status
            } catch (error) {
                console.error('Error saving data:', error);
                showAlert(error.message, 'error'); // Show error alert
                return false; // Return false on error
            }
        }

        // Fetch data from server on page load
        window.onload = async function () {
            await fetchDrivers();
            await fetchDataAndUpdateTable(); // Fetch data on initial load
        };

        // Delete data from server
        async function deleteFromServer(driver, date) {
            try {
                const response = await fetch('data/save_data_setoran.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ hapus: { driver: driver, date: date } })
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal menghapus data');
                }
                return true; // Return success status
            } catch (error) {
                console.error('Error deleting data:', error);
                showAlert(error.message, 'error'); // Show error alert
                return false; // Return false on error
            }
        }

        // Update row numbers after deletion
        function updateRowNumbers() {
            const rows = rekapanTableBody.getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                rows[i].cells[0].textContent = i + 1;
            }
        }

        // Reset input fields
        function resetInputFields() {
            document.getElementById('driverSelect').value = '';
            document.getElementById('totalOrders').value = '';
            document.getElementById('uangOrderan').value = '';
            document.getElementById('uangKas').value = '';
            document.getElementById('date').value = '';
            document.getElementById('paymentStatus').value = 'bayar';
        }

        // Filter table based on driver name and date
        function filterTable() {
            const searchDriver = document.getElementById('searchDriver').value.toLowerCase();
            const searchDate = document.getElementById('searchDate').value;
            const rows = document.querySelectorAll('#rekapanTableBody tr');

            rows.forEach(row => {
                const driverCell = row.cells[1].textContent.toLowerCase();
                const dateCell = row.cells[5].textContent.split(', ')[1]; // Get date from format "Day, Date"

                const driverMatch = driverCell.includes(searchDriver);
                const dateMatch = searchDate ? dateCell === new Date(searchDate).toLocaleDateString('id-ID') : true;

                row.style.display = (driverMatch && dateMatch) ? '' : 'none';
            });
        }

        // Calculate total for selected driver
        document.getElementById('calculateTotalByDriver').addEventListener('click', () => {
            const searchDriver = document.getElementById('searchDriver').value.toLowerCase();
            const rows = document.querySelectorAll('#rekapanTableBody tr');
            let total = 0;

            rows.forEach(row => {
                const driverCell = row.cells[1].textContent.toLowerCase();
                if (driverCell.includes(searchDriver)) {
                    const totalCell = row.cells[7].textContent.replace(/Rp |,/g, ''); // Remove 'Rp ' and commas
                    total += parseInt(totalCell);
                }
            });

            document.getElementById('totalDisplay').textContent = `Total untuk ${searchDriver}: Rp ${total.toLocaleString()}`;
        });

        // Show alert with animation
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `transition-opacity duration-500 ease-in-out opacity-0 bg-${type === 'success' ? 'green' : 'red'}-500 text-white p-4 rounded shadow-lg mb-4`;
            alertDiv.innerHTML = `
                <span>${message}</span>
                <button class="ml-4 text-white font-bold" onclick="this.parentElement.remove();">×</button>
            `;
            alertContainer.appendChild(alertDiv);

            // Trigger reflow to enable transition
            requestAnimationFrame(() => {
                alertDiv.classList.remove('opacity-0');
                alertDiv.classList.add('opacity-100');
            });

            // Automatically remove alert after 3 seconds
            setTimeout(() => {
                alertDiv.classList.remove('opacity-100');
                alertDiv.classList.add('opacity-0');
                setTimeout(() => {
                    alertDiv.remove();
                }, 500); // Wait for the fade-out transition to finish
            }, 3000);
        }

        // Export to Excel functionality
        async function exportToExcel() {
            try {
                const response = await fetch('data/data_setoran.json');
                if (!response.ok) throw new Error('Gagal memuat data untuk ekspor');
                const records = await response.json();

                // Create CSV content
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "No,Nama Driver,Total Orderan,Uang Orderan,Uang Kas,Tanggal dan Hari,Status Pembayaran,Total\n"; // Header

                records.forEach((record, index) => {
                    const row = [
                        index + 1,
                        record.driver,
                        record.totalOrders,
                        record.uangOrderan,
                        record.uangKas,
                        record.date,
                        record.paymentStatus === 'belum' ? 'Belum Bayar' : 'Sudah Bayar',
                        record.total
                    ].map(item => `"${item}"`).join(","); // Join values with commas and wrap in quotes
                    csvContent += row + "\n"; // Add row to CSV content
                });

                // Create a link to download the CSV file
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "rekapan_setoran.csv");
                document.body.appendChild(link); // Required for FF

                link.click(); // This will download the data file named "rekapan_setoran.csv"
                document.body.removeChild(link); // Clean up
            } catch (error) {
                console.error('Error exporting data:', error);
                showAlert('Gagal mengekspor data!', 'error');
            }
        }
    </script>

</body>

</html>