window.copyToClipboard = function () {
  navigator.clipboard
    .writeText(window.location.href)
    .then(() => {
      alert("Đã sao chép liên kết!");
    })
    .catch((error) => {
      console.error("Failed to copy: ", error);
    });
};

function formatRelatedDate(value) {
  if (!value) return null;

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return null;

  return {
    display: new Intl.DateTimeFormat("vi-VN").format(date),
    iso: `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`,
  };
}

function createRelatedArticle(item, baseUrl) {
  const article = document.createElement("article");
  article.className = "news-detail-related__item";

  const link = document.createElement("a");
  link.className = "news-detail-related__link";
  link.href = `${baseUrl}${encodeURIComponent(String(item.slug || ""))}`;

  const image = document.createElement("img");
  image.className = "news-detail-related__image";
  image.src = String(item.image_url || "");
  image.alt = "";
  image.loading = "lazy";

  const content = document.createElement("div");
  content.className = "news-detail-related__content";

  const category = document.createElement("span");
  category.className = "badge";
  category.dataset.variant = "outline";
  category.textContent = String(item.category || "Tin tức");

  const heading = document.createElement("h3");
  heading.className = "news-detail-related__heading";
  heading.textContent = String(item.title || "");

  content.append(category, heading);

  const formattedDate = formatRelatedDate(item.published_at || item.created_at);
  if (formattedDate) {
    const time = document.createElement("time");
    time.className = "news-detail-related__date";
    time.dateTime = formattedDate.iso;
    time.textContent = formattedDate.display;
    content.appendChild(time);
  }

  link.append(image, content);
  article.appendChild(link);
  return article;
}

document.addEventListener("DOMContentLoaded", () => {
  const backToTopButton = document.getElementById("news-back-to-top");
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  if (backToTopButton) {
    const updateBackToTopVisibility = () => {
      backToTopButton.classList.toggle("is-visible", window.scrollY > 300);
    };

    window.addEventListener("scroll", updateBackToTopVisibility, { passive: true });
    updateBackToTopVisibility();

    backToTopButton.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: reducedMotion.matches ? "auto" : "smooth",
      });
    });
  }

  const loadMoreButton = document.getElementById("load-more-related-btn");
  const relatedList = document.getElementById("related-articles-list");
  const relatedBlock = document.getElementById("related-articles-block");

  if (!loadMoreButton || !relatedList || !relatedBlock) return;

  loadMoreButton.addEventListener("click", async () => {
    const apiUrl = relatedBlock.dataset.apiUrl;
    const baseUrl = relatedBlock.dataset.baseUrl;
    const offset = Number.parseInt(relatedBlock.dataset.offset || "0", 10);
    const limit = 6;

    if (!apiUrl || !baseUrl) return;

    const originalContent = loadMoreButton.innerHTML;
    loadMoreButton.disabled = true;
    loadMoreButton.setAttribute("aria-busy", "true");
    loadMoreButton.innerHTML = '<span>Đang tải...</span><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>';

    try {
      const requestUrl = new URL(apiUrl, window.location.href);
      requestUrl.searchParams.set("offset", String(offset));
      requestUrl.searchParams.set("limit", String(limit));

      const response = await fetch(requestUrl);
      if (!response.ok) throw new Error(`HTTP ${response.status}`);

      const payload = await response.json();
      const items = Array.isArray(payload.data?.items) ? payload.data.items : [];
      const fragment = document.createDocumentFragment();

      items.forEach((item) => {
        const separator = document.createElement("hr");
        separator.className = "separator";
        separator.setAttribute("aria-hidden", "true");
        fragment.append(separator, createRelatedArticle(item, baseUrl));
      });

      relatedList.appendChild(fragment);
      relatedBlock.dataset.offset = String(offset + items.length);

      if (!payload.data?.has_more || items.length === 0) {
        loadMoreButton.closest(".card__footer")?.remove();
        return;
      }

      loadMoreButton.innerHTML = originalContent;
      loadMoreButton.disabled = false;
      loadMoreButton.removeAttribute("aria-busy");
    } catch (error) {
      console.error("Error loading related posts:", error);
      loadMoreButton.innerHTML = originalContent;
      loadMoreButton.disabled = false;
      loadMoreButton.removeAttribute("aria-busy");
      window.toast?.error?.("Lỗi", "Không thể tải thêm bài viết liên quan.");
    }
  });
});
