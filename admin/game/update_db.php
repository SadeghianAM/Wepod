<?php
// File: update_db.php
require_once 'database.php'; // Connects to the DB

try {
    $sql = "
        -- Table to link quizzes to teams
        CREATE TABLE IF NOT EXISTS QuizTeamAssignments (
            quiz_id INTEGER NOT NULL,
            team_id INTEGER NOT NULL,
            PRIMARY KEY (quiz_id, team_id),
            FOREIGN KEY (quiz_id) REFERENCES Quizzes(id) ON DELETE CASCADE,
            FOREIGN KEY (team_id) REFERENCES Teams(id) ON DELETE CASCADE
        );

        -- Table to link quizzes to specific users
        CREATE TABLE IF NOT EXISTS QuizUserAssignments (
            quiz_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            PRIMARY KEY (quiz_id, user_id),
            FOREIGN KEY (quiz_id) REFERENCES Quizzes(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
        );
    ";

    $pdo->exec($sql);

    echo "<h1>Success!</h1><p>The two new tables, QuizTeamAssignments and QuizUserAssignments, were created successfully.</p>";
} catch (Exception $e) {
    die("<h1>Error!</h1><p>An error occurred while creating the tables: " . $e->getMessage() . "</p>");
}
