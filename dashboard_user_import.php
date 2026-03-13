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

    <!-- IMPORT MESSAGES -->
    <?php if (!empty($_SESSION['import_errors'])): ?>
      <div class="alert alert--error">
        <div class="alert__title">
          Import thất bại — vui lòng kiểm tra các lỗi sau:
        </div>
        <ul class="alert__list">
          <?php foreach ($_SESSION['import_errors'] as $rowErrors): ?>
            <?php foreach ((array) $rowErrors as $message): ?>
              <li><?= htmlspecialchars($message) ?></li>
            <?php endforeach ?>
          <?php endforeach ?>
        </ul>
      </div>
      <?php unset($_SESSION['import_errors']) ?>
    <?php endif ?>

    <?php if (!empty($_SESSION['import_success'])): ?>
      <div class="alert alert--success">
        <?= htmlspecialchars($_SESSION['import_success']) ?>
      </div>
      <?php unset($_SESSION['import_success']) ?>
    <?php endif ?>

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