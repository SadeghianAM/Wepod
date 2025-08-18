<?php
// Set the correct header for JSON response, ensuring UTF-8 encoding for Persian characters.
header('Content-Type: application/json; charset=utf-8');

// Define the path to the data file, relative to this PHP script's location.
// __DIR__ gives the directory of the current file (/php/), so we go one level up (../) and then into /data/.
$dataFile = __DIR__ . '/../data/disruptions.json';

// Function to read all records from the JSON file.
function get_records($file)
{
    // If the file doesn't exist, create it with an empty JSON array.
    if (!file_exists($file)) {
        file_put_contents($file, '[]');
    }
    $json = file_get_contents($file);
    return json_decode($json, true); // `true` converts it to an associative array.
}

// Function to save records back to the JSON file.
function save_records($file, $data)
{
    // JSON_PRETTY_PRINT makes the file human-readable.
    // JSON_UNESCAPED_UNICODE is crucial for saving Persian characters correctly.
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $json);
}

// Get the HTTP request method (e.g., GET or POST).
$method = $_SERVER['REQUEST_METHOD'];

// --- Handle GET requests (to fetch all data) ---
if ($method === 'GET') {
    $records = get_records($dataFile);
    echo json_encode($records);
}
// --- Handle POST requests (to create, update, or delete) ---
elseif ($method === 'POST') {
    $records = get_records($dataFile);

    // Determine the action: 'delete' or 'save' (save covers both create and update).
    $action = isset($_POST['action']) ? $_POST['action'] : 'save';

    if ($action === 'delete') {
        // --- DELETE LOGIC ---
        $id_to_delete = $_POST['id'];

        // Filter the array, keeping all records EXCEPT the one with the matching ID.
        $records = array_filter($records, function ($record) use ($id_to_delete) {
            return $record['id'] !== $id_to_delete;
        });

        // Re-index the array to prevent it from becoming a JSON object.
        $records = array_values($records);

        save_records($dataFile, $records);
        echo json_encode(['status' => 'success', 'message' => 'رکورد با موفقیت حذف شد.']);
    } else {
        // --- CREATE / UPDATE LOGIC ---
        $recordData = $_POST;
        $recordId = $recordData['id'] ?? null;

        if ($recordId && !empty($recordId)) {
            // --- UPDATE ---
            $found = false;
            foreach ($records as $key => $record) {
                if ($record['id'] === $recordId) {
                    // Replace the old record with the new data.
                    $records[$key] = $recordData;
                    $found = true;
                    break;
                }
            }
            if ($found) {
                save_records($dataFile, $records);
                echo json_encode(['status' => 'success', 'message' => 'رکورد با موفقیت به‌روزرسانی شد.']);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'رکورد برای به‌روزرسانی یافت نشد.']);
            }
        } else {
            // --- CREATE ---
            // Generate a unique ID for the new record.
            $recordData['id'] = 'rec_' . uniqid();
            // Add the new record to the end of the array.
            $records[] = $recordData;

            save_records($dataFile, $records);
            echo json_encode(['status' => 'success', 'message' => 'رکورد جدید با موفقیت ثبت شد.', 'data' => $recordData]);
        }
    }
} else {
    // If the request method is not GET or POST, send an error.
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'متد غیرمجاز.']);
}
