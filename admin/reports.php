<?php
require __DIR__ . '/../php/auth_check.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>به‌روزرسانی گزارش‌ها</title>
    <style>
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --danger-color: #dc3545;
            --danger-dark: #c82333;
            --bg-color: #f8fcf9;
            --text-color: #222;
            --secondary-text-color: #555;
            --card-bg: #ffffff;
            --header-text: #ffffff;
            --shadow-color-light: rgba(0, 174, 112, 0.07);
            --shadow-color-medium: rgba(0, 174, 112, 0.12);
            --border-radius: 0.75rem;
            --border-color: #e9e9e9;
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
            font-family: "Vazirmatn", sans-serif !important;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            direction: rtl;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        header,
        footer {
            background: var(--primary-color);
            color: var(--header-text);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px var(--shadow-color-light);
            position: relative;
            z-index: 10;
            flex-shrink: 0;
        }

        header {
            height: 70px;
        }

        header h1 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--header-text);
            margin-bottom: 0;
        }

        footer {
            height: 60px;
            font-size: 0.85rem;
            margin-top: auto;
        }

        main {
            padding: 1.5rem;
            max-width: 900px;
            width: 100%;
            margin: 2rem auto;
            flex-grow: 1;
        }

        .form-container {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 15px var(--shadow-color-light);
            border-top: 4px solid var(--primary-color);
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            text-align: center;
            font-weight: 700;
        }

        .description {
            color: #555;
            text-align: center;
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        textarea {
            width: 100%;
            min-height: 250px;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            box-sizing: border-box;
            background-color: #fcfcfc;
            transition: border-color 0.2s;
            direction: ltr;
            text-align: left;
        }

        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        button {
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            display: block;
            width: 100%;
            transition: background-color 0.2s, transform 0.2s;
        }

        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            box-shadow: 0 4px 10px var(--shadow-color-medium);
        }

        button[type="submit"]:hover:not(:disabled) {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .response-message {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            font-weight: 500;
            display: none;
        }

        .response-message.success {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary-color);
        }

        .response-message.error {
            background-color: #fff0f3;
            color: #c82333;
            border: 1px solid #ff0040;
        }
    </style>
</head>

<body>
    <div id="header-placeholder"></div>
    <main>
        <div class="form-container">
            <h1>سامانه به‌روزرسانی گزارش‌ها</h1>
            <p class="description">
                اطلاعات کپی شده از فایل اکسل را در کادر زیر وارد کرده و دکمه ذخیره را بزنید.
            </p>
            <form id="reportForm">
                <label for="excel_data">محتوای گزارش:</label>
                <textarea id="excel_data" name="excel_data" required placeholder="داده‌های کپی شده از اکسل را اینجا جای‌گذاری کنید..."></textarea>
                <button type="submit">ذخیره تغییرات</button>
            </form>
            <div id="response" class="response-message"></div>
        </div>
    </main>
    <div id="footer-placeholder"></div>

    <script src="/js/header.js"></script>
    <script>
        document.getElementById("reportForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const responseDiv = document.getElementById("response");
            const submitButton = form.querySelector('button[type="submit"]');

            responseDiv.style.display = "none";
            submitButton.disabled = true;
            submitButton.textContent = "در حال ذخیره...";

            try {
                const response = await fetch("/php/process_reports.php", {
                    method: "POST",
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error(`خطای سرور: ${response.statusText}`);
                }
                const result = await response.json();

                responseDiv.textContent = result.message;
                if (result.success) {
                    responseDiv.className = "response-message success";
                } else {
                    responseDiv.className = "response-message error";
                }
                responseDiv.style.display = "block";

            } catch (error) {
                responseDiv.textContent = `یک خطای غیرمنتظره رخ داد: ${error.message}`;
                responseDiv.className = "response-message error";
                responseDiv.style.display = "block";
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = "ذخیره تغییرات";
            }
        });
    </script>
</body>

</html>
