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
function toPersianDigitsText(num) {
  return num.toString().replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[d]);
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

// نمایش ساعت فعلی به صورت فارسی
function getCurrentTimePersian() {
  const now = new Date();
  const h = now.getHours();
  const m = now.getMinutes();
  const persianH = toPersianDigitsText(h.toString().padStart(2, "0"));
  const persianM = toPersianDigitsText(m.toString().padStart(2, "0"));
  return `ساعت ${persianH}:${persianM}`;
}

// درج تاریخ و ساعت در هدر
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("today-date").innerText = getTodayPersianDate();
  const timeElem = document.getElementById("current-time");
  function updateTime() {
    timeElem.innerText = getCurrentTimePersian();
  }
  updateTime();
  setInterval(updateTime, 60 * 1000);

  updateVideoBankingStatus();
  setNewsAlerts([
    {
      text: "<b>متن های زیر جهت نمایش ویژگی‌ها و قابلیت‌های این بخش وارد شده اند.</b>",
      color: "yellow",
    },
    {
      text: "مشکل سامانه اعتبارسنجی ایرانیان درحال پیگیری / اختلال ثبت کنید.",
      color: "red",
    },
    {
      text: 'نسخه جدید ویپاد در بازار منتشر شد! <a href="https://cafebazaar.ir/app/com.dotin.wepod" target="_blank" style="color:#00ae70; text-decoration:underline;">صفحه برنامه</a>',
      color: "green",
    },
  ]);
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

  if (isHoliday || weekday === 5) {
    statusHTML = `
      <div class="video-banking-box closed">
        <b>بانکداری ویدیویی : <span style="font-size:1.2em;">❌ غیرفعال</span></b>
        <br>
        امروز تعطیل رسمی است و خدمات بانکداری ویدیویی ارائه نمی‌شود.
      </div>
    `;
  } else if (weekday >= 6 || weekday <= 3) {
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
    statusHTML = `
      <div class="video-banking-box">
        <b>بانکداری ویدیویی: <span style="font-size:1.2em;">✅ فعال</span></b>
        <br>
        بخش احراز هویت از ساعت <b>۷:۳۰ تا ۱۷</b>
        <br>
        بخش انتقال وجه از ساعت <b>۷:۳۰ تا ۱۲:۳۰</b>
      </div>
    `;
  }
  statusDiv.innerHTML = statusHTML;
}

// ====== اخبار و اطلاعیه‌های مهم ======
function setNewsAlerts(newsItems) {
  const newsDiv = document.getElementById("news-alerts");
  if (!newsDiv) return;
  if (!newsItems || newsItems.length === 0) {
    newsDiv.innerHTML = "";
    return;
  }
  let html = "";
  newsItems.forEach((item) => {
    const colorClass = item.color || "green";
    html += `
      <div class="news-alert-box ${colorClass}">
        ${item.text}
      </div>
    `;
  });
  newsDiv.innerHTML = html;
}

// ======= نمایش زمان‌بندی پایا و شمارش معکوس =======

// لیست سیکل‌های پایا (روز غیر تعطیل)
const payaaCycles = [
  { hour: 3, min: 45, endH: 4, endM: 50 },
  { hour: 10, min: 45, endH: 11, endM: 50 },
  { hour: 13, min: 45, endH: 14, endM: 50 },
  { hour: 18, min: 45, endH: 19, endM: 50 },
];

// سیکل مخصوص تعطیلات رسمی
const holidayCycle = [{ hour: 13, min: 45, endH: 14, endM: 50 }];

// تابع بررسی تعطیلی رسمی بودن یک تاریخ شمسی
function isHolidayJalali(jy, jm, jd, holidays) {
  const dateStr = `${jy}-${pad(jm)}-${pad(jd)}`;
  return holidays.some((h) => h.date === dateStr);
}

function toPersianTimeStr(totalMin) {
  let h = Math.floor(totalMin / 60);
  let m = totalMin % 60;
  if (h > 0 && m > 0) {
    return `${toPersianDigitsText(h)} ساعت و ${toPersianDigitsText(m)} دقیقه`;
  } else if (h > 0) {
    return `${toPersianDigitsText(h)} ساعت`;
  } else {
    return `${toPersianDigitsText(m)} دقیقه`;
  }
}

// تابع یافتن چرخه بعدی پایا با در نظر گرفتن تعطیلی امروز و فردا
function getNextPayaaCycle(now, holidays) {
  // تاریخ امروز شمسی
  const [jy, jm, jd] = toJalali(
    now.getFullYear(),
    now.getMonth() + 1,
    now.getDate()
  );
  const isHolidayToday = isHolidayJalali(jy, jm, jd, holidays);

  // سیکل‌های امروز (با توجه به تعطیلی)
  let todayCycles = isHolidayToday ? holidayCycle : payaaCycles;

  for (let cycle of todayCycles) {
    const cycleTime = new Date(now);
    cycleTime.setHours(cycle.hour, cycle.min, 0, 0);
    if (now < cycleTime) {
      return {
        ...cycle,
        start: cycleTime,
        end: new Date(
          cycleTime.getTime() +
            (cycle.endH * 60 + cycle.endM - (cycle.hour * 60 + cycle.min)) *
              60000
        ),
      };
    }
    // اگر داخل بازه تسویه باشیم
    const endTime = new Date(now);
    endTime.setHours(cycle.endH, cycle.endM, 0, 0);
    if (now >= cycleTime && now < endTime) {
      return { ...cycle, start: cycleTime, end: endTime, inProgress: true };
    }
  }

  // اگر همه سیکل‌های امروز تمام شده، اولین سیکل فردا با توجه به تعطیلی فردا
  const tomorrow = new Date(now);
  tomorrow.setDate(now.getDate() + 1);
  const [ty, tm, td] = toJalali(
    tomorrow.getFullYear(),
    tomorrow.getMonth() + 1,
    tomorrow.getDate()
  );
  const isHolidayTomorrow = isHolidayJalali(ty, tm, td, holidays);
  const tomorrowCycles = isHolidayTomorrow ? holidayCycle : payaaCycles;
  const firstCycle = tomorrowCycles[0];
  tomorrow.setHours(firstCycle.hour, firstCycle.min, 0, 0);
  return {
    ...firstCycle,
    start: tomorrow,
    end: new Date(
      tomorrow.getTime() +
        (firstCycle.endH * 60 +
          firstCycle.endM -
          (firstCycle.hour * 60 + firstCycle.min)) *
          60000
    ),
  };
}

// تابع رندر وضعیت چرخه
function renderPayaaCycleStatus(holidays) {
  const statusDiv = document.getElementById("payaa-cycle-status");
  if (!statusDiv) return;

  function updateStatus() {
    const now = new Date();
    const cycle = getNextPayaaCycle(now, holidays);

    // ساخت رشته تاریخ و ساعت چرخه بعدی
    const nextDate = cycle.start;

    // تاریخ امروز به شمسی
    const today = new Date();
    const [jy, jm, jd] = toJalali(
      today.getFullYear(),
      today.getMonth() + 1,
      today.getDate()
    );
    // تاریخ چرخه بعدی به شمسی
    const [cy, cm, cd] = toJalali(
      nextDate.getFullYear(),
      nextDate.getMonth() + 1,
      nextDate.getDate()
    );

    let dayLabel = "";
    if (jy === cy && jm === cm && jd === cd) {
      dayLabel = "امروز";
    } else {
      // یک روز بعد؟
      const tomorrow = new Date(today);
      tomorrow.setDate(today.getDate() + 1);
      const [ty, tm, td] = toJalali(
        tomorrow.getFullYear(),
        tomorrow.getMonth() + 1,
        tomorrow.getDate()
      );
      if (cy === ty && cm === tm && cd === td) {
        dayLabel = "فردا";
      } else {
        dayLabel = weekdays[nextDate.getDay()];
      }
    }

    const pMonth = persianMonths[cm - 1];
    const persianDay = toPersianDigitsText(cd);
    const persianHour = toPersianDigitsText(cycle.hour);
    const persianMin = toPersianDigitsText(
      cycle.min.toString().padStart(2, "0")
    );
    const nextCycleText = `چرخه بعدی : ${dayLabel} ${persianDay} ${pMonth} ساعت ${persianHour}:${persianMin}`;

    if (cycle.inProgress) {
      statusDiv.innerHTML = `
        <div class="news-alert-box yellow" style="font-weight:bold;">
          <span>درحال تسویه درخواست‌های ثبت‌شده پایا</span>
          <div style="color:#888; font-size:0.95em; margin-top:0.5em;">${nextCycleText}</div>
        </div>
      `;
    } else {
      const diffMs = cycle.start - now;
      let diffMin = Math.ceil(diffMs / (60 * 1000));
      if (diffMin < 1) diffMin = 1;
      statusDiv.innerHTML = `
        <div class="news-alert-box green" style="font-weight:bold;">
          <span>${toPersianTimeStr(diffMin)} تا چرخه بعدی پایا</span>
          <div style="color:#888; font-size:0.95em; margin-top:0.5em;">${nextCycleText}</div>
        </div>
      `;
    }
  }

  updateStatus();
  setInterval(updateStatus, 60 * 1000); // هر دقیقه یکبار آپدیت شود
}

// ترکیب با سیستم تعطیلات موجود
async function setupPayaaCycleStatus() {
  let holidays = [];
  try {
    const res = await fetch("data/holidays-1404.json");
    holidays = await res.json();
  } catch (e) {}

  renderPayaaCycleStatus(holidays);
}

document.addEventListener("DOMContentLoaded", setupPayaaCycleStatus);

// ====================== جستجوی ابزارهای پشتیبانی =======================
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("tools-search");
  if (!searchInput) return;

  // همه عناوین و لیست‌ها را جدا می‌کنیم
  const column = document.querySelector(".column-tools");
  const sections = [];
  let curTitle = null,
    curList = null;

  Array.from(column.children).forEach((el) => {
    if (el.tagName === "H2") curTitle = el;
    if (el.tagName === "UL" && curTitle) {
      sections.push({ title: curTitle, list: el });
      curTitle = null;
    }
  });

  searchInput.addEventListener("input", function () {
    const value = searchInput.value.trim().toLowerCase();
    const showAll = !value;

    sections.forEach(({ title, list }) => {
      let hasVisible = false;
      Array.from(list.querySelectorAll("li")).forEach((li) => {
        const text = li.innerText.replace(/\s+/g, " ").toLowerCase();
        const matched = showAll || text.includes(value);
        li.style.display = matched ? "" : "none";
        if (matched) hasVisible = true;
      });
      // فقط تیترهایی که آیتم نمایش داده شده دارند نشان بده
      title.style.display = hasVisible ? "" : "none";
      list.style.display = hasVisible ? "" : "none";
    });
  });
});
