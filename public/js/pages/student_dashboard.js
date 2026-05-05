document.addEventListener("DOMContentLoaded", () => {
  // Edit Profile Modal logic
  const editProfileBtn = document.getElementById("editProfileBtn");
  const editProfileModal = document.getElementById("editProfileModal");
  const closeModalBtn = document.getElementById("closeModalBtn");
  const cancelEditBtn = document.getElementById("cancelEditBtn");
  const editProfileForm = document.getElementById("editProfileForm");

  if (editProfileBtn && editProfileModal) {
    editProfileBtn.addEventListener("click", () => {
      editProfileModal.classList.remove("dashboard-modal--hidden");
    });

    const closeModal = () => {
      editProfileModal.classList.add("dashboard-modal--hidden");
    };

    closeModalBtn.addEventListener("click", closeModal);
    cancelEditBtn.addEventListener("click", closeModal);

    editProfileForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const saveBtn = document.getElementById("saveProfileBtn");
      const spinner = saveBtn.querySelector(".fa-spinner");
      const btnText = saveBtn.querySelector(".btn-text");

      saveBtn.disabled = true;
      spinner.classList.remove("hidden");
      btnText.classList.add("opacity-50");

      const formData = new FormData(editProfileForm);
      const data = Object.fromEntries(formData.entries());

      try {
        const response = await fetch("/api/student/profile/update", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(data),
        });

        const result = await response.json();

        if (result.success) {
          window.toast.success("Thành công", "Cập nhật thông tin thành công!");
          // Update UI
          updateProfileUI(data);
          closeModal();
        } else {
          window.toast.error(
            "Lỗi",
            result.message || "Có lỗi xảy ra khi cập nhật.",
          );
        }
      } catch (error) {
        window.toast.error("Lỗi", "Không thể kết nối đến máy chủ.");
      } finally {
        saveBtn.disabled = false;
        spinner.classList.add("hidden");
        btnText.classList.remove("opacity-50");
      }
    });
  }

  function updateProfileUI(data) {
    if (data.full_name)
      document.getElementById("val-full_name").textContent = data.full_name;
    if (data.dob) {
      const parts = data.dob.split("-");
      if (parts.length === 3) {
        document.getElementById("val-dob").textContent =
          `${parts[2]}/${parts[1]}/${parts[0]}`;
      }
    }
    if (data.gender) {
      const genderMap = { male: "Nam", female: "Nữ", other: "Khác" };
      document.getElementById("val-gender").textContent =
        genderMap[data.gender] || data.gender;
    }
    if (data.phone)
      document.getElementById("val-phone").textContent = data.phone;
    if (data.address)
      document.getElementById("val-address").textContent = data.address;
    if (data.birth_place)
      document.getElementById("val-birth_place").textContent = data.birth_place;
  }

  // Upload area interaction (Visual feedback)
  const uploadArea = document.querySelector(".upload-area");
  if (uploadArea) {
    uploadArea.addEventListener("dragover", (e) => {
      e.preventDefault();
      uploadArea.classList.add("border-primary");
    });

    uploadArea.addEventListener("dragleave", () => {
      uploadArea.classList.remove("border-primary");
    });

    uploadArea.addEventListener("drop", (e) => {
      e.preventDefault();
      uploadArea.classList.remove("border-primary");
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        handleFileUpload(files[0]);
      }
    });

    uploadArea.addEventListener("click", () => {
      const input = document.createElement("input");
      input.type = "file";
      input.accept = ".pdf,.docx,.zip";
      input.onchange = (e) => {
        if (e.target.files.length > 0) {
          handleFileUpload(e.target.files[0]);
        }
      };
      input.click();
    });
  }

  function handleFileUpload(file) {
    // Check file size (30MB)
    const maxSize = 30 * 1024 * 1024;
    if (file.size > maxSize) {
      window.toast.error("Lỗi", "Dung lượng file không được vượt quá 30MB");
      return;
    }

    window.toast.info("Thông báo", `Đang chuẩn bị tải lên: ${file.name}`);

    const internshipData = document.getElementById("internship-data");
    if (!internshipData) {
      window.toast.error("Lỗi", "Không tìm thấy thông tin đợt thực tập.");
      return;
    }

    const batchStudentId = internshipData.getAttribute("data-batch-student-id");
    const formData = new FormData();
    formData.append("file", file);
    formData.append("batch_student_id", batchStudentId);
    formData.append("type", "internship_report");

    const uploadArea = document.querySelector(".upload-area");
    uploadArea.classList.add("opacity-50", "pointer-events-none");

    fetch("/api/student/profile/upload-document", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((result) => {
        if (result.success) {
          window.toast.success("Thành công", "Tải lên tài liệu thành công!");
          // Refresh page after a short delay to show new status/history
          setTimeout(() => window.location.reload(), 1500);
        } else {
          window.toast.error("Lỗi", result.message || "Tải lên thất bại.");
        }
      })
      .catch((err) => {
        window.toast.error("Lỗi", "Không thể kết nối đến máy chủ.");
      })
      .finally(() => {
        uploadArea.classList.remove("opacity-50", "pointer-events-none");
      });
  }
});
