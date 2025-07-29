// بررسی اینکه آیا کاربر وارد شده یا نه
function isAuthenticated() {
  return !!localStorage.getItem("jwt");
}

// گرفتن اطلاعات کاربر از توکن JWT
async function getCurrentUser() {
  const token = localStorage.getItem("jwt");
  if (!token) return null;

  const res = await fetch("/php/jwt-verify.php", {
    headers: { Authorization: `Bearer ${token}` },
  });

  if (res.ok) {
    const { user } = await res.json();
    return user;
  } else {
    return null;
  }
}

// محافظت از صفحه بر اساس نقش کاربر
async function protectPage(allowedRoles = []) {
  const user = await getCurrentUser();

  if (!user) {
    alert("برای دسترسی به این صفحه، ابتدا وارد شوید.");
    window.location.href = "/admin/login.html";
    return;
  }

  if (allowedRoles.length && !allowedRoles.includes(user.role)) {
    alert("شما به این صفحه دسترسی ندارید.");
    window.location.href = "/admin/index.html";
  }
}

// خروج از سیستم
function logout() {
  localStorage.removeItem("jwt");
  window.location.href = "/admin/login.html";
}

// بررسی توکن JWT و اجازه دسترسی به صفحات
async function verifyToken(requiredRoles = []) {
  const token = localStorage.getItem("jwt");
  if (!token) return redirectToLogin();

  const res = await fetch("/php/jwt-verify.php", {
    headers: { Authorization: `Bearer ${token}` },
  });

  if (!res.ok) return redirectToLogin();

  const { user } = await res.json();

  if (requiredRoles.length && !requiredRoles.includes(user.role)) {
    alert("دسترسی ندارید.");
    window.location.href = "/admin/index.html";
  }

  return user;
}

// انتقال به صفحه لاگین در صورت عدم وجود توکن یا دسترسی
function redirectToLogin() {
  alert("ابتدا وارد شوید.");
  window.location.href = "/admin/login.html";
}
