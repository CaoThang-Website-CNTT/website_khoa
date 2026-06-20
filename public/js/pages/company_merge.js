document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("target_search");
  const suggestionsBox = document.getElementById("target_suggestions");
  const selectedInfo = document.getElementById("target_selected_info");
  const targetIdDisplay = document.getElementById("target_id_display");
  const targetNameDisplay = document.getElementById("target_name_display");
  const btnClearTarget = document.getElementById("btn_clear_target");
  const targetIdInput = document.getElementById("target_id");
  const btnSubmit = document.getElementById("btn_submit_merge");
  const mstWarning = document.getElementById("mst-warning");
  const confirmDiffMst = document.getElementById("confirm_diff_mst");

  let currentTarget = null;
  let sourceData = {};

  document.querySelectorAll(".field-row").forEach((row) => {
    const field = row.dataset.field;
    const sourceVal = row.querySelector(".val-source").textContent.trim();
    sourceData[field] = sourceVal === "--" ? "" : sourceVal;
  });

  let debounceTimer;

  searchInput.addEventListener("input", (e) => {
    clearTimeout(debounceTimer);
    const query = e.target.value.trim();

    if (query.length < 2) {
      suggestionsBox.classList.add("hidden");
      return;
    }

    debounceTimer = setTimeout(() => {
      fetch(
        `${window.API_BASE_URL}/companies/search-merge?q=${encodeURIComponent(query)}&exclude=${window.MERGE_SOURCE_ID}`,
      )
        .then((res) => res.json())
        .then((json) => {
          const data = json.data || [];
          suggestionsBox.innerHTML = "";
          if (data.length === 0) {
            suggestionsBox.innerHTML =
              '<div class="p-4 text-sm text-gray-500">Không tìm thấy công ty phù hợp.</div>';
          } else {
            data.forEach((item) => {
              const div = document.createElement("div");
              div.className = "suggestion-item";
              div.innerHTML = `
                                <div class="font-medium text-primary-600">#${item.id} - ${item.name}</div>
                                <div class="text-xs text-gray-500">MST: ${item.tax_code || "--"} | ĐC: ${item.address || "--"}</div>
                            `;
              div.addEventListener("click", () => selectTarget(item));
              suggestionsBox.appendChild(div);
            });
          }
          suggestionsBox.classList.remove("hidden");
        });
    }, 300);
  });

  document.addEventListener("click", (e) => {
    if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
      suggestionsBox.classList.add("hidden");
    }
  });

  btnClearTarget.addEventListener("click", () => {
    currentTarget = null;
    targetIdInput.value = "";
    searchInput.value = "";
    searchInput.classList.remove("hidden");
    selectedInfo.classList.add("hidden");

    // Đặt lại các trường của công ty đích
    document.querySelectorAll(".field-row").forEach((row) => {
      const targetLabel = row.querySelector(".target-label");
      const targetValSpan = row.querySelector(".val-target");
      const targetRadio = row.querySelector('input[value="target"]');
      const sourceRadio = row.querySelector('input[value="source"]');

      targetLabel.classList.add("merge-choice-label--disabled");
      targetValSpan.textContent = "Chưa chọn đích";
      targetValSpan.classList.add("merge-choice-value--empty");
      targetRadio.disabled = true;
      sourceRadio.checked = true;

      // Kích hoạt sự kiện thay đổi
      sourceRadio.dispatchEvent(new Event("change", { bubbles: true }));
    });

    checkMstWarning();
  });

  function selectTarget(item) {
    currentTarget = item;
    targetIdInput.value = item.id;
    searchInput.classList.add("hidden");
    selectedInfo.classList.remove("hidden");
    targetIdDisplay.textContent = `#${item.id}`;
    targetNameDisplay.textContent = item.name;
    suggestionsBox.classList.add("hidden");

    // Nợ kỹ thuật: API search-merge có thể thiếu email, sđt. Do chưa có API lấy chi tiết công ty (GET /companies/{id}),
    // nên tạm thời dùng luôn dữ liệu từ đối tượng kết quả tìm kiếm để hiển thị. Cần đảm bảo API tìm kiếm trả về đủ dữ liệu.

    populateTargetFields(item);
    checkMstWarning();
  }

  function populateTargetFields(item) {
    document.querySelectorAll(".field-row").forEach((row) => {
      const field = row.dataset.field;
      const targetLabel = row.querySelector(".target-label");
      const targetValSpan = row.querySelector(".val-target");
      const targetRadio = row.querySelector('input[value="target"]');

      let val = item[field] || "";

      targetLabel.classList.remove("merge-choice-label--disabled");
      targetValSpan.textContent = val || "--";
      targetValSpan.classList.remove("merge-choice-value--empty");
      targetRadio.disabled = false;

      // Tự động chọn giá trị đích nếu đích có dữ liệu và nguồn đang trống
      if (val && !sourceData[field]) {
        targetRadio.checked = true;
        targetRadio.dispatchEvent(new Event("change", { bubbles: true }));
      } else if (val) {
        // Mặc định chọn giá trị đích
        targetRadio.checked = true;
        targetRadio.dispatchEvent(new Event("change", { bubbles: true }));
      }
    });
  }

  document.querySelectorAll(".field-row").forEach((row) => {
    const field = row.dataset.field;
    const input = document.getElementById(`${field}_input`);
    const radios = row.querySelectorAll('input[type="radio"]');

    radios.forEach((radio) => {
      radio.addEventListener("change", (e) => {
        if (e.target.checked) {
          if (e.target.value === "source") {
            input.value = sourceData[field];
          } else if (e.target.value === "target" && currentTarget) {
            input.value = currentTarget[field] || "";
          }
        }
      });
    });
  });

  function checkMstWarning() {
    const sourceMst = (sourceData["tax_code"] || "").trim();
    const targetMst = currentTarget && currentTarget["tax_code"] ? String(currentTarget["tax_code"]).trim() : "";

    if (sourceMst && targetMst && sourceMst !== targetMst) {
      mstWarning.classList.remove("hidden");
      confirmDiffMst.required = true;
    } else {
      mstWarning.classList.add("hidden");
      confirmDiffMst.required = false;
      confirmDiffMst.checked = false;
    }
    updateSubmitBtn();
  }

  confirmDiffMst.addEventListener("change", updateSubmitBtn);

  function updateSubmitBtn() {
    if (!currentTarget) {
      btnSubmit.disabled = true;
      return;
    }

    const sourceMst = (sourceData["tax_code"] || "").trim();
    const targetMst = currentTarget && currentTarget["tax_code"] ? String(currentTarget["tax_code"]).trim() : "";

    if (
      sourceMst &&
      targetMst &&
      sourceMst !== targetMst &&
      !confirmDiffMst.checked
    ) {
      btnSubmit.disabled = true;
    } else {
      btnSubmit.disabled = false;
    }
  }

  // Điền sẵn nếu tham số truy vấn có truyền từ khóa tìm kiếm
  if (window.MERGE_PREFILL_QUERY) {
    searchInput.value = window.MERGE_PREFILL_QUERY;
    searchInput.dispatchEvent(new Event("input", { bubbles: true }));
    searchInput.focus();
  }
});
