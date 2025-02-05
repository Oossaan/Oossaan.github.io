<?php
// Start session
session_start();

// Define constants for response codes and messages
define('HTTP_UNAUTHORIZED', 403);
define('HTTP_CONFLICT', 409);
define('HTTP_NOT_FOUND', 404);
define('HTTP_BAD_REQUEST', 400);
define('DATA_FILE_PATH', 'data_setoran.json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(HTTP_UNAUTHORIZED);
    echo json_encode(['message' => 'Unauthorized']);
    exit();
}

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Validate data
if (isset($data['rekapan'])) {
    handleAddRecord($data['rekapan']);
} elseif (isset($data['hapus'])) {
    handleDeleteRecord($data['hapus']);
} else {
    http_response_code(HTTP_BAD_REQUEST);
    echo json_encode(['message' => 'Data tidak valid']);
}

/**
 * Handle adding a new record
 */
function handleAddRecord($rekapan)
{
    $filePath = DATA_FILE_PATH;

    // Validate rekapan structure
    if (!validateRekapan($rekapan)) {
        http_response_code(HTTP_BAD_REQUEST);
        echo json_encode(['message' => 'Data rekapan tidak lengkap']);
        return;
    }

    // Load existing data
    $existingData = loadExistingData($filePath);

    // Check for duplicates
    if (isDuplicateRecord($existingData, $rekapan)) {
        http_response_code(HTTP_CONFLICT);
        echo json_encode(['message' => 'Data sudah ada untuk driver dan tanggal ini.']);
        return;
    }

    // Add new record
    $existingData[] = $rekapan;

    // Save back to file
    saveDataToFile($filePath, $existingData);
    echo json_encode(['message' => 'Data berhasil disimpan']);
}

/**
 * Handle deleting a record
 */
function handleDeleteRecord($hapus)
{
    $filePath = DATA_FILE_PATH;

    // Load existing data
    $existingData = loadExistingData($filePath);

    // Find the index of the record to delete
    $index = findRecordIndex($existingData, $hapus);

    if ($index !== null) {
        // Delete the record
        unset($existingData[$index]);

        // Reindex the array
        $existingData = array_values($existingData);

        // Save back to file
        saveDataToFile($filePath, $existingData);
        echo json_encode(['message' => 'Data berhasil dihapus']);
    } else {
        http_response_code(HTTP_NOT_FOUND);
        echo json_encode(['message' => 'Data tidak ditemukan']);
    }
}

/**
 * Load existing data from the JSON file
 */
function loadExistingData($filePath)
{
    if (file_exists($filePath)) {
        $data = file_get_contents($filePath);
        return json_decode($data, true) ?: [];
    }
    return [];
}

/**
 * Check if the record already exists
 */
function isDuplicateRecord($existingData, $rekapan)
{
    foreach ($existingData as $record) {
        if (
            filter_var($record['driver'], FILTER_SANITIZE_STRING) === filter_var($rekapan['driver'], FILTER_SANITIZE_STRING) &&
            filter_var($record['date'], FILTER_SANITIZE_STRING) === filter_var($rekapan['date'], FILTER_SANITIZE_STRING)
        ) {
            return true; // Duplicate found
        }
    }
    return false; // No duplicates
}

/**
 * Find the index of a record to delete
 */
function findRecordIndex($existingData, $hapus)
{
    foreach ($existingData as $key => $record) {
        if (
            filter_var($record['driver'], FILTER_SANITIZE_STRING) === filter_var($hapus['driver'], FILTER_SANITIZE_STRING) &&
            filter_var($record['date'], FILTER_SANITIZE_STRING) === filter_var($hapus['date'], FILTER_SANITIZE_STRING)
        ) {
            return $key; // Record found
        }
    }
    return null; // Record not found
}

/**
 * Save data back to the JSON file
 */
function saveDataToFile($filePath, $data)
{
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Validate rekapan structure
 */
function validateRekapan($rekapan)
{
    return isset($rekapan['driver'], $rekapan['date'], $rekapan['totalOrders'], $rekapan['uangOrderan'], $rekapan['uangKas'], $rekapan['paymentStatus']);
}
?>