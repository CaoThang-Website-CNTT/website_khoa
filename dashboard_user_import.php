<div class="detail-panel card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>
        Import from file
      </h6>
    </div>
    <div class="card__description">
      Upload your .xlsx file to automatically add data.
    </div>
  </div>
  <div class="card__content">
    <form id="import-form" method="POST" enctype="multipart/form-data">
      <div class="field-group">
        <div class="field">
          <input id="import-file" class="field__input" type="file" name="uploaded_file" accept=".xlsx">
        </div>
      </div>
    </form>
  </div>
  <div class="card__footer">
  </div>
</div>
<script>
  document.querySelector("#import-file").addEventListener("change", () => {
    document.querySelector("#import-form").submit();
  })
</script>