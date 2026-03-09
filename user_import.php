<?php
include __DIR__ . '/services/mock_education_service.php';
use App\Services\MockEducationService;

$mockService = new MockEducationService();

$user_id = $_GET['id'] ?? null;

$student = $mockService->getStudentById((int) $user_id);
$classrooms = $mockService->getAllClassrooms();
ob_start();
?>
<div class="detail-panel card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>
        Import from file
      </h6>
    </div>
    <div class="card__description">
      Upload your .csv file to automatically add data.
    </div>
  </div>
  <div class="card__content">
    <form action="routes/users/import.php" method="POST" enctype="multipart/form-data">
      <div class="field-group">
        <div class="field" data-field-auto-submit>
          <input id="file" class="field__input" type="file" name="uploaded_file" accept=".csv">
        </div>
      </div>
    </form>
  </div>
  <div class="card__footer">
  </div>
</div>
<?php
$content = ob_get_clean();

require 'templates/layouts/dashboard_layout.php';
?>