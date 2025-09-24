<?php
// فایل: quizzes_api.php (نسخه نهایی با قابلیت تخصیص)
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/require-auth.php';
$claims = requireAuth('admin', '/../auth/login.html');
require_once __DIR__ . '/../../db/database.php';

$action = $_REQUEST['action'] ?? null;
$response = ['success' => false, 'message' => 'عملیات نامعتبر است.'];

try {
    switch ($action) {
        case 'get_quiz':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه آزمون نامعتبر است.');

            $stmt_quiz = $pdo->prepare("SELECT * FROM Quizzes WHERE id = ?");
            $stmt_quiz->execute([$id]);
            $quiz = $stmt_quiz->fetch(PDO::FETCH_ASSOC);

            if ($quiz) {
                $stmt_q = $pdo->prepare("SELECT question_id FROM QuizQuestions WHERE quiz_id = ?");
                $stmt_q->execute([$id]);
                $quiz['questions'] = $stmt_q->fetchAll(PDO::FETCH_COLUMN, 0);

                $stmt_t = $pdo->prepare("SELECT team_id FROM QuizTeamAssignments WHERE quiz_id = ?");
                $stmt_t->execute([$id]);
                $quiz['assigned_teams'] = $stmt_t->fetchAll(PDO::FETCH_COLUMN, 0);

                $stmt_u = $pdo->prepare("SELECT user_id FROM QuizUserAssignments WHERE quiz_id = ?");
                $stmt_u->execute([$id]);
                $quiz['assigned_users'] = $stmt_u->fetchAll(PDO::FETCH_COLUMN, 0);

                $response = ['success' => true, 'quiz' => $quiz];
            } else {
                throw new Exception('آزمون یافت نشد.');
            }
            break;

        case 'create_quiz':
        case 'update_quiz':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['title']) || !isset($data['questions'])) throw new Exception('داده‌های ارسالی ناقص است.');

            $pdo->beginTransaction();

            if ($action === 'update_quiz') {
                $id = $data['id'];
                $stmt = $pdo->prepare("UPDATE Quizzes SET title = ?, description = ? WHERE id = ?");
                $stmt->execute([$data['title'], $data['description'], $id]);

                $pdo->prepare("DELETE FROM QuizQuestions WHERE quiz_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM QuizTeamAssignments WHERE quiz_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM QuizUserAssignments WHERE quiz_id = ?")->execute([$id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO Quizzes (title, description) VALUES (?, ?)");
                $stmt->execute([$data['title'], $data['description']]);
                $id = $pdo->lastInsertId();
            }

            $stmt_q_link = $pdo->prepare("INSERT INTO QuizQuestions (quiz_id, question_id) VALUES (?, ?)");
            foreach ($data['questions'] as $q_id) {
                $stmt_q_link->execute([$id, $q_id]);
            }

            if (!empty($data['assigned_teams'])) {
                $stmt_t_link = $pdo->prepare("INSERT INTO QuizTeamAssignments (quiz_id, team_id) VALUES (?, ?)");
                foreach ($data['assigned_teams'] as $t_id) {
                    $stmt_t_link->execute([$id, $t_id]);
                }
            }

            if (!empty($data['assigned_users'])) {
                $stmt_u_link = $pdo->prepare("INSERT INTO QuizUserAssignments (quiz_id, user_id) VALUES (?, ?)");
                foreach ($data['assigned_users'] as $u_id) {
                    $stmt_u_link->execute([$id, $u_id]);
                }
            }

            $pdo->commit();

            $new_quiz_data = ['id' => $id, 'title' => $data['title']];
            $response = ['success' => true, 'message' => 'عملیات با موفقیت انجام شد.', 'quiz' => $new_quiz_data];
            break;

        case 'delete_quiz':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('شناسه آزمون نامعتبر است.');
            $stmt = $pdo->prepare("DELETE FROM Quizzes WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['success' => $stmt->rowCount() > 0, 'message' => 'آزمون حذف شد.'];
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
