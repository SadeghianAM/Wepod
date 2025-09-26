document.addEventListener("DOMContentLoaded", () => {
  function fetchNoCache(url, options = {}) {
    const timestamp = new Date().getTime();
    const separator = url.includes("?") ? "&" : "?";
    const urlWithCacheBust = `${url}${separator}t=${timestamp}`;
    return fetch(urlWithCacheBust, options);
  }

  async function initializeApp() {
    try {
      await loadLayout();
    } catch (error) {
      console.error(
        "Could not load the main layout (header/footer). App initialization stopped.",
        error
      );
      return;
    }
    await checkLoginStatus();
    setupHeader();
    loadMainContent();
  }

  async function loadLayout() {
    const headerPlaceholder = document.getElementById("header-placeholder");
    const footerPlaceholder = document.getElementById("footer-placeholder");
    if (!headerPlaceholder || !footerPlaceholder) {
      console.error(
        "Critical Error: Header or Footer placeholder not found in HTML."
      );
      throw new Error("Missing placeholder elements.");
    }
    const [headerRes, footerRes] = await Promise.all([
      fetchNoCache("/header.html").catch((e) => {
        console.error("Failed to fetch header.html:", e);
        return null;
      }),
      fetchNoCache("/footer.html").catch((e) => {
        console.error("Failed to fetch footer.html:", e);
        return null;
      }),
    ]);
    if (headerRes && headerRes.ok) {
      headerPlaceholder.innerHTML = await headerRes.text();
    } else {
      headerPlaceholder.innerHTML = `<header style="background-color: #d93025; color: white; text-align: center; padding: 1rem;">خطا: فایل header.html یافت نشد.</header>`;
    }
    if (footerRes && footerRes.ok) {
      footerPlaceholder.innerHTML = await footerRes.text();
    } else {
      footerPlaceholder.innerHTML = `<footer style="background-color: #d93025; color: white; text-align: center; padding: 1rem;">خطا: فایل footer.html یافت نشد.</footer>`;
    }
  }

  function loadMainContent() {
    if (document.getElementById("card-tracking-info")) updateCardTrackingInfo();
    if (document.getElementById("video-banking-status"))
      updateVideoBankingStatus();
    if (document.getElementById("service-status"))
      loadAndDisplayServiceStatus();
    if (document.getElementById("news-alerts-page")) loadAndDisplayNewsAlerts();
    if (document.getElementById("payaa-cycle-status")) setupPayaaCycleStatus();
    if (document.getElementById("tools-search")) setupToolsSearch();
  }

  // ==========================
  // Auth (HttpOnly Cookie Flow)
  // ==========================
  async function logout() {
    try {
      // این بخش با بک‌اند هماهنگ است و نیازی به تغییر ندارد
      await fetchNoCache("/auth/logout.php?json=1", {
        credentials: "same-origin",
      });
    } catch (e) {
      console.warn("Logout request failed (ignored):", e);
    }
    // این خط برای پاک‌سازی حالت‌های قدیمی است و بودن آن مشکلی ایجاد نمی‌کند
    try {
      localStorage.removeItem("jwt");
    } catch (e) {}
    window.location.href = "/auth/login.html";
  }

  async function checkLoginStatus() {
    const placeholder = document.getElementById("user-info-placeholder");
    if (!placeholder) return;
    const pick = (obj, ...keys) => {
      for (const k of keys) {
        if (obj && typeof obj[k] === "string" && obj[k].trim() !== "")
          return obj[k];
        if (obj && typeof obj[k] === "number") return String(obj[k]);
      }
      return "";
    };
    try {
      // این بخش کاملاً صحیح است و با بک‌اند جدید کار می‌کند
      const response = await fetchNoCache("/auth/get-user-info.php", {
        credentials: "same-origin",
      });
      if (response.ok) {
        const payload = await response.json();
        if (!payload || !payload.ok || !payload.user) {
          throw new Error("Invalid user info payload");
        }
        const u = payload.user;

        // *** تغییر کلیدی در اینجا اعمال شد ***
        // کلید 'full_name' حالا از 'name' در دیتابیس پر می‌شود
        const name =
          pick(u, "full_name", "name", "displayName") ||
          pick(u, "username") ||
          "کاربر";

        // *** تغییر کلیدی برای شماره داخلی (استفاده از id) ***
        const internal =
          pick(
            u,
            "extension",
            "ext",
            "internal",
            "internal_number",
            "phone_extension",
            "phoneExt"
          ) || pick(u, "id"); // اگر فیلدهای مرسوم نبود، از ستون id استفاده می‌شود
        const avatarLetter = (name || "؟").trim().charAt(0) || "؟";
        placeholder.innerHTML = `
          <div id="user-info-container">
            <span id="user-name-display">${name}</span>
            <div id="logout-popup">
              <div class="popup-header">
                <div class="user-avatar-large">${avatarLetter}</div>
                <div class="user-details">
                  <p class="user-name">${name}</p>
                  <p class="user-id">داخلی: ${toPersianDigits(internal)}</p>
                </div>
              </div>
              <button id="logout-button">خروج از حساب</button>
            </div>
          </div>`;
        const container = document.getElementById("user-info-container");
        const popup = document.getElementById("logout-popup");
        container.addEventListener("click", (event) => {
          event.stopPropagation();
          popup.classList.toggle("show");
        });
        document
          .getElementById("logout-button")
          .addEventListener("click", logout);
        document.addEventListener("click", () => {
          if (popup.classList.contains("show")) {
            popup.classList.remove("show");
          }
        });
      } else {
        placeholder.innerHTML = `<button id="login-button">ورود به حساب کاربری</button>`;
        document
          .getElementById("login-button")
          .addEventListener("click", () => {
            window.location.href = "/auth/login.html";
          });
      }
    } catch (error) {
      console.error("Error checking login status:", error);
      placeholder.innerHTML = `<button id="login-button">ورود به حساب کاربری</button>`;
      const btn = document.getElementById("login-button");
      if (btn)
        btn.addEventListener("click", () => {
          window.location.href = "/auth/login.html";
        });
    }
  }

  // ==========================
  // Utils (Jalali / Persian)
  // (بدون تغییر)
  // ==========================
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

  function toJalali(g_y, g_m, g_d) {
    var g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    var j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    var gy = g_y - 1600;
    var gm = g_m - 1;
    var gd = g_d - 1;
    var g_day_no =
      365 * gy +
      Math.floor((gy + 3) / 4) -
      Math.floor((gy + 99) / 100) +
      Math.floor((gy + 399) / 400);
    for (var i = 0; i < gm; ++i) g_day_no += g_days_in_month[i];
    if (gm > 1 && ((gy % 4 == 0 && gy % 100 != 0) || gy % 400 == 0)) g_day_no++;
    g_day_no += gd;
    var j_day_no = g_day_no - 79;
    var j_np = Math.floor(j_day_no / 12053);
    j_day_no %= 12053;
    var jy = 979 + 33 * j_np + 4 * Math.floor(j_day_no / 1461);
    j_day_no %= 1461;
    if (j_day_no >= 366) {
      jy += Math.floor((j_day_no - 1) / 365);
      j_day_no = (j_day_no - 1) % 365;
    }
    for (var i = 0; i < 11 && j_day_no >= j_days_in_month[i]; ++i) {
      j_day_no -= j_days_in_month[i];
    }
    var jm = i + 1;
    var jd = j_day_no + 1;
    return [jy, jm, jd];
  }

  function toPersianDigits(str) {
    if (str === null || str === undefined) return "";
    return str.toString().replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[d]);
  }
  function pad(num) {
    return num.toString().padStart(2, "0");
  }
  function toPersianTimeStr(totalMin) {
    let h = Math.floor(totalMin / 60);
    let m = totalMin % 60;
    if (h > 0 && m > 0)
      return `${toPersianDigits(h)} ساعت و ${toPersianDigits(m)} دقیقه`;
    if (h > 0) return `${toPersianDigits(h)} ساعت`;
    return `${toPersianDigits(m)} دقیقه`;
  }
  function isHolidayJalali(jy, jm, jd, holidays) {
    const dateStr = `${jy}-${pad(jm)}-${pad(jd)}`;
    return holidays.some((h) => h.date === dateStr);
  }

  function formatJalaliDate(gregorianDateStr) {
    if (!gregorianDateStr) return "";
    const parts = gregorianDateStr.split("-");
    if (parts.length !== 3) return toPersianDigits(gregorianDateStr);

    const gy = parseInt(parts[0], 10);
    const gm = parseInt(parts[1], 10);
    const gd = parseInt(parts[2], 10);

    const [jy, jm, jd] = toJalali(gy, gm, gd);
    const monthName = persianMonths[jm - 1];

    return toPersianDigits(`${jd} ${monthName} ${jy}`);
  }

  function setupHeader() {
    const dateElem = document.getElementById("today-date");
    const timeElem = document.getElementById("current-time");
    if (dateElem) {
      const today = new Date();
      const [jy, jm, jd] = toJalali(
        today.getFullYear(),
        today.getMonth() + 1,
        today.getDate()
      );
      dateElem.innerText = toPersianDigits(
        `امروز ${weekdays[today.getDay()]} ${jd} ${persianMonths[jm - 1]} ${jy}`
      );
    }
    if (timeElem) {
      const updateTime = () => {
        const now = new Date();
        const h = now.getHours().toString().padStart(2, "0");
        const m = now.getMinutes().toString().padStart(2, "0");
        timeElem.innerText = `ساعت ${toPersianDigits(h)}:${toPersianDigits(m)}`;
      };
      updateTime();
      setInterval(updateTime, 60 * 1000);
    }

    const menuToggle = document.getElementById("mobile-menu-toggle");
    const mainNav = document.getElementById("main-navigation");

    if (menuToggle && mainNav) {
      menuToggle.addEventListener("click", () => {
        mainNav.classList.toggle("active");
      });

      mainNav.addEventListener("click", (e) => {
        if (e.target.tagName === "A") {
          mainNav.classList.remove("active");
        }
      });

      document.addEventListener("click", (e) => {
        if (
          !mainNav.contains(e.target) &&
          !menuToggle.contains(e.target) &&
          mainNav.classList.contains("active")
        ) {
          mainNav.classList.remove("active");
        }
      });
    }
  }

  // ==========================
  // Business widgets
  // (بدون تغییر، چون به سیستم احراز هویت وابسته نیستند)
  // ==========================
  async function updateCardTrackingInfo() {
    const container = document.getElementById("card-tracking-info");
    if (!container) return;
    container.innerHTML = "در حال محاسبه تاریخ پیگیری کارت...";
    try {
      const response = await fetchNoCache("/data/holidays-1404.json");
      const holidays = await response.json();
      const holidayDates = new Set(holidays.map((h) => h.date));
      let businessDaysToCount = 14;
      let targetDate = new Date();
      while (businessDaysToCount > 0) {
        targetDate.setDate(targetDate.getDate() - 1);
        const dayOfWeek = targetDate.getDay();
        const [jy, jm, jd] = toJalali(
          targetDate.getFullYear(),
          targetDate.getMonth() + 1,
          targetDate.getDate()
        );
        const jalaliDateStr = `${jy}-${pad(jm)}-${pad(jd)}`;
        const isHoliday = holidayDates.has(jalaliDateStr);
        if (dayOfWeek !== 4 && dayOfWeek !== 5 && !isHoliday) {
          businessDaysToCount--;
        }
      }
      const [finalJy, finalJm, finalJd] = toJalali(
        targetDate.getFullYear(),
        targetDate.getMonth() + 1,
        targetDate.getDate()
      );
      const formattedDate = `${toPersianDigits(finalJy)}/${toPersianDigits(
        pad(finalJm)
      )}/${toPersianDigits(pad(finalJd))}`;
      container.innerHTML = `<div class="news-alert-box yellow"><b>پیگیری کارت فیزیکی</b><p style="margin-top: 8px; margin-bottom: 0;">برای درخواست‌های کارت قبل از تاریخ <b>${formattedDate}</b> تیکت ثبت کنید.</p></div>`;
    } catch (error) {
      console.error("Could not calculate card tracking date:", error);
      container.innerHTML = `<div class="news-alert-box red">خطا در محاسبه تاریخ پیگیری کارت.</div>`;
    }
  }

  async function updateVideoBankingStatus() {
    const statusDiv = document.getElementById("video-banking-status");
    if (!statusDiv) return;
    statusDiv.innerHTML = "در حال بررسی وضعیت بانکداری ویدیویی...";
    const today = new Date();
    const [jy, jm, jd] = toJalali(
      today.getFullYear(),
      today.getMonth() + 1,
      today.getDate()
    );
    const todayStr = `${jy}-${pad(jm)}-${pad(jd)}`;
    let holidays = [];
    try {
      const res = await fetchNoCache("/data/holidays-1404.json");
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
      statusHTML = `<div class="video-banking-box closed"><b>بانکداری ویدیویی : <span style="font-size:1.2em;">❌ غیرفعال</span></b><br>امروز تعطیل است و خدمات بانکداری ویدیویی ارائه نمی‌شود.</div>`;
    } else {
      let startHour,
        endHour,
        endMinute,
        activeMessage,
        beforeHoursMessage,
        afterHoursMessage;
      if (weekday >= 6 || weekday <= 3) {
        startHour = workingHours["weekday-sat-wed"].startHour;
        endHour = workingHours["weekday-sat-wed"].endHour;
        endMinute = workingHours["weekday-sat-wed"].endMinute;
        activeMessage = `<div class="video-banking-box"><b>بانکداری ویدیویی: <span style="font-size:1.2em;">✅ فعال</span></b><br>بخش احراز هویت از ساعت <b>۷:۰۰ تا ۱۷:۰۰</b><br>بخش انتقال وجه از ساعت <b>۷:۰۰ تا ۱۳:۰۰</b></div>`;
      } else {
        startHour = workingHours["weekday-thu"].startHour;
        endHour = workingHours["weekday-thu"].endHour;
        endMinute = workingHours["weekday-thu"].endMinute;
        activeMessage = `<div class="video-banking-box"><b>بانکداری ویدیویی: <span style="font-size:1.2em;">✅ فعال</span></b><br>بخش احراز هویت از ساعت <b>۷:۰۰ تا ۱۷:۰۰</b><br>بخش انتقال وجه از ساعت <b>۷:۰۰ تا ۱۲:۳۰</b></div>`;
      }
      beforeHoursMessage = `<div class="video-banking-box closed"><b>بانکداری ویدیویی: <span style="font-size:1.2em;">❌ خارج از ساعت کاری</span></b><br>ساعات کاری هنوز شروع نشده است (۷ صبح)</div>`;
      afterHoursMessage = `<div class="video-banking-box closed"><b>بانکداری ویدیویی: <span style="font-size:1.2em;">❌ خارج از ساعت کاری</span></b><br>ساعات کاری امروز به پایان رسیده است.</div>`;
      const currentTimeInMinutes = currentHour * 60 + currentMinute;
      const startTimeInMinutes = startHour * 60;
      const endTimeInMinutes = endHour * 60 + endMinute;
      if (
        currentTimeInMinutes >= startTimeInMinutes &&
        currentTimeInMinutes < endTimeInMinutes
      ) {
        statusHTML = activeMessage;
      } else if (currentTimeInMinutes < startTimeInMinutes) {
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
      const endTime = new Date(now);
      endTime.setHours(cycle.endH, cycle.endM, 0, 0);
      if (now < cycleTime) return { ...cycle, start: cycleTime, end: endTime };
      if (now >= cycleTime && now < endTime)
        return { ...cycle, start: cycleTime, end: endTime, inProgress: true };
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
    const firstCycle = (isHolidayTomorrow ? holidayCycle : payaaCycles)[0];
    tomorrow.setHours(firstCycle.hour, firstCycle.min, 0, 0);
    const endOfTomorrowCycle = new Date(tomorrow);
    endOfTomorrowCycle.setHours(firstCycle.endH, firstCycle.endM, 0, 0);
    return { ...firstCycle, start: tomorrow, end: endOfTomorrowCycle };
  }

  function renderPayaaCycleStatus(holidays) {
    const statusDiv = document.getElementById("payaa-cycle-status");
    if (!statusDiv) return;
    function updateStatus() {
      const now = new Date();
      const cycle = getNextPayaaCycle(now, holidays);
      const nextDate = cycle.start;
      const [cy, cm, cd] = toJalali(
        nextDate.getFullYear(),
        nextDate.getMonth() + 1,
        nextDate.getDate()
      );
      const today = new Date();
      const isToday =
        today.getFullYear() === nextDate.getFullYear() &&
        today.getMonth() === nextDate.getMonth() &&
        today.getDate() === nextDate.getDate();
      const tomorrow = new Date();
      tomorrow.setDate(today.getDate() + 1);
      const isTomorrow =
        tomorrow.getFullYear() === nextDate.getFullYear() &&
        tomorrow.getMonth() === nextDate.getMonth() &&
        tomorrow.getDate() === nextDate.getDate();
      let dayLabel = isToday
        ? "امروز"
        : isTomorrow
        ? "فردا"
        : weekdays[nextDate.getDay()];
      const pMonth = persianMonths[cm - 1];
      const nextCycleText = `چرخه بعدی : ${dayLabel} ${toPersianDigits(
        cd
      )} ${pMonth} ساعت ${toPersianDigits(cycle.hour)}:${toPersianDigits(
        pad(cycle.min)
      )}`;
      if (cycle.inProgress) {
        statusDiv.innerHTML = `<div class="news-alert-box yellow" style="font-weight:bold;"><span>درحال تسویه درخواست‌های ثبت‌شده پایا</span><div style="color:#888; font-size:0.95em; margin-top:0.5em;">${nextCycleText}</div></div>`;
      } else {
        const diffMs = cycle.start - now;
        let diffMin = Math.ceil(diffMs / (60 * 1000));
        if (diffMin < 1) diffMin = 1;
        statusDiv.innerHTML = `<div class="news-alert-box green" style="font-weight:bold;"><span>${toPersianTimeStr(
          diffMin
        )} تا چرخه بعدی پایا </span><div style="color:#888; font-size:0.95em; margin-top:0.5em;">${nextCycleText}</div></div>`;
      }
    }
    updateStatus();
    setInterval(updateStatus, 60 * 1000);
  }

  async function setupPayaaCycleStatus() {
    let holidays = [];
    try {
      const res = await fetchNoCache("/data/holidays-1404.json");
      holidays = await res.json();
    } catch (e) {
      console.error("Failed to load holidays for Payaa cycle:", e);
    }
    renderPayaaCycleStatus(holidays);
  }

  function setupToolsSearch() {
    const searchInput = document.getElementById("tools-search");
    const toolCards = document.querySelectorAll(".column-tools .tool-card");
    if (!searchInput || !toolCards.length) return;
    searchInput.addEventListener("input", function () {
      const query = searchInput.value.trim().toLowerCase();
      toolCards.forEach((card) => {
        const titleElement = card.querySelector("h2");
        const listItems = card.querySelectorAll(".tools-grid li");
        let hasVisibleItem = false;
        listItems.forEach((li) => {
          const itemText = li.innerText.toLowerCase();
          if (itemText.includes(query)) {
            li.style.display = "";
            hasVisibleItem = true;
          } else {
            li.style.display = "none";
          }
        });
        const titleText = titleElement
          ? titleElement.innerText.toLowerCase()
          : "";
        if (hasVisibleItem || titleText.includes(query)) {
          card.style.display = "";
          if (!hasVisibleItem && titleText.includes(query)) {
            listItems.forEach((li) => (li.style.display = ""));
          }
        } else {
          card.style.display = "none";
        }
      });
    });
  }

  async function loadAndDisplayServiceStatus() {
    const serviceStatusDiv = document.getElementById("service-status");
    if (!serviceStatusDiv) return;
    serviceStatusDiv.innerHTML = "در حال بارگذاری وضعیت سرویس‌ها...";
    try {
      const response = await fetchNoCache("/data/service-status.json");
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      const services = await response.json();
      let html = "";
      if (services.length === 0) {
        html = `<div class="news-alert-box green">همه سرویس‌ها فعال هستند.</div>`;
      } else {
        services.forEach((service) => {
          let colorClass = "green";
          if (service.status === "غیرفعال") colorClass = "red";
          else if (service.status === "در حال بررسی") colorClass = "yellow";
          else if (service.status === "اختلال در عملکرد") colorClass = "yellow";
          html += `<div class="news-alert-box ${colorClass}"><b>${
            service.name
          }:</b> ${service.status}${
            service.description
              ? `<p style="margin-top: 8px; margin-bottom: 0;">${service.description}</p>`
              : ""
          }</div>`;
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
      const response = await fetchNoCache("/data/news-alerts.json");
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      const alerts = await response.json();
      let html = "";
      if (alerts.length === 0) {
        html = `<div class="news-alert-box green">در حال حاضر اطلاعیه جدیدی وجود ندارد.</div>`;
      } else {
        alerts.forEach((alert) => {
          const colorClass = alert.color;
          let startDateTimeInfo = "";
          let endDateTimeInfo = "";
          if (alert.startDate || alert.startTime) {
            const parts = [];
            if (alert.startDate) {
              parts.push(formatJalaliDate(alert.startDate));
            }
            if (alert.startTime) {
              parts.push(`ساعت ${toPersianDigits(alert.startTime)}`);
            }
            const dateTimeString = parts.join(" ");
            startDateTimeInfo = `<p style="font-size:0.9em; color:#666; margin-top:5px; margin-bottom:0;">شروع: ${dateTimeString}</p>`;
          }
          if (alert.endDate || alert.endTime) {
            const parts = [];
            if (alert.endDate) {
              parts.push(formatJalaliDate(alert.endDate));
            }
            if (alert.endTime) {
              parts.push(`ساعت ${toPersianDigits(alert.endTime)}`);
            }
            const dateTimeString = parts.join(" ");
            endDateTimeInfo = `<p style="font-size:0.9em; color:#666; margin-top:5px; margin-bottom:0;">پایان: ${dateTimeString}</p>`;
          }
          html += `<div class="news-alert-box ${colorClass}"><b>${
            alert.title
          }</b>${
            alert.description
              ? `<p style="margin-top: 8px; margin-bottom: 0;">${alert.description}</p>`
              : ""
          }${startDateTimeInfo}${endDateTimeInfo}</div>`;
        });
      }
      newsAlertsDiv.innerHTML = html;
    } catch (error) {
      console.error("Could not fetch news alerts:", error);
      newsAlertsDiv.innerHTML = `<div class="news-alert-box red">خطا در بارگذاری اطلاعیه‌ها.</div>`;
    }
  }

  initializeApp();
});
