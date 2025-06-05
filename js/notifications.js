let lastNotificationId = null;

function showNotification(title, desc, type = "green") {
  let old = document.getElementById("global-notification");
  if (old) old.remove();

  let notif = document.createElement("div");
  notif.id = "global-notification";
  notif.className = `news-alert-box ${type}`;
  notif.style.position = "fixed";
  notif.style.top = "20px";
  notif.style.right = "20px";
  notif.style.zIndex = "9999";
  notif.style.maxWidth = "400px";
  notif.style.boxShadow = "0 4px 24px rgba(0,0,0,0.13)";

  notif.innerHTML = `<strong>${title}</strong><br>${desc}
    <button style="float:left; margin-top:5px; background:none; border:none; color:#b00; cursor:pointer" onclick="this.parentNode.remove()">×</button>`;
  document.body.appendChild(notif);

  setTimeout(() => notif.remove(), 25000);

  // ===== پخش صدا =====
  try {
    let audio = new Audio("../assets/notif.wav");
    audio.play();
  } catch (e) {
    // silent fail
  }
}

async function checkNotification() {
  try {
    let res = await fetch("notifications.json?" + Date.now());
    if (!res.ok) return;
    let data = await res.json();
    if (data.id !== lastNotificationId) {
      showNotification(data.title, data.description, data.type || "green");
      lastNotificationId = data.id;
    }
  } catch (e) {
    // silent fail
  }
}

checkNotification();
setInterval(checkNotification, 5000);
