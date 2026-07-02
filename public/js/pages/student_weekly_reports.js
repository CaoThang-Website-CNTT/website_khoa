document.addEventListener("DOMContentLoaded", () => {
  const weeklyDataNode = document.getElementById("weeklyDataJson");
  const weeklyData = weeklyDataNode
    ? JSON.parse(weeklyDataNode.textContent)
    : {};

  const form = document.getElementById("weeklyReportForm");
  const weekSelect = document.getElementById("week_number");
  const isExemptCheckbox = document.getElementById("is_exempt");
  const reportContentSection = document.getElementById("reportContentSection");
  const reportContentTextarea = document.getElementById("report_content");
  const reportImagesInput = document.getElementById("report_images");
  const imagePreviewContainer = document.getElementById(
    "imagePreviewContainer",
  );
  const submitBtn = document.getElementById("submitBtn");

  const MAX_IMAGES = 5;
  const MAX_SIZE_MB = 5;
  const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024;

  // Toggle timeline details
  document.querySelectorAll(".weekly-timeline__header").forEach((header) => {
    header.addEventListener("click", () => {
      const weekNum = header.dataset.week;
      const details = document.getElementById(`details-week-${weekNum}`);
      if (details) {
        details.classList.toggle("hidden");
        const icon = header.querySelector(".fa-chevron-down, .fa-chevron-up");
        if (icon) {
          icon.classList.toggle("fa-chevron-down");
          icon.classList.toggle("fa-chevron-up");
        }
      }
    });
  });

  // Handle Exempt checkbox
  const toggleExemptState = () => {
    if (isExemptCheckbox.checked) {
      reportContentSection.classList.add("hidden");
      reportContentTextarea.required = false;
      reportContentTextarea.value = "";
      reportImagesInput.value = "";
      imagePreviewContainer.innerHTML = "";
    } else {
      reportContentSection.classList.remove("hidden");
      reportContentTextarea.required = true;
    }
  };

  isExemptCheckbox.addEventListener("change", toggleExemptState);

  // Populate form based on selected week
  const populateForm = () => {
    const weekNum = weekSelect.value;
    const data = weeklyData[weekNum];

    // Clear first
    reportImagesInput.value = "";
    imagePreviewContainer.innerHTML = "";

    if (data && data.report) {
      isExemptCheckbox.checked =
        data.report.is_exempt === 1 || data.report.is_exempt === true;
      reportContentTextarea.value = data.report.content || "";
      submitBtn.innerHTML =
        '<i class="fa-solid fa-rotate-right mr-2"></i> Nộp lại';
    } else {
      isExemptCheckbox.checked = false;
      reportContentTextarea.value = "";
      submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Gửi';
    }

    toggleExemptState();
  };

  weekSelect.addEventListener("change", populateForm);
  // Initial population
  populateForm();

  // Validate images
  reportImagesInput.addEventListener("change", () => {
    imagePreviewContainer.innerHTML = "";
    submitBtn.disabled = false;

    if (reportImagesInput.files.length === 0) return;

    if (reportImagesInput.files.length > MAX_IMAGES) {
      window.toast?.error(
        "Lỗi",
        `Chỉ được phép tải lên tối đa ${MAX_IMAGES} ảnh.`,
      );
      reportImagesInput.value = "";
      return;
    }

    let hasError = false;
    Array.from(reportImagesInput.files).forEach((file) => {
      if (file.size > MAX_SIZE_BYTES) {
        window.toast?.error(
          "Lỗi",
          `File ${file.name} vượt quá dung lượng ${MAX_SIZE_MB}MB.`,
        );
        hasError = true;
      }
      if (!file.type.startsWith("image/")) {
        window.toast?.error("Lỗi", `File ${file.name} không phải là hình ảnh.`);
        hasError = true;
      }
    });

    if (hasError) {
      reportImagesInput.value = "";
      return;
    }

    // Preview
    Array.from(reportImagesInput.files).forEach((file) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        const div = document.createElement("div");
        div.className =
          "relative border rounded-lg overflow-hidden flex items-center justify-center";
        div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        imagePreviewContainer.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  });

  // Handle Form Submit
  form.addEventListener("submit", (e) => {
    if (!isExemptCheckbox.checked && !reportContentTextarea.value.trim()) {
      e.preventDefault();
      window.toast?.error("Lỗi", "Vui lòng nhập nội dung báo cáo.");
      reportContentTextarea.focus();
    }
  });
});
