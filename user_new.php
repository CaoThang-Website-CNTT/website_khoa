<?php
include __DIR__ . '/services/mock_education_service.php';
use App\Services\MockEducationService;

$mockService = new MockEducationService();

$classrooms = $mockService->getAllClassrooms();
ob_start();
?>
<div class="detail-panel card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>Create new user</h6>
    </div>
    <div class="card__description">
      This is creating new user form
    </div>
  </div>
  <div class="card__content">
    <form id="user-add-form" method="POST" action="users/add.php">
      <div class="field-group">
        <div class="field">
          <label for="student_id">Student ID</label>
          <input id="student_id" class="field__input" type="text" name="student_id" value="">
        </div>

        <div class="field">
          <label for="fullname">Full Name</label>
          <input id="fullname" class="field__input" type="text" name="fullname" value="">
        </div>

        <div class="field">
          <label for="gender">Gender</label>
          <select id="gender" class="field__input" name="gender">
            <option value="">Nam</option>
            <option value="">Nữ</option>
            <option value="">Khác</option>
          </select>
        </div>

        <div class="field">
          <label for="dob">Date of Birth</label>
          <input id="dob" class="field__input" type="date" name="dob" value="">
        </div>

        <div class="field">
          <label for="phone">Phone</label>
          <input id="phone" class="field__input" type="text" name="phone" value="">
        </div>

        <div class="field">
          <label for="class_id">Classroom</label>
          <select id="class_id" class="field__input" name="class_id">
            <option value="" selected>
              -- Chọn lớp học--
            </option>
            <?php foreach ($classrooms as $classroom): ?>
              <option value=<?php htmlspecialchars($classroom->name); ?>>
                <?= htmlspecialchars($classroom->name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </form>
  </div>
  <div class="card__footer">
    <button type="submit" variant="primary" class="w-full btn">Thêm</button>
  </div>
</div>
<?php
$content = ob_get_clean();

require 'includes/dashboard_layout.php';
?>