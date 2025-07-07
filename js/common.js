// بارگذاری فوتر
fetch("/footer.html")
  .then((res) => res.text())
  .then((data) => {
    document.getElementById("footer-placeholder").innerHTML = data;
  });

// بارگذاری هدر
fetch("/header.html")
  .then((res) => res.text())
  .then((data) => {
    document.getElementById("header-placeholder").innerHTML = data;
    const pageTitleElement = document.querySelector("title");
    if (pageTitleElement && typeof setupHeader === "function") {
      setupHeader(pageTitleElement.innerText);
    }
  });
