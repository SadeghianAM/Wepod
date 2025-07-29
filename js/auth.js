function isAuthenticated() {
  return !!localStorage.getItem("jwt");
}

async function getCurrentUser() {
  const token = localStorage.getItem("jwt");
  if (!token) return null;

  try {
    const res = await fetch("/php/jwt-verify.php", {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (res.ok) {
      const { user } = await res.json();
      return user;
    } else {
      localStorage.removeItem("jwt");
      return null;
    }
  } catch (error) {
    console.error("Error verifying token:", error);
    return null;
  }
}

function displayUserAndLogout(user) {
  const userInfoEl = document.getElementById("user-info");

  if (userInfoEl && user && user.username) {
    userInfoEl.textContent = `خوش آمدید، ${user.username}`;

    userInfoEl.addEventListener("click", () => {
      if (confirm("آیا برای خروج از حساب کاربری خود مطمئن هستید؟")) {
        logout();
      }
    });
  }
}

async function protectPage(allowedRoles = []) {
  const user = await getCurrentUser();

  if (!user) {
    alert("برای دسترسی به این صفحه، ابتدا وارد شوید.");
    window.location.href = "/admin/login.html";
    return;
  }

  displayUserAndLogout(user);

  if (allowedRoles.length && !allowedRoles.includes(user.role)) {
    alert("شما به این صفحه دسترسی ندارید.");
    window.location.href = "/admin/index.html";
    return;
  }
}

function logout() {
  localStorage.removeItem("jwt");
  window.location.href = "/admin/login.html";
}

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
    return;
  }

  return user;
}

function redirectToLogin() {
  alert("ابتدا وارد شوید.");
  window.location.href = "/admin/login.html";
}
