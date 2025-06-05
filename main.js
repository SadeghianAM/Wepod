// ===== بارگذاری فوتر از فایل جداگانه =====
fetch("footer.html")
  .then((res) => res.text())
  .then((data) => {
    document.getElementById("footer-placeholder").innerHTML = data;
  });

// ===== تاریخ شمسی با اعداد فارسی =====
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

// تبدیل تاریخ میلادی به شمسی
function toJalali(gy, gm, gd) {
  var g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
  var jy = gy > 1600 ? 979 : 0;
  gy -= gy > 1600 ? 1600 : 621;
  var gy2 = gm > 2 ? gy + 1 : gy;
  var days =
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
  var jm =
    days < 186 ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
  var jd = 1 + (days < 186 ? days % 31 : (days - 186) % 30);
  return [jy, jm, jd];
}

// تبدیل اعداد لاتین به فارسی
function toPersianDigits(str) {
  return str.toString().replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[d]);
}

// گرفتن و نمایش تاریخ امروز شمسی با اعداد فارسی
function getTodayPersianDate() {
  var today = new Date();
  var weekday = weekdays[today.getDay()];
  var gYear = today.getFullYear();
  var gMonth = today.getMonth() + 1;
  var gDay = today.getDate();
  var [jy, jm, jd] = toJalali(gYear, gMonth, gDay);
  var pMonth = persianMonths[jm - 1];
  return toPersianDigits(`امروز ${weekday} ${jd} ${pMonth} ${jy}`);
}

// درج تاریخ در هدر
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("today-date").innerText = getTodayPersianDate();
  updateVideoBankingStatus();
});

// ===== اسکریپت بانکداری ویدیویی =====
function pad(num) {
  return num.toString().padStart(2, "0");
}

async function updateVideoBankingStatus() {
  const statusDiv = document.getElementById("video-banking-status");
  statusDiv.innerHTML = "در حال بررسی وضعیت بانکداری ویدیویی...";

  // تاریخ امروز شمسی
  var today = new Date();
  var gYear = today.getFullYear();
  var gMonth = today.getMonth() + 1;
  var gDay = today.getDate();
  var [jy, jm, jd] = toJalali(gYear, gMonth, gDay);
  var todayStr = `${jy}-${pad(jm)}-${pad(jd)}`;

  // خواندن تعطیلات رسمی
  let holidays = [];
  try {
    const res = await fetch("data/holidays-1404.json");
    holidays = await res.json();
  } catch (e) {
    statusDiv.innerHTML =
      "<div class='video-banking-box closed'>خطا در دریافت لیست تعطیلات رسمی.</div>";
    return;
  }

  // آیا امروز تعطیل رسمی است؟
  const isHoliday = holidays.some((h) => h.date === todayStr);

  // تعیین روز هفته (0=یک‌شنبه ... 6=شنبه)
  let weekday = today.getDay();
  let statusHTML = "";

  if (isHoliday) {
    statusHTML = `
      <div class="video-banking-box closed">
        <b>بانکداری ویدیویی : <span style="font-size:1.2em;">❌ غیرفعال</span></b>
          <br>
        <b>سیکل تسویه پایا :</b> ۱۳:۴۵ الی ۱۴:۵۰
      </div>
    `;
  } else if (weekday >= 1 && weekday <= 3) {
    // دوشنبه تا چهارشنبه (1،2،3)
    statusHTML = `
      <div class="video-banking-box">
        <b>بانکداری ویدیویی: <span style="font-size:1.2em;">✅ فعال</span></b>
        <br>
        بخش احراز هویت از ساعت <b>۷:۳۰ تا ۱۷</b>
        <br>
        بخش انتقال وجه از ساعت <b>۷:۳۰ تا ۱۳:۳۰</b>
      </div>
    `;
  } else if (weekday === 4) {
    // پنج‌شنبه (4)
    statusHTML = `
      <div class="video-banking-box">
        <b>بانکداری ویدیویی: <span style="font-size:1.2em;">✅ فعال</span></b>
        <br>
        بخش احراز هویت از ساعت <b>۷:۳۰ تا ۱۷</b>
        <br>
        بخش انتقال وجه از ساعت <b>۷:۳۰ تا ۱۲:۳۰</b>
      </div>
    `;
  } else {
    // جمعه (5) و شنبه (6) و یک‌شنبه (0)
    statusHTML = `
      <div class="video-banking-box closed">
        <b>بانکداری ویدیویی: <span style="font-size:1.2em;">❌</span></b>
        <br>
        خدمات بانکداری ویدیویی در این روز ارائه نمی‌شود.
      </div>
    `;
  }
  statusDiv.innerHTML = statusHTML;
}
