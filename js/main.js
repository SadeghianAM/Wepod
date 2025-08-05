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
  return toPersianDigits(`${weekday}، ${jd} ${persianMonths[jm - 1]} ${jy}`);
}

function getCurrentTimePersian() {
  const now = new Date();
  const h = now.getHours().toString().padStart(2, "0");
  const m = now.getMinutes().toString().padStart(2, "0");
  return `ساعت ${toPersianDigits(h)}:${toPersianDigits(m)}`;
}

function setupThemeToggle() {
  const themeToggle = document.getElementById("theme-toggle");
  const currentTheme = localStorage.getItem("theme");
  if (currentTheme) {
    document.body.classList.add(currentTheme);
    if (currentTheme === "dark-mode") {
      themeToggle.checked = true;
    }
  }
  themeToggle.addEventListener("change", function () {
    if (this.checked) {
      document.body.classList.add("dark-mode");
      localStorage.setItem("theme", "dark-mode");
    } else {
      document.body.classList.remove("dark-mode");
      localStorage.setItem("theme", "light-mode");
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const todayDateElem = document.getElementById("today-date");
  const timeElem = document.getElementById("current-time");
  if (todayDateElem) todayDateElem.innerText = getTodayPersianDate();
  if (timeElem) {
    const updateTime = () => (timeElem.innerText = getCurrentTimePersian());
    updateTime();
    setInterval(updateTime, 60 * 1000);
  }
  setupThemeToggle();
  if (document.getElementById("video-banking-status"))
    updateVideoBankingStatus();
  if (document.getElementById("service-status")) loadAndDisplayServiceStatus();
  if (document.getElementById("news-alerts-page")) loadAndDisplayNewsAlerts();
  if (document.getElementById("tools-search")) setupToolsSearch();
  if (document.getElementById("payaa-cycle-status")) setupPayaaCycleStatus();
});

function pad(num) {
  return num.toString().padStart(2, "0");
}

async function updateVideoBankingStatus() {
  const statusDiv = document.getElementById("video-banking-status");
  statusDiv.innerHTML = `<div class="status-box blue">در حال بررسی وضعیت بانکداری ویدیویی...</div>`;
  const today = new Date();
  const [jy, jm, jd] = toJalali(
    today.getFullYear(),
    today.getMonth() + 1,
    today.getDate()
  );
  const todayStr = `${jy}-${pad(jm)}-${pad(jd)}`;
  let holidays = [];
  try {
    const res = await fetch("data/holidays-1404.json");
    holidays = await res.json();
  } catch (e) {
    statusDiv.innerHTML = `<div class="status-box red"><b>خطا:</b> عدم دسترسی به لیست تعطیلات.</div>`;
    return;
  }
  const isHoliday = holidays.some((h) => h.date === todayStr);
  const weekday = today.getDay();
  const currentHour = today.getHours();
  const currentMinute = today.getMinutes();
  let statusHTML = "";
  const workingHours = {
    "weekday-sat-wed": { startHour: 7, endHour: 17, endMinute: 0 },
    "weekday-thu": { startHour: 7, endHour: 17, endMinute: 0 },
  };
  if (isHoliday || weekday === 5) {
    statusHTML = `<div class="status-box red"><b>بانکداری ویدیویی: ❌ غیرفعال</b><p style="margin: 0.5rem 0 0; font-size: 0.9em;">امروز تعطیل است و این سرویس ارائه نمی‌شود.</p></div>`;
  } else {
    let wh =
      weekday >= 6 || weekday <= 3
        ? workingHours["weekday-sat-wed"]
        : workingHours["weekday-thu"];
    let nowInMinutes = currentHour * 60 + currentMinute;
    let startInMinutes = wh.startHour * 60;
    let endInMinutes = wh.endHour * 60 + wh.endMinute;
    let transferEndTime = weekday === 4 ? "۱۲:۳۰" : "۱۳:۰۰";
    if (nowInMinutes >= startInMinutes && nowInMinutes < endInMinutes) {
      statusHTML = `<div class="status-box green"><b>بانکداری ویدیویی: ✅ فعال</b><p style="margin: 0.5rem 0 0; font-size: 0.9em;">احراز هویت: ۷ صبح تا ۵ عصر <br> انتقال وجه: ۷ صبح تا ${toPersianDigits(
        transferEndTime
      )}</p></div>`;
    } else if (nowInMinutes < startInMinutes) {
      statusHTML = `<div class="status-box yellow"><b>بانکداری ویدیویی: ❌ خارج از ساعت کاری</b><p style="margin: 0.5rem 0 0; font-size: 0.9em;">ساعت کاری از ۷ صبح شروع می‌شود.</p></div>`;
    } else {
      statusHTML = `<div class="status-box red"><b>بانکداری ویدیویی: ❌ خارج از ساعت کاری</b><p style="margin: 0.5rem 0 0; font-size: 0.9em;">ساعت کاری امروز به پایان رسیده است.</p></div>`;
    }
  }
  statusDiv.innerHTML = statusHTML;
}

const payaaCycles = [
  { hour: 3, min: 45, endH: 4, endM: 50 },
  { hour: 9, min: 45, endH: 10, endM: 50 },
  { hour: 12, min: 45, endH: 13, endM: 50 },
  { hour: 18, min: 45, endH: 19, endM: 50 },
];
const holidayCycle = [{ hour: 12, min: 45, endH: 13, endM: 50 }];

function isHolidayJalali(jy, jm, jd, holidays) {
  const dateStr = `${jy}-${pad(jm)}-${pad(jd)}`;
  return holidays.some((h) => h.date === dateStr);
}

function toPersianTimeStr(totalMin) {
  let h = Math.floor(totalMin / 60);
  let m = totalMin % 60;
  if (h > 0 && m > 0)
    return `${toPersianDigits(h)} ساعت و ${toPersianDigits(m)} دقیقه`;
  if (h > 0) return `${toPersianDigits(h)} ساعت`;
  return `${toPersianDigits(m)} دقیقه`;
}

function getNextPayaaCycle(now, holidays) {
  const [jy, jm, jd] = toJalali(
    now.getFullYear(),
    now.getMonth() + 1,
    now.getDate()
  );
  const weekday = now.getDay();
  const isHolidayToday = isHolidayJalali(jy, jm, jd, holidays);
  let todayCycles =
    isHolidayToday || weekday === 5 ? holidayCycle : payaaCycles;
  for (let cycle of todayCycles) {
    const cycleTime = new Date(now);
    cycleTime.setHours(cycle.hour, cycle.min, 0, 0);
    if (now < cycleTime)
      return { ...cycle, start: cycleTime, inProgress: false };
    const endTime = new Date(now);
    endTime.setHours(cycle.endH, cycle.endM, 0, 0);
    if (now >= cycleTime && now < endTime)
      return { ...cycle, start: cycleTime, inProgress: true };
  }
  const tomorrow = new Date(now);
  tomorrow.setDate(now.getDate() + 1);
  const [ty, tm, td] = toJalali(
    tomorrow.getFullYear(),
    tomorrow.getMonth() + 1,
    tomorrow.getDate()
  );
  const isHolidayTomorrow =
    isHolidayJalali(ty, tm, td, holidays) || tomorrow.getDay() === 5;
  const tomorrowCycles = isHolidayTomorrow ? holidayCycle : payaaCycles;
  const firstCycle = tomorrowCycles[0];
  tomorrow.setHours(firstCycle.hour, firstCycle.min, 0, 0);
  return { ...firstCycle, start: tomorrow, inProgress: false };
}

function renderPayaaCycleStatus(holidays) {
  const statusDiv = document.getElementById("payaa-cycle-status");
  if (!statusDiv) return;
  function updateStatus() {
    const now = new Date();
    const cycle = getNextPayaaCycle(now, holidays);
    const [cy, cm, cd] = toJalali(
      cycle.start.getFullYear(),
      cycle.start.getMonth() + 1,
      cycle.start.getDate()
    );
    let dayLabel = "";
    const todayJalali = toJalali(
      now.getFullYear(),
      now.getMonth() + 1,
      now.getDate()
    );
    if (todayJalali[0] === cy && todayJalali[1] === cm && todayJalali[2] === cd)
      dayLabel = "امروز";
    else dayLabel = "فردا";
    const nextCycleText = `چرخه بعدی: ${dayLabel} ساعت ${toPersianDigits(
      cycle.hour.toString().padStart(2, "0")
    )}:${toPersianDigits(cycle.min.toString().padStart(2, "0"))}`;
    if (cycle.inProgress) {
      statusDiv.innerHTML = `<div class="status-box yellow"><b>چرخه پایا در حال انجام است</b><div class="payaa-subtext">درحال تسویه درخواست‌های ثبت‌شده...</div><div class="payaa-subtext" style="opacity: 0.7;">${nextCycleText}</div></div>`;
    } else {
      const diffMin = Math.ceil((cycle.start - now) / 60000);
      statusDiv.innerHTML = `<div class="status-box green"><b>${toPersianTimeStr(
        diffMin
      )} تا چرخه بعدی پایا</b><div class="payaa-subtext">${nextCycleText}</div></div>`;
    }
  }
  updateStatus();
  setInterval(updateStatus, 60 * 1000);
}

async function setupPayaaCycleStatus() {
  let holidays = [];
  try {
    const res = await fetch("data/holidays-1404.json");
    holidays = await res.json();
  } catch (e) {}
  renderPayaaCycleStatus(holidays);
}

function setupToolsSearch() {
  const searchInput = document.getElementById("tools-search");
  if (!searchInput) return;
  const column = document.querySelector(".column-right");
  const sections = [];
  let curTitle = null;
  Array.from(column.children).forEach((el) => {
    if (el.tagName === "H2") {
      curTitle = el;
    }
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
      title.style.display = hasVisible || showAll ? "" : "none";
      list.style.display = hasVisible || showAll ? "" : "none";
    });
  });
}

async function loadAndDisplayServiceStatus() {
  const serviceStatusDiv = document.getElementById("service-status");
  if (!serviceStatusDiv) return;
  serviceStatusDiv.innerHTML = `<div class="status-box blue">در حال بارگذاری وضعیت سرویس‌ها...</div>`;
  try {
    const response = await fetch("data/service-status.json");
    const services = await response.json();
    let html = "";
    if (services.length === 0) {
      html = `<div class="status-box green"><b>وضعیت پایدار:</b> همه سرویس‌ها فعال هستند.</div>`;
    } else {
      services.forEach((service) => {
        let colorClass = "green";
        if (service.status === "غیرفعال") colorClass = "red";
        else if (service.status === "در حال بررسی") colorClass = "yellow";
        else if (service.status === "اختلال در عملکرد") colorClass = "orange";
        html += `<div class="status-box ${colorClass}"><b>${
          service.name
        }:</b> ${service.status}${
          service.description
            ? `<p style="margin: 0.5rem 0 0; font-size: 0.9em;">${service.description}</p>`
            : ""
        }</div>`;
      });
    }
    serviceStatusDiv.innerHTML = html;
  } catch (error) {
    serviceStatusDiv.innerHTML = `<div class="status-box red"><b>خطا:</b> در بارگذاری وضعیت سرویس‌ها مشکلی پیش آمد.</div>`;
  }
}

async function loadAndDisplayNewsAlerts() {
  const newsAlertsDiv = document.getElementById("news-alerts-page");
  if (!newsAlertsDiv) return;
  newsAlertsDiv.innerHTML = `<div class="status-box blue">در حال بارگذاری اطلاعیه‌ها...</div>`;
  try {
    const response = await fetch("data/news-alerts.json");
    const alerts = await response.json();
    let html = "";
    if (alerts.length === 0) {
      html = `<div class="status-box green">در حال حاضر اطلاعیه جدیدی وجود ندارد.</div>`;
    } else {
      alerts.forEach((alert) => {
        const colorClass = alert.color || "blue";
        html += `<div class="news-alert-box ${colorClass}"><b>${
          alert.title
        }</b>${
          alert.description
            ? `<p style="margin: 0.5rem 0 0;">${alert.description}</p>`
            : ""
        }${
          alert.startDate
            ? `<p style="margin: 0.75rem 0 0; font-size: 0.85em; opacity: 0.8;">${toPersianDigits(
                `شروع: ${alert.startDate} ${alert.startTime}`
              )}${
                alert.endDate
                  ? ` | ${toPersianDigits(
                      `پایان: ${alert.endDate} ${alert.endTime}`
                    )}`
                  : ""
              }</p>`
            : ""
        }</div>`;
      });
    }
    newsAlertsDiv.innerHTML = html;
  } catch (error) {
    newsAlertsDiv.innerHTML = `<div class="news-alert-box red"><b>خطا:</b> در بارگذاری اطلاعیه‌ها مشکلی پیش آمد.</div>`;
  }
}
