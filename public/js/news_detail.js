document.addEventListener("DOMContentLoaded", function () {
  initTableOfContents();
  initShareButtons();
  initSmoothScroll();
});
const tocOffset = 100;
/**
 * Table of Contents
 * Highlights phần heading đang xem
 */
function initTableOfContents() {
  const tocLinks = document.querySelectorAll(".news-detail__toc-link");
  if (!tocLinks.length) return;

  const headings = Array.from(tocLinks)
    .map((link) => {
      const id = link.getAttribute("href").replace("#", "");
      return document.getElementById(id);
    })
    .filter(Boolean);

  if (!headings.length) return;

  function updateActiveToc() {
    const scrollPos = window.scrollY + tocOffset;

    let currentHeading = null;
    for (let i = headings.length - 1; i >= 0; i--) {
      if (headings[i].offsetTop <= scrollPos) {
        currentHeading = headings[i];
        break;
      }
    }

    tocLinks.forEach((link) => {
      const href = link.getAttribute("href").replace("#", "");
      if (currentHeading && currentHeading.id === href) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  }

  let ticking = false;
  window.addEventListener("scroll", function () {
    if (!ticking) {
      window.requestAnimationFrame(function () {
        updateActiveToc();
        ticking = false;
      });
      ticking = true;
    }
  });

  updateActiveToc();
}

/**
 * Share Buttons: redirect chia sẻ lên social media hoặc copy link web.
 */
function initShareButtons() {
  const shareButtons = document.querySelectorAll(".news-detail__share-btn");
  if (!shareButtons.length) return;

  const pageUrl = encodeURIComponent(window.location.href);
  const pageTitle =
    document.querySelector(".news-detail__title")?.textContent?.trim() ||
    document.title;

  shareButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const shareType = this.getAttribute("data-share");

      switch (shareType) {
        case "facebook":
          window.open(
            `https://www.facebook.com/sharer/sharer.php?u=${pageUrl}`,
            "_blank",
            "width=600,height=400", //TODO: thống nhất kích thước popup
          );
          break;

        case "zalo":
          window.open(
            `https://sp.zalo.me/plugins/sdk/send?url=${pageUrl}&title=${encodeURIComponent(
              pageTitle,
            )}`,
            "_blank",
            "width=600,height=400", //TODO: thống nhất kích thước popup
          );
          break;

        case "email":
          window.location.href = `mailto:?subject=${encodeURIComponent(
            pageTitle,
          )}&body=${encodeURIComponent(pageUrl)}`;
          break;

        case "copy":
          copyToClipboard(window.location.href, this);
          break;
      }
    });
  });
}

function copyToClipboard(text, button) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(() => {
      showCopySuccess(button);
    });
  } else {
    // Fallback cho trình duyệt cũ
    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    textarea.style.opacity = "0";
    document.body.appendChild(textarea);
    textarea.select();
    try {
      document.execCommand("copy");
      showCopySuccess(button);
    } catch (err) {
      console.error("Failed to copy:", err);
    }
    document.body.removeChild(textarea);
  }
}

/**
 * Hiển thị thông báo copy thành công
 */
function showCopySuccess(button) {
  const icon = button.querySelector("i");
  const originalClass = icon.className;

  button.classList.add("copied");
  icon.className = "fa-solid fa-check";

  setTimeout(() => {
    button.classList.remove("copied");
    icon.className = originalClass;
  }, 2000);
}

/**
 * Scroll đến heading tương ứng với link trong mục lục
 */
function initSmoothScroll() {
  const tocLinks = document.querySelectorAll(
    '.news-detail__toc-link[href^="#"]',
  );

  tocLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href").replace("#", "");
      const targetElement = document.getElementById(targetId);

      if (targetElement) {
        const headerOffset = 100;
        const elementPosition = targetElement.offsetTop;
        const offsetPosition = elementPosition - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: "smooth",
        });

        // Update URL, không reload trang
        history.pushState(null, null, `#${targetId}`);
      }
    });
  });
}
