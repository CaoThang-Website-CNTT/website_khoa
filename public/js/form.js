document.addEventListener("DOMContentLoaded", () => {
  // Lấy các DOM Element
  const fields = document.querySelectorAll(".field");

  for (const field of fields) {
    const dataAttrs = getFieldAttributes(field.dataset);
    const fieldInput = field.querySelector(".field__input");

    console.log(fieldInput)

    // Áp dụng rule
    for (const dataAttr of dataAttrs) {
      console.log(dataAttr);
      applyFieldRule(fieldInput, dataAttr);
    }

    console.log(dataAttrs);
  }
})

const getFieldAttributes = (dataset) => {
  const result = []

  for (const data in dataset) {
    if (data.startsWith("field"))
      result.push(data);
  }

  return result;
}

const applyFieldRule = (field, type) => {
  switch (type) {
    case "fieldReadonly":
      field.setAttribute("readonly", "true");
      break;
    case "fieldDisabled":
      field.setAttribute("disabled", "true");
      break;
  }
}