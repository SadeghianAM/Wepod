<?php
// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');
$action = $_GET['action'] ?? '';

// --- Logging Function ---
// Writes a formatted message to a log file.
function write_to_log(string $message): void
{
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] - " . $message . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// --- Centralized Authentication for Write Actions & Secure Reads ---
$write_actions = ['add_asset', 'assign_asset', 'return_asset', 'delete_asset'];
$admin_username = 'system'; // Default user for logs if auth fails somehow
if (in_array($action, $write_actions) || $action === 'get_experts') {
    require_once __DIR__ . '/../../auth/require-auth.php';
    $claims = requireAuth('admin');
    $admin_username = $claims['username'] ?? 'unknown_admin';
}

// --- Database Connection ---
try {
    $db_dir = __DIR__ . '/database';
    if (!is_dir($db_dir)) mkdir($db_dir, 0755, true);
    $db_file = $db_dir . '/assets.sqlite';
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    http_response_code(500);
    write_to_log("FATAL: Database connection failed: " . $e->getMessage());
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// --- Helper Functions ---
function loadUsers(): array
{
    $path = $_SERVER['DOCUMENT_ROOT'] . '/data/users.json';
    if (file_exists($path)) {
        $json = json_decode(file_get_contents($path), true);
        return is_array($json) ? $json : [];
    }
    return [];
}
function create_tables($pdo)
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS assets (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, serial_number TEXT UNIQUE NOT NULL, status TEXT NOT NULL DEFAULT 'In Stock', assigned_to_username TEXT, assigned_to_name TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS history (id INTEGER PRIMARY KEY AUTOINCREMENT, asset_id INTEGER NOT NULL, action TEXT NOT NULL, details TEXT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE)");
}

// --- Main API Logic ---
try {
    create_tables($pdo);
    switch ($action) {
        case 'get_experts':
            $users = loadUsers();
            $experts = [];
            foreach ($users as $user) {
                if (isset($user['username']) && isset($user['name'])) {
                    $experts[] = ['username' => $user['username'], 'name' => $user['name']];
                }
            }
            echo json_encode($experts);
            break;

        case 'get_assets':
            $stmt = $pdo->query("SELECT * FROM assets ORDER BY created_at DESC");
            echo json_encode($stmt->fetchAll());
            break;

        case 'add_asset':
            $data = json_decode(file_get_contents('php://input'), true);
            $name = htmlspecialchars(trim($data['name']));
            $serial = htmlspecialchars(trim($data['serial']));
            if (empty($name) || empty($serial)) {
                throw new Exception("Name and serial cannot be empty.");
            }
            $stmt = $pdo->prepare("INSERT INTO assets (name, serial_number) VALUES (?, ?)");
            $stmt->execute([$name, $serial]);
            $asset_id = $pdo->lastInsertId();
            write_to_log("Admin '{$admin_username}' CREATED asset '{$name}' (SN: {$serial}), ID: {$asset_id}.");
            echo json_encode(['success' => true, 'id' => $asset_id]);
            break;

        case 'assign_asset':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmtAsset = $pdo->prepare("SELECT name, serial_number FROM assets WHERE id = ?");
            $stmtAsset->execute([$data['asset_id']]);
            $asset = $stmtAsset->fetch();

            $stmt = $pdo->prepare("UPDATE assets SET status = 'Assigned', assigned_to_username = ?, assigned_to_name = ? WHERE id = ?");
            $stmt->execute([$data['username'], $data['user_name'], $data['asset_id']]);
            write_to_log("Admin '{$admin_username}' ASSIGNED asset '{$asset['name']}' (SN: {$asset['serial_number']}) to user '{$data['user_name']}'.");
            echo json_encode(['success' => true]);
            break;

        case 'return_asset':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmtAsset = $pdo->prepare("SELECT name, serial_number, assigned_to_name FROM assets WHERE id = ?");
            $stmtAsset->execute([$data['asset_id']]);
            $asset = $stmtAsset->fetch();
            $previous_user = $asset['assigned_to_name'] ?? 'N/A';

            $stmt = $pdo->prepare("UPDATE assets SET status = 'In Stock', assigned_to_username = NULL, assigned_to_name = NULL WHERE id = ?");
            $stmt->execute([$data['asset_id']]);
            write_to_log("Admin '{$admin_username}' RETURNED asset '{$asset['name']}' (SN: {$asset['serial_number']}) from user '{$previous_user}'.");
            echo json_encode(['success' => true]);
            break;

        case 'delete_asset':
            $data = json_decode(file_get_contents('php://input'), true);
            $asset_id = $data['asset_id'];

            $stmt = $pdo->prepare("SELECT name, serial_number, status FROM assets WHERE id = ?");
            $stmt->execute([$asset_id]);
            $asset = $stmt->fetch();

            if (!$asset) {
                throw new Exception("Asset not found.");
            }
            if ($asset['status'] === 'Assigned') {
                http_response_code(409); // Conflict
                echo json_encode(['error' => 'امکان حذف کالا وجود ندارد زیرا به یک کارشناس تخصیص داده شده است.']);
                exit();
            }

            $stmt = $pdo->prepare("DELETE FROM assets WHERE id = ?");
            $stmt->execute([$asset_id]);
            write_to_log("Admin '{$admin_username}' DELETED asset '{$asset['name']}' (SN: {$asset['serial_number']}), ID: {$asset_id}.");
            echo json_encode(['success' => true, 'message' => 'Asset deleted successfully.']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => "Invalid action '{$action}' specified."]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    write_to_log("ERROR: " . $e->getMessage());
    echo json_encode(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
}
