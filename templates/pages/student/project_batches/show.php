<?php

/**
 * View: Đồ án tốt nghiệp sinh viên
 * Route: /student/project_batches
 */
$student = $student ?? null;
$currentBatch = $currentBatch ?? null;
$isEligible = $isEligible ?? false;
$ineligibilityReason = $ineligibilityReason ?? '';
$group = $group ?? null;
$groupMembers = $groupMembers ?? [];

$isLeader = false;
$isConfirmed = false;
$leaderPhone = '';
$leaderName = '';
if ($group) {
  foreach ($groupMembers as $member) {
    if ($member['student_id'] == $student->id) {
      $isLeader = (bool)$member['is_leader'];
      $isConfirmed = (bool)$member['is_confirmed'];
    }
    if ($member['is_leader']) {
      $leaderPhone = $member['phone'] ?? 'Chưa cập nhật';
      $leaderName = $member['full_name'];
    }
  }
}
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">Đồ án tốt nghiệp</h2>
<p class="title-wrapper__description">Theo dõi tiến độ, quản lý nhóm và đăng ký nguyện vọng đề tài.</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('student/project_batches') ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
  Danh sách đợt
</a>
<?php $layout->end() ?>

<div class="grid grid-cols-1 gap-6">
  <?php if (!$currentBatch): ?>
    <div class="card shadow py-12 text-center">
      <div class="card__content flex flex-col items-center">
        <div class="text-5xl mb-6">
          <i class="fa-solid fa-folder-open"></i>
        </div>
        <h2 class="text-xl font-semibold mb-2">Không có đợt đồ án nào</h2>
        <p class="mx-auto">
          Hiện tại chưa có đợt đồ án tốt nghiệp nào đang mở.
          Vui lòng quay lại sau.
        </p>
      </div>
    </div>
  <?php elseif (!$isEligible): ?>
    <div class="alert" data-variant="error">
      <i class="fa-solid fa-ban"></i>
      <div class="alert-content">
        <h3 class="alert-title">Không đủ điều kiện tham gia</h3>
        <p class="alert-description">
          <?= htmlspecialchars($ineligibilityReason) ?><br><br>
          Nếu bạn cho rằng đây là sự nhầm lẫn, vui lòng liên hệ giáo vụ khoa.
        </p>
      </div>
    </div>
  <?php else: ?>
    <!-- Thông tin đợt đồ án -->
    <div class="card shadow">
      <div class="card__content">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-xl font-bold"><?= htmlspecialchars($currentBatch['title']) ?></h2>
            <?php if (!empty($currentBatch['description'])): ?>
              <p class="mt-1"><?= nl2br(htmlspecialchars($currentBatch['description'])) ?></p>
            <?php endif; ?>
          </div>
          <span class="badge" data-variant="primary">
            Đang diễn ra
          </span>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-calendar-check text-xl"></i>
            </div>
            <div>
              <p class="text-sm font-medium">Đăng ký nguyện vọng</p>
              <p class="text-sm mt-1">
                <?= $currentBatch['registration_start'] ? date('d/m/Y', strtotime($currentBatch['registration_start'])) : 'Chưa có' ?>
                -
                <?= $currentBatch['registration_end'] ? date('d/m/Y', strtotime($currentBatch['registration_end'])) : 'Chưa có' ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Nhóm & Nguyện vọng -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div>
        <div class="card shadow">
          <div class="card__header">
            <h3 class="text-lg font-semibold">Nhóm của tôi</h3>
          </div>
          <hr class="separator">
          <div class="card__content">

            <?php if (!$group): ?>
              <!-- Form tạo nhóm -->
              <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/create") ?>" method="POST" class="mt-4">
                <?= csrf_field() ?>
                <p class="text-sm mb-3">Nhập MSSV của bạn cùng nhóm để gửi lời mời (Nhóm 2 người).</p>
                <div class="field">
                  <input type="text" name="partner_mssv" class="field__input" placeholder="MSSV thành viên" data-field-required>
                  <button type="submit" class="btn" data-size="md" data-variant="primary">Mời & Tạo nhóm</button>
                </div>
              </form>

            <?php elseif (!$isLeader && !$isConfirmed): ?>
              <!-- Lời mời vào nhóm -->
              <div class="alert" data-variant="info">
                <i class="fa-solid fa-envelope-open-text"></i>
                <div class="alert-content">
                  <h3 class="alert-title">Lời mời vào nhóm</h3>
                  <p class="alert-description">
                    Bạn được mời tham gia nhóm đồ án bởi <strong><?= htmlspecialchars($leaderName) ?></strong>.<br>
                    Liên hệ: <strong><?= htmlspecialchars($leaderPhone) ?></strong><br><br>
                    <em>Lưu ý: Bạn phải đồng ý thì nhóm mới được duyệt.</em>
                  </p>
                  <div class="mt-3 flex gap-2">
                    <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/confirm") ?>" method="POST">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn" data-variant="primary" data-size="sm">Đồng ý</button>
                    </form>
                    <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/reject") ?>" method="POST">
                      <?= csrf_field() ?>
                      <button type="button" class="btn btn-confirm-action" data-variant="destructive" data-size="sm" data-confirm-msg="Bạn có chắc chắn muốn từ chối tham gia nhóm này?" data-modal-trigger="#action-confirm-modal">Từ chối</button>
                    </form>
                  </div>
                </div>
              </div>

            <?php else: ?>
              <!-- Danh sách thành viên nhóm -->
              <div class="space-y-3">
                <?php foreach ($groupMembers as $member): ?>
                  <div class="flex justify-between items-center p-3 border rounded-md">
                    <div>
                      <div class="font-medium">
                        <?= htmlspecialchars($member['student_code']) ?> - <?= htmlspecialchars($member['full_name']) ?>
                        <?php if ($member['is_leader']): ?>
                          <span class="badge" data-variant="primary">Nhóm trưởng</span>
                        <?php endif; ?>
                      </div>
                      <div class="text-sm text-gray-500 mt-1">
                        SĐT: <?= htmlspecialchars($member['phone'] ?? 'N/A') ?> | Lớp: <?= htmlspecialchars($member['classroom_name'] ?? 'N/A') ?>
                      </div>
                    </div>
                    <div>
                      <?php if ($member['is_confirmed']): ?>
                        <span class="badge" data-variant="success">Đã xác nhận</span>
                      <?php else: ?>
                        <span class="badge" data-variant="warning">Chờ xác nhận</span>
                        <?php if ($isLeader): ?>
                          <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/cancel") ?>" method="POST" class="inline-block ml-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="student_id" value="<?= $member['student_id'] ?>">
                            <button type="button" class="btn btn-confirm-action" data-variant="destructive" data-size="sm" title="Hủy lời mời / Xóa" data-confirm-msg="Bạn có chắc chắn muốn xóa thành viên này?" data-modal-trigger="#action-confirm-modal">
                              <i class="fa-solid fa-xmark"></i>
                            </button>
                          </form>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <?php
              $maxStudents = $currentBatch['max_students'] ?? 2;
              if ($isLeader && count($groupMembers) < $maxStudents):
              ?>
                <div class="alert mt-4" data-variant="warning">
                  <i class="fa-solid fa-triangle-exclamation"></i>
                  <div class="alert-content">
                    <h3 class="alert-title">Thiếu thành viên</h3>
                    <p class="alert-description">Nhóm của bạn hiện tại chưa đủ số lượng thành viên (<?= count($groupMembers) ?>/<?= $maxStudents ?>). Vui lòng mời thêm thành viên để nhóm hợp lệ.</p>
                  </div>
                </div>
                <form action="<?= url("student/project_batches/{$currentBatch['id']}/group/create") ?>" method="POST" class="mt-4 border-t pt-4">
                  <?= csrf_field() ?>
                  <p class="text-sm mb-3">Nhập MSSV của sinh viên khác để gửi lời mời.</p>
                  <div class="field">
                    <input type="text" name="partner_mssv" class="field__input" placeholder="MSSV thành viên" data-field-required>
                    <button type="submit" class="btn" data-size="md" data-variant="primary">Gửi lời mời</button>
                  </div>
                </form>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div>
        <div class="card shadow">
          <div class="card__header">
            <h3 class="text-lg font-semibold">Nguyện vọng đề tài</h3>
          </div>
          <hr class="separator">
          <div class="card__content">

            <?php if (!$group): ?>
              <div class="py-8 text-center">
                <i class="fa-solid fa-list-check text-3xl mb-3"></i>
                <p>Bạn cần tham gia nhóm trước khi đăng ký NV.</p>
              </div>
            <?php elseif (empty($aspirations)): ?>
              <div class="py-8 text-center text-gray-500">
                <i class="fa-solid fa-file-circle-question text-3xl mb-3"></i>
                <p>Nhóm chưa đăng ký nguyện vọng nào.</p>
                <?php if ($isLeader): ?>
                  <a href="<?= url("student/project_batches/{$currentBatch['id']}/topics") ?>" class="btn" data-size="sm" data-variant="primary">Đăng ký ngay</a>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="space-y-3">
                <?php foreach ($aspirations as $index => $aspiration): ?>
                  <div class="flex justify-between items-center p-3 border rounded-md">
                    <div class="flex-1">
                      <div class="font-medium">
                        <span class="mr-2 font-bold">#<?= $index + 1 ?></span>
                        <?= htmlspecialchars($aspiration['topic_title']) ?>
                      </div>
                      <div class="text-sm mt-1">
                        GV: <?= htmlspecialchars($aspiration['teacher_name']) ?>
                      </div>
                    </div>
                    <?php if ($isLeader): ?>
                      <form action="<?= url("student/project_batches/{$currentBatch['id']}/aspirations/remove") ?>" method="POST" class="ml-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="topic_id" value="<?= $aspiration['topic_id'] ?>">
                        <button type="button" class="btn btn-confirm-action" data-variant="destructive" data-size="sm" title="Xóa nguyện vọng" data-confirm-msg="Bạn có chắc chắn muốn xóa nguyện vọng này?" data-modal-trigger="#action-confirm-modal">
                          <i class="fa-solid fa-trash-can"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
              <?php if ($isLeader && count($aspirations) > 1): ?>
                <div class="mt-4 text-xs">
                  * Thứ tự ưu tiên tính từ trên xuống dưới (NV 1 cao nhất). Để thay đổi, vui lòng xóa và thêm lại theo đúng thứ tự bạn muốn.
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <div class="card__footer">
            <?php if ($group): ?>
              <a href="<?= url("student/project_batches/{$currentBatch['id']}/topics") ?>" class="btn" data-variant="secondary" data-size="md">Xem danh sách tất cả đề tài</a>
            <?php else: ?>
              <button class="btn" data-variant="secondary" data-size="md" disabled>Thêm NV</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Modal xác nhận thao tác -->
<div class="modal" id="action-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận thao tác</h3>
    <p class="modal__description" id="action-confirm-msg">
      Bạn có chắc chắn muốn thực hiện thao tác này?
    </p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="md" class="btn" type="button">Hủy</button>
    <button id="action-confirm-btn" data-variant="primary" data-size="md" class="btn" type="button">Chắc chắn</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<?php $layout->start("scripts") ?>
<script>
  let currentForm = null;

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-confirm-action');
    if (btn) {
      currentForm = btn.closest('form');
      const msg = btn.getAttribute('data-confirm-msg') || 'Bạn có chắc chắn muốn thực hiện thao tác này?';

      const confirmMsg = document.getElementById('action-confirm-msg');
      if (confirmMsg) confirmMsg.textContent = msg;
    }
  });

  const confirmBtn = document.getElementById('action-confirm-btn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
      if (currentForm) {
        currentForm.submit();
      }
    });
  }
</script>
<?php $layout->end() ?>