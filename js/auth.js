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
      return user; // { username: '...', ... }
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

async function protectPage(allowedUsernames = []) {
  // پارامتر به allowedUsernames تغییر کرد
  const user = await getCurrentUser();

  if (!user) {
    alert("برای دسترسی به این صفحه، ابتدا وارد شوید.");
    window.location.href = "/admin/login.html";
    return;
  }

  displayUserAndLogout(user);

  if (allowedUsernames.length && !allowedUsernames.includes(user.username)) {
    alert("شما به این صفحه دسترسی ندارید.");
    window.location.href = "/admin/index.html"; // یا هر صفحه دیگری که مد نظر دارید
    return;
  }
}

function logout() {
  localStorage.removeItem("jwt");
  window.location.href = "/admin/login.html";
}

async function verifyToken(requiredUsernames = []) {
  // پارامتر به requiredUsernames تغییر کرد
  const token = localStorage.getItem("jwt");
  if (!token) return redirectToLogin();

  const res = await fetch("/php/jwt-verify.php", {
    headers: { Authorization: `Bearer ${token}` },
  });

  if (!res.ok) return redirectToLogin();

  const { user } = await res.json();

  if (requiredUsernames.length && !requiredUsernames.includes(user.username)) {
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
