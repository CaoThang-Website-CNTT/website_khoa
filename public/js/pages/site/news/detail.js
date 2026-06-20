window.copyToClipboard = function () {
  const url = window.location.href;
  navigator.clipboard
    .writeText(url)
    .then(() => {
      alert("Đã sao chép liên kết!");
    })
    .catch((err) => {
      console.error("Failed to copy: ", err);
    });
};

document.addEventListener("DOMContentLoaded", () => {
  const loadMoreBtn = document.getElementById("load-more-related-btn");
  const relatedList = document.getElementById("related-articles-list");
  const relatedBlock = document.getElementById("related-articles-block");

  if (loadMoreBtn && relatedList && relatedBlock) {
    loadMoreBtn.addEventListener("click", async () => {
      const apiUrl = relatedBlock.getAttribute("data-api-url");
      if (!apiUrl) return;

      const originalText = loadMoreBtn.innerHTML;
      loadMoreBtn.innerHTML =
        '<span>Đang tải...</span><i class="fa-solid fa-spinner fa-spin"></i>';
      loadMoreBtn.disabled = true;

      try {
        const offset = 3;
        const limit = 18;
        const response = await fetch(
          `${apiUrl}?offset=${offset}&limit=${limit}`,
        );

        if (!response.ok) {
          throw new Error("Network response was not ok");
        }

        const resJson = await response.json();
        const items = resJson.data?.items || [];

        if (items.length > 0) {
          const fragment = document.createDocumentFragment();

          items.forEach((item) => {
            const hr = document.createElement("hr");
            hr.className = "separator";
            hr.setAttribute("aria-hidden", "true");
            fragment.appendChild(hr);

            const article = document.createElement("article");
            article.className = "news-detail-related__item";

            // Format date DD/MM/YYYY
            const d = new Date(item.published_at || item.created_at);
            const dateStr =
              d.getDate().toString().padStart(2, "0") +
              "/" +
              (d.getMonth() + 1).toString().padStart(2, "0") +
              "/" +
              d.getFullYear();
            const dateISO = d.toISOString().split("T")[0];

            // Escape title
            const div = document.createElement("div");
            div.innerText = item.title;
            const escapedTitle = div.innerHTML;

            // Escape category
            div.innerText = item.category;
            const escapedCategory = div.innerHTML;

            article.innerHTML = `
                <a href="/tin-tuc/${item.slug}" class="news-detail-related__link">
                  <div class="news-detail-related__thumb">
                    <img class="news-detail-related__image" src="${item.image_url}" alt="${escapedTitle}" loading="lazy">
                  </div>
                  <div class="news-detail-related__content">
                    <span class="badge" data-variant="outline">${escapedCategory}</span>
                    <h4 class="news-detail-related__heading">${escapedTitle}</h4>
                    <time class="news-detail-related__date" datetime="${dateISO}">${dateStr}</time>
                  </div>
                </a>
              `;
            fragment.appendChild(article);
          });

          relatedList.appendChild(fragment);
        }

        // Hide button after loading once since we loaded all remaining up to max 21
        loadMoreBtn.style.display = "none";
      } catch (error) {
        console.error("Error loading related posts:", error);
        loadMoreBtn.innerHTML = originalText;
        loadMoreBtn.disabled = false;
      }
    });
  }
});
