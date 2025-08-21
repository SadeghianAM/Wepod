<?php
require_once __DIR__ . '/../auth/require-auth.php';
$claims = requireAuth('admin', '/auth/login.html');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>پنل مدیریت - وی هاب</title>
  <style>
    :root {
      --primary-color: #00ae70;
      --primary-dark: #089863;
      --primary-light: #e6f7f2;
      --bg-color: #f7f9fa;
      /* Slightly more neutral background */
      --text-color: #1a1a1a;
      /* Darker text for better contrast */
      --secondary-text-color: #555;
      --card-bg: #ffffff;
      --header-text: #ffffff;
      --shadow-color-light: rgba(0, 120, 80, 0.06);
      /* Adjusted shadow color */
      --shadow-color-medium: rgba(0, 120, 80, 0.1);
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
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    a {
      text-decoration: none;
      transition: all 0.2s ease-in-out;
    }

    /* --- HEADER & FOOTER Styles (Unchanged as requested) --- */
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
    }

    header {
      height: 70px;
    }

    footer {
      height: 60px;
      font-size: 0.85rem;
    }

    header h1 {
      font-size: 1.2rem;
      font-weight: 700;
    }

    #today-date,
    #user-info {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1rem;
      opacity: 0.85;
      font-weight: 500;
      white-space: nowrap;
    }

    #today-date {
      inset-inline-start: 1.5rem;
    }

    #user-info {
      inset-inline-end: 1.5rem;
      cursor: pointer;
      padding: 0.5rem 0.8rem;
      border-radius: 0.5rem;
      transition: background-color 0.2s;
    }

    #user-info:hover {
      background-color: rgba(255, 255, 255, 0.15);
    }

    /* --- Main Content Styles (Redesigned) --- */
    main {
      flex-grow: 1;
      padding: 2.5rem 2rem;
      /* Increased padding */
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
    }

    .page-title {
      font-size: 1.8rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
      color: var(--primary-dark);
    }

    .page-subtitle {
      font-size: 1rem;
      font-weight: 400;
      color: var(--secondary-text-color);
      margin-bottom: 2.5rem;
    }

    .search-container {
      margin-bottom: 2rem;
      max-width: 600px;
    }

    #tools-search {
      width: 100%;
      font-size: 1rem;
      padding: 0.8em 1.2em;
      border-radius: var(--border-radius);
      border: 1.5px solid var(--border-color);
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
      background-color: var(--card-bg);
    }

    #tools-search:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(0, 174, 112, 0.15);
    }

    .tools-grid {
      list-style: none;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
      padding: 0;
      /* Resetting ul padding */
    }

    .tool-card a {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 0.75rem;
      padding: 1.75rem;
      background-color: var(--card-bg);
      border-radius: var(--border-radius);
      border: 1px solid var(--border-color);
      box-shadow: 0 4px 15px var(--shadow-color-light);
      color: var(--text-color);
      transition: transform 0.2s ease-out, box-shadow 0.2s ease-out, border-color 0.2s ease-out;
    }

    .tool-card a:hover {
      transform: translateY(-5px);
      border-color: var(--primary-color);
      box-shadow: 0 8px 25px var(--shadow-color-medium);
      color: var(--primary-dark);
    }

    .tool-icon {
      font-size: 2rem;
      /* Larger icons */
      line-height: 1;
    }

    .tool-title {
      font-size: 1.1rem;
      font-weight: 700;
    }

    #no-results {
      display: none;
      /* Hidden by default */
      text-align: center;
      padding: 3rem 1rem;
      color: var(--secondary-text-color);
      font-size: 1.1rem;
      font-weight: 500;
      border: 2px dashed var(--border-color);
      border-radius: var(--border-radius);
    }

    /* --- Responsive Design --- */
    @media (max-width: 768px) {
      .tools-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      }

      .page-title {
        font-size: 1.6rem;
      }

      main {
        padding: 2rem 1.5rem;
      }
    }

    @media (max-width: 480px) {
      .tools-grid {
        grid-template-columns: 1fr;
      }

      main {
        padding: 1.5rem 1rem;
      }

      .page-title {
        font-size: 1.4rem;
      }

      .page-subtitle {
        font-size: 0.9rem;
        margin-bottom: 2rem;
      }

      /* Hiding header elements on small screens as per original design */
      #today-date,
      #user-info {
        display: none;
      }
    }
  </style>
</head>

<body>
  <div id="header-placeholder"></div>

  <main>
    <h1 class="page-title">پنل مدیریت وی هاب</h1>
    <p class="page-subtitle">ابزارهای مورد نیاز خود را از اینجا پیدا و مدیریت کنید.</p>

    <div class="search-container">
      <input type="text" id="tools-search" placeholder="جستجوی سریع ابزار..." aria-label="جستجو در ابزارها" />
    </div>

    <ul class="tools-grid" id="tools-list">
      <li class="tool-card">
        <a href="/admin/status.php">
          <span class="tool-icon">📊</span>
          <span class="tool-title">ویرایش وضعیت سرویس‌ها</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/news.php">
          <span class="tool-icon">📢</span>
          <span class="tool-title">ویرایش اطلاعیه‌ها</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/wiki.php">
          <span class="tool-icon">📚</span>
          <span class="tool-title">ویرایش ویکی</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/process_shifts.php">
          <span class="tool-icon">⚙️</span>
          <span class="tool-title">به‌روزرسانی برنامه شیفت‌ها</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/admin-shifts.php">
          <span class="tool-icon">🗓️</span>
          <span class="tool-title">مدیریت شیفت‌ها</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/reports.php">
          <span class="tool-icon">📈</span>
          <span class="tool-title">مدیریت داشبورد عملکرد</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/disruptions.php">
          <span class="tool-icon">⚠️</span>
          <span class="tool-title">مدیریت اختلالات</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/hash-tool.php">
          <span class="tool-icon">🔒</span>
          <span class="tool-title">ابزار تولید هش</span>
        </a>
      </li>
    </ul>
    <div id="no-results">
      <p>هیچ ابزاری با عبارت جستجو شده مطابقت ندارد.</p>
    </div>
  </main>

  <div id="footer-placeholder"></div>

  <script src="/js/header.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('tools-search');
      const toolsList = document.getElementById('tools-list');
      const toolCards = toolsList.getElementsByClassName('tool-card');
      const noResultsMessage = document.getElementById('no-results');

      searchInput.addEventListener('input', function() {
        const filter = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        for (let i = 0; i < toolCards.length; i++) {
          const card = toolCards[i];
          const title = card.querySelector('.tool-title').textContent.toLowerCase();

          if (title.includes(filter)) {
            card.style.display = '';
            visibleCount++;
          } else {
            card.style.display = 'none';
          }
        }

        // Show or hide the 'no results' message
        if (visibleCount === 0) {
          toolsList.style.display = 'none';
          noResultsMessage.style.display = 'block';
        } else {
          toolsList.style.display = 'grid'; // Ensure grid layout is restored
          noResultsMessage.style.display = 'none';
        }
      });
    });
  </script>
</body>

</html>
