<?php
// فایل: take_quiz.php (کامل و نهایی)
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

$quiz_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$quiz_id) {
    die("شناسه آزمون نامعتبر است.");
}

$stmt_quiz_info = $pdo->prepare("SELECT title FROM Quizzes WHERE id = ?");
$stmt_quiz_info->execute([$quiz_id]);
$quiz_title = $stmt_quiz_info->fetchColumn();
if (!$quiz_title) {
    die("آزمون یافت نشد.");
}

$stmt = $pdo->prepare("
    SELECT q.id as question_id, q.question_text, a.id as answer_id, a.answer_text
    FROM Questions q
    JOIN QuizQuestions qq ON q.id = qq.question_id
    JOIN Answers a ON q.id = a.question_id
    WHERE qq.quiz_id = ?
    ORDER BY q.id, a.id
");
$stmt->execute([$quiz_id]);
$flat_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$questions = [];
foreach ($flat_results as $row) {
    if (!isset($questions[$row['question_id']])) {
        $questions[$row['question_id']] = [
            'id' => $row['question_id'],
            'text' => $row['question_text'],
            'answers' => []
        ];
    }
    $questions[$row['question_id']]['answers'][] = ['id' => $row['answer_id'], 'text' => $row['answer_text']];
}
$quiz_data = array_values($questions);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>آزمون: <?= htmlspecialchars($quiz_title) ?></title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --bg-color: #f7f9fa;
            --card-bg: #fff;
            --text-color: #1a1a1a;
            --secondary-text: #555;
            --header-text: #fff;
            --border-color: #e9e9e9;
            --radius: 12px;
            --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
            --shadow-md: 0 6px 20px rgba(0, 120, 80, .10);
        }

        @font-face {
            font-family: "Vazirmatn";
            src: url("/assets/fonts/Vazirmatn[wght].ttf") format("truetype");
            font-weight: 100 900;
            font-display: swap;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Vazirmatn", system-ui, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            direction: rtl;
            background: var(--bg-color);
            color: var(--text-color);
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: all .2s ease;
        }

        header,
        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 10;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
        }

        header {
            min-height: 70px;
        }

        footer {
            min-height: 60px;
            font-size: .85rem;
            justify-content: center;
        }

        header h1 {
            font-weight: 700;
            font-size: 1.2rem;
        }

        main {
            flex: 1;
            width: min(900px, 100%);
            padding: 2.5rem 2rem;
            margin-inline: auto;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: .5rem;
        }

        .page-subtitle {
            color: var(--secondary-text);
            font-weight: 400;
            font-size: 1rem;
        }

        .btn {
            display: inline-block;
            padding: .75rem 1.25rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: .95rem;
            font-weight: 600;
            text-align: center;
            margin: 5px 0;
            transition: all .2s;
        }

        .btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover:not(:disabled) {
            background-color: #5a6268;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background-color: #218838;
        }

        .hidden {
            display: none;
        }

        .quiz-container {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: var(--border-color);
            border-radius: 5px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .progress-bar-inner {
            height: 100%;
            width: 0;
            background-color: var(--primary-color);
            transition: width .3s ease;
        }

        .question-text {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .answer-option {
            position: relative;
            margin-bottom: .75rem;
        }

        .answer-label {
            display: flex;
            align-items: center;
            padding: .75rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: border-color .2s, background-color .2s;
        }

        .answer-label:hover {
            border-color: #ccc;
        }

        .answer-correct-radio {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .radio-custom {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #ccc;
            display: grid;
            place-items: center;
            margin-left: .75rem;
            transition: border-color .2s;
        }

        .radio-custom::before {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--primary-color);
            transform: scale(0);
            transition: transform .2s ease-in-out;
        }

        .answer-text-display {
            flex-grow: 1;
            font-size: 1rem;
        }

        .answer-correct-radio:checked+.answer-label {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
        }

        .answer-correct-radio:checked+.answer-label .radio-custom {
            border-color: var(--primary-color);
        }

        .answer-correct-radio:checked+.answer-label .radio-custom::before {
            transform: scale(1);
        }

        .quiz-nav {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
        }

        .result-view {
            text-align: center;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div id="quiz-container" class="quiz-container">
            <div class="progress-bar">
                <div id="progress-bar-inner" class="progress-bar-inner"></div>
            </div>
            <h2 id="question-text" class="question-text"></h2>
            <div id="answers-container"></div>
            <div class="quiz-nav">
                <button id="prev-btn" class="btn btn-secondary">سوال قبلی</button>
                <button id="next-btn" class="btn btn-primary">سوال بعدی</button>
            </div>
        </div>

        <div id="result-view" class="quiz-container hidden">
            <h1 class="page-title">ثبت موفق</h1>
            <p class="page-subtitle" style="font-size: 1.1rem; line-height: 1.8;">
                آزمون شما با موفقیت ثبت شد. نتیجه آن به‌زودی توسط مدیر سیستم بررسی و اعلام خواهد شد.
            </p>
            <a href="index.php" class="btn btn-primary" style="margin-top: 2rem;">بازگشت به لیست آزمون‌ها</a>
        </div>
    </main>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js?v=1.0"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const quizData = <?= json_encode($quiz_data) ?>;
            const quizId = <?= $quiz_id ?>;
            let currentQuestionIndex = 0;
            const userAnswers = {}; // { questionId: answerId }

            const questionTextEl = document.getElementById('question-text');
            const answersContainerEl = document.getElementById('answers-container');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            const progressBar = document.getElementById('progress-bar-inner');

            const renderQuestion = (index) => {
                const question = quizData[index];
                questionTextEl.textContent = `سوال ${index + 1}: ${question.text}`;
                answersContainerEl.innerHTML = '';

                question.answers.forEach((answer) => {
                    const uniqueId = `ans-${question.id}-${answer.id}`;
                    const div = document.createElement('div');
                    div.className = 'answer-option';
                    div.innerHTML = `
                        <input type="radio" name="answer" value="${answer.id}" class="answer-correct-radio" id="${uniqueId}">
                        <label for="${uniqueId}" class="answer-label">
                            <span class="radio-custom"></span>
                            <span class="answer-text-display">${answer.text}</span>
                        </label>
                    `;
                    answersContainerEl.appendChild(div);
                });

                if (userAnswers[question.id]) {
                    const radioToCheck = document.querySelector(`input[value="${userAnswers[question.id]}"]`);
                    if (radioToCheck) radioToCheck.checked = true;
                }

                updateNavButtons();
                updateProgressBar();
            };

            const updateNavButtons = () => {
                prevBtn.disabled = currentQuestionIndex === 0;
                if (currentQuestionIndex === quizData.length - 1) {
                    nextBtn.textContent = 'پایان و ثبت نهایی';
                    nextBtn.classList.remove('btn-primary');
                    nextBtn.classList.add('btn-success');
                } else {
                    nextBtn.textContent = 'سوال بعدی';
                    nextBtn.classList.add('btn-primary');
                    nextBtn.classList.remove('btn-success');
                }
            };

            const updateProgressBar = () => {
                const progress = ((currentQuestionIndex + 1) / quizData.length) * 100;
                progressBar.style.width = `${progress}%`;
            };

            answersContainerEl.addEventListener('change', e => {
                if (e.target.name === 'answer') {
                    userAnswers[quizData[currentQuestionIndex].id] = parseInt(e.target.value);
                }
            });

            nextBtn.addEventListener('click', async () => {
                if (currentQuestionIndex < quizData.length - 1) {
                    currentQuestionIndex++;
                    renderQuestion(currentQuestionIndex);
                } else {
                    if (confirm('آیا از پایان آزمون و ثبت نهایی پاسخ‌ها مطمئن هستید؟')) {
                        await submitQuiz();
                    }
                }
            });

            prevBtn.addEventListener('click', () => {
                if (currentQuestionIndex > 0) {
                    currentQuestionIndex--;
                    renderQuestion(currentQuestionIndex);
                }
            });

            const submitQuiz = async () => {
                nextBtn.disabled = true;
                prevBtn.disabled = true;

                const data = {
                    quizId: quizId,
                    answers: userAnswers
                };

                const response = await fetch('quiz_api.php?action=submit_attempt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    document.getElementById('quiz-container').classList.add('hidden');
                    document.getElementById('result-view').classList.remove('hidden');
                } else {
                    alert('خطا در ثبت نتایج: ' + (result.message || 'خطای نامشخص'));
                    nextBtn.disabled = false;
                    prevBtn.disabled = false;
                }
            };

            if (quizData.length > 0) {
                renderQuestion(0);
            } else {
                document.getElementById('quiz-container').innerHTML = '<h1>سوالی برای این آزمون یافت نشد.</h1>';
            }
        });
    </script>
</body>

</html>
