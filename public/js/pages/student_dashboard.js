document.addEventListener("DOMContentLoaded", () => {
  const apiBase = window.API_BASE_URL;

  const internshipTabs = document.querySelector(
    '[data-tabs-id="internship-journey"]',
  );
  if (internshipTabs) {
    const triggers = Array.from(internshipTabs.querySelectorAll("[data-tabs-trigger]"));
    const tasks = Array.from(document.querySelectorAll("[data-internship-phase]"));
    const heading = document.querySelector("[data-internship-phase-heading]");
    const labels = ["Chuẩn bị", "Thực tập", "Chấm điểm & kết thúc"];
    let selectedPhase = Number(
      internshipTabs.dataset.tabsPanelActive?.replace("phase-", "") || 0,
    );

    const decoratePhase = (phase, animate = true) => {
      selectedPhase = phase;
      tasks.forEach((task) => {
        const isVisible = Number(task.dataset.internshipPhase) === phase;
        task.setAttribute("aria-hidden", String(!isVisible));
        task.classList.remove("animate-fade-in-up");
        if (isVisible && animate) {
          requestAnimationFrame(() => task.classList.add("animate-fade-in-up"));
        }
      });
      if (heading) {
        heading.innerHTML = `<p class="internship-phase-heading__kicker">Giai đoạn ${phase + 1}/3</p><h3>${labels[phase]}</h3>`;
      }
    };

    triggers.forEach((trigger) => {
      trigger.addEventListener("click", () =>
        decoratePhase(Number(trigger.dataset.tabsTrigger.replace("phase-", ""))),
      );
      trigger.addEventListener("keydown", (event) => {
        if (!["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) return;
        event.preventDefault();
        let nextPhase = selectedPhase;
        if (event.key === "ArrowLeft") nextPhase = (selectedPhase + triggers.length - 1) % triggers.length;
        if (event.key === "ArrowRight") nextPhase = (selectedPhase + 1) % triggers.length;
        if (event.key === "Home") nextPhase = 0;
        if (event.key === "End") nextPhase = triggers.length - 1;
        triggers[nextPhase].click();
        triggers[nextPhase].focus();
      });
    });
    decoratePhase(selectedPhase, false);
  }

  // Khởi tạo tính năng tìm MST và autocomplete cho một tập các elements
  const initCompanyFormLogic = (prefix) => {
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

    if (companyNameInput) {
      if (companyNameInput.dataset.companyLogicInit) return;
      companyNameInput.dataset.companyLogicInit = "true";
    }

    // Nút clear thông tin công ty chọn từ danh sách gợi ý
    let clearBtn;
    if (companyNameInput && suggestionsContainer) {
      clearBtn = document.createElement("button");
      clearBtn.type = "button";
      clearBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
      clearBtn.className = "suggestions-list__clear hidden";
      clearBtn.title = "Bỏ chọn công ty";
      companyNameInput.parentNode.appendChild(clearBtn);

      clearBtn.addEventListener("click", () => {
        companyNameInput.value = "";
        companyAddressInput.value = "";
        companyNameInput.removeAttribute("readonly");
        companyAddressInput.removeAttribute("readonly");
        clearBtn.classList.add("hidden");
        if (taxCodeInput && isManualToggle && isManualToggle.checked) {
          taxCodeInput.value = "";
        }
        companyNameInput.focus();
      });
    }

    if (btnCheckMST && taxCodeInput) {
      if (isManualToggle) {
        const updateManualState = () => {
          const isManual = isManualToggle.checked;
          if (isManual) {
            taxCodeInput.value = "";
            taxCodeInput.setAttribute("readonly", "readonly");
            taxCodeInput.required = false;
            btnCheckMST.classList.add("hidden");
            if (mstError) mstError.classList.add("hidden");

            companyNameInput.removeAttribute("readonly");
            companyAddressInput.removeAttribute("readonly");
            companyNameInput.value = "";
            companyAddressInput.value = "";
            if (clearBtn) clearBtn.classList.add("hidden");
          } else {
            taxCodeInput.removeAttribute("readonly");
            taxCodeInput.required = true;
            btnCheckMST.classList.remove("hidden");

            companyNameInput.setAttribute("readonly", "readonly");
            companyAddressInput.setAttribute("readonly", "readonly");
            companyNameInput.value = "";
            companyAddressInput.value = "";
            if (clearBtn) clearBtn.classList.add("hidden");
          }
        };

        isManualToggle.addEventListener("change", updateManualState);
        if (
          !companyNameInput.value &&
          !companyAddressInput.value &&
          isManualToggle.checked
        ) {
          companyNameInput.removeAttribute("readonly");
          companyAddressInput.removeAttribute("readonly");
        }
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
        if (clearBtn) clearBtn.classList.add("hidden");

        try {
          const response = await fetch(
            `https://api.vietqr.io/v2/business/${mst}`,
          );
          const result = await response.json();

          if (result.code === "00" && result.data) {
            companyNameInput.value = result.data.name;
            companyAddressInput.value = result.data.address;
            companyNameInput.setAttribute("readonly", "readonly");
            companyAddressInput.setAttribute("readonly", "readonly");
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
            companyNameInput.setAttribute("readonly", "readonly");
            companyAddressInput.setAttribute("readonly", "readonly");
            clearBtn.classList.remove("hidden");

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
          if (isManual && !companyNameInput.hasAttribute("readonly")) {
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
  initCompanyFormLogic("");

  // Khởi tạo cho Modal đăng ký giấy giới thiệu (prefix = 'rl_')
  initCompanyFormLogic("rl_");
  const uploadBtn = document.getElementById("uploadBtn");
  const fileInputs = document.querySelectorAll(".file-input");

  if (fileInputs.length > 0) {
    const maxSize = 10 * 1024 * 1024; // 10MB

    const validateFiles = () => {
      let hasFile = false;
      let hasError = false;

      fileInputs.forEach((input) => {
        if (input.files.length > 0) {
          hasFile = true;
          const file = input.files[0];

          if (file.size > maxSize) {
            window.toast?.error(
              "Lỗi",
              `File ${file.name} vượt quá dung lượng 10MB`,
            );
            input.value = "";
            hasError = true;
            return;
          }

          const isImage =
            file.type.startsWith("image/") ||
            file.name.match(/\.(jpg|jpeg|png|webp)$/i);
          const isPdf =
            file.type === "application/pdf" || file.name.match(/\.pdf$/i);

          if (input.accept.includes("image") && !isImage) {
            window.toast?.error(
              "Lỗi",
              "Tài liệu này yêu cầu định dạng hình ảnh (JPG, PNG, WEBP)",
            );
            input.value = "";
            hasError = true;
          } else if (input.accept.includes(".pdf") && !isPdf) {
            window.toast?.error("Lỗi", "Tài liệu này yêu cầu định dạng PDF");
            input.value = "";
            hasError = true;
          }
        }
      });

      if (uploadBtn) {
        // Kiểm tra required inputs
        const requiredInputs = Array.from(fileInputs).filter((inp) =>
          inp.hasAttribute("required"),
        );
        const allRequiredFilled = requiredInputs.every(
          (inp) => inp.files.length > 0,
        );

        uploadBtn.disabled = !hasFile || hasError || !allRequiredFilled;
      }
    };

    fileInputs.forEach((input) => {
      input.addEventListener("change", validateFiles);
    });
  }
});
