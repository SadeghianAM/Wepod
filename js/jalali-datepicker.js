// A self-invoking function to inject CSS into the document's head
(function injectDatePickerStyles() {
  // Check if styles are already injected to avoid duplication
  if (document.getElementById("jalali-datepicker-styles-v2")) {
    return;
  }

  // CSS code is stored in a template literal string
  const css = `
    /* -- Modern Jalali DatePicker Styles -- */
    :root {
      --jdp-primary: #00ae70;
      --jdp-primary-dark: #089863;
      --jdp-primary-light: #e6f7f2;
      --jdp-text-color: #333;
      --jdp-border-color: #e9e9e9;
      --jdp-bg-color: #fff;
      --jdp-box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
      --jdp-selected-bg: var(--jdp-primary);
      --jdp-selected-text: #fff;
      --jdp-today-border: 1px solid var(--jdp-primary);
    }

    .jdp-popover {
  position: absolute;
  background: var(--jdp-bg-color);
  border: 1px solid var(--jdp-border-color);
  border-radius: .5rem;
  box-shadow: var(--jdp-box-shadow);
  padding: .75rem;
  width: 300px;
  z-index: 9999;
  user-select: none;

  /* -- Changes are here -- */
  opacity: 0;
  visibility: hidden; /* Add this line */
  transform: translateY(-10px);
  transition: opacity 150ms ease-in-out, transform 150ms ease-in-out, visibility 150ms; /* Add visibility to transition */
}

.jdp-popover.jdp-visible {
  opacity: 1;
  visibility: visible; /* Add this line */
  transform: translateY(0);
}

    .jdp-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: .5rem;
      font-weight: 700;
      color: var(--jdp-primary-dark);
    }

    .jdp-month-year-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 700;
        padding: .25rem .5rem;
        border-radius: .4rem;
        transition: background-color 150ms;
    }

    .jdp-month-year-btn:hover {
        background-color: var(--jdp-primary-light);
    }

    .jdp-nav-btn {
      background: var(--jdp-primary);
      color: #fff;
      border: none;
      width: 2rem;
      height: 2rem;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      line-height: 1;
    }

    .jdp-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 4px;
    }

    .jdp-cell {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 38px;
        border-radius: .4rem;
        cursor: pointer;
        transition: background-color 150ms;
        border: 1px solid transparent;
    }

    .jdp-weekday {
      font-size: .85rem;
      color: #555;
      cursor: default;
    }

    .jdp-day:hover {
      background: var(--jdp-primary-light);
    }

    .jdp-day.jdp-other-month {
      color: #bbb;
      background: #f8f9fa;
      cursor: default;
    }

    .jdp-day.jdp-today {
        border: var(--jdp-today-border);
        font-weight: bold;
    }

    .jdp-day.jdp-selected {
        background-color: var(--jdp-selected-bg);
        color: var(--jdp-selected-text);
        font-weight: bold;
    }

    .jdp-footer {
        margin-top: .5rem;
        text-align: center;
    }

    .jdp-today-btn {
        background: none;
        border: 1px solid var(--jdp-border-color);
        color: var(--jdp-primary);
        padding: .3rem .8rem;
        border-radius: .4rem;
        cursor: pointer;
        width: 100%;
    }

    /* Month/Year View */
    .jdp-months-grid, .jdp-years-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .jdp-month, .jdp-year {
        padding: .75rem 0;
    }
  `;

  const styleElement = document.createElement("style");
  styleElement.id = "jalali-datepicker-styles-v2";
  styleElement.textContent = css;
  document.head.appendChild(styleElement);
})();

/* =======================
  Jalali Date Helpers (Unchanged)
 ======================= */
function jalaliToGregorian(jy, jm, jd) {
  var sal_a, gy, gm, gd, days;
  jy += 1595;
  days =
    -355668 +
    365 * jy +
    ~~(jy / 33) * 8 +
    ~~(((jy % 33) + 3) / 4) +
    jd +
    (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186);
  gy = 400 * ~~(days / 146097);
  days %= 146097;
  if (days > 36524) {
    gy += 100 * ~~(--days / 36524);
    days %= 36524;
    if (days >= 365) days++;
  }
  gy += 4 * ~~(days / 1461);
  days %= 1461;
  if (days > 365) {
    gy += ~~((days - 1) / 365);
    days = (days - 1) % 365;
  }
  gd = days + 1;
  sal_a = [
    0,
    31,
    (gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0 ? 29 : 28,
    31,
    30,
    31,
    30,
    31,
    31,
    30,
    31,
    30,
    31,
  ];
  for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
  return new Date(gy, gm - 1, gd);
}
function toPersian(date) {
  const parts = date.toLocaleDateString("fa-IR-u-nu-latn").split("/");
  return parts.map((part) => parseInt(part, 10));
}
function formatJalaliDisplay(jy, jm, jd) {
  return `${jy}/${String(jm).padStart(2, "0")}/${String(jd).padStart(2, "0")}`;
}
function formatISO(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(
    2,
    "0"
  )}-${String(date.getDate()).padStart(2, "0")}`;
}
function isJalaliLeap(jy) {
  return (
    ((((((jy - 474) % 2820) + 2820) % 2820) + 474 + 38) * 682) % 2816 < 682
  );
}
function jalaliMonthLength(jy, jm) {
  if (jm <= 6) return 31;
  if (jm <= 11) return 30;
  return isJalaliLeap(jy) ? 30 : 29;
}

/* =======================
  🚀 Improved Jalali DatePicker
 ======================= */
class JalaliDatePicker {
  constructor(inputId, altId) {
    this.input = document.getElementById(inputId);
    this.alt = document.getElementById(altId);
    if (!this.input || !this.alt) return;

    const gNow = new Date();
    this.gToday = new Date(gNow.getFullYear(), gNow.getMonth(), gNow.getDate());
    [this.jTodayYear, this.jTodayMonth, this.jTodayDay] = toPersian(
      this.gToday
    );

    this.currentView = "days"; // 'days', 'months', 'years'
    this.viewYear = this.jTodayYear;
    this.viewMonth = this.jTodayMonth;

    this.selectedJalali = null;

    this.pop = document.createElement("div");
    this.pop.className = "jdp-popover";
    this.pop.setAttribute("role", "dialog");
    document.body.appendChild(this.pop);

    this.bindEvents();
  }

  bindEvents() {
    this.input.addEventListener("focus", () => this.show());
    this.input.addEventListener("click", () => this.show());
    window.addEventListener("resize", () => this.position());

    // Event delegation for better performance
    this.pop.addEventListener("click", (e) => this.handlePopoverClick(e));

    // Keyboard navigation
    this.boundKeyDown = (e) => this.handleKeyDown(e);
    this.boundClickOutside = (e) => {
      if (!this.pop.contains(e.target) && e.target !== this.input) this.hide();
    };
  }

  parseInitialDate() {
    const parts = this.input.value.split("/");
    if (parts.length === 3) {
      const [jy, jm, jd] = parts.map((p) => parseInt(p, 10));
      if (!isNaN(jy) && !isNaN(jm) && !isNaN(jd)) {
        this.selectedJalali = { jy, jm, jd };
        this.viewYear = jy;
        this.viewMonth = jm;
        return;
      }
    }
    this.selectedJalali = null;
    this.viewYear = this.jTodayYear;
    this.viewMonth = this.jTodayMonth;
  }

  show() {
    this.parseInitialDate();
    this.currentView = "days";
    this.render();
    this.position();
    this.pop.classList.add("jdp-visible");

    document.addEventListener("mousedown", this.boundClickOutside);
    document.addEventListener("keydown", this.boundKeyDown);
  }

  hide() {
    this.pop.classList.remove("jdp-visible");
    document.removeEventListener("mousedown", this.boundClickOutside);
    document.removeEventListener("keydown", this.boundKeyDown);
  }

  position() {
    if (!this.pop.classList.contains("jdp-visible")) return;
    const rect = this.input.getBoundingClientRect();
    this.pop.style.top = window.scrollY + rect.bottom + 6 + "px";
    const leftPos = window.scrollX + rect.left;
    this.pop.style.left = leftPos + "px";
    if (leftPos + 300 > window.innerWidth) {
      this.pop.style.left = window.scrollX + rect.right - 300 + "px";
    }
  }

  handlePopoverClick(e) {
    const target = e.target.closest("[data-action]");
    if (!target) return;

    const { action, value } = target.dataset;

    switch (action) {
      case "nav":
        this.navigate(parseInt(value, 10));
        break;
      case "select-day":
        this.selectDay(parseInt(value, 10));
        break;
      case "go-to-today":
        this.goToToday();
        break;
      case "change-view":
        this.changeView(value);
        break;
      case "select-month":
        this.selectMonth(parseInt(value, 10));
        break;
      case "select-year":
        this.selectYear(parseInt(value, 10));
        break;
    }
  }

  handleKeyDown(e) {
    if (e.key === "Escape") this.hide();
    // Add more keyboard nav logic here (ArrowKeys, Enter) if needed
  }

  navigate(delta) {
    if (this.currentView === "days") {
      this.viewMonth += delta;
      if (this.viewMonth < 1) {
        this.viewMonth = 12;
        this.viewYear--;
      }
      if (this.viewMonth > 12) {
        this.viewMonth = 1;
        this.viewYear++;
      }
    } else if (this.currentView === "years") {
      this.viewYear += delta * 12;
    }
    this.render();
  }

  selectDay(day) {
    const gDate = jalaliToGregorian(this.viewYear, this.viewMonth, day);
    this.input.value = formatJalaliDisplay(this.viewYear, this.viewMonth, day);
    this.alt.value = formatISO(gDate);
    this.alt.dispatchEvent(new Event("change", { bubbles: true }));
    this.hide();
  }

  goToToday() {
    this.viewYear = this.jTodayYear;
    this.viewMonth = this.jTodayMonth;
    this.currentView = "days";
    this.render();
  }

  changeView(view) {
    this.currentView = view;
    this.render();
  }

  selectMonth(month) {
    this.viewMonth = month;
    this.currentView = "days";
    this.render();
  }

  selectYear(year) {
    this.viewYear = year;
    this.currentView = "months";
    this.render();
  }

  render() {
    let html = "";
    const firstG = jalaliToGregorian(this.viewYear, this.viewMonth, 1);
    const monthName = new Intl.DateTimeFormat("fa-IR", {
      month: "long",
    }).format(firstG);
    const yearName = new Intl.NumberFormat("fa-IR", {
      useGrouping: false,
    }).format(this.viewYear);

    const header = `
        <div class="jdp-header">
          <button type="button" class="jdp-nav-btn" data-action="nav" data-value="-1" aria-label="ماه قبل">«</button>
          <button type="button" class="jdp-month-year-btn" data-action="change-view" data-value="${
            this.currentView === "days" ? "months" : "years"
          }">
              ${
                this.currentView === "days"
                  ? `${monthName} ${yearName}`
                  : yearName
              }
          </button>
          <button type="button" class="jdp-nav-btn" data-action="nav" data-value="1" aria-label="ماه بعد">»</button>
        </div>`;

    switch (this.currentView) {
      case "months":
        html = this.renderMonthsView();
        break;
      case "years":
        html = this.renderYearsView();
        break;
      default: // days
        html = this.renderDaysView();
        break;
    }

    this.pop.innerHTML = header + html;
  }

  renderDaysView() {
    const weekDays = ["ش", "ی", "د", "س", "چ", "پ", "ج"];
    const firstG = jalaliToGregorian(this.viewYear, this.viewMonth, 1);
    const firstWeekday = (firstG.getDay() + 1) % 7;
    const daysInMonth = jalaliMonthLength(this.viewYear, this.viewMonth);
    const faNum = (n) => new Intl.NumberFormat("fa-IR").format(n);

    let grid = `<div class="jdp-grid" role="grid">
        ${weekDays
          .map(
            (w) =>
              `<div class="jdp-cell jdp-weekday" role="columnheader">${w}</div>`
          )
          .join("")}`;

    for (let i = 0; i < firstWeekday; i++) {
      grid += `<div class="jdp-cell jdp-day jdp-other-month"></div>`;
    }

    for (let d = 1; d <= daysInMonth; d++) {
      const isToday =
        this.viewYear === this.jTodayYear &&
        this.viewMonth === this.jTodayMonth &&
        d === this.jTodayDay;
      const isSelected =
        this.selectedJalali &&
        this.viewYear === this.selectedJalali.jy &&
        this.viewMonth === this.selectedJalali.jm &&
        d === this.selectedJalali.jd;

      const classes = ["jdp-cell", "jdp-day"];
      if (isToday) classes.push("jdp-today");
      if (isSelected) classes.push("jdp-selected");

      grid += `<div class="${classes.join(
        " "
      )}" data-action="select-day" data-value="${d}" role="gridcell" aria-selected="${isSelected}">${faNum(
        d
      )}</div>`;
    }

    grid += `</div>`;

    const footer = `<div class="jdp-footer"><button type="button" class="jdp-today-btn" data-action="go-to-today">امروز</button></div>`;

    return grid + footer;
  }

  renderMonthsView() {
    const monthNames = [
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
    let grid = `<div class="jdp-months-grid">`;
    for (let i = 0; i < 12; i++) {
      grid += `<div class="jdp-cell jdp-month" data-action="select-month" data-value="${
        i + 1
      }">${monthNames[i]}</div>`;
    }
    grid += `</div>`;
    return grid;
  }

  renderYearsView() {
    const startYear = Math.floor(this.viewYear / 12) * 12;
    let grid = `<div class="jdp-years-grid">`;
    for (let i = 0; i < 12; i++) {
      const year = startYear + i;
      grid += `<div class="jdp-cell jdp-year" data-action="select-year" data-value="${year}">${new Intl.NumberFormat(
        "fa-IR",
        { useGrouping: false }
      ).format(year)}</div>`;
    }
    grid += `</div>`;
    return grid;
  }
}
