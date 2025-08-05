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
function toPersianDigitsText(num) {
  return num.toString().replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[d]);
}

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

function getCurrentTimePersian() {
  const now = new Date();
  const h = now.getHours();
  const m = now.getMinutes();
  const persianH = toPersianDigitsText(h.toString().padStart(2, "0"));
  const persianM = toPersianDigitsText(m.toString().padStart(2, "0"));
  return `ساعت ${persianH}:${persianM}`;
}

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("today-date").innerText = getTodayPersianDate();
  const timeElem = document.getElementById("current-time");
  function updateTime() {
    timeElem.innerText = getCurrentTimePersian();
  }
  updateTime();
  setInterval(updateTime, 60 * 1000);

  if (document.getElementById("video-banking-status")) {
    updateVideoBankingStatus();
  }

  if (document.getElementById("service-status")) {
    loadAndDisplayServiceStatus();
  }

  if (document.getElementById("news-alerts-page")) {
    loadAndDisplayNewsAlerts();
  }

  if (document.getElementById("tools-search")) {
    setupToolsSearch();
  }

  if (document.getElementById("payaa-cycle-status")) {
    setupPayaaCycleStatus();
  }
});

function pad(num) {
  return num.toString().padStart(2, "0");
}

async function updateVideoBankingStatus() {
  const statusDiv = document.getElementById("video-banking-status");
  statusDiv.innerHTML = "در حال بررسی وضعیت بانکداری ویدیویی...";

  const today = new Date();
  const gYear = today.getFullYear();
  const gMonth = today.getMonth() + 1;
  const gDay = today.getDate();
  const [jy, jm, jd] = toJalali(gYear, gMonth, gDay);
  const todayStr = `${jy}-${pad(jm)}-${pad(jd)}`;

  let holidays = [];
  try {
    const res = await fetch("data/holidays-1404.json");
    holidays = await res.json();
  } catch (e) {
    statusDiv.innerHTML =
      "<div class='video-banking-box closed'>خطا در دریافت لیست تعطیلات رسمی.</div>";
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
    statusHTML = `
      <div class="video-banking-box closed">
        <b>بانکداری ویدیویی : <span style="font-size:1.2em;">❌ غیرفعال</span></b>
        <br>
        امروز تعطیل است و خدمات بانکداری ویدیویی ارائه نمی‌شود.
      </div>
    `;
  } else {
    let startHour, endHour, endMinute;
    let activeMessage = "";
    let beforeHoursMessage = `
        <div class="video-banking-box closed">
          <b>بانکداری ویدیویی: <span style="font-size:1.2em;">❌ خارج از ساعت کاری</span></b>
          <br>
          ساعات کاری هنوز شروع نشده است (۷ صبح)
        </div>
      `;
    let afterHoursMessage = `
        <div class="video-banking-box closed">
          <b>بانکداری ویدیویی: <span style="font-size:1.2em;">❌ خارج از ساعت کاری</span></b>
          <br>
          ساعات کاری امروز به پایان رسیده است.
        </div>
      `;

    if (weekday >= 6 || weekday <= 3) {
      startHour = workingHours["weekday-sat-wed"].startHour;
      endHour = workingHours["weekday-sat-wed"].endHour;
      endMinute = workingHours["weekday-sat-wed"].endMinute;
      activeMessage = `
        <div class="video-banking-box">
          <b>بانکداری ویدیویی: <span style="font-size:1.2em;">✅ فعال</span></b>
          <br>
          بخش احراز هویت از ساعت <b>۷:۰۰ تا ۱۷:۰۰</b>
          <br>
          بخش انتقال وجه از ساعت <b>۷:۰۰ تا ۱۳:۰۰</b>
        </div>
      `;
    } else if (weekday === 4) {
      startHour = workingHours["weekday-thu"].startHour;
      endHour = workingHours["weekday-thu"].endHour;
      endMinute = workingHours["weekday-thu"].endMinute;
      activeMessage = `
        <div class="video-banking-box">
          <b>بانکداری ویدیویی: <span style="font-size:1.2em;">✅ فعال</span></b>
          <br>
          بخش احراز هویت از ساعت <b>۷:۰۰ تا ۱۷:۰۰</b>
          <br>
          بخش انتقال وجه از ساعت <b>۷:۰۰ تا ۱۲:۳۰</b>
        </div>
      `;
    }
    if (
      currentHour > startHour &&
      (currentHour < endHour ||
        (currentHour === endHour && currentMinute < endMinute))
    ) {
      statusHTML = activeMessage;
    } else if (
      currentHour < startHour ||
      (currentHour === startHour && currentMinute < 0)
    ) {
      statusHTML = beforeHoursMessage;
    } else {
      statusHTML = afterHoursMessage;
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
  if (h > 0 && m > 0) {
    return `${toPersianDigitsText(h)} ساعت و ${toPersianDigitsText(m)} دقیقه`;
  } else if (h > 0) {
    return `${toPersianDigitsText(h)} ساعت`;
  } else {
    return `${toPersianDigitsText(m)} دقیقه`;
  }
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

    const endTime = new Date(now);
    endTime.setHours(cycle.endH, cycle.endM, 0, 0);
    if (now >= cycleTime && now < endTime) {
      return { ...cycle, start: cycleTime, end: endTime, inProgress: true };
    }
  }

  const tomorrow = new Date(now);
  tomorrow.setDate(now.getDate() + 1);
  const [ty, tm, td] = toJalali(
    tomorrow.getFullYear(),
    tomorrow.getMonth() + 1,
    tomorrow.getDate()
  );

  const tomorrowWeekday = tomorrow.getDay();
  const isHolidayTomorrow =
    isHolidayJalali(ty, tm, td, holidays) || tomorrowWeekday === 5;

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

function renderPayaaCycleStatus(holidays) {
  const statusDiv = document.getElementById("payaa-cycle-status");
  if (!statusDiv) return;

  function updateStatus() {
    const now = new Date();
    const cycle = getNextPayaaCycle(now, holidays);

    const nextDate = cycle.start;

    const today = new Date();
    const [jy, jm, jd] = toJalali(
      today.getFullYear(),
      today.getMonth() + 1,
      today.getDate()
    );

    const [cy, cm, cd] = toJalali(
      nextDate.getFullYear(),
      nextDate.getMonth() + 1,
      nextDate.getDate()
    );

    let dayLabel = "";
    if (jy === cy && jm === cm && jd === cd) {
      dayLabel = "امروز";
    } else {
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
          <span>${toPersianTimeStr(diffMin)} تا چرخه بعدی پایا </span>
          <div style="color:#888; font-size:0.95em; margin-top:0.5em;">${nextCycleText}</div>
        </div>
      `;
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
  // Select all the cards that contain tools
  const toolCards = document.querySelectorAll(".column-tools .tool-card");

  if (!searchInput || !toolCards.length) return;

  searchInput.addEventListener("input", function () {
    const query = searchInput.value.trim().toLowerCase();
    const showAll = !query;

    toolCards.forEach((card) => {
      const titleElement = card.querySelector("h2");
      const listItems = card.querySelectorAll("li");

      // The first card containing the search input is special.
      // It should always be visible, but its internal items are filtered.
      if (card.contains(searchInput)) {
        listItems.forEach((li) => {
          const itemText = li.innerText.toLowerCase();
          // Hide or show the item based on the query
          li.style.display = showAll || itemText.includes(query) ? "" : "none";
        });
        return; // Go to the next card
      }

      // For all other cards:
      const titleText = titleElement
        ? titleElement.innerText.toLowerCase()
        : "";
      let hasVisibleItem = false;

      // Filter list items inside the card
      listItems.forEach((li) => {
        const itemText = li.innerText.toLowerCase();
        if (showAll || itemText.includes(query)) {
          li.style.display = "";
          hasVisibleItem = true;
        } else {
          li.style.display = "none";
        }
      });

      // Now, decide if the entire card should be visible
      const titleMatches = titleText.includes(query);

      if (hasVisibleItem || titleMatches) {
        card.style.display = ""; // Show the card

        // If the card is visible ONLY because the title matched (but no items did),
        // then we should show all of its items.
        if (titleMatches && !hasVisibleItem) {
          listItems.forEach((li) => {
            li.style.display = "";
          });
        }
      } else {
        card.style.display = "none"; // Hide the card
      }
    });
  });
}

async function loadAndDisplayServiceStatus() {
  const serviceStatusDiv = document.getElementById("service-status");
  if (!serviceStatusDiv) return;

  serviceStatusDiv.innerHTML = "در حال بارگذاری وضعیت سرویس‌ها...";

  try {
    const response = await fetch("data/service-status.json");
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const services = await response.json();

    let html = "";
    if (services.length === 0) {
      html = `<div class="news-alert-box green">همه سرویس‌ها فعال هستند.</div>`;
    } else {
      services.forEach((service) => {
        let colorClass = "green";
        if (service.status === "غیرفعال") {
          colorClass = "red";
        } else if (service.status === "در حال بررسی") {
          colorClass = "yellow";
        } else if (service.status === "اختلال در عملکرد") {
          colorClass = "orange";
        }

        html += `
          <div class="news-alert-box ${colorClass}">
            <b>${service.name}:</b> ${service.status}
            ${
              service.description
                ? `<p style="margin-top: 8px; margin-bottom: 0;">${service.description}</p>`
                : ""
            }
          </div>
        `;
      });
    }
    serviceStatusDiv.innerHTML = html;
  } catch (error) {
    console.error("Could not fetch service status:", error);
    serviceStatusDiv.innerHTML = `<div class="news-alert-box red">خطا در بارگذاری وضعیت سرویس‌ها.</div>`;
  }
}

async function loadAndDisplayNewsAlerts() {
  const newsAlertsDiv = document.getElementById("news-alerts-page");
  if (!newsAlertsDiv) return;

  newsAlertsDiv.innerHTML = "در حال بارگذاری اطلاعیه‌ها...";

  try {
    const response = await fetch("data/news-alerts.json");
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const alerts = await response.json();

    let html = "";
    if (alerts.length === 0) {
      html = `<div class="news-alert-box green">در حال حاضر اطلاعیه جدیدی وجود ندارد.</div>`;
    } else {
      alerts.forEach((alert) => {
        const colorClass = alert.color;
        let startDateTimeInfo = "";
        let endDateTimeInfo = "";
        let durationInfo = "";

        if (alert.startDate && alert.startTime) {
          const persianStartDate = toPersianDigits(alert.startDate);
          const persianStartTime = toPersianDigits(alert.startTime);
          startDateTimeInfo = `<p style="font-size:0.9em; color:#666; margin-top:5px; margin-bottom:0;">شروع: ${persianStartDate} ساعت ${persianStartTime}</p>`;
        }

        if (alert.endDate && alert.endTime) {
          const persianEndDate = toPersianDigits(alert.endDate);
          const persianEndTime = toPersianDigits(alert.endTime);
          endDateTimeInfo = `<p style="font-size:0.9em; color:#666; margin-top:5px; margin-bottom:0;">پایان: ${persianEndDate} ساعت ${persianEndTime}</p>`;
          try {
            const [sYear, sMonth, sDay] = alert.startDate
              .split("-")
              .map(Number);
            const [eYear, eMonth, eDay] = alert.endDate.split("-").map(Number);
            const [sHour, sMin] = alert.startTime.split(":").map(Number);
            const [eHour, eMin] = alert.endTime.split(":").map(Number);
          } catch (e) {
            console.error("خطا در محاسبه مدت زمان:", e);
          }
        }

        html += `
          <div class="news-alert-box ${colorClass}">
            <b>${alert.title}</b>
            ${
              alert.description
                ? `<p style="margin-top: 8px; margin-bottom: 0;">${alert.description}</p>`
                : ""
            }
            ${startDateTimeInfo}
            ${endDateTimeInfo}
          </div>
        `;
      });
    }
    newsAlertsDiv.innerHTML = html;
  } catch (error) {
    console.error("Could not fetch news alerts:", error);
    newsAlertsDiv.innerHTML = `<div class="news-alert-box red">خطا در بارگذاری اطلاعیه‌ها.</div>`;
  }
}
