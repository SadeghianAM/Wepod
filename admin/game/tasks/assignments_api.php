<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../auth/require-auth.php';
$claims = requireAuth('admin');
require_once __DIR__ . '/../../../db/database.php';

$action = $_REQUEST['action'] ?? null;
$response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];

try {
    if (!$pdo->inTransaction()) $pdo->beginTransaction();

    switch ($action) {
        case 'get_active_scenarios_and_teams':
            $stmt_scenarios = $pdo->query("SELECT id, title FROM Scenarios ORDER BY title");
            $scenarios = $stmt_scenarios->fetchAll(PDO::FETCH_ASSOC);
            $stmt_teams = $pdo->query("SELECT id, team_name FROM Teams ORDER BY team_name");
            $teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'scenarios' => $scenarios, 'teams' => $teams];
            break;

        case 'create_assignment':
            $scenario_id = filter_input(INPUT_POST, 'scenario_id', FILTER_VALIDATE_INT);
            $team_id = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
            if (!$scenario_id || !$team_id) throw new Exception('انتخاب سناریو و تیم الزامی است.');
            $stmt_check = $pdo->prepare("SELECT id FROM ScenarioAssignments WHERE scenario_id = ? AND team_id = ?");
            $stmt_check->execute([$scenario_id, $team_id]);
            if ($stmt_check->fetch()) throw new Exception('این سناریو قبلاً به این تیم تخصیص داده شده است.');
            $stmt = $pdo->prepare("INSERT INTO ScenarioAssignments (scenario_id, team_id) VALUES (?, ?)");
            $stmt->execute([$scenario_id, $team_id]);
            $response = ['success' => true, 'message' => 'سناریو با موفقیت به تیم تخصیص داده شد.'];
            break;

        case 'delete_assignment':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه تخصیص نامعتبر است.');
            $stmt = $pdo->prepare("DELETE FROM ScenarioAssignments WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['success' => true, 'message' => 'تخصیص با موفقیت حذف شد.'];
            break;

        case 'toggle_assignment_status':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه نامعتبر است.');

            $stmt = $pdo->prepare("UPDATE ScenarioAssignments SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);

            $stmt_status = $pdo->prepare("SELECT is_active FROM ScenarioAssignments WHERE id = ?");
            $stmt_status->execute([$id]);
            $new_status = $stmt_status->fetchColumn();

            $response = ['success' => true, 'message' => 'وضعیت پاسخ‌دهی تغییر کرد.', 'is_active' => $new_status];
            break;
    }

    if ($pdo->inTransaction()) $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
