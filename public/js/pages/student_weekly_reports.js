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

  // Hàm hỗ trợ giảm chất lượng ảnh upload
  const compressImage = async (file) => {
    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onload = ({ target }) => {
        const img = new Image();
        img.onload = () => {
          const MAX_WIDTH = 1280;
          const MAX_HEIGHT = 1280;
          let width = img.width;
          let height = img.height;

          if (width > height) {
            if (width > MAX_WIDTH) {
              height = Math.round(height * (MAX_WIDTH / width));
              width = MAX_WIDTH;
            }
          } else {
            if (height > MAX_HEIGHT) {
              width = Math.round(width * (MAX_HEIGHT / height));
              height = MAX_HEIGHT;
            }
          }

          const canvas = document.createElement("canvas");
          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext("2d");
          ctx.drawImage(img, 0, 0, width, height);

          // Nén thành dạng JPEG để tiết kiệm bandwidth
          canvas.toBlob((blob) => {
            const newFileName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
            const newFile = new File([blob], newFileName, {
              type: "image/jpeg",
              lastModified: Date.now()
            });
            resolve({ file: newFile, previewSrc: URL.createObjectURL(blob) });
          }, "image/jpeg", 0.8);
        };
        img.src = target.result;
      };
      reader.readAsDataURL(file);
    });
  };

  images.addEventListener("change", async () => {
    previews.innerHTML = "";
    const files = [...images.files];
    
    if (files.length > 5) {
      window.toast?.error("Ảnh không hợp lệ", "Chỉ được phép chọn tối đa 5 hình ảnh báo cáo.");
      images.value = "";
      return;
    }
    
    if (files.some((file) => file.size > 10 * 1024 * 1024)) {
      window.toast?.error("Ảnh không hợp lệ", "Dung lượng mỗi ảnh không được vượt quá 10MB.");
      images.value = "";
      return;
    }
    
    if (files.some((file) => !["image/jpeg", "image/png", "image/webp"].includes(file.type))) {
      window.toast?.error("Ảnh không hợp lệ", "Chỉ hỗ trợ định dạng ảnh JPG, PNG hoặc WEBP.");
      images.value = "";
      return;
    }

    submitBtn.disabled = true;
    const oldHtml = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Đang xử lý...';
    
    const dataTransfer = new DataTransfer();
    
    for (const file of files) {
      try {
        const result = await compressImage(file);
        dataTransfer.items.add(result.file);
        
        const img = document.createElement("img");
        img.src = result.previewSrc;
        img.alt = result.file.name;
        previews.appendChild(img);
      } catch (err) {
        window.toast?.error("Lỗi xử lý ảnh", "Có lỗi xảy ra khi xử lý ảnh. Vui lòng thử lại.");
        images.value = "";
        previews.innerHTML = "";
        submitBtn.disabled = false;
        submitBtn.innerHTML = oldHtml;
        return;
      }
    }
    
    images.files = dataTransfer.files;
    submitBtn.disabled = false;
    submitBtn.innerHTML = oldHtml;
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
