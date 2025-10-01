<?php
// فایل: take_quiz.php (نسخه کامل و نهایی با جلوگیری از شرکت مجدد)
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth(null, '/auth/login.html');
require_once __DIR__ . '/../db/database.php';

$quiz_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$quiz_id) {
    die("شناسه آزمون نامعتبر است.");
}

// ⭐ بررسی اینکه آیا کاربر قبلاً در این آزمون شرکت کرده است یا خیر
$user_id = $claims['sub']; // گرفتن شناسه کاربر از توکن احراز هویت

$stmt_check_attempt = $pdo->prepare("SELECT COUNT(*) FROM QuizAttempts WHERE user_id = ? AND quiz_id = ?");
$stmt_check_attempt->execute([$user_id, $quiz_id]);
$attempt_count = $stmt_check_attempt->fetchColumn();

if ($attempt_count > 0) {
    // اگر کاربر قبلاً شرکت کرده، پیام مناسب نمایش داده و اسکریپت را متوقف کن
    $stmt_quiz_info = $pdo->prepare("SELECT title FROM Quizzes WHERE id = ?");
    $stmt_quiz_info->execute([$quiz_id]);
    $quiz_title = $stmt_quiz_info->fetchColumn();
?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>خطا: آزمون قبلاً تکمیل شده</title>
        <style>
            :root {
                --primary-color: #00ae70;
                --primary-dark: #089863;
                --card-bg: #fff;
                --text-color: #1a1a1a;
                --border-color: #e9e9e9;
                --radius: 16px;
                --shadow-md: 0 8px 25px rgba(0, 120, 80, .12);
            }

            @font-face {
                font-family: "Vazirmatn";
                src: url("/assets/fonts/Vazirmatn[wght].ttf") format("truetype");
                font-weight: 100 900;
            }

            body {
                font-family: "Vazirmatn", sans-serif;
                background-color: #f7f9fa;
                display: grid;
                place-items: center;
                min-height: 100vh;
                margin: 0;
            }

            .message-box {
                background: var(--card-bg);
                padding: 2.5rem 3rem;
                border-radius: var(--radius);
                border: 1px solid var(--border-color);
                box-shadow: var(--shadow-md);
                text-align: center;
                max-width: 500px;
            }

            h1 {
                color: var(--primary-dark);
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }

            p {
                color: #555;
                margin-bottom: 2rem;
                line-height: 1.7;
            }

            .btn {
                display: inline-block;
                padding: .75rem 1.5rem;
                border-radius: 10px;
                background-color: var(--primary-color);
                color: white;
                text-decoration: none;
                font-weight: 600;
                transition: all .2s ease;
            }

            .btn:hover {
                background-color: var(--primary-dark);
                transform: translateY(-2px);
            }
        </style>
    </head>

    <body>
        <div class="message-box">
            <h1>شما قبلاً در این آزمون شرکت کرده‌اید</h1>
            <p>شما قبلاً آزمون "<?= htmlspecialchars($quiz_title ?: 'ناشناس') ?>" را به پایان رسانده‌اید و امکان شرکت مجدد وجود ندارد.</p>
            <a href="index.php" class="btn">بازگشت به لیست آزمون‌ها</a>
        </div>
    </body>

    </html>
<?php
    exit; // اجرای اسکریپت را به طور کامل متوقف می‌کند
}


$stmt_quiz_info = $pdo->prepare("SELECT title FROM Quizzes WHERE id = ?");
$stmt_quiz_info->execute([$quiz_id]);
$quiz_title = $stmt_quiz_info->fetchColumn();
if (!$quiz_title) {
    die("آزمون یافت نشد.");
}

// کوئری برای دریافت سوالات، گزینه‌ها و امتیازها
$stmt = $pdo->prepare("
    SELECT
        q.id as question_id, q.question_text, q.points_correct, q.points_incorrect,
        a.id as answer_id, a.answer_text
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
            'points_correct' => $row['points_correct'],
            'points_incorrect' => $row['points_incorrect'],
            'answers' => []
        ];
    }
    $questions[$row['question_id']]['answers'][] = ['id' => $row['answer_id'], 'text' => $row['answer_text']];
}

// تصادفی کردن ترتیب گزینه‌های هر سوال
foreach ($questions as &$question) {
    shuffle($question['answers']);
}
unset($question); // پاک کردن رفرنس

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
            --radius: 16px;
            --shadow-sm: 0 4px 12px rgba(0, 120, 80, .08);
            --shadow-md: 0 8px 25px rgba(0, 120, 80, .12);
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
            background-image: linear-gradient(to top, #f3fdf9 0%, #f7f9fa 100%);
            color: var(--text-color);
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: all .2s ease;
        }

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
            min-height: 60px;
            font-size: .85rem;
            justify-content: center;
        }

        main {
            flex: 1;
            width: min(900px, 95%);
            padding: 2.5rem 1rem;
            margin-inline: auto;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .75rem 1.5rem;
            border: 1px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            transition: all .25s ease;
        }

        .btn:disabled {
            background-color: #e9ecef !important;
            border-color: #dee2e6 !important;
            color: #adb5bd !important;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover:not(:disabled) {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-outline {
            background-color: transparent;
            color: #d9534f;
            border-color: #d9534f;
        }

        .btn-outline:hover:not(:disabled) {
            background-color: #fdf2f2;
        }

        .quiz-container,
        .loader-view,
        .result-view {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }

        .questions-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding-bottom: 2rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .q-nav-item {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary-light);
            background-color: var(--primary-light);
            color: var(--primary-dark);
            display: grid;
            place-items: center;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s ease;
        }

        .q-nav-item:hover {
            transform: scale(1.1);
        }

        .q-nav-item.current {
            background-color: var(--primary-color);
            color: #fff;
            border-color: var(--primary-dark);
        }

        .q-nav-item.answered {
            background-color: var(--primary-dark);
            color: #fff;
            border-color: var(--primary-dark);
        }

        .question-header {
            margin-bottom: 2rem;
        }

        .question-text {
            font-size: 1.3rem;
            font-weight: 700;
            line-height: 1.7;
            color: #333;
        }

        .question-points {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--secondary-text);
            background-color: #f8f9fa;
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 1rem;
        }

        .answer-option {
            margin-bottom: 1rem;
        }

        .answer-label {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all .2s;
        }

        .answer-label:hover {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
        }

        .answer-radio {
            position: absolute;
            opacity: 0;
        }

        .radio-custom {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #ccc;
            display: grid;
            place-items: center;
            margin-left: .8rem;
            transition: border-color .2s;
        }

        .radio-custom::before {
            content: '';
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-color);
            transform: scale(0);
            transition: transform .2s ease-in-out;
        }

        .answer-radio:checked+.answer-label {
            border-color: var(--primary-dark);
            background-color: var(--primary-light);
            font-weight: 600;
        }

        .answer-radio:checked+.answer-label .radio-custom {
            border-color: var(--primary-dark);
        }

        .answer-radio:checked+.answer-label .radio-custom::before {
            transform: scale(1);
        }

        .quiz-nav-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2.5rem;
            border-top: 1px solid var(--border-color);
            padding-top: 2rem;
        }

        .loader-view {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 300px;
            text-align: center;
        }

        .spinner {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 8px solid #f3f3f3;
            border-top: 8px solid var(--primary-color);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loader-view p {
            margin-top: 1.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .result-view {
            display: none;
            text-align: center;
        }

        .result-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }

        .result-subtitle {
            font-size: 1.1rem;
            color: var(--secondary-text);
            margin-bottom: 3rem;
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background-color: #f8f9fa;
            border-radius: var(--radius);
            padding: 1.5rem;
            border-bottom: 5px solid;
        }

        .stat-card-value {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .stat-card-label {
            margin-top: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            color: var(--secondary-text);
        }

        .stat-card.correct {
            border-color: #28a745;
        }

        .stat-card.correct .stat-card-value {
            color: #28a745;
        }

        .stat-card.incorrect {
            border-color: #dc3545;
        }

        .stat-card.incorrect .stat-card-value {
            color: #dc3545;
        }

        .stat-card.unanswered {
            border-color: #6c757d;
        }

        .stat-card.unanswered .stat-card-value {
            color: #6c757d;
        }

        .stat-card.total-score {
            grid-column: 1 / -1;
            border-color: var(--primary-color);
            background-color: var(--primary-light);
        }

        .stat-card.total-score .stat-card-value {
            color: var(--primary-dark);
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div id="quiz-container" class="quiz-container">
            <div id="questions-nav" class="questions-nav"></div>
            <div class="question-header">
                <h2 id="question-text" class="question-text"></h2>
                <div id="question-points" class="question-points"></div>
            </div>
            <div id="answers-container"></div>
            <div class="quiz-nav-buttons">
                <button id="clear-answer-btn" class="btn btn-outline" disabled>پاک کردن پاسخ</button>
                <div>
                    <button id="prev-btn" class="btn btn-secondary">سوال قبلی</button>
                    <button id="next-btn" class="btn btn-primary" style="margin-right: 10px;">سوال بعدی</button>
                </div>
            </div>
        </div>

        <div id="loader-view" class="loader-view">
            <div class="spinner"></div>
            <p>در حال ثبت و محاسبه نتایج...</p>
        </div>

        <div id="result-view" class="result-view">
            <h1 class="result-title">آزمون شما ثبت شد!</h1>
            <p class="result-subtitle">این هم از نتیجه تلاش شما:</p>
            <div class="result-stats">
                <div class="stat-card correct">
                    <div id="result-correct" class="stat-card-value">0</div>
                    <div class="stat-card-label">✔ پاسخ صحیح</div>
                </div>
                <div class="stat-card incorrect">
                    <div id="result-incorrect" class="stat-card-value">0</div>
                    <div class="stat-card-label">✖ پاسخ غلط</div>
                </div>
                <div class="stat-card unanswered">
                    <div id="result-unanswered" class="stat-card-value">0</div>
                    <div class="stat-card-label">⚪ بی‌جواب</div>
                </div>
                <div class="stat-card total-score">
                    <div id="result-score" class="stat-card-value">0</div>
                    <div class="stat-card-label">⭐ امتیاز نهایی</div>
                </div>
            </div>
            <a href="index.php" class="btn btn-primary" style="font-size: 1.1rem;">بازگشت به لیست آزمون‌ها</a>
        </div>
    </main>
    <div id="footer-placeholder"></div>
    <script src="/js/header.js?v=1.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const quizData = <?= json_encode($quiz_data) ?>;
            const quizId = <?= $quiz_id ?>;
            let currentQuestionIndex = 0;
            const userAnswers = {};

            const questionTextEl = document.getElementById('question-text');
            const questionPointsEl = document.getElementById('question-points');
            const answersContainerEl = document.getElementById('answers-container');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            const clearAnswerBtn = document.getElementById('clear-answer-btn');
            const questionsNavEl = document.getElementById('questions-nav');

            const renderNav = () => {
                questionsNavEl.innerHTML = '';
                quizData.forEach((q, index) => {
                    const navItem = document.createElement('div');
                    navItem.className = 'q-nav-item';
                    navItem.textContent = index + 1;
                    navItem.dataset.index = index;
                    if (userAnswers[q.id]) navItem.classList.add('answered');
                    if (index === currentQuestionIndex) navItem.classList.add('current');
                    navItem.addEventListener('click', () => {
                        currentQuestionIndex = index;
                        renderQuestion(currentQuestionIndex);
                    });
                    questionsNavEl.appendChild(navItem);
                });
            };

            const renderQuestion = (index) => {
                const question = quizData[index];
                questionTextEl.textContent = question.text;
                questionPointsEl.textContent = `(پاسخ صحیح: ${question.points_correct} امتیاز | پاسخ غلط: ${question.points_incorrect} امتیاز)`;
                answersContainerEl.innerHTML = '';
                question.answers.forEach(answer => {
                    const uniqueId = `ans-${question.id}-${answer.id}`;
                    const div = document.createElement('div');
                    div.className = 'answer-option';
                    div.innerHTML = `
                    <input type="radio" name="answer-${question.id}" value="${answer.id}" class="answer-radio" id="${uniqueId}">
                    <label for="${uniqueId}" class="answer-label">
                        <span class="radio-custom"></span>
                        <span>${answer.text}</span>
                    </label>
                `;
                    answersContainerEl.appendChild(div);
                });

                const savedAnswerId = userAnswers[question.id];
                if (savedAnswerId) {
                    const radioToCheck = document.querySelector(`input[value="${savedAnswerId}"]`);
                    if (radioToCheck) radioToCheck.checked = true;
                    clearAnswerBtn.disabled = false;
                } else {
                    clearAnswerBtn.disabled = true;
                }

                updateNavButtons();
                renderNav();
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

            answersContainerEl.addEventListener('change', e => {
                const radio = e.target;
                if (radio.type === 'radio' && radio.checked) {
                    const questionId = quizData[currentQuestionIndex].id;
                    userAnswers[questionId] = parseInt(radio.value);
                    clearAnswerBtn.disabled = false;
                    renderNav();
                }
            });

            clearAnswerBtn.addEventListener('click', () => {
                const questionId = quizData[currentQuestionIndex].id;
                delete userAnswers[questionId];
                const checkedRadio = document.querySelector(`input[name="answer-${questionId}"]:checked`);
                if (checkedRadio) checkedRadio.checked = false;
                clearAnswerBtn.disabled = true;
                renderNav();
            });

            nextBtn.addEventListener('click', async () => {
                if (currentQuestionIndex < quizData.length - 1) {
                    currentQuestionIndex++;
                    renderQuestion(currentQuestionIndex);
                } else {
                    const unansweredCount = quizData.length - Object.keys(userAnswers).length;
                    let msg = 'آیا از پایان آزمون و ثبت نهایی پاسخ‌ها مطمئن هستید؟';
                    if (unansweredCount > 0) {
                        msg = `شما به ${unansweredCount} سوال پاسخ نداده‌اید. آیا همچنان مایل به ثبت نهایی آزمون هستید؟`;
                    }
                    if (confirm(msg)) await submitQuiz();
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
                document.getElementById('quiz-container').style.display = 'none';
                document.getElementById('loader-view').style.display = 'flex';
                const data = {
                    quizId: quizId,
                    answers: userAnswers
                };
                try {
                    await new Promise(resolve => setTimeout(resolve, 1500));
                    const response = await fetch('quiz_api.php?action=submit_attempt', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });
                    const responseData = await response.json();
                    if (!response.ok || !responseData.success) {
                        throw new Error(responseData.message || 'خطای سرور');
                    }

                    const results = responseData.results;
                    document.getElementById('result-score').textContent = results.score;
                    document.getElementById('result-correct').textContent = results.correctCount;
                    document.getElementById('result-incorrect').textContent = results.incorrectCount;
                    document.getElementById('result-unanswered').textContent = results.unansweredCount;

                    document.getElementById('loader-view').style.display = 'none';
                    document.getElementById('result-view').style.display = 'block';

                } catch (error) {
                    alert('خطا در ثبت نتایج: ' + error.message);
                    document.getElementById('loader-view').style.display = 'none';
                    document.getElementById('quiz-container').style.display = 'block';
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
