document.addEventListener("DOMContentLoaded", () => {
  const apiBase = window.API_BASE_URL;

  // Khởi tạo tính năng tìm MST và autocomplete cho một tập các elements
  window.initCompanyFormLogic = function (prefix) {
    const isManualToggle = document.getElementById(`${prefix}is_manual`);
    const btnCheckMST = document.getElementById(`${prefix}btnCheckMST`);
    const taxCodeInput = document.getElementById(`${prefix}tax_code`);
    const companyNameInput = document.getElementById(`${prefix}company_name`);
    const companyAddressInput = document.getElementById(
      `${prefix}company_address`,
    );
    const mstLoading = document.getElementById(`${prefix}mstLoading`);
    const mstError = document.getElementById(`${prefix}mstError`);
    const suggestionsContainer = document.getElementById(
      `${prefix}companySuggestions`,
    );

    if (btnCheckMST && taxCodeInput) {
      if (isManualToggle) {
        isManualToggle.addEventListener("change", () => {
          const isManual = isManualToggle.checked;
          if (isManual) {
            taxCodeInput.value = "";
            taxCodeInput.setAttribute("disabled", "disabled");
            taxCodeInput.required = false;
            btnCheckMST.classList.add("hidden");
            if (mstError) mstError.classList.add("hidden");
          } else {
            taxCodeInput.removeAttribute("disabled");
            taxCodeInput.required = true;
            btnCheckMST.classList.remove("hidden");
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
            <div class="text-xs">${company.tax_code || "Không có MST"} - ${company.address}</div>
          `;
          div.addEventListener("click", () => {
            companyNameInput.value = company.name;
            companyAddressInput.value = company.address;
            if (company.tax_code && taxCodeInput) {
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
          const isManual = isManualToggle?.checked;
          if (isManual) {
            fetchSuggestions(e.target.value);
          }
        }, 300),
      );

      document.addEventListener("click", (e) => {
        if (
          !companyNameInput.contains(e.target) &&
          !suggestionsContainer.contains(e.target)
        ) {
          suggestionsContainer.classList.add("hidden");
        }
      });
    }
  };

  // Khởi tạo cho Form công ty hiện tại (không có prefix)
  window.initCompanyFormLogic("");

  // Khởi tạo cho Modal đăng ký giấy giới thiệu (prefix = 'rl_')
  window.initCompanyFormLogic("rl_");
  const uploadArea = document.getElementById("uploadArea");
  const docTypeSelect = document.getElementById("doc_type");
  const fileInput = document.getElementById("report_file");
  const filePreview = document.getElementById("filePreview");
  const uploadBtn = document.getElementById("uploadBtn");

  if (uploadArea && fileInput) {
    if (docTypeSelect) {
      docTypeSelect.addEventListener("change", () => {
        const type = docTypeSelect.value;
        if (type === 'related_photo') {
          fileInput.accept = ".jpg,.jpeg,.png,.webp,image/*";
        } else {
          fileInput.accept = ".pdf,application/pdf";
        }
        updateFilePreview();
      });
    }
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
          window.toast?.error("Lỗi", "Dung lượng file không được vượt quá 50MB");
          fileInput.value = "";
          filePreview.classList.add("hidden");
          if (uploadBtn) uploadBtn.disabled = true;
          return;
        }

        if (docTypeSelect && docTypeSelect.value) {
          const type = docTypeSelect.value;
          const isImage = file.type.startsWith('image/') || file.name.match(/\.(jpg|jpeg|png|webp)$/i);
          const isPdf = file.type === 'application/pdf' || file.name.match(/\.pdf$/i);

          if (type === 'related_photo' && !isImage) {
            window.toast?.error("Lỗi", "Hình ảnh liên quan phải là định dạng JPG, PNG, WEBP");
            fileInput.value = "";
            filePreview.classList.add("hidden");
            if (uploadBtn) uploadBtn.disabled = true;
            return;
          } else if (type !== 'related_photo' && type !== '' && !isPdf) {
            window.toast?.error("Lỗi", "Tài liệu này yêu cầu định dạng PDF");
            fileInput.value = "";
            filePreview.classList.add("hidden");
            if (uploadBtn) uploadBtn.disabled = true;
            return;
          }
        }

        filePreview.textContent = `Đã chọn: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        filePreview.classList.remove("hidden");
        if (uploadBtn) {
          uploadBtn.disabled = docTypeSelect ? !docTypeSelect.value : false;
        }
      } else {
        filePreview.classList.add("hidden");
        if (uploadBtn) uploadBtn.disabled = true;
      }
    }
  }
});
