document.addEventListener("DOMContentLoaded", () => {
  // Edit Profile Modal logic
  const apiBase = window.API_BASE_URL;
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

      saveBtn.disabled = true;
      spinner.classList.remove("hidden");

      const formData = new FormData(editProfileForm);
      const data = Object.fromEntries(formData.entries());

      try {
        const response = await fetch(`${apiBase}/student/profile/update`, {
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
  const uploadArea = document.getElementById("uploadArea");
  const fileInput = document.getElementById("report_file");
  const filePreview = document.getElementById("filePreview");
  const uploadBtn = document.getElementById("uploadBtn");

  if (uploadArea && fileInput) {
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
      if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        updateFilePreview();
      }
    });

    uploadArea.addEventListener("click", () => {
      fileInput.click();
    });

    fileInput.addEventListener("change", () => {
      updateFilePreview();
    });

    function updateFilePreview() {
      if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const maxSize = 50 * 1024 * 1024; // 50MB
        if (file.size > maxSize) {
          window.toast.error("Lỗi", "Dung lượng file không được vượt quá 50MB");
          fileInput.value = "";
          filePreview.classList.add("hidden");
          if (uploadBtn) uploadBtn.disabled = true;
          return;
        }

        filePreview.textContent = `Đã chọn: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        filePreview.classList.remove("hidden");
        if (uploadBtn) uploadBtn.disabled = false;
      } else {
        filePreview.classList.add("hidden");
        if (uploadBtn) uploadBtn.disabled = true;
      }
    }
  }

  // Company Tax Code logic
  const btnCheckMST = document.getElementById("btnCheckMST");
  const taxCodeInput = document.getElementById("tax_code");
  const companyNameInput = document.getElementById("company_name");
  const companyAddressInput = document.getElementById("company_address");
  const mstLoading = document.getElementById("mstLoading");
  const mstError = document.getElementById("mstError");

  if (btnCheckMST && taxCodeInput) {
    // Bật tắt chế độ nhập thủ công
    const isManualToggle = document.getElementById("is_manual");
    if (isManualToggle) {
      isManualToggle.addEventListener("change", () => {
        const isManual = isManualToggle.checked;

        // Reset inputs
        if (isManual) {
          taxCodeInput.value = "";
          taxCodeInput.setAttribute("disabled", "disabled");
          taxCodeInput.required = false;
          btnCheckMST.classList.add("hidden");
          companyNameInput.removeAttribute("readonly");
          companyAddressInput.removeAttribute("readonly");
          mstError.classList.add("hidden");
        } else {
          taxCodeInput.removeAttribute("disabled");
          taxCodeInput.required = true;
          btnCheckMST.classList.remove("hidden");
          companyNameInput.setAttribute("readonly", "readonly");
          companyAddressInput.setAttribute("readonly", "readonly");
        }
      });
    }

    btnCheckMST.addEventListener("click", async () => {
      const mst = taxCodeInput.value.trim();
      if (!mst) {
        mstError.textContent = "Vui lòng nhập mã số thuế.";
        mstError.classList.remove("hidden");
        return;
      }

      mstLoading.classList.remove("hidden");
      mstError.classList.add("hidden");
      companyNameInput.value = "";
      companyAddressInput.value = "";

      try {
        const response = await fetch(
          `https://api.vietqr.io/v2/business/${mst}`,
        );
        const result = await response.json();

        if (result.code === "00" && result.data) {
          companyNameInput.value = result.data.name;
          companyAddressInput.value = result.data.address;
        } else {
          mstError.textContent = "Không tìm thấy thông tin công ty.";
          mstError.classList.remove("hidden");
        }
      } catch (error) {
        mstError.textContent = "Lỗi kết nối API lấy mã số thuế.";
        mstError.classList.remove("hidden");
      } finally {
        mstLoading.classList.add("hidden");
      }
    });
  }

  // Company Autocomplete logic
  const suggestionsContainer = document.getElementById("companySuggestions");
  if (companyNameInput && suggestionsContainer) {
    const debounce = (func, wait) => {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    };

    const fetchSuggestions = async (query) => {
      if (query.length < 2) {
        suggestionsContainer.classList.add("hidden");
        return;
      }

      try {
        const response = await fetch(
          `${apiBase}/companies/suggest-by-name?q=${encodeURIComponent(query)}`,
        );
        const result = await response.json();

        if (result.success && result.data.length > 0) {
          renderSuggestions(result.data);
        } else {
          suggestionsContainer.classList.add("hidden");
        }
      } catch (error) {
        console.error("Error fetching suggestions:", error);
      }
    };

    const renderSuggestions = (companies) => {
      suggestionsContainer.innerHTML = "";
      companies.forEach((company) => {
        const div = document.createElement("div");
        div.className = "suggestions-list__item";
        div.innerHTML = `
          <div class="font-medium text-sm">${company.name}</div>
          <div class="text-xs text-muted-foreground">${company.tax_code || "Không có MST"} - ${company.address}</div>
        `;
        div.addEventListener("click", () => {
          companyNameInput.value = company.name;
          companyAddressInput.value = company.address;
          if (company.tax_code) {
            taxCodeInput.value = company.tax_code;
          }
          suggestionsContainer.classList.add("hidden");
        });
        suggestionsContainer.appendChild(div);
      });
      suggestionsContainer.classList.remove("hidden");
    };

    companyNameInput.addEventListener(
      "input",
      debounce((e) => {
        const isManual = document.getElementById("is_manual")?.checked;
        if (isManual) {
          fetchSuggestions(e.target.value);
        }
      }, 300),
    );

    // Đóng danh sách gợi ý khi click ra ngoài
    document.addEventListener("click", (e) => {
      if (
        !companyNameInput.contains(e.target) &&
        !suggestionsContainer.contains(e.target)
      ) {
        suggestionsContainer.classList.add("hidden");
      }
    });
  }
});
