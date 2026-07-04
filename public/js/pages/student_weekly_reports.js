document.addEventListener("DOMContentLoaded", () => {
  const dataNode = document.getElementById("weeklyDataJson");
  const weeklyData = dataNode ? JSON.parse(dataNode.textContent) : {};
  const form = document.getElementById("weeklyReportForm");
  const weekSelect = document.getElementById("weekSelect");
  const weekInput = document.getElementById("week_number");
  const reasonSelect = document.getElementById("reasonSelect");
  const reasonInput = document.getElementById("no_activity_reason");
  const exemptInput = document.getElementById("is_exempt");
  const contentSection = document.getElementById("reportContentSection");
  const noActivitySection = document.getElementById("noActivitySection");
  const content = document.getElementById("report_content");
  const note = document.getElementById("no_activity_note");
  const images = document.getElementById("report_images");
  const previews = document.getElementById("imagePreviewContainer");
  const submitBtn = document.getElementById("submitBtn");
  const confirmTitle = document.getElementById("weeklyConfirmTitle");
  const confirmDescription = document.getElementById("weeklyConfirmDescription");
  const confirmSubmit = document.getElementById("weeklyConfirmSubmit");

  const setMode = (mode) => {
    const noActivity = mode === "no_activity";
    exemptInput.value = noActivity ? "1" : "0";
    noActivitySection.classList.toggle("hidden", !noActivity);
    contentSection.classList.toggle("hidden", noActivity);
    content.required = !noActivity;
    reasonInput.required = noActivity;
    if (noActivity) {
      content.value = "";
      images.value = "";
      previews.innerHTML = "";
    } else {
      reasonInput.value = "";
      note.value = "";
    }
  };

  const populateForm = () => {
    const report = weeklyData[weekInput.value]?.report;
    images.value = "";
    previews.innerHTML = "";
    submitBtn.innerHTML = report
      ? 'Cập nhật báo cáo'
      : 'Lưu báo cáo';
    confirmTitle.textContent = report ? "Xác nhận cập nhật báo cáo" : "Xác nhận gửi báo cáo";
    confirmDescription.textContent = report
      ? "Báo cáo mới sẽ trở thành bản hiện tại. Bản cũ vẫn được lưu trong lịch sử."
      : "Bạn có chắc chắn muốn gửi báo cáo tuần này?";
    confirmSubmit.textContent = report ? "Xác nhận cập nhật" : "Xác nhận gửi";
    const mode = report?.is_exempt ? "no_activity" : "activity";
    content.value = report?.content || "";
    reasonInput.value = report?.no_activity_reason || "";
    note.value = report?.no_activity_note || "";
    window.RadioHandler?.instance?.setValue(document.getElementById("reportMode"), mode);
    window.SelectHandler?.instance?.setValue("no-activity-reason", report?.no_activity_reason || null, { emit: false });
    setMode(mode);
  };

  weekSelect.addEventListener("select:change", (event) => {
    if (!event.detail.value) return;
    weekInput.value = event.detail.value;
    populateForm();
  });
  reasonSelect.addEventListener("select:change", (event) => {
    reasonInput.value = event.detail.value || "";
  });
  document.getElementById("reportMode").addEventListener("radio:change", (event) => {
    setMode(event.detail.value);
  });

  images.addEventListener("change", () => {
    previews.innerHTML = "";
    const files = [...images.files];
    if (files.length > 5 || files.some((file) => file.size > 5 * 1024 * 1024 || !["image/jpeg", "image/png", "image/webp"].includes(file.type))) {
      window.toast?.error("Ảnh không hợp lệ", "Chọn tối đa 5 ảnh JPG, PNG hoặc WEBP, mỗi ảnh không quá 5 MB.");
      images.value = "";
      return;
    }
    files.forEach((file) => {
      const reader = new FileReader();
      reader.onload = ({ target }) => {
        const img = document.createElement("img");
        img.src = target.result;
        img.alt = file.name;
        previews.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  });

  submitBtn.addEventListener("click", (event) => {
    const missingReason = exemptInput.value === "1" && !reasonInput.value;
    const missingContent = exemptInput.value === "0" && !content.value.trim();
    if (!missingReason && !missingContent && form.reportValidity()) return;

    event.preventDefault();
    event.stopPropagation();
    if (missingReason) {
      window.toast?.error("Thiếu lý do", "Vui lòng chọn lý do tuần không có hoạt động thực tập.");
      reasonSelect.querySelector(".select__trigger")?.focus();
    } else if (missingContent) {
      window.toast?.error("Thiếu nội dung", "Vui lòng nhập nội dung công việc trong tuần.");
      content.focus();
    }
  });

  form.addEventListener("submit", (event) => {
    if (exemptInput.value === "1" && !reasonInput.value) {
      event.preventDefault();
      window.toast?.error("Thiếu lý do", "Vui lòng chọn lý do tuần không có hoạt động thực tập.");
      reasonSelect.querySelector(".select__trigger")?.focus();
    } else if (exemptInput.value === "0" && !content.value.trim()) {
      event.preventDefault();
      window.toast?.error("Thiếu nội dung", "Vui lòng nhập nội dung công việc trong tuần.");
      content.focus();
    }
  });

  populateForm();
});
