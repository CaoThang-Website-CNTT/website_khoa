(function () {
  if (window.Utils && window.AppUtils) return;

  const UtilsClass = class {
    static isInViewport(element, offset = 0) {
      const rect = element.getBoundingClientRect();

      return (
        rect.top >= -offset &&
        rect.left >= -offset &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + offset &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth) + offset
      );
    }

    static toCleanAscii(str) {
      if (!str) return "";

      return str
        .trim()
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/g, "d");
    }

    static escapeHtml(value) {
      const div = document.createElement("div");
      div.textContent = value ?? "";
      return div.innerHTML;
    }

    static bindTemplate(html, values) {
      return String(html).replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, function (_, key) {
        return UtilsClass.escapeHtml(values[key] ?? "");
      });
    }

    static formatDate(value, locale = "vi-VN") {
      if (!value) return "";

      const date = new Date(String(value).replace(" ", "T"));
      if (Number.isNaN(date.getTime())) return "";

      return new Intl.DateTimeFormat(locale, {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      }).format(date);
    }

    static formatDateTimeAttribute(value) {
      if (!value) return "";

      const date = new Date(String(value).replace(" ", "T"));
      if (Number.isNaN(date.getTime())) return "";

      return date.toISOString().slice(0, 10);
    }

    static formatNumber(value, locale = "vi-VN") {
      return new Intl.NumberFormat(locale).format(Number(value || 0));
    }

    static joinUrl(baseUrl, path) {
      return String(baseUrl).replace(/\/$/, "") + "/" + String(path || "").replace(/^\/+/, "");
    }

    static resolveAssetUrl(value, baseUrl, fallbackUrl) {
      if (!value) return fallbackUrl;
      if (/^https?:\/\//i.test(value)) return value;
      return UtilsClass.joinUrl(baseUrl, value);
    }
  };

  window.Utils = window.Utils || UtilsClass;
  window.AppUtils = window.AppUtils || window.Utils;
})();
