document.addEventListener("DOMContentLoaded", () => {
  const levelSelect = document.getElementById("level");
  const majorSelect = document.getElementById("major_id");
  const specializationSelect = document.getElementById("specialization_id");

  const shortNameInput = document.getElementById("short_name");
  const shortNameInputDisplay = document.getElementById("short_name_display");
  const classOfInput = document.getElementById("class_of");
  const letterInput = document.getElementById("letter");

  const parentSpecializationSelect = specializationSelect.parentElement;

  // Các nút và modal thêm mới
  const btnAddMajor = document.getElementById("btn-add-major");
  const btnAddSpecialization = document.getElementById(
    "btn-add-specialization",
  );

  const modalAddMajor = new Modal("#add-major-modal");
  const modalAddSpec = new Modal("#add-specialization-modal");

  majorSelect.disabled = true;
  parentSpecializationSelect.parentElement.style.display = "none";

  let majors = [];
  let specializations = [];

  levelSelect.value = "";
  majorSelect.value = "";
  specializationSelect.value = "";

  levelSelect.addEventListener("change", async function () {
    showClassroomShortName();

    const level = levelSelect.value;

    // Bật nút thêm ngành nếu đã chọn bậc học
    if (btnAddMajor) btnAddMajor.disabled = !level;

    // Chỉ hiển thị specialization selectbox khi chọn `Cao đẳng`
    parentSpecializationSelect.parentElement.style.display =
      level === "CĐN" ? "none" : "flex";

    majorSelect.innerHTML = `<option value="" disabled selected> -- Chọn ${level === "CĐ" ? "Ngành" : "Nghề"} -- </option>`;
    majorSelect.disabled = true;
    majorSelect.value = "";

    // Tắt nút thêm chuyên ngành
    if (btnAddSpecialization) btnAddSpecialization.disabled = true;

    if (!level) return;

    try {
      const res = await fetch(`/website_khoa/api/majors?level=${level}`);
      const result = await res.json();
      if (!result.success) throw new Error(result.message);

      majors = result.data;
      majors.forEach((m) => {
        const option = document.createElement("option");
        option.value = m.id;
        option.textContent = m.full_name;
        majorSelect.appendChild(option);
      });

      majorSelect.disabled = false;
    } catch (error) {
      console.error(error);
      majorSelect.innerHTML = "<option>Lỗi tải dữ liệu</option>";
    }
  });

  majorSelect.addEventListener("change", async function () {
    showClassroomShortName();

    const majorId = this.value;
    const level = levelSelect.value;

    // Bật nút thêm chuyên ngành nếu chọn CĐ và đã chọn ngành
    if (btnAddSpecialization) {
      btnAddSpecialization.disabled = !majorId || level === "CĐN";
    }

    if (level === "CĐN") return; // CĐN không có chuyên ngành

    specializationSelect.innerHTML =
      '<option value="" disabled selected> -- Chọn Chuyên Ngành -- </option>';
    specializationSelect.disabled = true;

    if (!majorId) return;

    try {
      const res = await fetch(
        `/website_khoa/api/specializations?majorId=${majorId}`,
      );
      const result = await res.json();
      if (!result.success) throw new Error(result.message);

      specializations = result.data;
      specializations.forEach((m) => {
        const option = document.createElement("option");
        option.value = m.id;
        option.textContent = m.full_name;
        specializationSelect.appendChild(option);
      });

      const option = document.createElement("option");
      option.value = "0";
      option.textContent = "Không có";
      specializationSelect.appendChild(option);

      specializationSelect.disabled = false;
      specializationSelect.value = "";
    } catch (error) {
      console.error(error);
      specializationSelect.innerHTML = "<option>Lỗi tải dữ liệu</option>";
    }
  });

  classOfInput.addEventListener("input", showClassroomShortName);

  letterInput.addEventListener("input", function () {
    // Letter chỉ cho phép nhập 1 chữ cái in hoa
    this.value = this.value
      .toUpperCase()
      .replace(/[^A-Z]/g, "")
      .slice(0, 1);
    showClassroomShortName();
  });

  specializationSelect.addEventListener("change", showClassroomShortName);

  function showClassroomShortName() {
    const selectedMajor = majors.find((p) => p.id == majorSelect.value);
    const selectedSpecialization = specializations.find(
      (m) => m.id == specializationSelect.value,
    );

    let shortName = `${levelSelect.value ?? ""} ${selectedMajor?.short_name ?? ""} ${classOfInput.value ?? ""} ${selectedSpecialization?.short_name ?? ""} ${letterInput.value ?? ""}`;

    shortName = shortName.replace(/\s+/g, " ").trim();

    shortNameInput.value = shortName;
    shortNameInputDisplay.value = shortName;
  }

  if (document.getElementById("submit-new-major")) {
    document
      .getElementById("submit-new-major")
      .addEventListener("click", async () => {
        const fullName = document
          .getElementById("new_major_full_name")
          .value.trim();
        const shortName = document
          .getElementById("new_major_short_name")
          .value.trim()
          .toUpperCase();
        const level = levelSelect.value;

        if (!fullName || !shortName)
          return alert("Vui lòng nhập đủ thông tin Ngành!");

        try {
          const res = await fetch(`/website_khoa/api/majors`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              full_name: fullName,
              short_name: shortName,
              level: level,
            }),
          });

          const result = await res.json();
          if (!result.success) throw new Error(result.message);
          majors.push(result.data);

          // Tạo option chèn vào select box
          const option = document.createElement("option");
          option.value = result.data.id;
          option.textContent = result.data.full_name;
          majorSelect.appendChild(option);

          // Chọn ngành vừa thêm và kích hoạt sự kiện change
          majorSelect.value = result.data.id;
          majorSelect.dispatchEvent(new Event("change"));

          // Reset input form và đóng modal
          document.getElementById("new_major_full_name").value = "";
          document.getElementById("new_major_short_name").value = "";
          modalAddMajor.hide();
        } catch (error) {
          alert("Lỗi: " + error.message);
        }
      });
  }

  if (document.getElementById("submit-new-specialization")) {
    document
      .getElementById("submit-new-specialization")
      .addEventListener("click", async () => {
        const fullName = document
          .getElementById("new_spec_full_name")
          .value.trim();
        const shortName = document
          .getElementById("new_spec_short_name")
          .value.trim()
          .toUpperCase();
        const majorId = majorSelect.value;

        if (!fullName || !shortName)
          return alert("Vui lòng nhập đủ thông tin Chuyên ngành!");

        try {
          const res = await fetch(`/website_khoa/api/specializations`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              major_id: majorId,
              full_name: fullName,
              short_name: shortName,
            }),
          });

          const result = await res.json();
          if (!result.success) throw new Error(result.message);
          specializations.push(result.data);

          const option = document.createElement("option");
          option.value = result.data.id;
          option.textContent = result.data.full_name;

          // Chèn vào trước option "Không có"
          specializationSelect.insertBefore(
            option,
            specializationSelect.lastElementChild,
          );

          // Chọn chuyên ngành vừa thêm
          specializationSelect.value = result.data.id;
          specializationSelect.dispatchEvent(new Event("change"));

          // Reset input form và đóng modal
          document.getElementById("new_spec_full_name").value = "";
          document.getElementById("new_spec_short_name").value = "";
          modalAddSpec.hide();
        } catch (error) {
          alert("Lỗi: " + error.message);
        }
      });
  }
});
