<?php
$importErrors = $_SESSION['import_errors'] ?? null;
unset($_SESSION['import_errors']);

$importErrorMessage = '';
if (!empty($importErrors)) {
  foreach ($importErrors as $rowErrors) {
    foreach ($rowErrors as $error) {
      $importErrorMessage .= (string) $error . "\n";
    }
  }
}
?>

<div class="detail-panel card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>Import from file</h6>
    </div>
    <div class="card__description">
      Upload your .xlsx file to automatically add data.
    </div>
  </div>
  <div class="card__content">
    <form id="import-form" action="<?= url('admin/students/import') ?>" method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="field-group">
        <div class="field">
          <input id="import-file" class="field__input" type="file" name="uploaded_file" accept=".xlsx">
        </div>
      </div>
    </form>
  </div>
  <div class="card__footer"></div>
</div>

<?php $layout->start("scripts") ?>
<?php if (!empty($importErrors)): ?>
  <?php $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT; ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.toast?.error(
        <?= json_encode(count($importErrors) . ' dòng có lỗi, vui lòng kiểm tra lại.', $jsonFlags) ?>,
        <?= json_encode(trim($importErrorMessage), $jsonFlags) ?>
      );
    });
  </script>
<?php endif; ?>
<script src="<?= url('public/js/pages/admin/students/import.js') ?>" type="module"></script>
<?php $layout->end() ?>
