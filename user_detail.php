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
        User
        <span class="font-bold">#<?= htmlspecialchars($student->account_id ?? '') ?></span>
      </h6>
    </div>
    <div class="card__description">
      This is user detail form
    </div>
  </div>
  <div class="card__content">
    <div class="field-group">
      <div class="field" data-field-disabled data-field-readonly>
        <label for="student_id">Student ID</label>
        <input id="student_id" class="field__input" type="text" name="student_id"
          value="<?= htmlspecialchars($student->student_id ?? '') ?>">
      </div>

      <div class="field" data-field-readonly>
        <label for="fullname">Full Name</label>
        <input id="fullname" class="field__input" type="text" name="fullname"
          value="<?= htmlspecialchars($student->fullname ?? '') ?>">
      </div>

      <div class="field" data-field-readonly>
        <label for="gender">Gender</label>
        <select id="gender" class="field__input" name="gender">
          <option value="Male" <?= ($student?->gender == 'Nam') ? 'selected' : '' ?>>Nam</option>
          <option value="Female" <?= ($student?->gender == 'Nữ') ? 'selected' : '' ?>>Nữ</option>
          <option value="Other" <?= ($student?->gender == 'Khác') ? 'selected' : '' ?>>Khác</option>
        </select>
      </div>

      <div class="field" data-field-readonly>
        <label for="dob">Date of Birth</label>
        <input id="dob" class="field__input" type="date" name="dob"
          value="<?= htmlspecialchars($student->dob ?? '') ?>">
      </div>

      <div class="field" data-field-readonly>
        <label for="phone">Phone</label>
        <input id="phone" class="field__input" type="text" name="phone"
          value="<?= htmlspecialchars($student->phone ?? '') ?>">
      </div>

      <div class="field" data-field-readonly>
        <label for="class_id">Classroom</label>
        <select id="class_id" class="field__input" name="class_id">
          <option value="" disabled hidden <?= is_null($student?->class_id) ? 'selected' : '' ?>>
            -- Chọn lớp học--
          </option>
          <?php foreach ($classrooms as $classroom): ?>
            <option value="<?= $classroom->id ?>" <?= ($student?->class_id == $classroom->id) ? 'selected' : '' ?>>
              <?= htmlspecialchars($classroom->name) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>
  <div class="card__footer">
    <button data-variant="primary" class="w-full btn">Lưu thay đổi</button>
    <button data-variant="destructive" class="w-full btn">Xóa</button>
  </div>
</div>
<?php
$content = ob_get_clean();

require 'includes/dashboard_layout.php';
?>