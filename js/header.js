const weekdays = [
  "یک‌شنبه",
  "دوشنبه",
  "سه‌شنبه",
  "چهارشنبه",
  "پنج‌شنبه",
  "جمعه",
  "شنبه",
];
const persianMonths = [
  "فروردین",
  "اردیبهشت",
  "خرداد",
  "تیر",
  "مرداد",
  "شهریور",
  "مهر",
  "آبان",
  "آذر",
  "دی",
  "بهمن",
  "اسفند",
];

// یک فلگ برای اینکه مطمئن شویم استایل‌ها فقط یک بار به صفحه اضافه می‌شوند
let stylesInjected = false;

/**
 * استایل‌های مربوط به باکس اطلاعات کاربر را به صورت داینامیک به <head> اضافه می‌کند
 */
function injectUserInfoStyles() {
  if (stylesInjected) return; // اگر استایل‌ها قبلاً اضافه شده‌اند، دوباره کاری نکن

  const css = `
    #user-info-box {
      position: fixed;
      top: 80px;
      left: 10px;
      background-color: #ffffff;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 12px 18px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      font-family: "Vazirmatn", sans-serif;
      z-index: 1000;
      direction: rtl;
      min-width: 220px;
    }
    #user-info-box p {
      margin: 0 0 8px 0;
      font-size: 14px;
      color: #343a40;
    }
    #user-info-box p strong {
      font-weight: bold;
      margin-left: 5px;
    }
    #logout-button {
      width: 100%;
      padding: 8px;
      background-color: #dc3545;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      font-family: "Vazirmatn", sans-serif;
      transition: background-color 0.3s;
      margin-top: 8px;
    }
    #logout-button:hover {
      background-color: #c82333;
    }
  `;

  const styleElement = document.createElement("style");
  styleElement.type = "text/css";
  styleElement.appendChild(document.createTextNode(css));
  document.head.appendChild(styleElement);
  stylesInjected = true;
}

function toJalali(gy, gm, gd) {
  const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
  let jy = gy > 1600 ? 979 : 0;
  gy -= gy > 1600 ? 1600 : 621;
  const gy2 = gm > 2 ? gy + 1 : gy;
  let days =
    365 * gy +
    Math.floor((gy2 + 3) / 4) -
    Math.floor((gy2 + 99) / 100) +
    Math.floor((gy2 + 399) / 400) -
    80 +
    gd +
    g_d_m[gm - 1];
  jy += 33 * Math.floor(days / 12053);
  days %= 12053;
  jy += 4 * Math.floor(days / 1461);
  days %= 1461;
  if (days > 365) {
    jy += Math.floor((days - 1) / 365);
    days = (days - 1) % 365;
  }
  const jm =
    days < 186 ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
  const jd = 1 + (days < 186 ? days % 31 : (days - 186) % 30);
  return [jy, jm, jd];
}

function toPersianDigits(str) {
  return str.toString().replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[d]);
}

function getTodayPersianDate() {
  const today = new Date();
  const weekday = weekdays[today.getDay()];
  const [jy, jm, jd] = toJalali(
    today.getFullYear(),
    today.getMonth() + 1,
    today.getDate()
  );
  const pMonth = persianMonths[jm - 1];
  return toPersianDigits(`امروز ${weekday} ${jd} ${pMonth} ${jy}`);
}

function getCurrentTimePersian() {
  const now = new Date();
  const h = now.getHours().toString().padStart(2, "0");
  const m = now.getMinutes().toString().padStart(2, "0");
  return `ساعت ${toPersianDigits(h)}:${toPersianDigits(m)}`;
}

function setupHeader(title) {
  const dateElem = document.getElementById("today-date");
  if (dateElem) {
    dateElem.innerText = getTodayPersianDate();
  }

  const timeElem = document.getElementById("current-time");
  if (timeElem) {
    const updateTime = () => {
      timeElem.innerText = getCurrentTimePersian();
    };
    updateTime();
    setInterval(updateTime, 60 * 1000);
  }

  if (title) {
    const pageTitleElem = document.getElementById("page-title");
    if (pageTitleElem) {
      pageTitleElem.innerText = title;
    }
  }
}

async function logout() {
  await fetch("/php/logout.php");
  localStorage.removeItem("jwt");
  window.location.href = "/login.html";
}

async function checkLoginStatus() {
  try {
    const response = await fetch("/php/get-user-info.php");
    if (response.ok) {
      const userData = await response.json();

      // مرحله 1: استایل‌ها را به صفحه اضافه کن
      injectUserInfoStyles();

      // مرحله 2: باکس اطلاعات کاربر را بساز
      const userInfoBox = document.createElement("div");
      userInfoBox.id = "user-info-box";
      userInfoBox.innerHTML = `
        <p><strong>نام:</strong> ${userData.name}</p>
        <p><strong>داخلی:</strong> ${toPersianDigits(userData.id)}</p>
        <button id="logout-button">خروج از وی هاب</button>
      `;

      document.body.prepend(userInfoBox);

      document
        .getElementById("logout-button")
        .addEventListener("click", logout);
    } else {
      console.log("User not logged in.");
    }
  } catch (error) {
    console.error("Error checking login status:", error);
  }
}

async function loadLayout() {
  try {
    const [headerRes, footerRes] = await Promise.all([
      fetch("/header.html"),
      fetch("/footer.html"),
    ]);

    const headerData = await headerRes.text();
    document.getElementById("header-placeholder").innerHTML = headerData;

    const pageTitle = document.querySelector("title")?.innerText;
    if (typeof setupHeader === "function") {
      setupHeader(pageTitle);
    }

    const footerData = await footerRes.text();
    document.getElementById("footer-placeholder").innerHTML = footerData;

    await checkLoginStatus();
  } catch (error) {
    console.error("Error loading layout components:", error);
  }
}

document.addEventListener("DOMContentLoaded", loadLayout);
