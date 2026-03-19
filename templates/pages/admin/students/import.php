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

    <!-- FLASH ALERT -->
    <?php
    include BASE_PATH . '/templates/components/flash_alert.php';
    ?>

    <?php
    $importErrors = $_SESSION['import_errors'] ?? null;
    unset($_SESSION['import_errors']);

    if ($importErrors) {
      foreach ($importErrors as $rowErrors) {
        foreach ($rowErrors as $error) {
          $msg .= htmlspecialchars($error, ENT_COMPAT, 'UTF-8') . "\n";
        }
      }

      $_SESSION['_flash'] = [
        'type' => 'error',
        'title' => count($importErrors) . ' dòng có lỗi, vui lòng kiểm tra lại.',
        'desc' => $msg,
      ];
    }

    include BASE_PATH . '/templates/components/flash_alert.php';
    ?>

    <form id="import-form" action=<?= url('admin/students/import') ?> method="POST" enctype="multipart/form-data">
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
  document.querySelector("#import-file").addEventListener("change", (e) => {
    e.preventDefault();
    document.querySelector("#import-form").submit();
  })
</script>