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
      --card-bg: #fff;
      --text-color: #1a1a1a;
      --secondary-text: #555;
      --header-text: #fff;
      --border-color: #e9e9e9;
      --radius: 12px;
      --shadow-sm: 0 2px 6px rgba(0, 120, 80, .06);
      --shadow-md: 0 6px 20px rgba(0, 120, 80, .10);
      --space-1: .25rem;
      --space-2: .5rem;
      --space-3: .75rem;
      --space-4: 1rem;
      --space-5: 1.25rem;
      --space-6: 1.5rem;
      --space-7: 1.75rem;
      --space-8: 2rem;
      --header-h: 70px;
      --footer-h: 60px;
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
      font-family: "Vazirmatn", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
    }

    html {
      scroll-behavior: smooth
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
      transition: color .2s ease, background-color .2s ease, border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    :focus {
      outline: none
    }

    :focus-visible {
      outline: 3px solid rgba(0, 174, 112, .35);
      outline-offset: 2px
    }

    header,
    footer {
      background: var(--primary-color);
      color: var(--header-text);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 10;
      box-shadow: var(--shadow-sm);
      flex-shrink: 0;
    }

    header {
      min-height: var(--header-h)
    }

    footer {
      min-height: var(--footer-h);
      font-size: .85rem
    }

    header h1 {
      font-weight: 700;
      font-size: clamp(1rem, 2.2vw, 1.2rem);
      white-space: nowrap;
      max-width: 60vw;
      text-overflow: ellipsis;
      overflow: hidden;
    }

    #today-date,
    #user-info {
      position: static !important;
      transform: none !important;
      white-space: nowrap;
      opacity: .9;
      font-weight: 500;
      font-size: clamp(.9rem, 2vw, 1rem);
    }

    #user-info {
      margin-inline-end: 1.5rem;
      padding: .5rem .8rem;
      border-radius: 8px;
      cursor: pointer;
    }

    #user-info:hover {
      background-color: rgba(255, 255, 255, .15)
    }

    main {
      flex: 1 1 auto;
      width: min(1200px, 100%);
      padding: clamp(1rem, 3vw, 2.5rem) clamp(1rem, 3vw, 2rem);
      margin-inline: auto;
    }

    .page-title {
      color: var(--primary-dark);
      font-weight: 800;
      font-size: clamp(1.3rem, 3vw, 1.8rem);
      margin-block-end: .5rem;
    }

    .page-subtitle {
      color: var(--secondary-text);
      font-weight: 400;
      font-size: clamp(.95rem, 2.2vw, 1rem);
      margin-block-end: 2rem;
    }

    .search-container {
      max-width: 600px;
      margin-block-end: 2rem
    }

    #tools-search {
      width: 100%;
      font-size: 1rem;
      padding: .8em 1.2em;
      border: 1.5px solid var(--border-color);
      border-radius: var(--radius);
      background: var(--card-bg);
      transition: border-color .2s, box-shadow .2s;
    }

    #tools-search:focus-visible {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(0, 174, 112, .15);
    }

    .tools-grid {
      list-style: none;
      display: grid;
      gap: 1.5rem;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }

    .tool-card a {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: .75rem;
      padding: 1.75rem;
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: var(--radius);
      box-shadow: 0 4px 15px rgba(0, 120, 80, .06);
      will-change: transform;
    }

    .tool-card a:hover {
      transform: translateY(-5px);
      border-color: var(--primary-color);
      box-shadow: var(--shadow-md);
      color: var(--primary-dark);
    }

    .tool-icon {
      font-size: 2rem;
      line-height: 1
    }

    .tool-title {
      font-size: 1.1rem;
      font-weight: 700
    }

    #no-results {
      display: none;
      text-align: center;
      padding: 3rem 1rem;
      color: var(--secondary-text);
      font-size: 1.05rem;
      font-weight: 500;
      border: 2px dashed var(--border-color);
      border-radius: var(--radius);
      margin-block: 1rem;
    }

    @media (max-width: 768px) {
      .tools-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr))
      }

      main {
        padding: clamp(1rem, 4vw, 2rem) clamp(1rem, 4vw, 1.5rem)
      }

      .page-title {
        font-size: clamp(1.2rem, 4vw, 1.6rem)
      }
    }

    @media (max-width: 480px) {
      .tools-grid {
        grid-template-columns: 1fr
      }

      main {
        padding: 1.25rem 1rem
      }

      .page-title {
        font-size: 1.4rem
      }

      .page-subtitle {
        font-size: .9rem;
        margin-block-end: 1.5rem
      }

      #today-date,
      #user-info {
        display: none
      }
    }

    @media (prefers-reduced-motion: reduce) {
      * {
        transition: none !important;
        animation: none !important
      }

      .tool-card a:hover {
        transform: none
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
        <a href="/admin/asset-management/">
          <span class="tool-icon">🧮</span>
          <span class="tool-title">مدیریت اموال</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/game/">
          <span class="tool-icon">✔️</span>
          <span class="tool-title">مدیریت نظرسنجی</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/game/">
          <span class="tool-icon">📑</span>
          <span class="tool-title">مدیریت آزمون ها</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/prize/">
          <span class="tool-icon">🎡</span>
          <span class="tool-title">مدیریت گردونه شانس</span>
        </a>
      </li>
      <li class="tool-card">
        <a href="/admin/user-management/">
          <span class="tool-icon">🔒</span>
          <span class="tool-title">مدیریت کاربران</span>
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

        if (visibleCount === 0) {
          toolsList.style.display = 'none';
          noResultsMessage.style.display = 'block';
        } else {
          toolsList.style.display = 'grid';
          noResultsMessage.style.display = 'none';
        }
      });
    });
  </script>
</body>

</html>
