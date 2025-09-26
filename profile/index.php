<?php
// =================================================================
// منطق اتصال و لود داده‌ها
// =================================================================

// فرض کنید کاربر با ID 100 وارد شده است.
// در سیستم واقعی، این مقدار باید از سشن (مانند $_SESSION['user_id']) خوانده شود.
$logged_in_user_id = 100;

// اتصال به دیتابیس با استفاده از فایل شما
// این فایل متغیر $pdo را تعریف و مقداردهی می‌کند.
require_once __DIR__ . '/../db/database.php';

$specialist_data = [];
if (isset($pdo)) {
    try {
        // کوئری برای خواندن name، username، role و start_work
        $stmt = $pdo->prepare("SELECT name, username, role, start_work FROM users WHERE id = ?");
        $stmt->execute([$logged_in_user_id]);
        $specialist_data = $stmt->fetch();

        // اگر ستون role هنوز تعریف نشده بود، آن را اضافه می‌کنیم (فقط برای اطمینان در اولین اجرا)
        if ($specialist_data && !isset($specialist_data['role'])) {
            // این دستور باید یک بار در هنگام تنظیم دیتابیس اجرا شود، اما اینجا برای اطمینان است
            $pdo->exec("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'کارشناس پشتیبانی مشتریان'");
            $specialist_data['role'] = 'کارشناس پشتیبانی مشتریان';
        }
    } catch (\PDOException $e) {
        // در صورت هرگونه خطا (مثلاً اگر ستون role هنوز وجود ندارد)، لاگ می‌کنیم.
        error_log("Database Query Error: " . $e->getMessage());
    }
}

// تخصیص مقادیر لودشده یا مقادیر پیش‌فرض
$specialist_name = $specialist_data['name'] ?? "کارشناس نامشخص";
$specialist_role = $specialist_data['role'] ?? "کارشناس پشتیبانی مشتریان"; // از ستون جدید
$specialist_username = $specialist_data['username'] ?? "N/A";
$start_work_date = $specialist_data['start_work'] ?? "N/A";

// تصویر پروفایل پیش‌فرض
$profile_pic_url = "/assets/profiles/profile.png";

// شبیه‌سازی داده‌های عملکرد (این بخش بعداً از دیتابیس عملکرد لود می‌شود)
$stats = [
    ['title' => 'تیکت‌های حل‌شده', 'value' => '۴۵', 'unit' => 'مورد', 'color_var' => '--primary-dark'],
    ['title' => 'رضایت مشتری (CSAT)', 'value' => '۹۲.۵', 'unit' => '%', 'color_var' => '--primary-color'],
    ['title' => 'میانگین زمان پاسخ‌دهی (AHT)', 'value' => '۳:۲۰', 'unit' => 'دقیقه', 'color_var' => '--yellow-color'],
    ['title' => 'ارجاعات موفق', 'value' => '۹۸', 'unit' => '%', 'color_var' => '--primary-dark'],
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پروفایل کارشناس - سامانه پشتیبانی</title>

    <style>
        /* Variables CSS از پروژه اصلی کپی شده است */
        :root {
            --primary-color: #00ae70;
            --primary-dark: #089863;
            --primary-light: #e6f7f2;
            --bg-color: #f8fcf9;
            --text-color: #222;
            --secondary-text-color: #555;
            --card-bg: #ffffff;
            --header-text: #ffffff;
            --shadow-color-light: rgba(0, 174, 112, 0.07);
            --shadow-color-medium: rgba(0, 174, 112, 0.12);
            --danger-color: #d93025;
            --danger-bg: #fce8e6;
            --red-color: #f90000;
            --red-bg: #fec3c3;
            --yellow-color: #f9ab00;
            --yellow-bg: #feefc3;
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
            font-family: "Vazirmatn", sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            direction: rtl;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }

        .profile-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 12px var(--shadow-color-light);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        /* -------------------- استایل Header -------------------- */
        .profile-header {
            background-color: var(--primary-color);
            color: var(--header-text);
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--primary-dark);
        }

        .header-info {
            display: flex;
            align-items: center;
        }

        .profile-picture-wrapper {
            position: relative;
            margin-left: 20px;
        }

        .profile-picture {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
        }

        .edit-pic-btn {
            position: absolute;
            bottom: 0;
            left: 0;
            background: var(--yellow-color);
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 14px;
            color: var(--text-color);
            line-height: 1;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-details h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: 700;
        }

        .user-details .role {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .user-details .user-id {
            font-size: 12px;
            opacity: 0.8;
        }

        /* -------------------- کنترل وضعیت (آنلاین/آفلاین) -------------------- */
        .status-control {
            display: flex;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
        }

        #status-toggle {
            height: 0;
            width: 0;
            visibility: hidden;
        }

        .status-control label {
            cursor: pointer;
            text-indent: -9999px;
            width: 50px;
            height: 25px;
            background: var(--danger-color);
            display: block;
            border-radius: 100px;
            position: relative;
            margin-right: 10px;
            transition: background 0.3s;
        }

        .status-control label:after {
            content: '';
            position: absolute;
            top: 2.5px;
            right: 3px;
            width: 20px;
            height: 20px;
            background: var(--card-bg);
            border-radius: 90px;
            transition: 0.3s;
        }

        #status-toggle:checked+label {
            background: var(--primary-dark);
        }

        #status-toggle:checked+label:after {
            right: calc(100% - 3px);
            transform: translateX(100%);
        }

        .status-label {
            width: 60px;
            text-align: left;
        }

        /* -------------------- استایل Tabs -------------------- */
        .profile-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            padding: 0 30px;
            background-color: var(--card-bg);
        }

        .tab-button {
            background: none;
            border: none;
            padding: 15px 25px;
            cursor: pointer;
            font-size: 16px;
            color: var(--secondary-text-color);
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease-in-out;
            margin-left: 10px;
        }

        .tab-button:hover {
            color: var(--primary-dark);
        }

        .tab-button.active {
            color: var(--primary-dark);
            border-bottom-color: var(--primary-color);
            font-weight: 700;
        }

        /* -------------------- استایل Tab Content -------------------- */
        .tab-content-container {
            padding: 2rem;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* -------------------- استایل کارت‌های داشبورد (همانند ابزارها) -------------------- */
        .tool-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px var(--shadow-color-light);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .tool-card h2 {
            font-size: 1.1rem;
            font-weight: 700;
            padding: 0.9rem 1.5rem;
            background-color: var(--bg-color);
            color: var(--primary-dark);
            border-bottom: 1px solid var(--border-color);
        }

        .tool-card .card-content {
            padding: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background-color: var(--primary-light);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
            border-right: 4px solid var(--primary-color);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.yellow {
            background-color: var(--yellow-bg);
            border-right-color: var(--yellow-color);
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: var(--secondary-text-color);
        }

        .stat-card .value {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-color);
            display: block;
        }

        .stat-card .unit {
            font-size: 18px;
            color: var(--secondary-text-color);
            margin-right: 5px;
        }

        /* -------------------- استایل فرم‌ها در تب حساب -------------------- */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--secondary-text-color);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 174, 112, 0.15);
            outline: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--header-text);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        /* -------------------- استایل تنظیمات (سوئیچ‌ها) -------------------- */
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-item span {
            font-weight: 500;
        }

        /* سوئیچ‌های کوچک برای تنظیمات اعلان */
        .setting-switch input[type="checkbox"] {
            visibility: hidden;
            width: 0;
            height: 0;
        }

        .setting-switch label {
            cursor: pointer;
            text-indent: -9999px;
            width: 45px;
            height: 25px;
            background: #ccc;
            display: block;
            border-radius: 100px;
            position: relative;
            transition: background 0.3s;
        }

        .setting-switch label:after {
            content: '';
            position: absolute;
            top: 3px;
            right: 3px;
            width: 19px;
            height: 19px;
            background: var(--card-bg);
            border-radius: 90px;
            transition: 0.3s;
        }

        .setting-switch input:checked+label {
            background: var(--primary-dark);
        }

        .setting-switch input:checked+label:after {
            right: calc(100% - 3px);
            transform: translateX(100%);
        }
    </style>
</head>

<body>

    <div class="profile-container">

        <header class="profile-header">
            <div class="header-info">
                <div class="profile-picture-wrapper">
                    <img src="<?php echo $profile_pic_url; ?>" alt="تصویر پروفایل" class="profile-picture">
                    <button class="edit-pic-btn" title="ویرایش عکس">🖼️</button>
                </div>
                <div class="user-details">
                    <h1><?php echo $specialist_name; ?></h1>
                    <p class="role"><?php echo $specialist_role; ?></p>
                    <span class="user-id">نام کاربری: <?php echo $specialist_username; ?></span>
                </div>
            </div>

            <div class="status-control">
                <input type="checkbox" id="status-toggle" checked>
                <label for="status-toggle">آنلاین</label>
                <span class="status-label" id="current-status-label">آنلاین</span>
            </div>
        </header>

        <nav class="profile-tabs">
            <button class="tab-button active" data-tab="performance">داشبورد عملکرد</button>
            <button class="tab-button" data-tab="account">اطلاعات حساب و شخصی</button>
            <button class="tab-button" data-tab="settings">تنظیمات سیستم و اعلان‌ها</button>
        </nav>

        <main class="tab-content-container">

            <section id="performance" class="tab-content active">
                <div class="tool-card">
                    <h2>📊 آمار عملکرد ماه جاری</h2>
                    <div class="card-content">
                        <div class="stats-grid">
                            <?php foreach ($stats as $stat): ?>
                                <div class="stat-card <?php echo str_replace('--', '', $stat['color_var']); ?>">
                                    <h3><?php echo $stat['title']; ?></h3>
                                    <span class="value"><?php echo $stat['value']; ?><span class="unit"><?php echo $stat['unit']; ?></span></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="tool-card">
                    <h2>📈 روند حل تیکت‌ها (۳ ماه اخیر)</h2>
                    <div class="card-content">
                        <div style="background-color:var(--bg-color); padding:40px; border-radius:8px; height:250px; display:flex; align-items:center; justify-content:center; color:var(--secondary-text-color); border: 1px dashed var(--border-color);">
                            فضای نگهدارنده نمودار (نیاز به کتابخانه‌ای مثل Chart.js دارد)
                        </div>
                    </div>
                </div>
            </section>

            <section id="account" class="tab-content">
                <div class="tool-card">
                    <h2>📝 اطلاعات شخصی و تماس</h2>
                    <div class="card-content">
                        <form id="personal-info-form">
                            <input type="hidden" name="user_id" value="<?php echo $logged_in_user_id; ?>">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="full_name">نام و نام خانوادگی</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo $specialist_name; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="emergency_phone">شماره تماس اضطراری</label>
                                    <input type="text" id="emergency_phone" name="emergency_phone" value="">
                                </div>
                                <div class="form-group">
                                    <label>پست سازمانی</label>
                                    <input type="text" value="<?php echo $specialist_role; ?>" disabled style="background-color: #f0f0f0; cursor: not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label>تاریخ شروع به کار</label>
                                    <input type="text" value="<?php echo $start_work_date; ?>" disabled style="background-color: #f0f0f0; cursor: not-allowed;">
                                </div>
                            </div>
                            <button type="submit" class="btn-primary" style="margin-top: 1rem;">ذخیره اطلاعات شخصی</button>
                            <span id="save-message" style="margin-right: 15px; color: var(--primary-dark); display: none;"></span>
                        </form>
                    </div>
                </div>

                <div class="tool-card">
                    <h2>📅 شیفت کاری هفته جاری</h2>
                    <div class="card-content">
                        <div style="background-color:var(--primary-light); padding:20px; border-radius:var(--border-radius); border: 1px solid var(--primary-color);">
                            <p style="color:var(--primary-dark); font-weight:600;">شنبه تا چهارشنبه: ۹:۰۰ الی ۱۷:۰۰</p>
                            <p style="color:var(--secondary-text-color); font-size: 0.9em; margin-top: 5px;">شیفت عصر (پنجشنبه): ۱۲:۰۰ الی ۱۶:۰۰</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="settings" class="tab-content">
                <div class="tool-card">
                    <h2>🔐 امنیت حساب کاربری</h2>
                    <div class="card-content">
                        <div class="setting-item">
                            <span>تغییر رمز عبور</span>
                            <button class="btn-primary" style="background-color: var(--danger-color);">تغییر</button>
                        </div>
                        <div class="setting-item">
                            <span>تأیید دو مرحله‌ای (2FA)</span>
                            <div class="setting-switch">
                                <input type="checkbox" id="2fa-toggle" checked>
                                <label for="2fa-toggle"></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tool-card">
                    <h2>🔔 تنظیمات اعلان‌ها</h2>
                    <div class="card-content">
                        <div class="setting-item">
                            <span>دریافت اعلان برای تیکت‌های جدید</span>
                            <div class="setting-switch">
                                <input type="checkbox" id="notif-ticket" checked>
                                <label for="notif-ticket"></label>
                            </div>
                        </div>
                        <div class="setting-item">
                            <span>اعلان پاپ‌آپ درون سیستمی</span>
                            <div class="setting-switch">
                                <input type="checkbox" id="notif-popup" checked>
                                <label for="notif-popup"></label>
                            </div>
                        </div>
                        <div class="setting-item">
                            <span>پخش صدای هشدار</span>
                            <div class="setting-switch">
                                <input type="checkbox" id="notif-sound">
                                <label for="notif-sound"></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tool-card">
                    <h2>✨ تنظیمات ظاهری</h2>
                    <div class="card-content">
                        <div class="setting-item">
                            <span>حالت نمایش تیره (Dark Mode)</span>
                            <div class="setting-switch">
                                <input type="checkbox" id="dark-mode">
                                <label for="dark-mode"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            const statusToggle = document.getElementById('status-toggle');
            const statusLabel = document.getElementById('current-status-label');
            const infoForm = document.getElementById('personal-info-form');
            const saveMessage = document.getElementById('save-message');

            // --- منطق تعویض تب‌ها ---
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.dataset.tab;
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    button.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');
                });
            });

            // --- منطق کنترل وضعیت (آنلاین/آفلاین) ---
            statusToggle.addEventListener('change', (event) => {
                const statusControlLabel = document.querySelector('.status-control label');
                const isChecked = event.target.checked;
                statusLabel.textContent = isChecked ? 'آنلاین' : 'آفلاین';
                statusControlLabel.style.backgroundColor = isChecked ? 'var(--primary-dark)' : 'var(--danger-color)';

                // در اینجا باید درخواست AJAX به یک فایل PHP دیگر برای به‌روزرسانی وضعیت در سرور بفرستید
                console.log(`وضعیت به ${isChecked ? 'آنلاین' : 'آفلاین'} تغییر یافت. (نیاز به درخواست AJAX)`);
            });

            // --- منطق ذخیره اطلاعات شخصی (AJAX) ---
            infoForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('action', 'save_personal_info');

                saveMessage.style.display = 'inline';
                saveMessage.textContent = 'در حال ذخیره...';

                fetch('api/profile_handler.php', { // نیاز به ایجاد این فایل
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            saveMessage.textContent = '✅ با موفقیت ذخیره شد!';
                            setTimeout(() => saveMessage.style.display = 'none', 3000);
                        } else {
                            saveMessage.textContent = `❌ خطا: ${data.message || 'مشکلی پیش آمد.'}`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        saveMessage.textContent = '❌ خطا در ارتباط با سرور.';
                    });
            });

            // فعال کردن تب پیش‌فرض
            if (!document.querySelector('.tab-content.active')) {
                document.getElementById('performance').classList.add('active');
                document.querySelector('.tab-button[data-tab="performance"]').classList.add('active');
            }
        });
    </script>
</body>

</html>
